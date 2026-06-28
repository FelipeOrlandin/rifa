{{-- resources/views/livewire/user-tickets.blade.php --}}

<div class="space-y-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Meus Ingressos</h1>
        <p class="text-gray-600 mt-2">Acompanhe suas compras e resultados</p>
    </div>

    @if($payments->count() > 0)
        {{-- Lista de Compras --}}
        <div class="space-y-4">
            @foreach($payments as $payment)
                @php
                    $rifa = $payment->rifa;
                    $numbers = $payment->numbers_purchased ?? [];
                    $hasWinner = $rifa?->winner;
                    $isWinner = $hasWinner && in_array($hasWinner->winning_number, $numbers);
                @endphp

                <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg overflow-hidden
                            {{ $isWinner ? 'ring-2 ring-amber-400 ring-offset-2' : '' }}">
                    
                    {{-- Header do Card --}}
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center overflow-hidden shadow-lg">
                                    @if($rifa?->prize_image)
                                        <img src="{{ asset('storage/' . $rifa->prize_image) }}" 
                                             class="w-full h-full object-cover" 
                                             alt="{{ $rifa->title }}">
                                    @else
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $rifa?->title ?? 'Rifa removida' }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Comprado em {{ $payment->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-2xl font-bold text-emerald-600">
                                    R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                </p>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $payment->status === 'completed' ? 'Pago' : 'Pendente' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Números Comprados --}}
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600 mb-3">Números comprados:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($numbers as $number)
                                @php
                                    $isWinningNumber = $hasWinner && $hasWinner->winning_number == $number;
                                @endphp
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-sm
                                            {{ $isWinningNumber 
                                                ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-lg shadow-amber-500/30 ring-2 ring-amber-300' 
                                                : 'bg-gradient-to-br from-blue-400 to-blue-600 text-white' }}">
                                    {{ str_pad($number, 3, '0', STR_PAD_LEFT) }}
                                </div>
                            @endforeach
                        </div>

                        {{-- Selo de Ganhador --}}
                        @if($isWinner)
                            <div class="mt-4 p-4 bg-gradient-to-r from-amber-400 to-amber-500 rounded-xl text-white">
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-bold text-lg">Parabéns! Você é o ganhador!</p>
                                        <p class="text-white/80">Número sorteado: {{ str_pad($hasWinner->winning_number, 3, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($hasWinner)
                            <div class="mt-4 p-3 bg-gray-100 rounded-xl text-gray-600 text-sm">
                                <span class="font-semibold">Rifa finalizada.</span> 
                                Número vencedor: {{ str_pad($hasWinner->winning_number, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $payments->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma compra encontrada</h3>
            <p class="text-gray-500 mb-6">Você ainda não comprou nenhum ingresso.</p>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Ver Rifas Disponíveis
            </a>
        </div>
    @endif
</div>
