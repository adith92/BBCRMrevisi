<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Invoice;
use App\Models\MaintenanceLog;
use App\Models\MeetingLog;
use App\Models\Pool;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role;
        
        switch ($role) {
            case 'gm':
                return $this->gmDashboard();
            case 'sales':
                return $this->salesDashboard();
            case 'operational':
                return $this->operationalDashboard();
            case 'finance':
                return $this->financeDashboard();
            default:
                abort(403);
        }
    }

    private function gmDashboard()
    {
        $today = now()->format('Y-m-d');
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $yearStart = now()->startOfYear()->format('Y-m-d');

        $dailyRevenue = Invoice::where('status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('amount');
        
        $weeklyRevenue = Invoice::where('status', 'paid')
            ->whereDate('paid_at', '>=', $weekStart)
            ->sum('amount');
        
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereDate('paid_at', '>=', $monthStart)
            ->sum('amount');
        
        $yearlyRevenue = Invoice::where('status', 'paid')
            ->whereDate('paid_at', '>=', $yearStart)
            ->sum('amount');

        $totalBookings = Booking::where('status', 'confirmed')->count();
        $totalClients = Client::where('status', 'active')->count();
        $totalFleet = Vehicle::where('status', 'available')->count();
        $outstandingInvoice = Invoice::whereIn('status', ['sent', 'draft'])->count();

        $recentBookings = Booking::with(['client', 'vehicle', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        $topClients = Client::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        return view('dashboard.gm', compact(
            'dailyRevenue', 'weeklyRevenue', 'monthlyRevenue', 'yearlyRevenue',
            'totalBookings', 'totalClients', 'totalFleet', 'outstandingInvoice',
            'recentBookings', 'topClients'
        ));
    }

    private function salesDashboard()
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $yearStart = now()->startOfYear()->format('Y-m-d');

        $dailyRevenue = Invoice::whereHas('booking', fn($q) => $q->where('sales_id', $user->id))
            ->where('status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('amount');
        
        $weeklyRevenue = Invoice::whereHas('booking', fn($q) => $q->where('sales_id', $user->id))
            ->where('status', 'paid')
            ->whereDate('paid_at', '>=', $weekStart)
            ->sum('amount');
        
        $monthlyRevenue = Invoice::whereHas('booking', fn($q) => $q->where('sales_id', $user->id))
            ->where('status', 'paid')
            ->whereDate('paid_at', '>=', $monthStart)
            ->sum('amount');
        
        $yearlyRevenue = Invoice::whereHas('booking', fn($q) => $q->where('sales_id', $user->id))
            ->where('status', 'paid')
            ->whereDate('paid_at', '>=', $yearStart)
            ->sum('amount');

        $activeBookings = Booking::where('sales_id', $user->id)
            ->whereIn('status', ['confirmed', 'on_trip'])
            ->count();
        
        $totalClients = Client::where('assigned_sales_id', $user->id)->count();
        $pendingBookings = Booking::where('sales_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $recentBookings = Booking::where('sales_id', $user->id)
            ->with(['client', 'vehicle', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        $recentClients = Client::where('assigned_sales_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingFollowUps = MeetingLog::where('sales_id', $user->id)
            ->where('follow_up_date', '>=', $today)
            ->orderBy('follow_up_date')
            ->take(5)
            ->get();

        return view('dashboard.sales', compact(
            'dailyRevenue', 'weeklyRevenue', 'monthlyRevenue', 'yearlyRevenue',
            'activeBookings', 'totalClients', 'pendingBookings',
            'recentBookings', 'recentClients', 'upcomingFollowUps'
        ));
    }

    private function operationalDashboard()
    {
        $availableFleet = Vehicle::where('status', 'available')->count();
        $onTripFleet = Vehicle::where('status', 'on_trip')->count();
        $maintenanceFleet = Vehicle::where('status', 'maintenance')->count();
        $totalDrivers = \App\Models\Driver::count();

        $activeBookings = Booking::with(['vehicle', 'driver', 'client'])
            ->whereIn('status', ['confirmed', 'on_trip'])
            ->get()
            ->map(function ($booking) {
                return [
                    'booking_number' => $booking->booking_number,
                    'client_name' => $booking->client->company_name,
                    'vehicle' => $booking->vehicle->plate_number,
                    'driver' => $booking->driver->name,
                    'pickup_datetime' => $booking->pickup_datetime,
                    'status' => $booking->status,
                ];
            });

        $pools = Pool::withCount('vehicles')->get();
        
        $maintenanceSchedule = MaintenanceLog::with('vehicle')
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date')
            ->take(10)
            ->get();

        return view('dashboard.operational', compact(
            'availableFleet', 'onTripFleet', 'maintenanceFleet', 'totalDrivers',
            'activeBookings', 'pools', 'maintenanceSchedule'
        ));
    }

    private function financeDashboard()
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereDate('paid_at', '>=', $monthStart)
            ->sum('amount');
        
        $pendingInvoices = Invoice::whereIn('status', ['sent', 'draft'])->count();
        $paidThisMonth = Invoice::where('status', 'paid')
            ->whereDate('paid_at', '>=', $monthStart)
            ->count();
        
        $outstanding = Invoice::whereIn('status', ['sent', 'draft'])
            ->sum('amount');

        $recentInvoices = Invoice::with(['booking', 'client'])
            ->latest()
            ->take(10)
            ->get();

        $pendingPayments = Invoice::with('client')
            ->whereIn('status', ['sent', 'draft'])
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $recentTransactions = \App\Models\Payment::with('invoice.client')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.finance', compact(
            'monthlyRevenue', 'pendingInvoices', 'paidThisMonth', 'outstanding',
            'recentInvoices', 'pendingPayments', 'recentTransactions'
        ));
    }
}
