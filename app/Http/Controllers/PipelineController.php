<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $stages = ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'won', 'lost'];

        // Build base query scoped by role
        $baseQuery = Opportunity::with(['client:id,company_name', 'sales:id,name'])
            ->when($user->isSales(), fn ($q) => $q->where('sales_id', $user->id))
            ->when($user->isManager(), function ($q) use ($user) {
                $teamIds = User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
                $q->whereIn('sales_id', $teamIds);
            });

        // Filter by sales (manager/gm/director only)
        if (!$user->isSales() && $request->filled('filter_sales')) {
            $baseQuery->where('sales_id', $request->filter_sales);
        }

        // Filter by month (previous, current, next)
        $filterMonth = $request->get('filter_month', 'all');
        if (in_array($filterMonth, ['previous', 'current', 'next'])) {
            $date = match ($filterMonth) {
                'previous' => now()->subMonth(),
                'current'  => now(),
                'next'     => now()->addMonth(),
            };
            $targetMonth = $date->month;
            $targetYear = $date->year;

            $baseQuery->where(function ($q) use ($targetMonth, $targetYear) {
                $q->where(function ($sq) use ($targetMonth, $targetYear) {
                    $sq->whereMonth('expected_close_date', $targetMonth)
                       ->whereYear('expected_close_date', $targetYear);
                })->orWhere(function ($sq) use ($targetMonth, $targetYear) {
                    $sq->whereMonth('actual_close_date', $targetMonth)
                       ->whereYear('actual_close_date', $targetYear);
                });
            });
        }

        // Filter by year
        $filterYear = $request->get('filter_year', 'all');
        if ($filterYear !== 'all') {
            $baseQuery->where(function ($q) use ($filterYear) {
                $q->whereYear('expected_close_date', $filterYear)
                  ->orWhereYear('actual_close_date', $filterYear);
            });
        }

        // Sort within each column
        $sortBy = $request->get('sort_by', 'updated');
        $baseQuery = match ($sortBy) {
            'value_desc' => $baseQuery->orderByDesc('estimated_value'),
            'value_asc'  => $baseQuery->orderBy('estimated_value'),
            'close_date' => $baseQuery->orderBy('expected_close_date'),
            'newest'     => $baseQuery->orderByDesc('created_at'),
            default      => $baseQuery->orderByDesc('updated_at'),
        };

        // Paginated opportunities for Alpine.js (to have ->items() method)
        $opportunities = $baseQuery->paginate(100);
        $opportunities->getCollection()->transform(function ($opp) {
            $opp->makeHidden(['history_timeline', 'notes']);
            return $opp;
        });

        // Fetch clients scoped by user role
        $clients = \App\Models\Client::when($user->isSales(), fn($q) => $q->where('assigned_sales_id', $user->id))
            ->orderBy('company_name')
            ->get();

        // Sales users for filter dropdown
        $salesUsers = collect();
        if (!$user->isSales()) {
            if ($user->isManager()) {
                $salesUsers = User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get();
            } else {
                $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();
            }
        }

        return view('pipeline.index', compact('stages', 'salesUsers', 'sortBy', 'clients', 'opportunities', 'filterMonth', 'filterYear'));
    }
}
