@extends('layouts.app')

@section('header_title', 'Manager Dashboard')

@section('content')
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- Row 1: Team Overview Cards (3 cards) --}}
    <div class="grid-stack-item" gs-id="w-pipeline-tim" gs-x="0" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Pipeline Tim</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    Rp {{ number_format($teamPipelineValue, 0, ',', '.') }}
                </p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">Total value aktif</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-won-alltime" gs-x="4" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Won (All Time)</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $teamWon }}</p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $teamLost }} lost</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-anggota-tim" gs-x="8" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Anggota Tim</p>
                <p class="text-2xl font-bold mt-1" style="color:var(--cc-text)">{{ $teamMembers->count() }}</p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">Sales aktif</p>
            </div>
        </div>
    </div>

    {{-- Revenue Trend Chart --}}
    <div class="grid-stack-item" gs-id="w-revenue-chart" gs-x="0" gs-y="2" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm p-5 h-full">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="font-semibold" style="color:var(--cc-text)">Revenue Trend Tim</h3>
                        <p class="text-xs text-[var(--cc-text-muted)] mt-1">Revenue won milik sales di bawah manager ini.</p>
                    </div>
                    <div class="flex flex-wrap gap-1 text-xs" id="revenueRangeButtons">
                        <button type="button" data-range="daily" class="range-btn px-3 py-1.5 rounded-lg border border-[var(--cc-border)] text-[var(--cc-text-muted)]">Hari</button>
                        <button type="button" data-range="weekly" class="range-btn px-3 py-1.5 rounded-lg border border-[var(--cc-border)] text-[var(--cc-text-muted)]">Minggu</button>
                        <button type="button" data-range="monthly" class="range-btn active px-3 py-1.5 rounded-lg border border-indigo-500 bg-indigo-500/10 text-indigo-400">Bulan</button>
                        <button type="button" data-range="quarter" class="range-btn px-3 py-1.5 rounded-lg border border-[var(--cc-border)] text-[var(--cc-text-muted)]">3 Bulan</button>
                        <button type="button" data-range="semester" class="range-btn px-3 py-1.5 rounded-lg border border-[var(--cc-border)] text-[var(--cc-text-muted)]">6 Bulan</button>
                    </div>
                </div>
                <div id="revenueChart" style="min-height:280px"></div>
            </div>
        </div>
    </div>

    {{-- Pipeline per Sales --}}
    <div class="grid-stack-item" gs-id="w-pipeline-breakdown" gs-x="0" gs-y="7" gs-w="8" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">Pipeline per Sales (Nilai & Stage)</h3>
                    <p class="text-xs text-[var(--cc-text-muted)] mt-1">Bar memakai nilai pipeline, angka kecil tetap menunjukkan jumlah deal.</p>
                </div>
                <div class="p-5 space-y-4 overflow-y-auto" style="max-height:calc(100% - 56px)">
                    @php
                        $stageColors = [
                            'prospecting' => 'bg-gray-400 dark:bg-gray-600',
                            'proposal'    => 'bg-blue-400 dark:bg-blue-500',
                            'negotiation' => 'bg-yellow-400 dark:bg-yellow-500',
                        ];
                        $stageLabelsMap = [
                            'prospecting' => 'Prospecting',
                            'proposal'    => 'Proposal',
                            'negotiation' => 'Negosiasi',
                        ];
                    @endphp

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-3 text-xs text-[var(--cc-text)]">
                        @foreach($stages as $s)
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded {{ $stageColors[$s] ?? 'bg-gray-300 dark:bg-gray-600' }} inline-block"></span>
                            {{ $stageLabelsMap[$s] ?? $s }}
                        </span>
                        @endforeach
                    </div>

                    @forelse($stageBreakdown as $row)
                    @php
                        $rowTotal = array_sum($row['totals']);
                        $rowValueTotal = array_sum($row['values'] ?? []);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-[var(--cc-text)]">{{ $row['name'] }}</span>
                            <span class="text-xs text-[var(--cc-text-muted)]">Rp {{ number_format($rowValueTotal, 0, ',', '.') }} • {{ $rowTotal }} deal</span>
                        </div>
                        <div class="flex h-5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @foreach($stages as $s)
                            @if(isset($row['values'][$s]) && $row['values'][$s] > 0 && $rowValueTotal > 0)
                            <div class="{{ $stageColors[$s] ?? 'bg-gray-300 dark:bg-gray-600' }} flex items-center justify-center text-gray-900 text-xs"
                                 style="width: {{ round(($row['values'][$s] / $rowValueTotal) * 100) }}%"
                                 title="{{ $stageLabelsMap[$s] ?? $s }}: Rp {{ number_format($row['values'][$s], 0, ',', '.') }} / {{ $row['totals'][$s] }} deal">
                                {{ $row['totals'][$s] }}
                            </div>
                            @endif
                            @endforeach
                            @if($rowValueTotal == 0)
                            <div class="flex-1 flex items-center justify-center text-xs text-[var(--cc-text-muted)]">Belum ada</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-[var(--cc-text-muted)]">Belum ada anggota tim.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar: Recent Activities --}}
    <div class="grid-stack-item" gs-id="w-recent-activities" gs-x="8" gs-y="7" gs-w="4" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">Aktivitas Terbaru Tim</h3>
                </div>
                <div class="divide-y divide-[var(--cc-border)] overflow-y-auto" style="max-height:calc(100% - 100px)">
                    @php
                        $activityIcons = [
                            'meeting'    => '🤝',
                            'call'       => '📞',
                            'visit'      => '🚗',
                            'follow_up'  => '📋',
                            'email'      => '📧',
                            'demo'       => '🎯',
                        ];
                    @endphp
                    @forelse($recentActivities as $activity)
                    <div class="px-5 py-3 hover:bg-black/5 dark:hover:bg-gray-100/5">
                        <div class="flex items-start gap-2">
                            <span class="text-lg leading-none mt-0.5">
                                {{ $activityIcons[$activity->type] ?? '📌' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[var(--cc-text)] truncate">{{ $activity->subject }}</p>
                                <p class="text-xs text-[var(--cc-text-muted)]">
                                    {{ optional($activity->sales)->name ?? '-' }}
                                    @if($activity->client) &bull; <a href="{{ route('clients.show', $activity->client->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ optional($activity->client)->company_name }}</a> @endif
                                </p>
                                <p class="text-xs text-[var(--cc-text-muted)]">{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-6 text-center text-[var(--cc-text-muted)] text-sm">Belum ada aktivitas.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-[var(--cc-border)]">
                    <a href="{{ route('activities.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Semua aktivitas</a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Achievement Table --}}
    <div class="grid-stack-item" gs-id="w-kpi-achievement" gs-x="0" gs-y="13" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">KPI Tim - {{ now()->format('F Y') }}</h3>
                </div>
                <div class="p-5 overflow-y-auto" style="max-height:calc(100% - 56px)">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse($teamMembers as $member)
                        <div class="rounded-2xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-[var(--cc-text)]">{{ $member->name }}</h4>
                                    <p class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $member->won_count }} won • Win rate {{ $member->win_rate }}%</p>
                                </div>
                                <span class="text-[10px] px-2 py-1 rounded-full font-bold {{ $member->kpi_pct >= 100 ? 'bg-green-500/15 text-green-400' : ($member->kpi_pct >= 60 ? 'bg-yellow-500/15 text-yellow-400' : 'bg-red-500/15 text-red-400') }}">
                                    {{ $member->kpi_status }}
                                </span>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-[var(--cc-text-muted)] mb-1">
                                    <span>Revenue</span>
                                    <span>Target</span>
                                </div>
                                <div class="flex justify-between text-sm font-mono font-bold text-[var(--cc-text)]">
                                    <span>Rp {{ number_format($member->won_revenue ?? 0, 0, ',', '.') }}</span>
                                    <span>Rp {{ number_format($member->target_revenue ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $member->kpi_pct >= 100 ? 'bg-green-500' : ($member->kpi_pct >= 60 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                        style="width: {{ min($member->kpi_pct, 100) }}%"></div>
                                </div>
                                <div class="text-right text-xs font-bold text-[var(--cc-text)] mt-1">{{ $member->kpi_pct }}%</div>
                            </div>
                        </div>
                        @empty
                        <div class="md:col-span-3 px-4 py-6 text-center text-[var(--cc-text-muted)]">Belum ada anggota tim.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard-grid>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.classList.contains('dark');
    var textColor = isDark ? '#94a3b8' : '#64748b';

    // Revenue Trend
    var revenueRanges = {!! json_encode($revenueTrendRanges ?? ['monthly' => ($revenueTrend ?? ['labels'=>[],'data'=>[]])]) !!};
    var revData = revenueRanges.monthly || {!! json_encode($revenueTrend ?? ['labels'=>[],'data'=>[]]) !!};
    var revenueOptions = {
        series: [{ name: "Revenue Tim", data: revData.data }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
        colors: ['#3b82f6'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [50, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: revData.labels, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor }, formatter: function (val) { return "Rp " + (val/1000000).toFixed(0) + "M"; } } },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (val) { return "Rp " + new Intl.NumberFormat('id-ID').format(val); } } }
    };

    setTimeout(() => {
        var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
        revenueChart.render();
        document.querySelectorAll('#revenueRangeButtons .range-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var selected = revenueRanges[button.dataset.range] || revenueRanges.monthly;
                document.querySelectorAll('#revenueRangeButtons .range-btn').forEach(function(btn) {
                    btn.classList.remove('active', 'border-indigo-500', 'bg-indigo-500/10', 'text-indigo-400');
                    btn.classList.add('border-[var(--cc-border)]', 'text-[var(--cc-text-muted)]');
                });
                button.classList.add('active', 'border-indigo-500', 'bg-indigo-500/10', 'text-indigo-400');
                revenueChart.updateOptions({ xaxis: { categories: selected.labels } });
                revenueChart.updateSeries([{ name: "Revenue Tim", data: selected.data }]);
            });
        });
    }, 300);
});
</script>
@endpush
@endsection
