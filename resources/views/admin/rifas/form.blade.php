@extends('admin.layouts.app')

@section('title', isset($rifa) ? 'Editar Rifa' : 'Nova Rifa')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ isset($rifa) ? 'Editar Rifa' : 'Nova Rifa' }}</h1>
            <p class="text-gray-600 mt-1">{{ isset($rifa) ? 'Atualize os dados da rifa' : 'Crie uma nova rifa' }}</p>
        </div>
        <a href="{{ route('admin.rifas.index') }}" 
           class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Form --}}
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
        <form action="{{ isset($rifa) ? route('admin.rifas.update', $rifa) : route('admin.rifas.store') }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="p-6 space-y-6">
            @csrf
            @if(isset($rifa))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Título --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
                    <input type="text" 
                           name="title" 
                           value="{{ old('title', $rifa->title ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                    <textarea name="description" 
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('description', $rifa->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Preço por Número --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preço por Número (R$) *</label>
                    <input type="number" 
                           name="number_price" 
                           value="{{ old('number_price', $rifa->number_price ?? '') }}"
                           step="0.01"
                           min="0.01"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           required>
                    @error('number_price')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Total de Números --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total de Números *</label>
                    <input type="number" 
                           name="total_numbers" 
                           value="{{ old('total_numbers', $rifa->total_numbers ?? '') }}"
                           min="1"
                           max="10000"
                           {{ isset($rifa) ? 'disabled' : '' }}
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent {{ isset($rifa) ? 'bg-gray-100' : '' }}">
                    @if(isset($rifa))
                        <p class="mt-1 text-xs text-gray-500">Não é possível alterar após criação</p>
                    @endif
                    @error('total_numbers')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Valor do Prêmio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor do Prêmio (R$)</label>
                    <input type="number" 
                           name="prize_value" 
                           value="{{ old('prize_value', $rifa->prize_value ?? '') }}"
                           step="0.01"
                           min="0"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @error('prize_value')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Data do Sorteio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data do Sorteio *</label>
                    <input type="date" 
                           name="draw_date" 
                           value="{{ old('draw_date', isset($rifa) ? $rifa->draw_date->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           required>
                    @error('draw_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" 
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            required>
                        <option value="draft" {{ old('status', $rifa->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="active" {{ old('status', $rifa->status ?? '') === 'active' ? 'selected' : '' }}>Ativa</option>
                        <option value="closed" {{ old('status', $rifa->status ?? '') === 'closed' ? 'selected' : '' }}>Encerrada</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Imagem do Prêmio --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Imagem do Prêmio</label>
                    <div class="flex items-center gap-6">
                        <div class="flex-1">
                            <input type="file" 
                                   name="prize_image" 
                                   accept="image/*"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="mt-1 text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                        </div>
                        @if(isset($rifa) && $rifa->prize_image)
                            <div class="w-24 h-24 rounded-xl overflow-hidden border border-gray-200">
                                <img src="{{ asset('storage/' . $rifa->prize_image) }}" 
                                     class="w-full h-full object-cover" 
                                     alt="Imagem atual">
                            </div>
                        @endif
                    </div>
                    @error('prize_image')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.rifas.index') }}" 
                   class="px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all font-semibold shadow-lg shadow-emerald-500/30">
                    {{ isset($rifa) ? 'Atualizar Rifa' : 'Criar Rifa' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
