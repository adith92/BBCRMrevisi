<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function getRevenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $role = auth()->user()->role;

        $query = Invoice::where('status', 'paid');

        // Sales only sees own revenue
        if ($role === 'sales') {
            $query->whereHas('booking', fn($q) => $q->where('sales_id', auth()->id()));
        }

        switch ($period) {
            case 'daily':
                $revenue = $query->where('paid_at', '>=', now()->subDays(30))
                    ->select(DB::raw("strftime('%Y-%m-%d', paid_at) as date"), DB::raw('SUM(amount) as total'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(fn($r) => ['date' => $r->date, 'total' => (float)$r->total]);
                break;

            case 'weekly':
                $revenue = $query->where('paid_at', '>=', now()->subWeeks(12))
                    ->select(DB::raw("strftime('%Y-W%W', paid_at) as week"), DB::raw('MIN(date(paid_at)) as date'), DB::raw('SUM(amount) as total'))
                    ->groupBy('week')
                    ->orderBy('week')
                    ->get()
                    ->map(fn($r) => ['date' => $r->week, 'total' => (float)$r->total]);
                break;

            case 'monthly':
                $revenue = $query->where('paid_at', '>=', now()->subMonths(12))
                    ->select(DB::raw("strftime('%Y-%m', paid_at) as month"), DB::raw('SUM(amount) as total'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(fn($r) => ['date' => $r->month, 'total' => (float)$r->total]);
                break;

            case 'yearly':
                $revenue = $query->where('paid_at', '>=', now()->subYears(5))
                    ->select(DB::raw("strftime('%Y', paid_at) as year"), DB::raw('SUM(amount) as total'))
                    ->groupBy('year')
                    ->orderBy('year')
                    ->get()
                    ->map(fn($r) => ['date' => (string)$r->year, 'total' => (float)$r->total]);
                break;

            default:
                $revenue = collect();
        }

        return response()->json($revenue);
    }

    public function getRevenuePerSales()
    {
        // GM only
        if (auth()->user()->role !== 'gm') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $salesRevenue = User::where('role', 'sales')
            ->withCount(['bookings as total_bookings'])
            ->withSum(['bookings as total_revenue' => function($q) {
                $q->whereHas('invoice', fn($i) => $i->where('status', 'paid'))
                  ->join('invoices', 'bookings.id', '=', 'invoices.booking_id')
                  ->select(DB::raw('SUM(invoices.amount)'));
            }])
            ->get()
            ->map(function($user) {
                return [
                    'sales_name' => $user->name,
                    'total_bookings' => $user->total_bookings,
                    'total_revenue' => (float)($user->total_revenue ?? 0),
                    'total_revenue_formatted' => formatIDR($user->total_revenue ?? 0),
                    'avg_per_booking' => $user->total_bookings > 0 
                        ? formatIDR(($user->total_revenue ?? 0) / $user->total_bookings) 
                        : 'Rp 0',
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        return response()->json($salesRevenue);
    }
}
