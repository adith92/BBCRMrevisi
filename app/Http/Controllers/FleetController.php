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

        $query = Vehicle::with(['pool', 'assignedOpportunity.client']);

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
                // Assume notes contains maintenance status or add a scope if we had a column. For now, filter in memory or ignore sub-status.
            } else {
                $query->where('status', request('status'));
            }
        }

        $vehicles = $query->orderBy('brand')->get();

        $stats = [
            'total'       => Vehicle::count(),
            'available'   => Vehicle::where('status', 'available')->count(),
            'rented'      => Vehicle::where('status', 'rent_out')->orWhere('status', 'assigned')->count(),
            'booked'      => Vehicle::where('status', 'booked')->count(),
            'hold'        => Vehicle::where('status', 'hold')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'beingServiced'=> Vehicle::where('status', 'maintenance')->where('notes', 'like', '%Servicing%')->count(),
            'inQueue'     => Vehicle::where('status', 'maintenance')->where('notes', 'not like', '%Servicing%')->count(),
        ];

        return view('fleet.index', compact('vehicles', 'stats'));
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
        $query = Vehicle::with('pool')
            ->where('status', 'available');
            
        if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }

    public function apiDriversAvailable()
    {
        $query = \App\Models\Driver::with('pool')
            ->where('status', 'available');

        if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }
}
