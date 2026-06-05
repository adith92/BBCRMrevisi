@extends('layouts.app')

@section('header_title', 'Director Dashboard')

@section('content')
<div class="space-y-6">

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pipeline Value</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">
                Rp {{ number_format($pipelineValue, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Semua opportunity aktif</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Win Rate</p>
            <p class="text-2xl font-bold {{ $winRate >= 50 ? 'text-green-600' : 'text-orange-500' }} mt-1">
                {{ $winRate }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">Won / (Won + Lost)</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pending Approvals</p>
            <p class="text-2xl font-bold {{ $pendingApprovals > 0 ? 'text-red-600' : 'text-gray-700' }} mt-1">
                {{ $pendingApprovals }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Menunggu persetujuan</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Revenue MTD</p>
            <p class="text-2xl font-bold text-green-700 mt-1">
                Rp {{ number_format($revenueMTD, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Team Performance Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Performa Tim Sales</h3>
                <a href="{{ route('analytics.sales') }}" class="text-xs text-blue-600 hover:underline">Lihat detail</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Sales</th>
                            <th class="px-4 py-3 text-center">Pipeline</th>
                            <th class="px-4 py-3 text-center">Won</th>
                            <th class="px-4 py-3 text-center">Win Rate</th>
                            <th class="px-4 py-3 text-right">Revenue</th>
                            <th class="px-4 py-3 text-center">KPI%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($salesTeam as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $member->name }}</div>
                                <div class="text-xs text-gray-400 uppercase">{{ $member->role }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $member->pipeline_count }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $member->won_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $member->win_rate >= 50 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $member->win_rate }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                Rp {{ number_format($member->won_revenue ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center gap-1 justify-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $member->kpi_pct >= 100 ? 'bg-green-500' : ($member->kpi_pct >= 60 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                            style="width: {{ min($member->kpi_pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600">{{ $member->kpi_pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada data sales.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Approval Queue --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Approval Queue</h3>
                @if($pendingApprovals > 0)
                <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ $pendingApprovals }}
                </span>
                @endif
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($approvalQueue as $approval)
                <div class="px-5 py-3 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ optional(optional($approval->opportunity)->client)->company_name ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst($approval->type) }} &bull;
                                {{ $approval->discount_percent }}% diskon
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                oleh {{ optional($approval->requestedBy)->name ?? '-' }}
                            </p>
                        </div>
                        <a href="{{ route('approvals.show', $approval) }}"
                           class="text-xs text-blue-600 hover:underline whitespace-nowrap mt-0.5">Review</a>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    Tidak ada approval pending.
                </div>
                @endforelse
            </div>
            @if($pendingApprovals > 5)
            <div class="px-5 py-3 border-t border-gray-100">
                <a href="{{ route('approvals.index') }}" class="text-xs text-blue-600 hover:underline">
                    Lihat semua {{ $pendingApprovals }} approval
                </a>
            </div>
            @endif
        </div>

    </div>

    {{-- Revenue Chart --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Revenue Trend (12 Bulan)</h3>
            <a href="{{ route('analytics.index') }}" class="text-xs text-blue-600 hover:underline">Analytics lengkap</a>
        </div>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('/api/revenue')
        .then(r => r.json())
        .then(data => {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Revenue (Rp)',
                        data: data.values || [],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt'
                            }
                        }
                    }
                }
            });
        })
        .catch(() => {});
});
</script>
@endpush
