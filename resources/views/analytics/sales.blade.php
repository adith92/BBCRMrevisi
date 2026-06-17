@extends('layouts.app')

@section('header_title', 'Sales Performance')

@section('content')
@php
    $totalRevenue = $salesRows->sum('revenue');
    $totalTarget = $salesRows->sum('target');
    $avgWinRate = $salesRows->count() > 0 ? round($salesRows->avg('win_rate'), 1) : 0;
    $topSales = $salesRows->first();
@endphp

<div class="space-y-6 font-sans">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-secondary text-[28px]">leaderboard</span>
            <div>
                <h2 class="text-xl font-bold text-cc">Sales Performance</h2>
                <p class="text-xs text-cc-muted">Periode {{ Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <form method="GET" class="cc-card p-3 flex flex-wrap items-end gap-3">
            <div>
                <label class="block dark-label mb-1">{{ __('ui.period') }}</label>
                <div class="flex gap-2">
                    <select name="month" class="dark-input text-sm px-3 py-2">
                        @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected((int)$month === $m)>{{ Carbon\Carbon::createFromDate($year, $m, 1)->translatedFormat('M') }}</option>
                        @endforeach
                    </select>
                    <select name="year" class="dark-input text-sm px-3 py-2">
                        @foreach(range(now()->year - 3, now()->year + 2) as $y)
                        <option value="{{ $y }}" @selected((int)$year === $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block dark-label mb-1">{{ __('ui.filter_manager') }}</label>
                <select name="manager_id" class="dark-input text-sm px-3 py-2 min-w-40">
                    <option value="">{{ __('ui.all_managers') }}</option>
                    @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" @selected((string)$managerId === (string)$manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">
                <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
            </button>
        </form>
    </div>

    @include('components.analytics-nav')

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="cc-card p-5 rounded-2xl">
            <div class="text-xs font-bold text-cc-muted uppercase">Revenue</div>
            <div class="text-2xl font-black text-cc mt-1">{{ \App\Helpers\FormatHelper::formatIDR($totalRevenue) }}</div>
            <div class="text-[11px] text-cc-muted mt-1">Target {{ \App\Helpers\FormatHelper::formatIDR($totalTarget) }}</div>
        </div>
        <div class="cc-card p-5 rounded-2xl">
            <div class="text-xs font-bold text-cc-muted uppercase">Achievement</div>
            <div class="text-2xl font-black text-cc mt-1">{{ $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 1) : 0 }}%</div>
            <div class="text-[11px] text-cc-muted mt-1">Revenue vs target</div>
        </div>
        <div class="cc-card p-5 rounded-2xl">
            <div class="text-xs font-bold text-cc-muted uppercase">Win Rate</div>
            <div class="text-2xl font-black text-cc mt-1">{{ $avgWinRate }}%</div>
            <div class="text-[11px] text-cc-muted mt-1">Rata-rata tim</div>
        </div>
        <div class="cc-card p-5 rounded-2xl">
            <div class="text-xs font-bold text-cc-muted uppercase">Top Sales</div>
            <div class="text-2xl font-black text-cc mt-1 truncate">{{ $topSales['name'] ?? '-' }}</div>
            <div class="text-[11px] text-cc-muted mt-1">{{ $topSales['revenue_fmt'] ?? \App\Helpers\FormatHelper::formatIDR(0) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="cc-card p-5 rounded-2xl xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-cc">Revenue vs Target</h3>
                <span class="text-xs text-cc-muted">{{ $salesRows->count() }} sales</span>
            </div>
            <div class="h-72">
                <canvas id="salesRevenueTargetChart"></canvas>
            </div>
        </div>

        <div class="cc-card p-5 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-cc">Win Rate</h3>
                <span class="text-xs text-cc-muted">Won vs Lost</span>
            </div>
            <div class="h-72">
                <canvas id="salesWinRateChart"></canvas>
            </div>
        </div>
    </div>

    <div class="cc-card p-5 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-cc">Trend 6 Bulan</h3>
            <span class="text-xs text-cc-muted">Top 5 by revenue</span>
        </div>
        <div class="h-72">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <div class="cc-card rounded-2xl shadow-sm border border-cc overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="cc-card border-b border-cc">
                    <tr class="text-left text-[11px] font-bold text-cc-muted uppercase tracking-wider">
                        <th class="px-5 py-3">Sales</th>
                        <th class="px-5 py-3">{{ __('ui.sales_manager') }}</th>
                        <th class="px-5 py-3 text-right">Revenue</th>
                        <th class="px-5 py-3 text-right">Target</th>
                        <th class="px-5 py-3 text-center">KPI</th>
                        <th class="px-5 py-3 text-center">Won/Lost</th>
                        <th class="px-5 py-3 text-center">Win Rate</th>
                        <th class="px-5 py-3 text-right">{{ __('ui.avg_deal') }}</th>
                        <th class="px-5 py-3 text-right">Pipeline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cc">
                    @forelse($salesRows as $row)
                    <tr class="hover:cc-card transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('sales.performance', $row['user_id']) }}" class="font-semibold text-cc-cyan hover:underline">{{ $row['name'] }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-cc-muted">{{ $row['manager_name'] }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-cc">{{ $row['revenue_fmt'] }}</td>
                        <td class="px-5 py-3.5 text-right text-cc-muted">{{ $row['target_fmt'] }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-cc-card rounded-full h-2 overflow-hidden min-w-[60px]">
                                    <div class="bg-gradient-to-r from-[var(--color-secondary)] to-secondary h-full" style="width: {{ min($row['target_pct'], 100) }}%"></div>
                                </div>
                                <span class="text-[11px] font-bold text-cc-muted">{{ $row['target_pct'] }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center text-cc-muted">{{ $row['deals_won'] }} / {{ $row['deals_lost'] }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $row['win_rate'] >= 50 ? 'bg-emerald-100 text-emerald-700' : ($row['win_rate'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-cc-card text-cc-muted') }}">{{ $row['win_rate'] }}%</span>
                        </td>
                        <td class="px-5 py-3.5 text-right text-cc-muted">{{ $row['avg_deal_fmt'] }}</td>
                        <td class="px-5 py-3.5 text-right text-cc-muted">{{ $row['pipeline_fmt'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-cc-muted">
                            <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">inbox</span>
                            Belum ada data performa sales
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rows = @json($salesRows);
        const labels = rows.map(row => row.name);
        const revenueValues = rows.map(row => row.revenue / 1000000);
        const targetValues = rows.map(row => row.target / 1000000);

        const revenueCtx = document.getElementById('salesRevenueTargetChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Revenue (Jt)', data: revenueValues, backgroundColor: 'rgba(59,130,246,0.72)', borderRadius: 6 },
                        { label: 'Target (Jt)', data: targetValues, backgroundColor: 'rgba(245,158,11,0.66)', borderRadius: 6 },
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const winCtx = document.getElementById('salesWinRateChart');
        if (winCtx) {
            new Chart(winCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Win Rate', 'Gap'],
                    datasets: [{
                        data: [{{ $avgWinRate }}, {{ max(0, 100 - $avgWinRate) }}],
                        backgroundColor: ['rgba(16,185,129,0.78)', 'rgba(148,163,184,0.18)'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%' }
            });
        }

        const trendCtx = document.getElementById('salesTrendChart');
        if (trendCtx) {
            const palette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];
            const topRows = [...rows].sort((a, b) => b.revenue - a.revenue).slice(0, 5);
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($trendLabels),
                    datasets: topRows.map((row, index) => ({
                        label: row.name,
                        data: row.trend_6m.map(value => value / 1000000),
                        borderColor: palette[index] || '#64748b',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        pointRadius: 3
                    }))
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
</script>
@endpush
@endsection
