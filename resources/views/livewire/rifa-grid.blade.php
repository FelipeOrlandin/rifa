{{-- resources/views/livewire/rifa-grid.blade.php --}}

<div class="w-full">
    {{-- Timer Regressivo --}}
    @if($rifa->status === 'active' && $rifa->draw_date->isFuture())
        <div class="mb-6 p-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg"
             x-data="{ 
                 deadline: '{{ $rifa->draw_date->toIso8601String() }}',
                 days: 0, hours: 0, minutes: 0, seconds: 0,
                 updateCountdown() {
                     const now = new Date();
                     const target = new Date(this.deadline);
                     const diff = target - now;
                     
                     if (diff <= 0) {
                         this.days = 0; this.hours = 0; this.minutes = 0; this.seconds = 0;
                         return;
                     }
                     
                     this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                     this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                     this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                     this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                 },
                 init() {
                     this.updateCountdown();
                     setInterval(() => this.updateCountdown(), 1000);
                 }
             }"
             x-init="init()">
            <div class="flex items-center justify-center gap-3">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-gray-700 font-medium">Sorteio em:</span>
                <div class="flex items-center gap-2">
                    <div class="text-center">
                        <span class="text-2xl font-bold text-emerald-600" x-text="String(days).padStart(2, '0')">00</span>
                        <span class="text-xs text-gray-500 block">dias</span>
                    </div>
                    <span class="text-2xl font-bold text-gray-400">:</span>
                    <div class="text-center">
                        <span class="text-2xl font-bold text-emerald-600" x-text="String(hours).padStart(2, '0')">00</span>
                        <span class="text-xs text-gray-500 block">horas</span>
                    </div>
                    <span class="text-2xl font-bold text-gray-400">:</span>
                    <div class="text-center">
                        <span class="text-2xl font-bold text-emerald-600" x-text="String(minutes).padStart(2, '0')">00</span>
                        <span class="text-xs text-gray-500 block">min</span>
                    </div>
                    <span class="text-2xl font-bold text-gray-400">:</span>
                    <div class="text-center">
                        <span class="text-2xl font-bold text-emerald-600" x-text="String(seconds).padStart(2, '0')">00</span>
                        <span class="text-xs text-gray-500 block">seg</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Aguardando Sorteio --}}
    @if($rifa->status === 'closed' || ($rifa->status === 'active' && $rifa->draw_date->isPast()))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
            <div class="flex items-center justify-center gap-3">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-amber-700 font-medium">Aguardando sorteio...</span>
            </div>
        </div>
    @endif

    {{-- Ganhador --}}
    @if($rifa->status === 'drawn' && $rifa->winner)
        <div class="mb-6 p-6 bg-gradient-to-r from-amber-400 to-amber-500 rounded-2xl shadow-lg text-white">
            <div class="flex items-center justify-center gap-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <div class="text-center">
                    <p class="text-xl font-bold">Rifa Finalizada!</p>
                    <p class="text-white/90">
                        Ganhador: <span class="font-bold">{{ $rifa->winner->user->name ?? 'Usuário' }}</span>
                    </p>
                    <p class="text-white/90">
                        Número: <span class="font-bold text-2xl">{{ str_pad($rifa->winner->winning_number, 3, '0', STR_PAD_LEFT) }}</span>
                    </p>
                    <p class="text-white/80 text-sm mt-1">
                        Sorteado em {{ $rifa->winner->sorteado_em?->format('d/m/Y H:i') ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Grid Container --}}
    <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2 p-4">
        @foreach($numbersPaginator as $numberObj)
            @php
                $number = $numberObj->number;
                $isAvailable = in_array($number, $availableNumbers);
                $isSold = in_array($number, $soldNumbers);
                $isSelected = in_array($number, $selectedNumbers);
                $isReserved = in_array($number, $reservedNumbers);
            @endphp

            {{-- Number Button --}}
            <button
                wire:click="toggleNumber({{ $number }})"
                @disabled(!$isAvailable)
                class="
                    relative aspect-square rounded-xl font-semibold text-sm
                    transition-all duration-200 ease-out
                    {{ $isAvailable ? 'cursor-pointer hover:scale-105' : 'cursor-not-allowed' }}
                    {{ $isSelected 
                        ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-300' 
                        : ($isSold 
                            ? 'bg-gray-200/50 text-gray-400 opacity-40' 
                            : ($isReserved 
                                ? 'bg-amber-100/50 text-amber-600 opacity-60' 
                                : 'bg-white/20 text-gray-700 hover:bg-white/40 hover:shadow-lg hover:shadow-white/20'
                            )
                        )
                    }}
                    backdrop-blur-sm border border-white/20
                "
                x-data
                @mouseenter="$el.style.transform = '{{ $isAvailable ? 'scale(1.1)' : 'scale(1)' }}'"
                @mouseleave="$el.style.transform = 'scale(1)'"
            >
                {{-- Glass effect overlay for available numbers --}}
                @if($isAvailable && !$isSelected)
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-white/30 to-transparent pointer-events-none"></div>
                @endif
                
                {{-- Number text --}}
                <span class="relative z-10">{{ str_pad($number, 3, '0', STR_PAD_LEFT) }}</span>
                
                {{-- Sold badge --}}
                @if($isSold)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
                
                {{-- Selected checkmark --}}
                @if($isSelected)
                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-md">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($numbersPaginator->hasPages())
        <div class="px-4 pb-4">
            {{ $numbersPaginator->links() }}
        </div>
    @endif

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 mt-4 px-4 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-white/20 backdrop-blur-sm border border-white/20"></div>
            <span class="text-gray-600">Disponível</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-emerald-500"></div>
            <span class="text-gray-600">Selecionado</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-gray-200/50 opacity-40"></div>
            <span class="text-gray-600">Vendido</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-amber-100/50 opacity-60"></div>
            <span class="text-gray-600">Reservado</span>
        </div>
    </div>

    {{-- Resumo e Botão de Compra --}}
    @if(count($selectedNumbers) > 0)
        <div class="mt-6 p-4 bg-white/30 backdrop-blur-sm rounded-xl border border-white/20">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-gray-800">
                    <span class="font-bold text-lg">{{ count($selectedNumbers) }}</span>
                    <span class="text-gray-600">
                        {{ Str::plural('número', count($selectedNumbers)) }} selecionado{{ count($selectedNumbers) > 1 ? 's' : '' }}
                    </span>
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="font-bold text-xl text-emerald-600">
                        R$ {{ number_format($totalSelecionado, 2, ',', '.') }}
                    </span>
                </div>
                <button
                    wire:click="abrirFormularioCheckout"
                    class="
                        px-6 py-3 rounded-xl font-semibold text-white
                        bg-gradient-to-r from-emerald-500 to-emerald-600
                        hover:from-emerald-600 hover:to-emerald-700
                        transform hover:scale-105 transition-all duration-200
                        shadow-lg shadow-emerald-500/30
                        flex items-center gap-2
                    "
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Finalizar Compra
                </button>
            </div>
        </div>
    @endif

    {{-- Formulário de Checkout --}}
    @if($showCheckoutForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white">Dados do Comprador</h3>
                    <p class="text-emerald-100 text-sm mt-1">Preencha seus dados para gerar o PIX</p>
                </div>
                
                {{-- Form --}}
                <div class="p-6">
                    <form wire:submit="processarPix">
                        {{-- Nome --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                            <input 
                                type="text" 
                                wire:model="buyerName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Seu nome completo"
                            >
                            @error('buyerName')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                            <input 
                                type="email" 
                                wire:model="buyerEmail"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="seu@email.com"
                            >
                            @error('buyerEmail')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Telefone --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                            <input 
                                type="tel" 
                                wire:model="buyerPhone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="(11) 99999-9999"
                            >
                            @error('buyerPhone')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Resumo --}}
                        <div class="bg-emerald-50 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">{{ count($selectedNumbers) }} {{ Str::plural('número', count($selectedNumbers)) }}</span>
                                <span class="font-bold text-emerald-600">R$ {{ number_format($totalSelecionado, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Botões --}}
                        <div class="flex gap-3">
                            <button
                                type="button"
                                wire:click="fecharFormularioCheckout"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 font-semibold"
                            >
                                Gerar PIX
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal QR Code PIX --}}
    @if($showPixModal && isset($pixData['pix']['success']) && $pixData['pix']['success'])
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4 text-center">
                    <svg class="w-12 h-12 text-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-white">PIX Gerado com Sucesso!</h3>
                </div>
                
                {{-- Content --}}
                <div class="p-6">
                    {{-- Valor --}}
                    <div class="text-center mb-6">
                        <p class="text-gray-600">Valor Total</p>
                        <p class="text-3xl font-bold text-emerald-600">R$ {{ number_format($pixData['amount'], 2, ',', '.') }}</p>
                    </div>

                    {{-- QR Code --}}
                    <div class="flex justify-center mb-6">
                        <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                            <img 
                                src="data:image/png;base64,{{ $pixData['pix']['qr_code_base64'] }}" 
                                alt="QR Code PIX"
                                class="w-48 h-48"
                            >
                        </div>
                    </div>

                    {{-- Código PIX Copia e Cola --}}
                    <div class="mb-6">
                        <p class="text-sm text-gray-600 mb-2">Ou copie o código PIX:</p>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                readonly
                                value="{{ $pixData['pix']['qr_code'] }}"
                                class="flex-1 px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-700 truncate"
                            >
                            <button
                                onclick="navigator.clipboard.writeText('{{ $pixData['pix']['qr_code'] }}')"
                                class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors"
                            >
                                Copiar
                            </button>
                        </div>
                    </div>

                    {{-- Aviso de Tempo --}}
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-red-800">Atenção!</p>
                                <p class="text-sm text-red-700">
                                    Reserva válida por <strong>{{ $reservationMinutes }} minutos</strong>. 
                                    Após esse tempo, os números serão liberados automaticamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Botão Fechar --}}
                    <button
                        wire:click="fecharPixModal"
                        class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Erro --}}
    @if($showPixModal && isset($pixData['pix']['success']) && !$pixData['pix']['success'])
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                <div class="bg-red-500 px-6 py-4 text-center">
                    <svg class="w-12 h-12 text-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-white">Erro ao Gerar PIX</h3>
                </div>
                
                <div class="p-6 text-center">
                    <p class="text-gray-600 mb-6">{{ $pixData['pix']['error'] ?? 'Erro desconhecido ao gerar PIX' }}</p>
                    <button
                        wire:click="fecharPixModal"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold"
                    >
                        Tentar Novamente
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
