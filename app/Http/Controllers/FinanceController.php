<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Booking;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isOperational()) {
            abort(403, 'Unauthorized');
        }

        $filter = request('filter', 'all');
        $status = request('status');

        $invoices = Invoice::with(['client', 'booking.sales', 'payments'])
            ->when($user->isSales(), function ($q) use ($user) {
                $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id));
            })
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->when($filter === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($filter === 'week', fn($q) => $q->where('created_at', '>=', now()->startOfWeek()))
            ->when($filter === 'month', fn($q) => $q->where('created_at', '>=', now()->startOfMonth()))
            ->when($filter === 'year', fn($q) => $q->where('created_at', '>=', now()->startOfYear()))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $summary = [
            'total'   => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->sum('amount'),
            'paid'    => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->where('status', 'paid')->sum('amount'),
            'pending' => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->where('status', 'sent')->sum('amount'),
            'overdue' => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->where('status', 'overdue')->sum('amount'),
            'paid_count'    => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->where('status', 'paid')->count(),
            'overdue_count' => Invoice::when($user->isSales(), fn($q) => $q->whereHas('booking', fn($b) => $b->where('sales_id', $user->id)))->where('status', 'overdue')->count(),
        ];

        return view('finance.index', compact('invoices', 'summary', 'filter', 'status'));
    }

    public function show(Invoice $invoice)
    {
        $user = auth()->user();

        if ($user->isSales() && $invoice->booking?->sales_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if ($user->isOperational()) {
            abort(403, 'Unauthorized');
        }

        $invoice->load(['client', 'booking.vehicle', 'booking.sales', 'booking.driver', 'payments']);

        return view('finance.show', compact('invoice'));
    }
}
