<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\MaintenanceLog;

class FleetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $query = Vehicle::with(['pool', 'assignedOpportunity.client'])
            ->whereIn('brand', ['goldenbird', 'executive']);

        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $query->where('pool_id', $user->pool_id);
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if (request('location') && request('location') !== 'All') {
            $loc = request('location');
            $query->whereHas('pool', function($q) use ($loc) {
                $q->where('name', 'like', "%{$loc}%");
            });
        }

        if (request('status') && request('status') !== 'All') {
            if (request('status') === 'Being Serviced' || request('status') === 'In Queue') {
                $query->where('status', 'Maintenance');
            } elseif (request('status') === 'rent_out') {
                $query->whereIn('status', ['rent_out', 'assigned']);
            } elseif (request('status') === 'booked') {
                $query->whereIn('status', ['booked', 'reserved']);
            } else {
                $query->where('status', request('status'));
            }
        }

        $vehicles = $query->orderBy('brand')->get();

        $statsQuery = Vehicle::whereIn('brand', ['goldenbird', 'executive']);
        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $statsQuery->where('pool_id', $user->pool_id);
        }

        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'available'   => (clone $statsQuery)->where('status', 'available')->count(),
            'rented'      => (clone $statsQuery)->where(function($q) { $q->where('status', 'rent_out')->orWhere('status', 'assigned'); })->count(),
            'booked'      => (clone $statsQuery)->whereIn('status', ['booked', 'reserved'])->count(),
            'hold'        => (clone $statsQuery)->where('status', 'hold')->count(),
            'maintenance' => (clone $statsQuery)->where('status', 'maintenance')->count(),
            'beingServiced'=> (clone $statsQuery)->where('status', 'maintenance')->where('notes', 'like', '%Servicing%')->count(),
            'inQueue'     => (clone $statsQuery)->where('status', 'maintenance')->where('notes', 'not like', '%Servicing%')->count(),
        ];

        // Ops Pending Assignments Logic
        $pendingAssignments = \App\Models\Opportunity::with(['client', 'sales', 'assignedVehicles', 'assignedDrivers'])
            ->whereIn('stage', ['negotiation', 'won'])
            ->get()
            ->filter(function ($opp) use ($user) {
                $requiredFleets = $opp->requiredFleetQty();
                $requiredDrivers = $opp->requiredDriverQty();
                
                $assignedFleets = $opp->assignedVehicles->count();
                $assignedDrivers = $opp->assignedDrivers->count();
                
                $opp->required_fleets = $requiredFleets;
                $opp->required_drivers = $requiredDrivers;
                $opp->missing_fleets = max(0, $requiredFleets - $assignedFleets);
                $opp->missing_drivers = max(0, $requiredDrivers - $assignedDrivers);
                $opp->fleet_status = $opp->missing_fleets > 0 ? 'pending' : 'fulfilled';
                $opp->driver_status = $opp->missing_drivers > 0 ? 'pending' : 'fulfilled';
                
                if ($requiredFleets <= 0) {
                    return false;
                }

                // Filter for pool role
                if ($user->isPool() && $user->pool_id !== null) {
                    $userPoolId = $user->pool_id;
                    $hasOtherPoolVehicles = $opp->assignedVehicles->contains(fn($v) => $v->pool_id !== $userPoolId);
                    $hasOtherPoolDrivers = $opp->assignedDrivers->contains(fn($d) => $d->pool_id !== $userPoolId);
                    if ($hasOtherPoolVehicles || $hasOtherPoolDrivers) {
                        return false;
                    }
                }

                return true;
            });

        // Sorting Logic
        $sortPending = request('sort_pending', 'date');
        $direction = request('direction', 'asc');

        if ($sortPending === 'name') {
            $pendingAssignments = $direction === 'desc' 
                ? $pendingAssignments->sortByDesc('title') 
                : $pendingAssignments->sortBy('title');
        } elseif ($sortPending === 'client') {
            $pendingAssignments = $direction === 'desc' 
                ? $pendingAssignments->sortByDesc(fn($opp) => $opp->client->company_name ?? '') 
                : $pendingAssignments->sortBy(fn($opp) => $opp->client->company_name ?? '');
        } else {
            // default: date (actual_close_date -> expected_close_date -> created_at)
            $pendingAssignments = $direction === 'desc' 
                ? $pendingAssignments->sortByDesc(fn($opp) => $opp->actual_close_date ?? $opp->expected_close_date ?? $opp->created_at) 
                : $pendingAssignments->sortBy(fn($opp) => $opp->actual_close_date ?? $opp->expected_close_date ?? $opp->created_at);
        }

        $pendingAssignments = $pendingAssignments->values();

        return view('fleet.index', compact('vehicles', 'stats', 'pendingAssignments'));
    }

    public function show(Vehicle $vehicle)
    {
        $user = auth()->user();

        if ($user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $vehicle->load(['pool']);

        $bookings = Booking::where('vehicle_id', $vehicle->id)
            ->with(['client', 'sales', 'driver'])
            ->orderBy('pickup_datetime', 'desc')
            ->limit(10)
            ->get();

        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $vehicle->id)
            ->orderBy('scheduled_date', 'desc')
            ->get();

        $activeBooking = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'on_trip'])
            ->with('client', 'driver', 'sales')
            ->first();

        $nextMaintenance = MaintenanceLog::where('vehicle_id', $vehicle->id)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date')
            ->first();

        return view('fleet.show', compact('vehicle', 'bookings', 'maintenanceLogs', 'activeBooking', 'nextMaintenance'));
    }

    public function apiAvailable()
    {
        $user = auth()->user();
        $query = Vehicle::with('pool')
            ->where(function ($q) {
                $q->where('status', 'available');
                if (request()->has('opportunity_id')) {
                    $q->orWhere('assigned_opportunity_id', request('opportunity_id'));
                }
            });
            
        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $query->where('pool_id', $user->pool_id);
        } else if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }

    public function apiDriversAvailable()
    {
        $user = auth()->user();
        $query = \App\Models\Driver::with('pool')
            ->where(function ($q) {
                $q->where('status', 'available');
                if (request()->has('opportunity_id')) {
                    $q->orWhere('assigned_opportunity_id', request('opportunity_id'));
                }
            });

        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $query->where('pool_id', $user->pool_id);
        } else if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        if (!$user->isOperational() && !$user->isPool() && !$user->isManager() && !$user->isGM()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'police_number' => 'required|string|max:50',
            'brand_model' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'stnk_expiry' => 'nullable|date',
            'pajak_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'pool_id' => 'nullable|exists:pools,id',
            'status' => 'required|in:available,maintenance,inactive',
        ]);

        if ($user->isPool()) {
            if ($user->pool_id === null) {
                abort(403, 'Pengguna pool wajib memiliki pool_id.');
            }
            $validated['pool_id'] = $user->pool_id;
        }

        $vehicleData = [
            'plate_number' => $validated['police_number'],
            'model' => $validated['brand_model'],
            'brand' => 'goldenbird', // Long term fleet
            'year' => $validated['year'] ?? null,
            'stnk_expiry' => $validated['stnk_expiry'] ?? null,
            'pajak_expiry' => $validated['pajak_expiry'] ?? null,
            'insurance_expiry' => $validated['insurance_expiry'] ?? null,
            'pool_id' => $validated['pool_id'] ?? null,
            'status' => $validated['status'],
        ];

        Vehicle::create($vehicleData);

        return redirect()->route('fleet.index')->with('success', 'Vehicle registered successfully.');
    }

    public function assignToOpportunity(\Illuminate\Http\Request $request, \App\Models\Opportunity $opportunity)
    {
        $user = auth()->user();
        if (!$user->isOperational() && !$user->isPool() && !$user->isManager() && !$user->isGM()) {
            abort(403, 'Unauthorized');
        }

        if ($user->isPool() && $user->pool_id === null) {
            abort(403, 'Pengguna pool wajib memiliki pool_id.');
        }

        $request->validate([
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'driver_ids' => 'nullable|array',
            'driver_ids.*' => 'exists:drivers,id'
        ]);

        $stage = strtolower($opportunity->stage);
        if (!in_array($stage, ['negotiation', 'won'])) {
            return response()->json(['message' => 'Tahapan Opportunity harus negotiation atau won.'], 422);
        }

        $vehicleIdsInput = $request->input('vehicle_ids', []);
        $driverIdsInput = $request->input('driver_ids', []);
        $shouldSyncVehicles = $request->has('vehicle_ids');
        $shouldSyncDrivers = $request->has('driver_ids');

        $requiredFleets = $opportunity->requiredFleetQty();
        $requiredDrivers = $opportunity->requiredDriverQty();

        $userPoolId = $user->isPool() ? $user->pool_id : null;
        
        $otherPoolVehiclesCount = 0;
        $otherPoolDriversCount = 0;
        
        if ($userPoolId !== null) {
            $otherPoolVehiclesCount = $opportunity->assignedVehicles()->withoutGlobalScope('pool')->where('pool_id', '!=', $userPoolId)->count();
            $otherPoolDriversCount = $opportunity->assignedDrivers()->withoutGlobalScope('pool')->where('pool_id', '!=', $userPoolId)->count();
        }

        $totalVehiclesToAssign = $shouldSyncVehicles
            ? count($vehicleIdsInput) + $otherPoolVehiclesCount
            : $opportunity->assignedVehicles()->count();
        $totalDriversToAssign = $shouldSyncDrivers
            ? count($driverIdsInput) + $otherPoolDriversCount
            : $opportunity->assignedDrivers()->count();

        if ($requiredFleets > 0 && $totalVehiclesToAssign > $requiredFleets) {
            return response()->json(['message' => "Jumlah kendaraan melebihi kebutuhan ({$requiredFleets} unit)."], 422);
        }
        if ($requiredDrivers > 0 && $totalDriversToAssign > $requiredDrivers) {
            return response()->json(['message' => "Jumlah supir melebihi kebutuhan ({$requiredDrivers} orang)."], 422);
        }

        if ($userPoolId !== null) {
            $invalidVehicles = Vehicle::withoutGlobalScope('pool')->whereIn('id', $vehicleIdsInput)->where('pool_id', '!=', $userPoolId)->exists();
            if ($invalidVehicles) {
                abort(403, 'Anda hanya dapat memilih kendaraan dari pool Anda sendiri.');
            }
            $invalidDrivers = \App\Models\Driver::withoutGlobalScope('pool')->whereIn('id', $driverIdsInput)->where('pool_id', '!=', $userPoolId)->exists();
            if ($invalidDrivers) {
                abort(403, 'Anda hanya dapat memilih supir dari pool Anda sendiri.');
            }
        }

        $status = $stage === 'won' ? 'assigned' : 'reserved';
        $logNotes = [];

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $opportunity, $userPoolId, $vehicleIdsInput, $driverIdsInput, $shouldSyncVehicles, $shouldSyncDrivers, $status, &$logNotes
        ) {
            // VEHICLES
            if ($shouldSyncVehicles) {
                $vehiclesToReleaseQuery = $opportunity->assignedVehicles();
                if ($userPoolId !== null) {
                    $vehiclesToReleaseQuery->where('pool_id', $userPoolId);
                }
                $vehiclesToRelease = $vehiclesToReleaseQuery->whereNotIn('id', $vehicleIdsInput)->get();
                if ($vehiclesToRelease->isNotEmpty()) {
                    Vehicle::whereIn('id', $vehiclesToRelease->pluck('id'))->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'available'
                    ]);
                    $releasedPlates = $vehiclesToRelease->pluck('plate_number')->join(', ');
                    $logNotes[] = 'Kendaraan dilepas: ' . $releasedPlates;
                }

                if (!empty($vehicleIdsInput)) {
                    Vehicle::whereIn('id', $vehicleIdsInput)->update([
                        'assigned_opportunity_id' => $opportunity->id,
                        'status' => $status
                    ]);
                    $assignedVehicles = Vehicle::whereIn('id', $vehicleIdsInput)->get();
                    $assignedPlates = $assignedVehicles->pluck('plate_number')->join(', ');
                    $logNotes[] = 'Kendaraan dialokasikan: ' . $assignedPlates;
                }
            }

            // DRIVERS
            if ($shouldSyncDrivers) {
                $driversToReleaseQuery = $opportunity->assignedDrivers();
                if ($userPoolId !== null) {
                    $driversToReleaseQuery->where('pool_id', $userPoolId);
                }
                $driversToRelease = $driversToReleaseQuery->whereNotIn('id', $driverIdsInput)->get();
                if ($driversToRelease->isNotEmpty()) {
                    \App\Models\Driver::whereIn('id', $driversToRelease->pluck('id'))->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'available'
                    ]);
                    $releasedNames = $driversToRelease->pluck('name')->join(', ');
                    $logNotes[] = 'Supir dilepas: ' . $releasedNames;
                }

                if (!empty($driverIdsInput)) {
                    \App\Models\Driver::whereIn('id', $driverIdsInput)->update([
                        'assigned_opportunity_id' => $opportunity->id,
                        'status' => $status
                    ]);
                    $assignedDrivers = \App\Models\Driver::whereIn('id', $driverIdsInput)->get();
                    $assignedNames = $assignedDrivers->pluck('name')->join(', ');
                    $logNotes[] = 'Supir dialokasikan: ' . $assignedNames;
                }
            }
        });

        $opportunity->load(['assignedVehicles', 'assignedDrivers']);
        $assignedFleets = $opportunity->assignedVehicles->count();
        $assignedDrivers = $opportunity->assignedDrivers->count();
        
        $missingFleets = max(0, $requiredFleets - $assignedFleets);
        $missingDrivers = max(0, $requiredDrivers - $assignedDrivers);

        if (!empty($logNotes)) {
            \App\Models\ActivityLog::create([
                'sales_id'       => auth()->id() ?? $opportunity->sales_id,
                'client_id'      => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'type'           => 'follow_up',
                'subject'        => 'Armada & Supir Dialokasikan oleh Pool',
                'activity_date'  => now(),
                'notes'          => implode('; ', $logNotes),
            ]);
        }

        return response()->json([
            'message' => 'Alokasi berhasil disimpan',
            'missing_fleets' => $missingFleets,
            'missing_drivers' => $missingDrivers,
            'assigned_vehicles' => $opportunity->assignedVehicles,
            'assigned_drivers' => $opportunity->assignedDrivers,
        ]);
    }
}
