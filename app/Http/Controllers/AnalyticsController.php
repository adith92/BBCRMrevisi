<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Client;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SalesTarget;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Pipeline value by stage
        $pipelineByStage = Opportunity::select('stage', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(estimated_value, 0)) as total_value'))
            ->whereNotIn('stage', ['won', 'lost'])
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        // Top clients by revenue (paid invoices)
        $topClients = Client::with('invoices')
            ->withSum(['invoices as total_revenue' => fn($q) => $q->where('status', 'paid')], 'amount')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Top sales by won deals
        $topSales = User::whereIn('role', ['sales', 'manager'])
            ->withCount(['opportunities as won_count' => fn($q) => $q->where('stage', 'won')])
            ->withSum(['opportunities as won_revenue' => fn($q) => $q->where('stage', 'won')], 'final_value')
            ->orderByDesc('won_count')
            ->limit(10)
            ->get();

        // Recent activity summary (last 30 days)
        $activitySummary = ActivityLog::select('type', DB::raw('COUNT(*) as count'))
            ->where('activity_date', '>=', Carbon::now()->subDays(30))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Cross-sell opportunity count
        $crossSellCount = $this->getCrossSellCounts();

        return view('analytics.index', compact(
            'pipelineByStage',
            'topClients',
            'topSales',
            'activitySummary',
            'crossSellCount'
        ));
    }

    public function crosssell()
    {
        // Get all clients and which product category types they have purchased via opportunities
        $clients = Client::with([
            'opportunities' => fn($q) => $q->where('stage', 'won')->with('product.productCategory'),
        ])->get();

        $shortTermOnly   = collect();
        $longTermOnly    = collect();
        $evoucherOnly    = collect();
        $shortAndLong    = collect();
        $shortAndEv      = collect();
        $longAndEv       = collect();
        $allThree        = collect();
        $none            = collect();

        foreach ($clients as $client) {
            $types = $client->opportunities
                ->map(fn($o) => optional(optional($o->product)->productCategory)->type)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $hasShort = in_array('short_term', $types);
            $hasLong  = in_array('long_term', $types);
            $hasEv    = in_array('evoucher', $types);

            if ($hasShort && $hasLong && $hasEv) {
                $allThree->push($client);
            } elseif ($hasShort && $hasLong) {
                $shortAndLong->push($client);
            } elseif ($hasShort && $hasEv) {
                $shortAndEv->push($client);
            } elseif ($hasLong && $hasEv) {
                $longAndEv->push($client);
            } elseif ($hasShort) {
                $shortTermOnly->push($client);
            } elseif ($hasLong) {
                $longTermOnly->push($client);
            } elseif ($hasEv) {
                $evoucherOnly->push($client);
            } else {
                $none->push($client);
            }
        }

        // Table data: each client with boolean flags
        $clientTable = $clients->map(function ($client) {
            $types = $client->opportunities
                ->map(fn($o) => optional(optional($o->product)->productCategory)->type)
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            return [
                'client'     => $client,
                'short_term' => in_array('short_term', $types),
                'long_term'  => in_array('long_term', $types),
                'evoucher'   => in_array('evoucher', $types),
            ];
        })->sortByDesc(fn($r) => (int)$r['short_term'] + (int)$r['long_term'] + (int)$r['evoucher']);

        return view('analytics.crosssell', compact(
            'shortTermOnly', 'longTermOnly', 'evoucherOnly',
            'shortAndLong', 'shortAndEv', 'longAndEv',
            'allThree', 'none', 'clientTable'
        ));
    }

    public function pipeline()
    {
        $stages = ['prospecting', 'proposal', 'negotiation', 'won', 'lost'];

        // Count and value per stage
        $stageData = Opportunity::select(
                'stage',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(COALESCE(estimated_value, 0)) as total_value'),
                DB::raw('AVG(COALESCE(estimated_value, 0)) as avg_value')
            )
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        // Average days in each stage (approximated via timestamps)
        $avgDays = [];
        foreach ($stages as $stage) {
            $avgDays[$stage] = 0;
        }

        // Conversion rates between stages
        $counts = [];
        foreach ($stages as $stage) {
            $counts[$stage] = $stageData[$stage]->count ?? 0;
        }

        $conversionRates = [];
        $stageList = ['prospecting', 'proposal', 'negotiation'];
        foreach ($stageList as $i => $stage) {
            $next = $stageList[$i + 1] ?? null;
            if ($next) {
                $from = $counts[$stage] + $counts[$next];
                $conversionRates["{$stage}_to_{$next}"] = $from > 0
                    ? round(($counts[$next] / $from) * 100, 1)
                    : 0;
            }
        }

        // Won rate from negotiation
        $negotiationTotal = ($counts['negotiation'] ?? 0) + ($counts['won'] ?? 0) + ($counts['lost'] ?? 0);
        $conversionRates['negotiation_to_won'] = $negotiationTotal > 0
            ? round((($counts['won'] ?? 0) / $negotiationTotal) * 100, 1)
            : 0;

        // Overall win rate
        $total = array_sum($counts);
        $overallWinRate = $total > 0 ? round((($counts['won'] ?? 0) / $total) * 100, 1) : 0;

        return view('analytics.pipeline', compact(
            'stages', 'stageData', 'counts', 'conversionRates', 'overallWinRate'
        ));
    }

    public function salesPerformance()
    {
        $now = Carbon::now();
        $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();

        $performance = $salesUsers->map(function ($user) use ($now) {
            $opportunities = Opportunity::where('sales_id', $user->id);
            $total   = (clone $opportunities)->count();
            $won     = (clone $opportunities)->where('stage', 'won')->count();
            $lost    = (clone $opportunities)->where('stage', 'lost')->count();
            $revenue = (clone $opportunities)->where('stage', 'won')->sum('final_value');

            $winRate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;

            // KPI achievement for current month
            $target = SalesTarget::where('user_id', $user->id)
                ->where('period_year', $now->year)
                ->where('period_month', $now->month)
                ->first();

            $kpiPct = 0;
            if ($target && $target->target_revenue > 0) {
                $kpiPct = round(($target->actual_revenue / $target->target_revenue) * 100, 1);
            }

            return [
                'user'              => $user,
                'total_opportunities' => $total,
                'won'               => $won,
                'lost'              => $lost,
                'win_rate'          => $winRate,
                'revenue'           => $revenue,
                'kpi_pct'           => $kpiPct,
                'target'            => $target,
            ];
        })->sortByDesc('won');

        return view('analytics.sales', compact('performance', 'now'));
    }

    private function getCrossSellCounts(): array
    {
        $clients = Client::with([
            'opportunities' => fn($q) => $q->where('stage', 'won')->with('product.productCategory'),
        ])->get();

        $counts = [
            'short_term_only' => 0,
            'long_term_only'  => 0,
            'evoucher_only'   => 0,
            'short_and_long'  => 0,
            'short_and_ev'    => 0,
            'long_and_ev'     => 0,
            'all_three'       => 0,
            'none'            => 0,
        ];

        foreach ($clients as $client) {
            $types = $client->opportunities
                ->map(fn($o) => optional(optional($o->product)->productCategory)->type)
                ->filter()->unique()->values()->toArray();

            $hasS = in_array('short_term', $types);
            $hasL = in_array('long_term', $types);
            $hasE = in_array('evoucher', $types);

            if ($hasS && $hasL && $hasE) $counts['all_three']++;
            elseif ($hasS && $hasL) $counts['short_and_long']++;
            elseif ($hasS && $hasE) $counts['short_and_ev']++;
            elseif ($hasL && $hasE) $counts['long_and_ev']++;
            elseif ($hasS) $counts['short_term_only']++;
            elseif ($hasL) $counts['long_term_only']++;
            elseif ($hasE) $counts['evoucher_only']++;
            else $counts['none']++;
        }

        return $counts;
    }
}
