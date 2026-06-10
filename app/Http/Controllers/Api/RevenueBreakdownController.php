<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Opportunity;
use Carbon\Carbon;

class RevenueBreakdownController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'today');

        $query = Opportunity::with(['client:id,company_name', 'sales:id,name'])
            ->where('stage', 'won');

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
            $query->whereIn('sales_id', $teamIds);
        }

        $now = Carbon::now();
        $query = match ($period) {
            'today' => $query->whereDate('actual_close_date', Carbon::today()),
            'week' => $query->whereBetween('actual_close_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'month' => $query->whereBetween('actual_close_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]),
            'year' => $query->whereBetween('actual_close_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]),
            default => $query->whereDate('actual_close_date', Carbon::today()),
        };

        $opportunities = $query->orderByDesc('actual_close_date')
            ->get()
            ->map(function ($opp) {
                return [
                    'id' => $opp->id,
                    'opp_number' => $opp->opp_number,
                    'title' => $opp->title,
                    'client_name' => $opp->client->company_name ?? 'Walk-in Client',
                    'sales_name' => $opp->sales->name ?? 'Unknown',
                    'value' => (float)($opp->final_value ?? $opp->estimated_value ?? 0),
                    'actual_close_date' => $opp->actual_close_date ? $opp->actual_close_date->format('Y-m-d') : null,
                ];
            });

        return response()->json($opportunities);
    }
}
