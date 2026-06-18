<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Opportunity;

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

        $driverStatusOrder = ['available', 'assigned', 'reserved', 'inactive'];
        $driverStatusSummary = collect($driverStatusOrder)->map(function (string $status) use ($drivers) {
            $count = $drivers->filter(fn ($driver) => strtolower((string) ($driver->status ?? '')) === $status)->count();

            return [
                'status' => $status,
                'label' => ucfirst($status === 'inactive' ? 'leave' : $status),
                'count' => $count,
            ];
        })->values();

        $driverPoolSummary = $drivers
            ->groupBy(fn ($driver) => $driver->pool?->name ?? 'Unassigned')
            ->map(fn ($items, $poolName) => [
                'pool' => $poolName,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(6)
            ->values();

        $pendingAssignments = Opportunity::with(['client', 'sales', 'assignedVehicles', 'assignedDrivers'])
            ->whereIn('stage', ['negotiation', 'won'])
            ->get()
            ->filter(function ($opp) use ($user) {
                $requiredFleets = $opp->demoRequiredFleetQty(10);
                $requiredDrivers = $opp->demoRequiredDriverQty(10);
                $assignedFleets = $opp->assignedVehicles->count();
                $assignedDrivers = $opp->assignedDrivers->count();

                $opp->required_fleets = $requiredFleets;
                $opp->required_drivers = $requiredDrivers;
                $opp->missing_fleets = max(0, $requiredFleets - $assignedFleets);
                $opp->missing_drivers = max(0, $requiredDrivers - $assignedDrivers);
                $opp->fleet_status = $opp->missing_fleets > 0 ? 'pending' : 'fulfilled';
                $opp->driver_status = $opp->missing_drivers > 0 ? 'pending' : 'fulfilled';

                if ($requiredDrivers <= 0) {
                    return false;
                }

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

        $sortPending = request('sort_pending', 'required');
        $direction = request('direction', 'desc');

        if ($sortPending === 'name') {
            $pendingAssignments = $direction === 'desc'
                ? $pendingAssignments->sortByDesc('title')
                : $pendingAssignments->sortBy('title');
        } elseif ($sortPending === 'client') {
            $pendingAssignments = $direction === 'desc'
                ? $pendingAssignments->sortByDesc(fn($opp) => $opp->client->company_name ?? '')
                : $pendingAssignments->sortBy(fn($opp) => $opp->client->company_name ?? '');
        } elseif ($sortPending === 'required') {
            $pendingAssignments = $pendingAssignments->sort(function ($a, $b) use ($direction) {
                $aMissing = (int) ($a->missing_drivers ?? 0);
                $bMissing = (int) ($b->missing_drivers ?? 0);

                if ($aMissing !== $bMissing) {
                    return $direction === 'asc' ? $aMissing <=> $bMissing : $bMissing <=> $aMissing;
                }

                $aDate = strtotime((string) ($a->actual_close_date ?? $a->expected_close_date ?? $a->created_at)) ?: 0;
                $bDate = strtotime((string) ($b->actual_close_date ?? $b->expected_close_date ?? $b->created_at)) ?: 0;

                return $direction === 'asc' ? $aDate <=> $bDate : $bDate <=> $aDate;
            });
        } else {
            $pendingAssignments = $direction === 'desc'
                ? $pendingAssignments->sortByDesc(fn($opp) => $opp->actual_close_date ?? $opp->expected_close_date ?? $opp->created_at)
                : $pendingAssignments->sortBy(fn($opp) => $opp->actual_close_date ?? $opp->expected_close_date ?? $opp->created_at);
        }

        $pendingAssignments = $pendingAssignments->values();

        return view('drivers.index', compact('drivers', 'stats', 'pendingAssignments', 'driverStatusSummary', 'driverPoolSummary'));
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

        if (!$user->isOperational() && !$user->isPool() && !$user->isManager()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'pool_id' => 'nullable|exists:pools,id',
            'status' => 'required|in:available,inactive',
        ]);

        if ($user->isPool()) {
            if ($user->pool_id === null) {
                abort(403, 'Pengguna pool wajib memiliki pool_id.');
            }
            $validated['pool_id'] = $user->pool_id;
        }

        Driver::create($validated);

        return redirect()->route('drivers.index')->with('success', 'Driver registered successfully.');
    }
}
