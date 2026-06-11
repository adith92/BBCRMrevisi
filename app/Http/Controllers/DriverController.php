<?php

namespace App\Http\Controllers;

use App\Models\Driver;

class DriverController extends Controller
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

        $query = Driver::with(['pool', 'assignedOpportunity.client']);

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if (request('location') && request('location') !== 'All') {
            $loc = request('location');
            $query->whereHas('pool', function($q) use ($loc) {
                $q->where('name', 'like', "%{$loc}%");
            });
        }

        if (request('status') && request('status') !== 'All') {
            $query->where('status', request('status'));
        }

        $drivers = $query->orderBy('name')->get();

        $stats = [
            'total'       => Driver::count(),
            'available'   => Driver::where('status', 'available')->count(),
            'assigned'    => Driver::where('status', 'assigned')->count(),
            'reserved'    => Driver::where('status', 'reserved')->count(),
            'leave'       => Driver::where('status', 'inactive')->count(), // Using inactive as leave
        ];

        return view('drivers.index', compact('drivers', 'stats'));
    }

    public function show(Driver $driver)
    {
        $user = auth()->user();

        if ($user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $driver->load(['pool']);

        // Since driver only has basic data, we just return the view.
        // We can add bookings related to driver later if needed.
        return view('drivers.show', compact('driver'));
    }
}
