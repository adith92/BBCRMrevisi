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

        $drivers = Driver::with('pool')
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'available'   => Driver::where('status', 'available')->count(),
            'assigned'    => Driver::where('status', 'assigned')->count(),
            'reserved'    => Driver::where('status', 'reserved')->count(),
            'inactive'    => Driver::where('status', 'inactive')->count(),
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
