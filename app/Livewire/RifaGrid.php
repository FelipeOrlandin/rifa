<?php

namespace App\Livewire;

use App\Models\Rifa;
use App\Services\RifaService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class RifaGrid extends Component
{
    use WithPagination;

    public Rifa $rifa;
    public array $selectedNumbers = [];
    public array $availableNumbers = [];
    public array $soldNumbers = [];
    public array $reservedNumbers = [];
    
    // Dados do formulário de compra
    public string $buyerName = '';
    public string $buyerEmail = '';
    public string $buyerPhone = '';
    
    // Estado do checkout
    public bool $showCheckoutForm = false;
    public bool $showPixModal = false;
    public array $pixData = [];
    public int $reservationMinutes = 15;
    
    // Total selecionado
    public float $totalSelecionado = 0;

    // Paginação
    public int $perPage = 50;

    protected RifaService $rifaService;

    public function boot(RifaService $rifaService): void
    {
        $this->rifaService = $rifaService;
    }

    public function mount(Rifa $rifa): void
    {
        $this->rifa = $rifa;
        $this->loadNumbers();
    }

    public function updatedSelectedNumbers(): void
    {
        $this->totalSelecionado = count($this->selectedNumbers) * $this->rifa->number_price;
    }

    public function loadNumbers(): void
    {
        $this->rifaService->limparReservasExpiradas($this->rifa);
        
        $allNumbers = $this->rifa->numbers()->pluck('number', 'status')->toArray();
        
        $this->availableNumbers = $allNumbers['available'] ?? [];
        $this->soldNumbers = $allNumbers['paid'] ?? [];
        $this->reservedNumbers = $allNumbers['reserved'] ?? [];
    }

    public function toggleNumber(int $number): void
    {
        if (!in_array($number, $this->availableNumbers)) {
            return;
        }

        if (in_array($number, $this->selectedNumbers)) {
            $this->selectedNumbers = array_diff($this->selectedNumbers, [$number]);
        } else {
            $this->selectedNumbers[] = $number;
        }
        
        $this->selectedNumbers = array_values($this->selectedNumbers);
        $this->updatedSelectedNumbers();
    }

    public function abrirFormularioCheckout(): void
    {
        if (empty($this->selectedNumbers)) {
            return;
        }

        $this->showCheckoutForm = true;
        $this->showPixModal = false;
    }

    public function fecharFormularioCheckout(): void
    {
        $this->showCheckoutForm = false;
    }

    public function processarPix(): void
    {
        $this->validate([
            'buyerName' => 'required|string|min:3|max:255',
            'buyerEmail' => 'required|email|max:255',
            'buyerPhone' => 'required|string|min:10|max:15',
        ]);

        if (!Auth::check()) {
            $this->dispatch('showAuthModal');
            return;
        }

        if (empty($this->selectedNumbers)) {
            return;
        }

        try {
            // Reservar números com TTL
            $result = $this->rifaService->reservarNumeros(
                $this->rifa,
                $this->selectedNumbers,
                Auth::id()
            );

            if (!empty($result['failed'])) {
                $this->dispatch('showError', [
                    'message' => 'Alguns números não puderam ser reservados: ' . implode(', ', $result['failed']),
                ]);
                return;
            }

            // Criar pagamento e gerar PIX
            $paymentResult = $this->rifaService->criarPagamentoPix(
                $this->rifa,
                $result['reserved'],
                [
                    'user_id' => Auth::id(),
                    'name' => $this->buyerName,
                    'email' => $this->buyerEmail,
                    'phone' => $this->buyerPhone,
                ]
            );

            $this->pixData = $paymentResult;
            $this->showCheckoutForm = false;
            $this->showPixModal = true;
            $this->selectedNumbers = [];
            
            $this->dispatch('reservationComplete');
            
        } catch (\Exception $e) {
            $this->dispatch('showError', [
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage(),
            ]);
        }
    }

    public function fecharPixModal(): void
    {
        $this->showPixModal = false;
        $this->pixData = [];
        $this->loadNumbers();
    }

    public function getNumbersPaginator()
    {
        return $this->rifa->numbers()
            ->orderBy('number')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $numbersPaginator = $this->getNumbersPaginator();

        return view('livewire.rifa-grid', [
            'numbersPaginator' => $numbersPaginator,
        ]);
    }
}
