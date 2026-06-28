<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\RifaNumber;
use App\Models\Winner;
use App\Models\Payment;
use App\Models\User;
use App\Mail\PaymentConfirmed;
use App\Mail\WinnerAnnounced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SorteioController extends Controller
{
    /**
     * Exibir página de sorteio de uma rifa
     */
    public function index(Rifa $rifa)
    {
        // Verificar se a rifa está encerrada ou finalizada
        if (!in_array($rifa->status, ['closed', 'drawn'])) {
            return redirect()->route('admin.rifas.show', $rifa)
                ->with('error', 'A rifa precisa estar encerrada para realizar o sorteio.');
        }

        // Verificar se já existe ganhador
        $winner = Winner::where('rifa_id', $rifa->id)->first();

        $paidNumbers = $rifa->numbers()
            ->where('status', 'paid')
            ->orderBy('number')
            ->get();

        return view('admin.sorteio.index', compact('rifa', 'winner', 'paidNumbers'));
    }

    /**
     * Realizar sorteio aleatório
     */
    public function sortear(Request $request, Rifa $rifa)
    {
        // Verificar se a rifa está encerrada
        if ($rifa->status !== 'closed') {
            return back()->with('error', 'A rifa precisa estar encerrada para realizar o sorteio.');
        }

        // Verificar se já existe ganhador
        if (Winner::where('rifa_id', $rifa->id)->exists()) {
            return back()->with('error', 'Esta rifa já possui um ganhador.');
        }

        // Validação de data (a menos que "ignorar data" esteja marcado)
        $ignoreDate = $request->boolean('ignore_date');
        if (!$ignoreDate && $rifa->draw_date->isFuture()) {
            return back()->with('error', 'A data do sorteio ainda não chegou. Aguarde ou marque "Ignorar data" para sortear antes do prazo.');
        }

        try {
            $winner = $this->rifaService->drawWinner($rifa);
            
            // Disparar emails
            $this->enviarEmailsSorteio($rifa, $winner);
            
            Cache::forget("rifa_{$rifa->slug}");
            Cache::forget('admin_dashboard_stats');

            return redirect()->route('admin.sorteio.index', $rifa)
                ->with('success', 'Sorteio realizado com sucesso!');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao realizar sorteio: ' . $e->getMessage());
        }
    }

    /**
     * Definir ganhador manualmente
     */
    public function definirGanhador(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar se a rifa está encerrada
        if ($rifa->status !== 'closed') {
            return back()->with('error', 'A rifa precisa estar encerrada para definir ganhador.');
        }

        // Verificar se já existe ganhador
        if (Winner::where('rifa_id', $rifa->id)->exists()) {
            return back()->with('error', 'Esta rifa já possui um ganhador.');
        }

        // Validação de data
        $ignoreDate = $request->boolean('ignore_date');
        if (!$ignoreDate && $rifa->draw_date->isFuture()) {
            return back()->with('error', 'A data do sorteio ainda não chegou. Aguarde ou marque "Ignorar data" para sortear antes do prazo.');
        }

        try {
            $winner = $this->rifaService->setManualWinner(
                $rifa, 
                $validated['number'], 
                $validated['notes'] ?? null
            );
            
            // Disparar emails
            $this->enviarEmailsSorteio($rifa, $winner);
            
            Cache::forget("rifa_{$rifa->slug}");
            Cache::forget('admin_dashboard_stats');

            return redirect()->route('admin.sorteio.index', $rifa)
                ->with('success', "Ganhador definido: Número {$validated['number']}");
                
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao definir ganhador: ' . $e->getMessage());
        }
    }

    /**
     * Enviar emails de notificação de sorteio
     */
    private function enviarEmailsSorteio(Rifa $rifa, Winner $winner): void
    {
        try {
            // Buscar todos os compradores pagos desta rifa
            $paidUserIds = RifaNumber::where('rifa_id', $rifa->id)
                ->where('status', 'paid')
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique();

            foreach ($paidUserIds as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                $isWinner = $winner->user_id == $userId;
                
                Mail::to($user->email)->send(new WinnerAnnounced(
                    $winner,
                    $user,
                    $isWinner
                ));
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar emails de sorteio', [
                'rifa_id' => $rifa->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verificar números pagos disponíveis para sorteio
     */
    public function verificarNumeros(Rifa $rifa)
    {
        $paidNumbers = $rifa->numbers()
            ->where('status', 'paid')
            ->with('user')
            ->orderBy('number')
            ->get();

        return response()->json([
            'numbers' => $paidNumbers->map(fn($n) => [
                'id' => $n->id,
                'number' => $n->number,
                'user' => $n->user->name ?? 'N/A',
                'email' => $n->user->email ?? 'N/A',
            ]),
        ]);
    }
}
