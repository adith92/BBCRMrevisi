<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\MeetingLog;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('assignedSales');
        
        if (auth()->user()->role === 'sales') {
            $query->where('assigned_sales_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients = $query->latest()->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();
        return view('clients.create', compact('salesUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'status' => 'required|in:active,prospect,inactive',
            'assigned_sales_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if (auth()->user()->role === 'sales') {
            $validated['assigned_sales_id'] = auth()->id();
        }

        $client = Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client berhasil ditambahkan: ' . $client->company_name);
    }

    public function show(Client $client)
    {
        if (auth()->user()->role === 'sales' && $client->assigned_sales_id !== auth()->id()) {
            abort(403);
        }

        $client->load(['assignedSales', 'bookings', 'meetingLogs']);
        $meetingLogs = $client->meetingLogs()->with('sales')->latest()->get();
        $bookings = $client->bookings()->with(['vehicle', 'driver'])->latest()->get();

        return view('clients.show', compact('client', 'meetingLogs', 'bookings'));
    }

    public function edit(Client $client)
    {
        if (auth()->user()->role === 'sales' && $client->assigned_sales_id !== auth()->id()) {
            abort(403);
        }

        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();
        return view('clients.edit', compact('client', 'salesUsers'));
    }

    public function update(Request $request, Client $client)
    {
        if (auth()->user()->role === 'sales' && $client->assigned_sales_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'status' => 'required|in:active,prospect,inactive',
            'assigned_sales_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client berhasil diupdate.');
    }

    public function destroy(Client $client)
    {
        if (auth()->user()->role === 'sales' && $client->assigned_sales_id !== auth()->id()) {
            abort(403);
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client berhasil dihapus.');
    }

    public function apiShow(Client $client)
    {
        if (auth()->user()->role === 'sales' && $client->assigned_sales_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $client->id,
            'company_name' => $client->company_name,
            'pic_name' => $client->pic_name,
            'phone' => $client->phone,
            'email' => $client->email,
            'industry' => $client->industry,
            'status' => $client->status,
            'address' => $client->address,
            'bookings_count' => $client->bookings()->count(),
            'meeting_logs_count' => $client->meetingLogs()->count(),
        ]);
    }
}
