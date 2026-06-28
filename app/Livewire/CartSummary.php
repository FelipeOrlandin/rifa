<?php

namespace App\Livewire;

use App\Models\Rifa;
use Livewire\Component;

class CartSummary extends Component
{
    public Rifa $rifa;
    public array $selectedNumbers = [];
    public float $total = 0;

    protected $listeners = ['numbersUpdated' => 'updateSelection'];

    public function mount(Rifa $rifa): void
    {
        $this->rifa = $rifa;
    }

    public function updateSelection(array $data): void
    {
        $this->selectedNumbers = $data['selected'];
        $this->total = $data['total'];
    }

    public function render()
    {
        return view('livewire.cart-summary');
    }
}
