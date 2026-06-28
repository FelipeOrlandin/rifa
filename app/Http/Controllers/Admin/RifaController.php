<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\RifaNumber;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class RifaController extends Controller
{
    /**
     * Listagem de rifas com filtros
     */
    public function index(Request $request)
    {
        $query = Rifa::withCount('numbers');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Busca por título
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $rifas = $query->latest()->paginate(10)->withQueryString();

        return view('admin.rifas.index', compact('rifas'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('admin.rifas.create');
    }

    /**
     * Salvar nova rifa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prize_image' => 'nullable|image|max:2048',
            'number_price' => 'required|numeric|min:0.01',
            'total_numbers' => 'required|integer|min:1|max:10000',
            'prize_value' => 'nullable|numeric|min:0',
            'draw_date' => 'required|date|after:now',
            'status' => 'required|in:active,closed,draft',
        ]);

        // Upload da imagem
        if ($request->hasFile('prize_image')) {
            $validated['prize_image'] = $request->file('prize_image')->store('rifas', 'public');
        }

        $validated['slug'] = Str::slug($validated['title']);

        // Criar rifa
        $rifa = Rifa::create($validated);

        // Gerar números automaticamente
        $numbers = [];
        for ($i = 1; $i <= $rifa->total_numbers; $i++) {
            $numbers[] = [
                'rifa_id' => $rifa->id,
                'number' => $i,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        RifaNumber::insert($numbers);

        // Limpar cache
        Cache::forget('rifas_active');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa criada com sucesso!');
    }

    /**
     * Exibir detalhes da rifa
     */
    public function show(Rifa $rifa)
    {
        $rifa->load(['numbers', 'winner.user', 'winner.rifaNumber']);
        
        $stats = [
            'total' => $rifa->numbers()->count(),
            'available' => $rifa->numbers()->where('status', 'available')->count(),
            'reserved' => $rifa->numbers()->where('status', 'reserved')->count(),
            'paid' => $rifa->numbers()->where('status', 'paid')->count(),
        ];

        return view('admin.rifas.show', compact('rifa', 'stats'));
    }

    /**
     * Formulário de edição
     */
    public function edit(Rifa $rifa)
    {
        return view('admin.rifas.edit', compact('rifa'));
    }

    /**
     * Atualizar rifa
     */
    public function update(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prize_image' => 'nullable|image|max:2048',
            'number_price' => 'required|numeric|min:0.01',
            'prize_value' => 'nullable|numeric|min:0',
            'draw_date' => 'required|date',
            'status' => 'required|in:active,closed,draft',
        ]);

        // Upload da imagem (remover anterior se existir)
        if ($request->hasFile('prize_image')) {
            if ($rifa->prize_image) {
                Storage::disk('public')->delete($rifa->prize_image);
            }
            $validated['prize_image'] = $request->file('prize_image')->store('rifas', 'public');
        }

        $rifa->update($validated);

        // Limpar cache
        Cache::forget("rifa_{$rifa->slug}");
        Cache::forget('rifas_active');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa atualizada com sucesso!');
    }

    /**
     * Remover rifa
     */
    public function destroy(Rifa $rifa)
    {
        // Remover imagem se existir
        if ($rifa->prize_image) {
            Storage::disk('public')->delete($rifa->prize_image);
        }

        $rifa->delete();

        // Limpar cache
        Cache::forget("rifa_{$rifa->slug}");
        Cache::forget('rifas_active');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.rifas.index')
            ->with('success', 'Rifa removida com sucesso!');
    }

    /**
     * Gerenciar números da rifa
     */
    public function numbers(Rifa $rifa)
    {
        $numbers = $rifa->numbers()
            ->with('user')
            ->orderBy('number')
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'total' => $rifa->numbers()->count(),
            'available' => $rifa->numbers()->where('status', 'available')->count(),
            'reserved' => $rifa->numbers()->where('status', 'reserved')->count(),
            'paid' => $rifa->numbers()->where('status', 'paid')->count(),
        ];

        return view('admin.rifas.numbers', compact('rifa', 'numbers', 'stats'));
    }

    /**
     * Atualizar status de um número
     */
    public function updateNumberStatus(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'number_id' => 'required|exists:rifa_numbers,id',
            'status' => 'required|in:available,reserved,paid',
        ]);

        RifaNumber::where('id', $validated['number_id'])
            ->update(['status' => $validated['status']]);

        // Limpar cache
        Cache::forget("rifa_{$rifa->id}_available_numbers");

        return back()->with('success', 'Status do número atualizado!');
    }

    /**
     * Atualizar status de múltiplos números
     */
    public function updateMultipleNumbers(Request $request, Rifa $rifa)
    {
        $validated = $request->validate([
            'number_ids' => 'required|array',
            'number_ids.*' => 'exists:rifa_numbers,id',
            'status' => 'required|in:available,reserved,paid',
        ]);

        RifaNumber::whereIn('id', $validated['number_ids'])
            ->update(['status' => $validated['status']]);

        // Limpar cache
        Cache::forget("rifa_{$rifa->id}_available_numbers");

        return back()->with('success', 'Status dos números atualizados!');
    }
}
