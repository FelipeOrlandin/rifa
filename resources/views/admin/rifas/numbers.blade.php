@extends('admin.layouts.app')

@section('title', "Números - {$rifa->title}")

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
                <h1 class="text-2xl font-bold text-gray-800">Números da Rifa</h1>
                <p class="text-gray-600 mt-1">{{ $rifa->title }}</p>
            </div>
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

    {{-- Numbers Table --}}
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Lista de Números</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Número</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reservado até</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($numbers as $number)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-800">{{ str_pad($number->number, 3, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $number->status === 'available' ? 'bg-emerald-100 text-emerald-700' : 
                                       ($number->status === 'reserved' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $number->status === 'available' ? 'Disponível' : 
                                       ($number->status === 'reserved' ? 'Reservado' : 'Vendido') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $number->user->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $number->reserved_until ? $number->reserved_until->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.rifas.numbers.update', $rifa) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="number_id" value="{{ $number->id }}">
                                    <select name="status" class="px-3 py-1 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                        <option value="available" {{ $number->status === 'available' ? 'selected' : '' }}>Disponível</option>
                                        <option value="reserved" {{ $number->status === 'reserved' ? 'selected' : '' }}>Reservado</option>
                                        <option value="paid" {{ $number->status === 'paid' ? 'selected' : '' }}>Vendido</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-emerald-500 text-white text-sm rounded-lg hover:bg-emerald-600 transition-colors">
                                        Salvar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-gray-500">Nenhum número encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($numbers->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $numbers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
