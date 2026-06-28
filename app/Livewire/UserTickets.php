<?php

namespace App\Livewire;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class UserTickets extends Component
{
    use WithPagination;

    public function render()
    {
        $payments = Payment::with(['rifa', 'rifa.winner'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        // Agrupar por rifa
        $groupedTickets = $payments->getCollection()->groupBy('rifa_id');

        return view('livewire.user-tickets', [
            'payments' => $payments,
            'groupedTickets' => $groupedTickets,
        ]);
    }
}
