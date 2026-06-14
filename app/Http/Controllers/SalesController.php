<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function performance(User $user)
    {
        // Only GM can see any sales; Sales can only see their own
        if (auth()->user()->isSales() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized');
        }
        if (auth()->user()->isFinance() || auth()->user()->isOperational()) {
            abort(403, 'Unauthorized');
        }

        $period = request('period', 'monthly');

        $bookings = Booking::where('sales_id', $user->id)
            ->with(['client', 'vehicle'])
            ->orderBy('pickup_datetime', 'desc')
            ->get();

        $clients = Client::where('assigned_sales_id', $user->id)
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->get();

        $stats = [
            'total_revenue'   => $bookings->where('status', 'completed')->sum('price'),
            'total_bookings'  => $bookings->count(),
            'completed'       => $bookings->where('status', 'completed')->count(),
            'active'          => $bookings->whereIn('status', ['confirmed', 'on_trip'])->count(),
            'cancelled'       => $bookings->where('status', 'cancelled')->count(),
            'avg_per_booking' => $bookings->where('status', 'completed')->count()
                ? $bookings->where('status', 'completed')->sum('price') / $bookings->where('status', 'completed')->count()
                : 0,
        ];

        // Chart data by period
        $chartData = $this->getChartData($user->id, $period);

        return view('sales.performance', compact('user', 'bookings', 'clients', 'stats', 'chartData', 'period'));
    }

    private function getChartData(int $userId, string $period): array
    {
        $query = Booking::where('sales_id', $userId)->where('status', 'completed');

        switch ($period) {
            case 'daily':
                $data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('d M'),
                        'value' => (clone $query)->whereDate('pickup_datetime', $date)->sum('price'),
                    ];
                }
                return $data;

            case 'weekly':
                $data = [];
                for ($i = 3; $i >= 0; $i--) {
                    $start = Carbon::now()->subWeeks($i)->startOfWeek();
                    $end   = (clone $start)->endOfWeek();
                    $data[] = [
                        'label' => 'Wk ' . $start->format('d M'),
                        'value' => (clone $query)->whereBetween('pickup_datetime', [$start, $end])->sum('price'),
                    ];
                }
                return $data;

            case 'yearly':
                $data = [];
                for ($i = 2; $i >= 0; $i--) {
                    $year = Carbon::now()->subYears($i)->year;
                    $data[] = [
                        'label' => (string) $year,
                        'value' => (clone $query)->whereYear('pickup_datetime', $year)->sum('price'),
                    ];
                }
                return $data;

            default: // monthly
                $data = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    $data[] = [
                        'label' => $month->format('M Y'),
                        'value' => (clone $query)->whereYear('pickup_datetime', $month->year)->whereMonth('pickup_datetime', $month->month)->sum('price'),
                    ];
                }
                return $data;
        }
    }
}
