@extends('admin.layouts.app')

@section('title', "Sorteio - {$rifa->title}")

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.rifas.show', $rifa) }}" 
               class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Sorteio da Rifa</h1>
                <p class="text-gray-600 mt-1">{{ $rifa->title }}</p>
            </div>
        </div>
    </div>

    @if($winner)
        {{-- Ganhador Encontrado --}}
        <div class="bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl shadow-lg p-8 text-white text-center">
            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
            <h2 class="text-3xl font-bold mb-2">Ganhador Definido!</h2>
            <p class="text-xl mb-4">
                <span class="font-bold">{{ $winner->user->name ?? 'Usuário' }}</span>
                com o número
                <span class="font-bold text-2xl">{{ str_pad($winner->winning_number, 3, '0', STR_PAD_LEFT) }}</span>
            </p>
            <p class="text-white/80">
                @if($winner->is_manual)
                    Sorteio manual realizado em {{ $winner->sorteado_em?->format('d/m/Y H:i') }}
                @else
                    Sorteio aleatório realizado em {{ $winner->sorteado_em?->format('d/m/Y H:i') }}
                @endif
            </p>
            @if($winner->notes)
                <p class="mt-4 text-white/80 italic">"{{ $winner->notes }}"</p>
            @endif
        </div>
    @else
        {{-- Ainda não sorteado --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Sorteio Aleatório --}}
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        Sorteio Aleatório
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-6">
                        O sistema sorteará aleatoriamente um número entre todos os pagos.
                    </p>
                    <div class="bg-purple-50 rounded-xl p-4 mb-6">
                        <p class="text-sm text-purple-700">
                            <strong>{{ $paidNumbers->count() }}</strong> números pagos disponíveis para sorteio
                        </p>
                    </div>
                    <form action="{{ route('admin.sorteio.sortear', $rifa) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja realizar o sorteio? Esta ação não pode ser desfeita.')">
                        @csrf
                        {{-- Aviso de data --}}
                        @if($rifa->draw_date->isFuture())
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                                <p class="text-sm text-amber-700">
                                    <strong>Atenção:</strong> A data do sorteio ({{ $rifa->draw_date->format('d/m/Y') }}) ainda não chegou.
                                </p>
                                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                    <input type="checkbox" 
                                           name="ignore_date" 
                                           value="1"
                                           class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                    <span class="text-sm text-amber-700 font-medium">Ignorar data e sortear agora</span>
                                </label>
                            </div>
                        @endif
                        <button type="submit" 
                                class="w-full px-6 py-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all font-semibold text-lg shadow-lg shadow-purple-500/30 flex items-center justify-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                            Sortear Agora
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sorteio Manual --}}
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Escolher Manualmente
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-6">
                        Digite o número do ganhador. O número deve estar com status "pago".
                    </p>
                    <form action="{{ route('admin.sorteio.definir-ganhador', $rifa) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja definir este ganhador? Esta ação não pode ser desfeita.')">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número do Ganhador *</label>
                                <input type="number" 
                                       name="number" 
                                       min="1" 
                                       max="{{ $rifa->total_numbers }}"
                                       required
                                       placeholder="Ex: 42"
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                                <textarea name="notes" 
                                          rows="3"
                                          placeholder="Motivo do sorteio manual..."
                                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
                            </div>
                            {{-- Aviso de data --}}
                            @if($rifa->draw_date->isFuture())
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                    <p class="text-sm text-amber-700">
                                        <strong>Atenção:</strong> A data do sorteio ({{ $rifa->draw_date->format('d/m/Y') }}) ainda não chegou.
                                    </p>
                                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="ignore_date" 
                                               value="1"
                                               class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                        <span class="text-sm text-amber-700 font-medium">Ignorar data e sortear agora</span>
                                    </label>
                                </div>
                            @endif
                            <button type="submit" 
                                    class="w-full px-6 py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all font-semibold text-lg shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Definir Ganhador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Números Pagos --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Números Pagos</h2>
            </div>
            <div class="p-6">
                @if($paidNumbers->count() > 0)
                    <div class="grid grid-cols-5 sm:grid-cols-10 md:grid-cols-15 lg:grid-cols-20 gap-2">
                        @foreach($paidNumbers as $number)
                            <div class="aspect-square rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                {{ str_pad($number->number, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Nenhum número pago ainda</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
