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
            ->whereIn('stage', ['proposal', 'negotiation', 'won'])
            ->get()
            ->filter(function ($opp) {
                $requiredFleets = 0;
                
                if ($opp->product_id) {
                    $opp->loadMissing('product');
                    if ($opp->product && ($opp->product->name === 'Mobil Long Term' || $opp->product->product_category_id == 2)) {
                        if (preg_match('/—\s*(\d+)\s*unit/i', $opp->title, $matches)) {
                            $requiredFleets = (int)$matches[1];
                        } else {
                            $requiredFleets = 1;
                        }
                    }
                }
                
                if (!empty($opp->products) && is_array($opp->products)) {
                    $requiredFleets += collect($opp->products)
                        ->filter(fn($p) => isset($p['category']) && ($p['category'] === 'Mobil Long Term' || $p['category'] === 'Long Term'))
                        ->sum(fn($p) => (int)($p['quantity'] ?? 0));
                }
                
                if ($requiredFleets == 0) return false;
                
                $opp->required_fleets = $requiredFleets;
                $opp->missing_fleets = max(0, $requiredFleets - $opp->assignedVehicles->count());
                $opp->missing_drivers = max(0, $requiredFleets - $opp->assignedDrivers->count());
                
                return $opp->missing_fleets > 0 || $opp->missing_drivers > 0;
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

        if (!$user->isOperational() && !$user->isManager()) {
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

        $request->validate([
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'driver_ids' => 'nullable|array',
            'driver_ids.*' => 'exists:drivers,id'
        ]);

        $status = $opportunity->stage === 'won' ? 'assigned' : 'reserved';
        $logNotes = [];

        if ($request->has('vehicle_ids') && !empty($request->vehicle_ids)) {
            Vehicle::whereIn('id', $request->vehicle_ids)->update([
                'assigned_opportunity_id' => $opportunity->id,
                'status' => $status
            ]);
            $vehicles = Vehicle::whereIn('id', $request->vehicle_ids)->get();
            $plateNumbers = $vehicles->pluck('plate_number')->join(', ');
            $logNotes[] = 'Kendaraan dialokasikan: ' . $plateNumbers;
        }

        if ($request->has('driver_ids') && !empty($request->driver_ids)) {
            \App\Models\Driver::whereIn('id', $request->driver_ids)->update([
                'assigned_opportunity_id' => $opportunity->id,
                'status' => $status
            ]);
            $drivers = \App\Models\Driver::whereIn('id', $request->driver_ids)->get();
            $driverNames = $drivers->pluck('name')->join(', ');
            $logNotes[] = 'Supir dialokasikan: ' . $driverNames;
        }

        if (!empty($logNotes)) {
            \App\Models\ActivityLog::create([
                'sales_id'       => auth()->id() ?? $opportunity->sales_id,
                'client_id'      => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'type'           => 'follow_up',
                'subject'        => 'Armada & Supir Dialokasikan oleh OPS',
                'activity_date'  => now(),
                'notes'          => implode('; ', $logNotes),
            ]);
        }

        return response()->json(['message' => 'Alokasi berhasil disimpan']);
    }
}
