<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Opportunity;
use App\Models\ApprovalRequest;
use App\Models\ActivityLog;
use App\Models\SalesTarget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::now()->startOfDay();
        $week  = Carbon::now()->subDays(7);
        $month = Carbon::now()->subMonth();
        $year  = Carbon::now()->subYear();

        if ($user->isDirector()) {
            $pipelineValue = Opportunity::whereNotIn('stage', ['won', 'lost'])->sum('estimated_value');
            $wonTotal      = Opportunity::where('stage', 'won')->count();
            $lostTotal     = Opportunity::where('stage', 'lost')->count();
            $winRate       = ($wonTotal + $lostTotal) > 0
                ? round(($wonTotal / ($wonTotal + $lostTotal)) * 100, 1)
                : 0;

            $pendingApprovals = class_exists(ApprovalRequest::class)
                ? ApprovalRequest::where('status', 'pending')->count()
                : 0;

            $revenueMTD = Invoice::where('status', 'paid')
                ->where('paid_at', '>=', Carbon::now()->startOfMonth())
                ->sum('amount');

            // Team performance per sales
            $salesTeam = User::whereIn('role', ['sales', 'manager'])
                ->withCount(['opportunities as pipeline_count' => fn($q) => $q->whereNotIn('stage', ['won', 'lost'])])
                ->withCount(['opportunities as won_count' => fn($q) => $q->where('stage', 'won')])
                ->withSum(['opportunities as won_revenue' => fn($q) => $q->where('stage', 'won')], 'final_value')
                ->orderByDesc('won_count')
                ->get()
                ->map(function ($u) {
                    $won  = $u->won_count ?? 0;
                    $lost = Opportunity::where('sales_id', $u->id)->where('stage', 'lost')->count();
                    $u->win_rate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;

                    // KPI % for current month
                    $now    = Carbon::now();
                    $target = SalesTarget::where('user_id', $u->id)
                        ->where('period_year', $now->year)
                        ->where('period_month', $now->month)
                        ->first();
                    $u->kpi_pct = ($target && $target->target_revenue > 0)
                        ? round(($target->actual_revenue / $target->target_revenue) * 100, 1)
                        : 0;

                    return $u;
                });

            // Top 5 pending approvals
            $approvalQueue = class_exists(ApprovalRequest::class)
                ? ApprovalRequest::where('status', 'pending')
                    ->with(['opportunity.client', 'requestedBy'])
                    ->orderBy('created_at')
                    ->limit(5)
                    ->get()
                : collect();

            return view('dashboard.director', compact(
                'pipelineValue', 'winRate', 'pendingApprovals', 'revenueMTD',
                'salesTeam', 'approvalQueue'
            ));
        }

        if ($user->isManager()) {
            // Team = sales users whose manager_id = current user
            $teamIds = User::where('manager_id', $user->id)->pluck('id');

            $teamPipelineValue = Opportunity::whereIn('sales_id', $teamIds)
                ->whereNotIn('stage', ['won', 'lost'])
                ->sum('estimated_value');

            $teamWon  = Opportunity::whereIn('sales_id', $teamIds)->where('stage', 'won')->count();
            $teamLost = Opportunity::whereIn('sales_id', $teamIds)->where('stage', 'lost')->count();

            $pendingApprovals = class_exists(ApprovalRequest::class)
                ? ApprovalRequest::where('status', 'pending')->where('level', 1)->count()
                : 0;

            // Team members with stage breakdown
            $teamMembers = User::whereIn('id', $teamIds)
                ->withCount(['opportunities as pipeline_count' => fn($q) => $q->whereNotIn('stage', ['won', 'lost'])])
                ->withCount(['opportunities as won_count' => fn($q) => $q->where('stage', 'won')])
                ->withSum(['opportunities as won_revenue' => fn($q) => $q->where('stage', 'won')], 'final_value')
                ->get()
                ->map(function ($u) {
                    $won  = $u->won_count ?? 0;
                    $lost = Opportunity::where('sales_id', $u->id)->where('stage', 'lost')->count();
                    $u->win_rate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;

                    $now    = Carbon::now();
                    $target = SalesTarget::where('user_id', $u->id)
                        ->where('period_year', $now->year)
                        ->where('period_month', $now->month)
                        ->first();
                    $u->kpi_pct = ($target && $target->target_revenue > 0)
                        ? round(($target->actual_revenue / $target->target_revenue) * 100, 1)
                        : 0;
                    $u->target = $target;

                    return $u;
                });

            // KPI table for team
            $now    = Carbon::now();
            $kpiTargets = SalesTarget::whereIn('user_id', $teamIds)
                ->where('period_year', $now->year)
                ->where('period_month', $now->month)
                ->with('user')
                ->get();

            // Pending level-1 approvals
            $approvalQueue = class_exists(ApprovalRequest::class)
                ? ApprovalRequest::where('status', 'pending')
                    ->where('level', 1)
                    ->with(['opportunity.client', 'requestedBy'])
                    ->orderBy('created_at')
                    ->limit(5)
                    ->get()
                : collect();

            // Recent team activities
            $recentActivities = ActivityLog::whereIn('sales_id', $teamIds)
                ->with(['sales', 'client'])
                ->orderByDesc('activity_date')
                ->limit(10)
                ->get();

            // Stage breakdown per sales for pipeline bar
            $stages = ['prospecting', 'proposal', 'negotiation'];
            $stageBreakdown = [];
            foreach ($teamIds as $tid) {
                $member = User::find($tid);
                if (!$member) continue;
                $row = ['name' => $member->name, 'totals' => []];
                foreach ($stages as $s) {
                    $row['totals'][$s] = Opportunity::where('sales_id', $tid)->where('stage', $s)->count();
                }
                $stageBreakdown[] = $row;
            }

            return view('dashboard.manager', compact(
                'teamPipelineValue', 'teamWon', 'teamLost',
                'pendingApprovals', 'teamMembers', 'kpiTargets',
                'approvalQueue', 'recentActivities', 'stageBreakdown', 'stages'
            ));
        }

        if ($user->isGM()) {
            $todayRevenue  = Booking::where('status', 'completed')->whereDate('created_at', $today)->sum('price');
            $weekRevenue   = Booking::where('status', 'completed')->where('created_at', '>=', $week)->sum('price');
            $monthRevenue  = Booking::where('status', 'completed')->where('created_at', '>=', $month)->sum('price');
            $yearRevenue   = Booking::where('status', 'completed')->where('created_at', '>=', $year)->sum('price');
            $activeBookings   = Booking::whereIn('status', ['confirmed', 'on_trip'])->count();
            $totalClients     = Client::count();
            $totalFleet       = Vehicle::count();
            $outstandingCount = Invoice::where('status', 'overdue')->count();
            $outstandingAmt   = Invoice::where('status', 'overdue')->sum('amount');

            // Top 5 Sales by revenue
            $topSales = User::where('role', 'sales')
                ->withSum(['bookings as total_revenue' => fn($q) => $q->where('status', 'completed')], 'price')
                ->withCount('bookings')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();

            // Top 5 Clients by spend
            $topClients = Client::withSum(['invoices as total_spend' => fn($q) => $q->where('status', 'paid')], 'amount')
                ->withCount('bookings')
                ->orderByDesc('total_spend')
                ->limit(5)
                ->get();

            return view('dashboard.gm', compact(
                'todayRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
                'activeBookings', 'totalClients', 'totalFleet',
                'outstandingCount', 'outstandingAmt',
                'topSales', 'topClients'
            ));
        }

        if ($user->isSales()) {
            $todayRevenue  = Booking::where('sales_id', $user->id)->where('status', 'completed')->whereDate('created_at', $today)->sum('price');
            $weekRevenue   = Booking::where('sales_id', $user->id)->where('status', 'completed')->where('created_at', '>=', $week)->sum('price');
            $monthRevenue  = Booking::where('sales_id', $user->id)->where('status', 'completed')->where('created_at', '>=', $month)->sum('price');
            $yearRevenue   = Booking::where('sales_id', $user->id)->where('status', 'completed')->where('created_at', '>=', $year)->sum('price');
            $activeBookings = Booking::where('sales_id', $user->id)->whereIn('status', ['confirmed', 'on_trip'])->count();
            $myClients      = Client::where('assigned_sales_id', $user->id)->count();

            $recentBookings = Booking::where('sales_id', $user->id)
                ->with(['client', 'vehicle'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard.sales', compact(
                'todayRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
                'activeBookings', 'myClients', 'recentBookings'
            ));
        }

        if ($user->isOperational()) {
            $availableFleet  = Vehicle::where('status', 'available')->count();
            $onTripFleet     = Vehicle::where('status', 'on_trip')->count();
            $maintenanceFleet = Vehicle::where('status', 'maintenance')->count();
            $activeBookings  = Booking::whereIn('status', ['confirmed', 'on_trip'])->count();

            $activeBookingList = Booking::whereIn('status', ['confirmed', 'on_trip'])
                ->with(['client', 'vehicle', 'driver'])
                ->orderBy('pickup_datetime')
                ->limit(10)
                ->get();

            return view('dashboard.operational', compact(
                'availableFleet', 'onTripFleet', 'maintenanceFleet',
                'activeBookings', 'activeBookingList'
            ));
        }

        if ($user->isFinance()) {
            $todayRevenue   = Booking::where('status', 'completed')->whereDate('created_at', $today)->sum('price');
            $monthRevenue   = Booking::where('status', 'completed')->where('created_at', '>=', $month)->sum('price');
            $pendingInvoice = Invoice::where('status', 'sent')->count();
            $paidThisMonth  = Payment::where('payment_date', '>=', $month)->sum('amount');
            $outstanding    = Invoice::where('status', 'overdue')->sum('amount');
            $overdueCount   = Invoice::where('status', 'overdue')->count();

            $overdueInvoices = Invoice::where('status', 'overdue')
                ->with('client')
                ->orderBy('due_date')
                ->limit(5)
                ->get();

            return view('dashboard.finance', compact(
                'todayRevenue', 'monthRevenue', 'pendingInvoice',
                'paidThisMonth', 'outstanding', 'overdueCount', 'overdueInvoices'
            ));
        }
    }
}
