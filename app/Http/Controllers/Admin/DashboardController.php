<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\Payment;
use App\Models\RifaNumber;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'total_rifas' => Rifa::count(),
                'active_rifas' => Rifa::where('status', 'active')->count(),
                'closed_rifas' => Rifa::where('status', 'closed')->count(),
                'drawn_rifas' => Rifa::where('status', 'drawn')->count(),
                'total_numbers' => RifaNumber::count(),
                'sold_numbers' => RifaNumber::where('status', 'paid')->count(),
                'reserved_numbers' => RifaNumber::where('status', 'reserved')->count(),
                'available_numbers' => RifaNumber::where('status', 'available')->count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'total_payments' => Payment::count(),
                'completed_payments' => Payment::where('status', 'completed')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'total_users' => User::count(),
            ];
        });

        $recentPayments = Payment::with(['user', 'rifa'])
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        $recentRifas = Rifa::latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPayments', 'recentRifas'));
    }
}
