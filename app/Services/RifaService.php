<?php

namespace App\Services;

use App\Models\Rifa;
use App\Models\RifaNumber;
use App\Models\Payment;
use App\Models\Winner;
use App\Models\User;
use App\Mail\PaymentConfirmed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Exception;

class RifaService
{
    private const RESERVATION_TTL_MINUTES = 15;
    private const MP_ACCESS_TOKEN = 'APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-XXXXXXX-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

    /**
     * Get rifa with caching for shared hosting
     */
    public function getRifaBySlug(string $slug): ?Rifa
    {
        return Cache::remember("rifa_{$slug}", 300, function () use ($slug) {
            return Rifa::where('slug', $slug)
                ->where('status', 'active')
                ->first();
        });
    }

    /**
     * Get available numbers with caching (inclui expirados via scope)
     */
    public function getAvailableNumbers(Rifa $rifa): array
    {
        $cacheKey = "rifa_{$rifa->id}_available_numbers";
        
        return Cache::remember($cacheKey, 60, function () use ($rifa) {
            return $rifa->numbers()
                ->disponiveis()
                ->pluck('number')
                ->toArray();
        });
    }

    /**
     * Reservar números com lockForUpdate e TTL de 15 minutos
     */
    public function reservarNumeros(Rifa $rifa, array $numbers, int $userId): array
    {
        return DB::transaction(function () use ($rifa, $numbers, $userId) {
            $reserved = [];
            $failed = [];
            
            foreach ($numbers as $number) {
                $rifaNumber = RifaNumber::where('rifa_id', $rifa->id)
                    ->where('number', $number)
                    ->lockForUpdate()
                    ->first();
                
                if (!$rifaNumber) {
                    $failed[] = $number;
                    continue;
                }
                
                // Verificar se está disponível ou se a reserva expirou
                $isAvailable = $rifaNumber->status === 'available';
                $isExpired = $rifaNumber->status === 'reserved' 
                    && $rifaNumber->reserved_until !== null 
                    && $rifaNumber->reserved_until->isPast();
                
                if (!$isAvailable && !$isExpired) {
                    $failed[] = $number;
                    continue;
                }
                
                // Reservar com TTL
                $rifaNumber->update([
                    'status' => 'reserved',
                    'user_id' => $userId,
                    'reserved_at' => now(),
                    'reserved_until' => now()->addMinutes(self::RESERVATION_TTL_MINUTES),
                ]);
                
                $reserved[] = $number;
            }
            
            $this->clearRifaCache($rifa);
            
            return [
                'reserved' => $reserved,
                'failed' => $failed,
                'total' => count($reserved),
            ];
        });
    }

    /**
     * Limpar todas as reservas expiradas de uma rifa
     */
    public function limparReservasExpiradas(Rifa $rifa): int
    {
        return RifaNumber::where('rifa_id', $rifa->id)
            ->where('status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->update([
                'status' => 'available',
                'user_id' => null,
                'reserved_at' => null,
                'reserved_until' => null,
            ]);
    }

    /**
     * Confirmar pagamento e marcar números como paid
     */
    public function confirmPayment(Rifa $rifa, array $numbers, int $userId, string $paymentMethod): bool
    {
        return DB::transaction(function () use ($rifa, $numbers, $userId, $paymentMethod) {
            RifaNumber::where('rifa_id', $rifa->id)
                ->whereIn('number', $numbers)
                ->where('user_id', $userId)
                ->where('status', 'reserved')
                ->update(['status' => 'paid']);
            
            $totalAmount = count($numbers) * $rifa->number_price;
            
            $rifa->payments()->create([
                'user_id' => $userId,
                'amount' => $totalAmount,
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'numbers_purchased' => $numbers,
            ]);
            
            $this->clearRifaCache($rifa);
            
            return true;
        });
    }

    /**
     * Criar pagamento pending e gerar payload PIX via Mercado Pago
     */
    public function criarPagamentoPix(Rifa $rifa, array $numbers, array $buyerData): array
    {
        $totalAmount = count($numbers) * $rifa->number_price;
        
        // Criar registro de pagamento pending
        $payment = Payment::create([
            'user_id' => $buyerData['user_id'] ?? null,
            'rifa_id' => $rifa->id,
            'amount' => $totalAmount,
            'status' => 'pending',
            'payment_method' => 'pix',
            'numbers_purchased' => $numbers,
        ]);

        // Gerar payload PIX via Mercado Pago
        $pixPayload = $this->gerarPayloadPix($totalAmount, $payment->id, $buyerData);
        
        return [
            'payment_id' => $payment->id,
            'amount' => $totalAmount,
            'pix' => $pixPayload,
        ];
    }

    /**
     * Gerar payload PIX via API Mercado Pago
     */
    public function gerarPayloadPix(float $amount, int $paymentId, array $buyerData): array
    {
        $payload = [
            'transaction_amount' => $amount,
            'description' => "Rifa - Pagamento #{$paymentId}",
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $buyerData['email'] ?? '',
                'first_name' => $buyerData['name'] ?? '',
                'last_name' => '',
                'identification' => [
                    'type' => 'CPF',
                    'number' => $buyerData['cpf'] ?? '00000000000',
                ],
                'address' => [
                    'zip_code' => '00000000',
                    'street_name' => 'N/A',
                    'street_number' => 0,
                    'neighborhood' => 'N/A',
                    'city' => 'N/A',
                    'federal_unit' => 'SP',
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . self::MP_ACCESS_TOKEN,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => uniqid('pix_', true),
            ])->post('https://api.mercadopago.com/v1/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Atualizar pagamento com ID externo
                Payment::where('id', $paymentId)->update([
                    'transaction_id' => $data['id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                    'qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                    'ticket_url' => $data['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                    'external_id' => $data['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro na API do Mercado Pago',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao conectar com Mercado Pago: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processar webhook de pagamento do Mercado Pago
     */
    public function processarWebhookPagamento(array $data): bool
    {
        $externalId = $data['data']['id'] ?? null;
        
        if (!$externalId) {
            throw new Exception('ID de pagamento não fornecido');
        }

        // Buscar pagamento pelo transaction_id
        $payment = Payment::where('transaction_id', $externalId)->first();
        
        if (!$payment) {
            throw new Exception("Pagamento #{$externalId} não encontrado");
        }

        // Consultar status atualizado no MP
        $statusMP = $this->consultarPagamentoMP($externalId);
        
        if ($statusMP['status'] === 'approved') {
            DB::transaction(function () use ($payment) {
                // Marcar números como paid
                RifaNumber::where('rifa_id', $payment->rifa_id)
                    ->whereIn('number', $payment->numbers_purchased)
                    ->where('status', 'reserved')
                    ->update(['status' => 'paid']);
                
                // Atualizar pagamento
                $payment->update(['status' => 'completed']);
            });

            // Enviar email de confirmação
            $this->enviarEmailConfirmacaoPagamento($payment);

            $rifa = $payment->rifa;
            $this->clearRifaCache($rifa);
            
            return true;
        }

        return false;
    }

    /**
     * Consultar pagamento no Mercado Pago
     */
    public function consultarPagamentoMP(string $externalId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . self::MP_ACCESS_TOKEN,
            ])->get("https://api.mercadopago.com/v1/payments/{$externalId}");

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'unknown'];
            
        } catch (\Exception $e) {
            return ['status' => 'error'];
        }
    }

    /**
     * Draw a winner using database randomization
     */
    public function drawWinner(Rifa $rifa): ?Winner
    {
        if ($rifa->status !== 'closed') {
            throw new Exception('Rifa must be closed before drawing');
        }
        
        return DB::transaction(function () use ($rifa) {
            $winningNumber = RifaNumber::where('rifa_id', $rifa->id)
                ->where('status', 'paid')
                ->orderByRaw('RAND()')
                ->first();
            
            if (!$winningNumber) {
                throw new Exception('No paid numbers available for drawing');
            }
            
            $winner = Winner::create([
                'rifa_id' => $rifa->id,
                'user_id' => $winningNumber->user_id,
                'rifa_number_id' => $winningNumber->id,
                'winning_number' => $winningNumber->number,
                'is_manual' => false,
            ]);
            
            $rifa->update(['status' => 'drawn']);
            
            $this->clearRifaCache($rifa);
            
            return $winner;
        });
    }

    /**
     * Manually set a winner (admin override)
     */
    public function setManualWinner(Rifa $rifa, int $number, ?string $notes = null): Winner
    {
        return DB::transaction(function () use ($rifa, $number, $notes) {
            $rifaNumber = RifaNumber::where('rifa_id', $rifa->id)
                ->where('number', $number)
                ->where('status', 'paid')
                ->first();
            
            if (!$rifaNumber) {
                throw new Exception("Number {$number} is not paid or does not exist");
            }
            
            $winner = Winner::create([
                'rifa_id' => $rifa->id,
                'user_id' => $rifaNumber->user_id,
                'rifa_number_id' => $rifaNumber->id,
                'winning_number' => $number,
                'is_manual' => true,
                'notes' => $notes,
            ]);
            
            $rifa->update(['status' => 'drawn']);
            
            $this->clearRifaCache($rifa);
            
            return $winner;
        });
    }

    /**
     * Clear all cache related to a rifa
     */
    protected function clearRifaCache(Rifa $rifa): void
    {
        Cache::forget("rifa_{$rifa->slug}");
        Cache::forget("rifa_{$rifa->id}_available_numbers");
        Cache::forget("rifas_active");
    }

    /**
     * Enviar email de confirmação de pagamento
     */
    protected function enviarEmailConfirmacaoPagamento(Payment $payment): void
    {
        try {
            $user = User::find($payment->user_id);
            if (!$user) return;

            Mail::to($user->email)->send(new PaymentConfirmed(
                $payment,
                $user,
                $payment->numbers_purchased ?? [],
                $payment->amount
            ));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de confirmação', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
