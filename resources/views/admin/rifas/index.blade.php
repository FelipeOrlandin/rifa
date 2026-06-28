@extends('admin.layouts.app')

@section('title', 'Gerenciar Rifas')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Rifas</h1>
            <p class="text-gray-600 mt-1">Gerencie suas rifas</p>
        </div>
        <a href="{{ route('admin.rifas.create') }}" 
           class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all font-semibold flex items-center gap-2 shadow-lg shadow-emerald-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Rifa
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg p-4">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Buscar por título..."
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">Todos os status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Encerrada</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="drawn" {{ request('status') === 'drawn' ? 'selected' : '' }}>Finalizada</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                Filtrar
            </button>
        </form>
    </div>

    {{-- Rifas List --}}
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rifa</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Preço/Número</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Números</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data Sorteio</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rifas as $rifa)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center overflow-hidden">
                                        @if($rifa->prize_image)
                                            <img src="{{ asset('storage/' . $rifa->prize_image) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $rifa->title }}</p>
                                        <p class="text-xs text-gray-500">{{ Str::limit($rifa->description, 50) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-emerald-600">R$ {{ number_format($rifa->number_price, 2, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-800">{{ $rifa->numbers_count }}</span>
                                    <span class="text-gray-500">total</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $rifa->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 
                                       ($rifa->status === 'closed' ? 'bg-amber-100 text-amber-700' : 
                                       ($rifa->status === 'drawn' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $rifa->status === 'active' ? 'Ativa' : 
                                       ($rifa->status === 'closed' ? 'Encerrada' : 
                                       ($rifa->status === 'drawn' ? 'Finalizada' : 'Rascunho')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $rifa->draw_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.rifas.show', $rifa) }}" 
                                       class="p-2 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                       title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.rifas.edit', $rifa) }}" 
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.rifas.numbers', $rifa) }}" 
                                       class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                       title="Gerenciar números">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                        </svg>
                                    </a>
                                    @if(in_array($rifa->status, ['closed', 'drawn']))
                                        <a href="{{ route('admin.sorteio.index', $rifa)" 
                                           class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                           title="Sorteio">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.rifas.destroy', $rifa) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta rifa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-gray-500">Nenhuma rifa encontrada</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($rifas->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $rifas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
