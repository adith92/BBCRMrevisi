<?php

namespace App\Http\Controllers;

use App\Models\Pool;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class PoolController extends Controller
{
    public function index()
    {
        $pools = Pool::withCount('vehicles')->latest()->paginate(15);
        return view('pool.index', compact('pools'));
    }

    public function create()
    {
        return view('pool.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        Pool::create($validated);

        return redirect()->route('pool.index')
            ->with('success', 'Pool berhasil ditambahkan.');
    }

    public function show(Pool $pool)
    {
        $pool->load('vehicles');
        $vehicles = $pool->vehicles()->paginate(15);
        
        return view('pool.show', compact('pool', 'vehicles'));
    }

    public function edit(Pool $pool)
    {
        return view('pool.edit', compact('pool'));
    }

    public function update(Request $request, Pool $pool)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $pool->update($validated);

        return redirect()->route('pool.show', $pool)
            ->with('success', 'Pool berhasil diupdate.');
    }

    public function destroy(Pool $pool)
    {
        $pool->delete();

        return redirect()->route('pool.index')
            ->with('success', 'Pool berhasil dihapus.');
    }
}
