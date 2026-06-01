<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Pool;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('pool');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        $vehicles = $query->latest()->paginate(15);

        return view('fleet.index', compact('vehicles'));
    }

    public function create()
    {
        $pools = Pool::all();
        return view('fleet.create', compact('pools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles',
            'brand' => 'required|in:bigbird,goldenbird,cititrans,executive',
            'model' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'status' => 'required|in:available,on_trip,maintenance,inactive',
            'pool_id' => 'nullable|exists:pools,id',
            'notes' => 'nullable|string',
        ]);

        Vehicle::create($validated);

        return redirect()->route('fleet.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show(Vehicle $fleet)
    {
        $fleet->load(['pool', 'maintenanceLogs', 'bookings']);
        $maintenanceLogs = $fleet->maintenanceLogs()->latest()->get();
        $bookings = $fleet->bookings()->with(['client', 'driver'])->latest()->take(10)->get();

        return view('fleet.show', compact('fleet', 'maintenanceLogs', 'bookings'));
    }

    public function edit(Vehicle $fleet)
    {
        $pools = Pool::all();
        return view('fleet.edit', compact('fleet', 'pools'));
    }

    public function update(Request $request, Vehicle $fleet)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $fleet->id,
            'brand' => 'required|in:bigbird,goldenbird,cititrans,executive',
            'model' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'status' => 'required|in:available,on_trip,maintenance,inactive',
            'pool_id' => 'nullable|exists:pools,id',
            'notes' => 'nullable|string',
        ]);

        $fleet->update($validated);

        return redirect()->route('fleet.show', $fleet)
            ->with('success', 'Kendaraan berhasil diupdate.');
    }

    public function destroy(Vehicle $fleet)
    {
        $fleet->delete();

        return redirect()->route('fleet.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }

    public function apiShow(Vehicle $fleet)
    {
        return response()->json([
            'id' => $fleet->id,
            'plate_number' => $fleet->plate_number,
            'brand' => $fleet->brand,
            'model' => $fleet->model,
            'capacity' => $fleet->capacity,
            'status' => $fleet->status,
            'pool' => $fleet->pool,
            'bookings_count' => $fleet->bookings()->count(),
        ]);
    }
}
