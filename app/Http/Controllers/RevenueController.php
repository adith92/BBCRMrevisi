<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RevenueController extends Controller
{
    public function getRevenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $user = auth()->user();

        $query = Booking::where('status', 'completed');
        
        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isFinance()) {
            // Finance lihat aggregate only
        }

        $data = match ($period) {
            'daily' => $this->getDailyRevenue($query),
            'weekly' => $this->getWeeklyRevenue($query),
            'monthly' => $this->getMonthlyRevenue($query),
            'yearly' => $this->getYearlyRevenue($query),
            default => $this->getMonthlyRevenue($query),
        };

        return response()->json($data);
    }

    public function getRevenuePerSales(Request $request)
    {
        abort_if(!auth()->user()->isGM(), 403);

        $data = Booking::where('status', 'completed')
            ->with('sales:id,name')
            ->selectRaw('sales_id, SUM(price) as total_revenue, COUNT(*) as total_bookings')
            ->groupBy('sales_id')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function($item) {
                return [
                    'sales_id' => $item->sales_id,
                    'sales_name' => $item->sales->name ?? 'Unknown',
                    'total_revenue' => (int)$item->total_revenue,
                    'total_bookings' => $item->total_bookings,
                    'avg_per_booking' => (int)($item->total_revenue / $item->total_bookings),
                ];
            });

        return response()->json($data);
    }

    private function getDailyRevenue($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw("DATE(created_at) as date, SUM(price) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getWeeklyRevenue($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subWeeks(12))
            ->selectRaw("STRFTIME('%Y-%W', created_at) as week, SUM(price) as total")
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }

    private function getMonthlyRevenue($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw("STRFTIME('%Y-%m', created_at) as month, SUM(price) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getYearlyRevenue($query)
    {
        return $query->selectRaw("STRFTIME('%Y', created_at) as year, SUM(price) as total")
            ->groupBy('year')
            ->orderBy('year')
            ->get();
    }
}
