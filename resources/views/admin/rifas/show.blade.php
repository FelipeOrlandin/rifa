@extends('admin.layouts.app')

@section('title', $rifa->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.rifas.index') }}" 
               class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $rifa->title }}</h1>
                <p class="text-gray-600 mt-1">{{ $rifa->description ?? 'Sem descrição' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.rifas.numbers', $rifa) }}" 
               class="px-4 py-2 bg-amber-100 text-amber-700 rounded-xl hover:bg-amber-200 transition-colors font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                Números
            </a>
            @if(in_array($rifa->status, ['closed', 'drawn']))
                <a href="{{ route('admin.sorteio.index', $rifa) }}" 
                   class="px-4 py-2 bg-purple-100 text-purple-700 rounded-xl hover:bg-purple-200 transition-colors font-medium flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    Sorteio
                </a>
            @endif
            <a href="{{ route('admin.rifas.edit', $rifa) }}" 
               class="px-4 py-2 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition-colors font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500">Total</p>
        </div>
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['available'] }}</p>
            <p class="text-sm text-gray-500">Disponíveis</p>
        </div>
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['reserved'] }}</p>
            <p class="text-sm text-gray-500">Reservados</p>
        </div>
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['paid'] }}</p>
            <p class="text-sm text-gray-500">Vendidos</p>
        </div>
    </div>

    {{-- Progress --}}
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Progresso de Vendas</h2>
        <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-500"
                 style="width: {{ $stats['total'] > 0 ? (($stats['paid'] / $stats['total']) * 100) : 0 }}%"></div>
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-500">
            <span>{{ $stats['paid'] }} vendidos</span>
            <span>{{ $stats['total'] }} total</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Info da Rifa --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Informações</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Preço por número</span>
                    <span class="font-semibold text-emerald-600">R$ {{ number_format($rifa->number_price, 2, ',', '.') }}</span>
                </div>
                @if($rifa->prize_value)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valor do prêmio</span>
                        <span class="font-semibold text-gray-800">R$ {{ number_format($rifa->prize_value, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Data do sorteio</span>
                    <span class="font-semibold text-gray-800">{{ $rifa->draw_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $rifa->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 
                           ($rifa->status === 'closed' ? 'bg-amber-100 text-amber-700' : 
                           ($rifa->status === 'drawn' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                        {{ $rifa->status === 'active' ? 'Ativa' : 
                           ($rifa->status === 'closed' ? 'Encerrada' : 
                           ($rifa->status === 'drawn' ? 'Finalizada' : 'Rascunho')) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Criada em</span>
                    <span class="text-gray-800">{{ $rifa->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Imagem --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Imagem do Prêmio</h2>
            </div>
            <div class="p-6">
                @if($rifa->prize_image)
                    <div class="aspect-video rounded-xl overflow-hidden bg-gray-100">
                        <img src="{{ asset('storage/' . $rifa->prize_image) }}" 
                             class="w-full h-full object-cover" 
                             alt="{{ $rifa->title }}">
                    </div>
                @else
                    <div class="aspect-video rounded-xl bg-gray-100 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-500">Sem imagem</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ganhador (se houver) --}}
    @if($rifa->winner)
        <div class="bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Ganhador: {{ $rifa->winner->user->name ?? 'N/A' }}</h3>
                    <p class="text-white/80">Número: {{ str_pad($rifa->winner->winning_number, 3, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-white/80 text-sm">Sorteado em: {{ $rifa->winner->sorteado_em?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
