<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        $clients = Client::with(['assignedSales', 'invoices'])
            ->when($user->isSales(), fn($q) => $q->where('assigned_sales_id', $user->id))
            ->orderBy('company_name')
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $user = auth()->user();

        // Sales can only see their own clients
        if ($user->isSales() && $client->assigned_sales_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Ops cannot access client profiles
        if ($user->isOperational()) {
            abort(403, 'Unauthorized');
        }

        $client->load([
            'assignedSales',
            'invoices.payments',
            'meetingLogs',
        ]);

        $stats = [
            'total_spend'   => $client->invoices->where('status', 'paid')->sum('amount'),
            'total_pending' => $client->invoices->whereIn('status', ['sent', 'draft'])->sum('amount'),
            'total_overdue' => $client->invoices->where('status', 'overdue')->sum('amount'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }
}
