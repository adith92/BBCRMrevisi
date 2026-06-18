<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Booking::with(['client', 'sales', 'vehicle', 'driver'])
            ->when(!$user->isGM() && !$user->isOperational() && !$user->isFinance(), fn($q) => $q->where('sales_id', $user->id))
            ->when(request('client_id'), fn($q, $id) => $q->where('client_id', $id))
            ->when(request('vehicle_id'), fn($q, $id) => $q->where('vehicle_id', $id))
            ->when(request('sales_id'), fn($q, $id) => $q->where('sales_id', $id))
            ->when(request('status') === 'active', fn($q) => $q->whereIn('status', ['confirmed', 'on_trip']))
            ->when(request('status') && request('status') !== 'active', fn($q) => $q->where('status', request('status')))
            ->latest();

        $bookings = $query->paginate(20);

        $summaryQuery = Booking::query()
            ->when(!$user->isGM() && !$user->isOperational() && !$user->isFinance(), fn($q) => $q->where('sales_id', $user->id))
            ->when(request('client_id'), fn($q, $id) => $q->where('client_id', $id))
            ->when(request('vehicle_id'), fn($q, $id) => $q->where('vehicle_id', $id))
            ->when(request('sales_id'), fn($q, $id) => $q->where('sales_id', $id));

        $statusSummary = (clone $summaryQuery)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(price) as revenue'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'label' => str($row->status)->replace('_', ' ')->title()->toString(),
                'status' => $row->status,
                'count' => (int) $row->total,
                'revenue' => (float) $row->revenue,
            ]);

        $dailyRows = (clone $summaryQuery)
            ->whereBetween('pickup_datetime', [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()])
            ->select(DB::raw('DATE(pickup_datetime) as day_key'), DB::raw('COUNT(*) as total'), DB::raw('SUM(price) as revenue'))
            ->groupBy(DB::raw('DATE(pickup_datetime)'))
            ->pluck('total', 'day_key');

        $bookingTrend = collect(range(6, 0))->map(function ($offset) use ($dailyRows) {
            $date = Carbon::today()->subDays($offset);
            return [
                'label' => $date->translatedFormat('d M'),
                'count' => (int) ($dailyRows[$date->format('Y-m-d')] ?? 0),
            ];
        });

        return view('bookings.index', compact('bookings', 'statusSummary', 'bookingTrend'));
    }

    public function create()
    {
        $user = auth()->user();
        $clients = $user->isSales() ? Client::where('assigned_sales_id', $user->id)->get() : Client::all();
        $vehicles = Vehicle::where('status', 'available')->get();
        $drivers = Driver::where('status', 'available')->get();
        $sales = User::where('role', 'sales')->get();

        return view('bookings.create', compact('clients', 'vehicles', 'drivers', 'sales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'pickup_datetime' => 'required|date',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
            'destination' => 'required|string',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        if ($user->isSales()) {
            $validated['sales_id'] = $user->id;
        } else {
            $validated['sales_id'] = $request->input('sales_id', $user->id);
        }

        $validated['created_by'] = $user->id;
        $validated['booking_number'] = 'BB-' . date('Ymd') . '-' . str_pad(Booking::count() + 1, 3, '0', STR_PAD_LEFT);
        $validated['vehicle_type'] = Vehicle::find($validated['vehicle_id'])->brand;
        $validated['status'] = 'pending';

        Booking::create($validated);
        return redirect()->route('bookings.index')->with('success', 'Booking created successfully!');
    }

    public function show(Booking $booking)
    {
        if (auth()->user()->isSales() && $booking->sales_id !== auth()->id()) {
            abort(403);
        }
        return view('bookings.show', compact('booking'));
    }
}
