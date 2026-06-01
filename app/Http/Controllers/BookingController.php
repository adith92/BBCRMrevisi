<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['client', 'vehicle', 'driver', 'sales']);
        
        // Sales only sees own bookings
        if (auth()->user()->role === 'sales') {
            $query->where('sales_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->get();
        $vehicles = Vehicle::where('status', 'available')->get();
        $drivers = Driver::where('status', 'available')->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('bookings.create', compact('clients', 'vehicles', 'drivers', 'salesUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'pickup_datetime' => 'required|date',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Auto-assign sales if logged-in user is sales
        $validated['sales_id'] = auth()->user()->role === 'sales' 
            ? auth()->id() 
            : ($request->sales_id ?? auth()->id());
        
        $validated['created_by'] = auth()->id();
        $validated['booking_number'] = 'BK' . now()->format('YmdHis');
        $validated['status'] = 'pending';

        // Parse price - remove dots (IDR formatting)
        $validated['price'] = str_replace('.', '', $validated['price']);

        $booking = Booking::create($validated);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking berhasil dibuat: ' . $booking->booking_number);
    }

    public function show(Booking $booking)
    {
        // Sales can only see own bookings
        if (auth()->user()->role === 'sales' && $booking->sales_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['client', 'vehicle', 'driver', 'sales', 'invoice']);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        if (auth()->user()->role === 'sales' && $booking->sales_id !== auth()->id()) {
            abort(403);
        }

        $clients = Client::where('status', 'active')->get();
        $vehicles = Vehicle::where('status', 'available')->get();
        $drivers = Driver::where('status', 'available')->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('bookings.edit', compact('booking', 'clients', 'vehicles', 'drivers', 'salesUsers'));
    }

    public function update(Request $request, Booking $booking)
    {
        if (auth()->user()->role === 'sales' && $booking->sales_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'pickup_datetime' => 'required|date',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,on_trip,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['price'] = str_replace('.', '', $validated['price']);
        $booking->update($validated);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil diupdate.');
    }

    public function destroy(Booking $booking)
    {
        if (auth()->user()->role === 'sales' && $booking->sales_id !== auth()->id()) {
            abort(403);
        }

        $booking->delete();

        return redirect()->route('bookings.index')
            ->with('success', 'Booking berhasil dihapus.');
    }

    public function apiShow(Booking $booking)
    {
        if (auth()->user()->role === 'sales' && $booking->sales_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->load(['client', 'vehicle', 'driver', 'sales']);
        
        return response()->json([
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'client' => $booking->client,
            'vehicle' => $booking->vehicle,
            'driver' => $booking->driver,
            'status' => $booking->status,
            'price_formatted' => formatIDR($booking->price),
            'pickup_datetime' => $booking->pickup_datetime,
            'dropoff_datetime' => $booking->dropoff_datetime,
            'destination' => $booking->destination,
        ]);
    }
}
