@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-600 mt-1">Visão geral do sistema</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Rifas --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total de Rifas</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_rifas'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="text-emerald-600 font-medium">{{ $stats['active_rifas'] }} ativas</span>
                <span class="text-gray-400">|</span>
                <span class="text-gray-500">{{ $stats['closed_rifas'] }} encerradas</span>
            </div>
        </div>

        {{-- Números Vendidos --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Números Vendidos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['sold_numbers'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="text-amber-600 font-medium">{{ $stats['reserved_numbers'] }} reservados</span>
                <span class="text-gray-400">|</span>
                <span class="text-gray-500">{{ $stats['available_numbers'] }} disponíveis</span>
            </div>
        </div>

        {{-- Faturamento --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Faturamento Total</p>
                    <p class="text-3xl font-bold text-emerald-600">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm">
                <span class="text-emerald-600 font-medium">{{ $stats['completed_payments'] }} pagamentos</span>
                <span class="text-gray-400">|</span>
                <span class="text-amber-600">{{ $stats['pending_payments'] }} pendentes</span>
            </div>
        </div>

        {{-- Usuários --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total de Usuários</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pagamentos Recentes --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Pagamentos Recentes</h2>
            </div>
            <div class="p-6">
                @forelse($recentPayments as $payment)
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-sm font-semibold">
                                {{ substr($payment->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $payment->user->name ?? 'Usuário removido' }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->rifa->title ?? 'Rifa removida' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-emerald-600">R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">Nenhum pagamento registrado</p>
                @endforelse
            </div>
        </div>

        {{-- Rifas Recentes --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Rifas Recentes</h2>
            </div>
            <div class="p-6">
                @forelse($recentRifas as $rifa)
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center">
                                @if($rifa->prize_image)
                                    <img src="{{ asset('storage/' . $rifa->prize_image) }}" class="w-full h-full object-cover rounded-xl" alt="">
                                @else
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $rifa->title }}</p>
                                <p class="text-xs text-gray-500">R$ {{ number_format($rifa->number_price, 2, ',', '.') }} por número</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                {{ $rifa->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 
                                   ($rifa->status === 'closed' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $rifa->status === 'active' ? 'Ativa' : ($rifa->status === 'closed' ? 'Encerrada' : 'Rascunho') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">Nenhuma rifa criada</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
