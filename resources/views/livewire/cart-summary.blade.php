{{-- resources/views/livewire/cart-summary.blade.php --}}

<div 
    x-data 
    x-show="{{ count($selectedNumbers) > 0 }}"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-0 left-0 right-0 z-50"
>
    {{-- Glassmorphism background --}}
    <div class="bg-white/80 backdrop-blur-lg border-t border-white/20 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Selection info --}}
                <div class="flex items-center gap-4">
                    {{-- Selected numbers preview --}}
                    <div class="flex -space-x-2">
                        @foreach(array_slice($selectedNumbers, 0, 5) as $number)
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-bold border-2 border-white shadow-sm">
                                {{ str_pad($number, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        @endforeach
                        @if(count($selectedNumbers) > 5)
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold border-2 border-white shadow-sm">
                                +{{ count($selectedNumbers) - 5 }}
                            </div>
                        @endif
                    </div>

                    {{-- Summary text --}}
                    <div class="text-gray-800">
                        <span class="font-bold text-lg">{{ count($selectedNumbers) }}</span>
                        <span class="text-gray-600">
                            {{ Str::plural('número', count($selectedNumbers)) }} selecionado{{ count($selectedNumbers) > 1 ? 's' : '' }}
                        </span>
                        <span class="mx-2 text-gray-400">|</span>
                        <span class="font-bold text-xl text-emerald-600">
                            R$ {{ number_format($total, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3">
                    <button
                        wire:click="$parent.reserveSelected"
                        @disabled="count($selectedNumbers) === 0"
                        class="
                            px-6 py-3 rounded-xl font-semibold text-white
                            bg-gradient-to-r from-emerald-500 to-emerald-600
                            hover:from-emerald-600 hover:to-emerald-700
                            transform hover:scale-105 transition-all duration-200
                            shadow-lg shadow-emerald-500/30
                            disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                            flex items-center gap-2
                        "
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Reservar e Pagar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
