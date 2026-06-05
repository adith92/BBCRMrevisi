<?php

namespace App\Http\Controllers;

use App\Models\VehicleContract;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VehicleContractController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Sales role has no access to vehicle contracts
        if ($user->isSales()) {
            abort(403, 'Akses ditolak.');
        }

        $contracts = VehicleContract::with(['vehicle', 'driver', 'client'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('vehicle-contracts.index', compact('contracts'));
    }

    public function create()
    {
        $vehicles = Vehicle::orderBy('plate_number')->get();
        $drivers  = Driver::where('status', '!=', 'off')->orderBy('name')->get();
        $clients  = Client::where('status', 'active')->orderBy('company_name')->get();

        return view('vehicle-contracts.create', compact('vehicles', 'drivers', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'driver_id'     => 'nullable|exists:drivers,id',
            'client_id'     => 'nullable|exists:clients,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'contract_type' => 'required|in:dedicated,shared,pool',
            'rate'          => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $data['status'] = 'active';

        $contract = VehicleContract::create($data);

        // If dedicated contract, update vehicle status to on_trip / reserved
        if ($data['contract_type'] === 'dedicated') {
            Vehicle::where('id', $data['vehicle_id'])->update(['status' => 'on_trip']);
        }

        return redirect()->route('vehicle-contracts.show', $contract)
            ->with('success', 'Kontrak kendaraan berhasil dibuat.');
    }

    public function show(VehicleContract $vehicleContract)
    {
        $vehicleContract->load(['vehicle', 'driver', 'client']);

        return view('vehicle-contracts.show', compact('vehicleContract'));
    }

    public function edit(VehicleContract $vehicleContract)
    {
        $vehicles = Vehicle::orderBy('plate_number')->get();
        $drivers  = Driver::orderBy('name')->get();
        $clients  = Client::orderBy('company_name')->get();

        return view('vehicle-contracts.edit', compact('vehicleContract', 'vehicles', 'drivers', 'clients'));
    }

    public function update(Request $request, VehicleContract $vehicleContract)
    {
        $data = $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'driver_id'     => 'nullable|exists:drivers,id',
            'client_id'     => 'nullable|exists:clients,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'contract_type' => 'required|in:dedicated,shared,pool',
            'rate'          => 'nullable|numeric|min:0',
            'status'        => 'required|in:active,completed,cancelled',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $vehicleContract->update($data);

        return redirect()->route('vehicle-contracts.show', $vehicleContract)
            ->with('success', 'Kontrak kendaraan berhasil diperbarui.');
    }

    public function destroy(VehicleContract $vehicleContract)
    {
        $vehicleContract->update(['status' => 'cancelled']);

        return redirect()->route('vehicle-contracts.index')
            ->with('success', 'Kontrak kendaraan dibatalkan.');
    }

    public function complete(VehicleContract $vehicleContract)
    {
        $vehicleContract->update([
            'status'   => 'completed',
            'end_date' => Carbon::today()->toDateString(),
        ]);

        // Free the vehicle back to available
        if ($vehicleContract->contract_type === 'dedicated') {
            Vehicle::where('id', $vehicleContract->vehicle_id)->update(['status' => 'available']);
        }

        return redirect()->route('vehicle-contracts.show', $vehicleContract)
            ->with('success', 'Kontrak selesai, kendaraan dikembalikan ke available.');
    }
}
