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

        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $query->where('pool_id', $user->pool_id);
        }

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

        $statsQuery = Driver::query();
        if (($user->isOperational() || $user->isPool()) && $user->pool_id !== null) {
            $statsQuery->where('pool_id', $user->pool_id);
        }

        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'available'   => (clone $statsQuery)->where('status', 'available')->count(),
            'assigned'    => (clone $statsQuery)->where('status', 'assigned')->count(),
            'reserved'    => (clone $statsQuery)->where('status', 'reserved')->count(),
            'leave'       => (clone $statsQuery)->where('status', 'inactive')->count(), // Using inactive as leave
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

    public function store(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        if (!$user->isOperational() && !$user->isManager()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'pool_id' => 'nullable|exists:pools,id',
            'status' => 'required|in:available,inactive',
        ]);

        Driver::create($validated);

        return redirect()->route('drivers.index')->with('success', 'Driver registered successfully.');
    }
}
