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

    public function index(Request $request)
    {
        $user = auth()->user();

        $baseQuery = Client::query()
            ->withCount(['opportunities as won_opportunities_count' => function ($q) {
                $q->where('stage', 'won');
            }])
            ->withSum(['opportunities as won_opportunities_sum' => function ($q) {
                $q->where('stage', 'won');
            }], 'final_value')
            ->when($user->isSales(), fn($q) => $q->where('assigned_sales_id', $user->id));

        $query = (clone $baseQuery)->with(['assignedSales', 'invoices']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhereHas('assignedSales', function ($salesQuery) use ($search) {
                        $salesQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->filled('filter_status')) {
            $status = $request->input('filter_status');
            if (in_array($status, ['active', 'inactive', 'prospect'])) {
                $query->where('status', $status);
            }
        }

        // Sort
        $sortBy = $request->input('sort_by', 'name_asc');
        if ($sortBy === 'transactions_desc') {
            $query->orderByDesc('won_opportunities_count');
        } elseif ($sortBy === 'value_desc') {
            $query->orderByDesc('won_opportunities_sum');
        } elseif ($sortBy === 'name_desc') {
            $query->orderBy('company_name', 'desc');
        } else {
            $query->orderBy('company_name', 'asc');
        }

        $clients = $query->paginate(20)->withQueryString();

        if (in_array($sortBy, ['transactions_desc', 'value_desc'], true)) {
            $sortedPageItems = $clients->getCollection()->sort(function ($left, $right) use ($sortBy) {
                $leftMetric = $sortBy === 'transactions_desc'
                    ? (int) ($left->won_opportunities_count ?? 0)
                    : (float) ($left->won_opportunities_sum ?? 0);

                $rightMetric = $sortBy === 'transactions_desc'
                    ? (int) ($right->won_opportunities_count ?? 0)
                    : (float) ($right->won_opportunities_sum ?? 0);

                if ($leftMetric === $rightMetric) {
                    return strcasecmp($left->company_name, $right->company_name);
                }

                return $rightMetric <=> $leftMetric;
            })->values();

            $clients->setCollection($sortedPageItems);
        }

        $summarySource = (clone $baseQuery)->get(['id', 'company_name', 'status', 'industry']);

        $summary = [
            'total_clients' => $summarySource->count(),
            'active_clients' => $summarySource->where('status', 'active')->count(),
            'prospect_clients' => $summarySource->where('status', 'prospect')->count(),
            'inactive_clients' => $summarySource->where('status', 'inactive')->count(),
            'active_revenue' => (clone $baseQuery)->where('status', 'active')->sum('won_opportunities_sum'),
            'at_risk_clients' => $summarySource
                ->filter(fn($client) => $client->status !== 'active' || ((int) ($client->won_opportunities_count ?? 0) === 0))
                ->count(),
            'top_industry' => $summarySource
                ->filter(fn($client) => filled($client->industry))
                ->groupBy('industry')
                ->sortByDesc(fn($group) => $group->count())
                ->keys()
                ->first() ?? 'Belum ada',
        ];

        $industryRevenue = (clone $baseQuery)
            ->get(['industry'])
            ->filter(fn($client) => filled($client->industry) && (float) ($client->won_opportunities_sum ?? 0) > 0)
            ->groupBy('industry')
            ->map(function ($group, $industry) {
                return [
                    'label' => $industry,
                    'value' => (float) $group->sum(fn($client) => $client->won_opportunities_sum ?? 0),
                ];
            })
            ->sortByDesc('value')
            ->take(4)
            ->values();

        $statusBreakdown = collect([
            ['label' => 'Active', 'value' => $summary['active_clients'], 'tone' => 'emerald'],
            ['label' => 'Prospect', 'value' => $summary['prospect_clients'], 'tone' => 'amber'],
            ['label' => 'At Risk', 'value' => $summary['at_risk_clients'], 'tone' => 'rose'],
            ['label' => 'Inactive', 'value' => $summary['inactive_clients'], 'tone' => 'slate'],
        ]);

        $sales = [];
        if ($user->isManager()) {
            $sales = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get();
        }

        return view('clients.index', compact('clients', 'sales', 'summary', 'industryRevenue', 'statusBreakdown'));
    }

    public function show(Client $client)
    {
        $user = auth()->user();

        // Sales can view assigned clients plus clients connected to their own bookings/opportunities.
        if ($user->isSales()
            && $client->assigned_sales_id !== $user->id
            && ! $client->bookings()->where('sales_id', $user->id)->exists()
            && ! $client->opportunities()->where('sales_id', $user->id)->exists()) {
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
            'opportunities' => function ($query) use ($user, $client) {
                if ($user->isSales()) {
                    if ($client->assigned_sales_id !== $user->id) {
                        $query->where('sales_id', $user->id);
                    }
                } elseif ($user->isManager()) {
                    $teamIds = \App\Models\User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
                    $query->whereIn('sales_id', $teamIds);
                }
            },
            'opportunities.product',
        ]);

        $stats = [
            'total_spend'   => $client->invoices->where('status', 'paid')->sum('amount'),
            'total_pending' => $client->invoices->whereIn('status', ['sent', 'draft'])->sum('amount'),
            'total_overdue' => $client->invoices->where('status', 'overdue')->sum('amount'),
            'won_deals_count' => $client->opportunities->where('stage', 'won')->count(),
            'won_deals_sum'   => $client->opportunities->where('stage', 'won')->sum('final_value'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSales() && !$user->isManager()) {
            abort(403, 'Hanya Sales Executive dan Sales Manager yang dapat mendaftarkan Client.');
        }

        $validated = $request->validate([
            'company_name'      => 'required|string|max:255|unique:clients,company_name',
            'pic_name'          => 'required|string|max:255',
            'phone'             => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'address'           => 'required|string|max:255',
            'industry'          => 'nullable|string|max:255',
            'status'            => 'nullable|in:active,prospect,inactive',
            'assigned_sales_id' => 'nullable|exists:users,id',
            'notes'             => 'nullable|string',
        ]);

        if (empty($validated['assigned_sales_id'])) {
            $validated['assigned_sales_id'] = auth()->id();
        }

        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        $client = Client::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'client' => $client], 201);
        }

        return redirect()->route('clients.index')->with('success', 'Client berhasil didaftarkan.');
    }
}
