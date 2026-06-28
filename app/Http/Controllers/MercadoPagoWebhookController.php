<?php

namespace App\Http\Controllers;

use App\Services\RifaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MercadoPagoWebhookController extends Controller
{
    private const MP_WEBHOOK_SECRET = 'YOUR_WEBHOOK_SECRET_KEY';

    public function __construct(
        private RifaService $rifaService
    ) {}

    /**
     * Processar webhook de pagamento do Mercado Pago
     * POST /webhook/mercadopago
     */
    public function handle(Request $request)
    {
        try {
            // Validar assinatura do webhook
            if (!$this->validarAssinatura($request)) {
                Log::warning('Webhook MP - Assinatura inválida', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all(),
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->all();
            
            Log::info('Webhook MP recebido', ['data' => $data]);

            // Validar tipo de notificação
            $type = $data['type'] ?? null;
            $action = $data['action'] ?? null;

            if ($type !== 'payment') {
                return response()->json(['status' => 'ignored'], 200);
            }

            // Validar ID da requisição para idempotência
            $requestId = $request->header('x-request-id');
            if ($requestId) {
                $cacheKey = "webhook_mp_{$requestId}";
                if (Cache::has($cacheKey)) {
                    Log::info('Webhook MP - Requisição duplicada', ['request_id' => $requestId]);
                    return response()->json(['status' => 'already_processed'], 200);
                }
                // Marcar como processado por 24 horas
                Cache::put($cacheKey, true, 86400);
            }

            // Processar pagamento
            $result = $this->rifaService->processarWebhookPagamento($data);

            if ($result) {
                Log::info('Pagamento MP processado com sucesso', [
                    'data' => $data,
                    'request_id' => $requestId,
                ]);
                return response()->json(['status' => 'processed'], 200);
            }

            return response()->json(['status' => 'pending'], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook MP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Validar assinatura do webhook Mercado Pago
     * Usa HMAC SHA256 com x-signature header
     */
    private function validarAssinatura(Request $request): bool
    {
        $signature = $request->header('x-signature');
        
        if (empty($signature)) {
            return false;
        }

        // Formato esperado: ts=timestamp,v1=hash
        $parts = explode(',', $signature);
        $ts = null;
        $v1 = null;

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 'ts') {
                $ts = $value;
            } elseif ($key === 'v1') {
                $v1 = $value;
            }
        }

        if (empty($ts) || empty($v1)) {
            return false;
        }

        // Verificar se o timestamp não é muito antigo (5 minutos)
        $timestamp = (int) $ts;
        if (abs(time() - $timestamp) > 300) {
            Log::warning('Webhook MP - Timestamp expirado', ['ts' => $timestamp]);
            return false;
        }

        // Calcular hash esperado
        $body = $request->getContent();
        $secret = self::MP_WEBHOOK_SECRET;
        
        $payload = $ts . $body;
        $expectedHash = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedHash, $v1);
    }
}
