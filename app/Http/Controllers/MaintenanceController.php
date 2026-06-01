<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceLog::with('vehicle');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('vehicle', fn($q) => $q->where('plate_number', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest()->paginate(15);

        return view('maintenance.index', compact('logs'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('maintenance.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:routine,repair,modification',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'scheduled_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'status' => 'required|in:scheduled,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        $validated['cost'] = str_replace('.', '', $validated['cost']);

        MaintenanceLog::create($validated);

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance log berhasil ditambahkan.');
    }

    public function show(MaintenanceLog $maintenance)
    {
        $maintenance->load('vehicle');
        return view('maintenance.show', compact('maintenance'));
    }

    public function edit(MaintenanceLog $maintenance)
    {
        $vehicles = Vehicle::all();
        return view('maintenance.edit', compact('maintenance', 'vehicles'));
    }

    public function update(Request $request, MaintenanceLog $maintenance)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:routine,repair,modification',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'scheduled_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'status' => 'required|in:scheduled,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        $validated['cost'] = str_replace('.', '', $validated['cost']);
        $maintenance->update($validated);

        return redirect()->route('maintenance.show', $maintenance)
            ->with('success', 'Maintenance log berhasil diupdate.');
    }

    public function destroy(MaintenanceLog $maintenance)
    {
        $maintenance->delete();

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance log berhasil dihapus.');
    }
}
