@extends('layouts.app')

@section('header_title', 'Opportunities')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Breadcrumb --}}
    <x-breadcrumb :items="[
        ['url' => route('dashboard'), 'label' => 'Dashboard'],
        ['url' => '#', 'label' => 'Opportunities'],
    ]" />

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-[var(--cc-accent-dim)] border border-[var(--cc-border)] shrink-0">
                <span class="material-symbols-outlined text-cc-cyan text-[26px]">handshake</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--cc-text)]">Sales Opportunities</h1>
                <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">Daftar peluang penjualan B2B</p>
            </div>
        </div>
        @if(!auth()->user()->isGM())
        <a href="{{ route('opportunities.create') }}" class="btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span> Opportunity Baru
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <form method="GET" class="cc-card p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block dark-label mb-1">Stage</label>
            <select name="stage" class="dark-input text-sm px-3 py-2 min-w-36">
                <option value="">Semua Stage</option>
                @foreach(['prospecting','proposal','negotiation','won','lost'] as $st)
                <option value="{{ $st }}" @selected(request('stage')===$st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        @if(isset($managers) && $managers->count() > 0)
        <div>
            <label class="block dark-label mb-1">{{ __('ui.filter_manager') }}</label>
            <select name="manager_id" class="dark-input text-sm px-3 py-2 min-w-40" onchange="this.form.sales_id.value = ''; this.form.submit();">
                <option value="">{{ __('ui.all_managers') }}</option>
                @foreach($managers as $manager)
                <option value="{{ $manager->id }}" @selected((string)($selectedManagerId ?? request('manager_id'))===(string)$manager->id)>{{ $manager->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @isset($salesUsers)
        <div>
            <label class="block dark-label mb-1">{{ __('ui.sales_rep') }}</label>
            <select name="sales_id" class="dark-input text-sm px-3 py-2 min-w-36" @disabled(isset($managers) && $managers->count() > 0 && blank($selectedManagerId))>
                @if(isset($managers) && $managers->count() > 0 && blank($selectedManagerId))
                <option value="">Pilih Sales Manager dulu</option>
                @else
                <option value="">{{ __('ui.all_sales') }}</option>
                @foreach($salesUsers as $s)
                <option value="{{ $s->id }}" @selected((string)request('sales_id')===(string)$s->id)>{{ $s->name }}</option>
                @endforeach
                @endif
            </select>
        </div>
        @endisset
        <button type="submit" class="btn-primary text-sm px-4 py-2">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
        </button>
        @if(request('stage') || request('sales_id') || request('manager_id'))
        <a href="{{ route('opportunities.index') }}" class="text-cc-muted hover:text-cc font-medium text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    {{-- Visual Summary --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="cc-card p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-[var(--cc-text)]">Pipeline Value by Stage</h3>
                    <p class="text-xs text-[var(--cc-text-muted)]">Nilai total dan jumlah opportunity per stage</p>
                </div>
                <span class="material-symbols-outlined text-[22px] text-cc-cyan">stacked_bar_chart</span>
            </div>
            @if($stageSummary->sum('count') > 0)
            <div class="h-[280px]">
                <canvas id="opportunity-stage-chart"></canvas>
            </div>
            @else
            <div class="h-[280px] flex items-center justify-center rounded-lg border border-dashed border-[var(--cc-border)] text-center">
                <div class="space-y-2">
                    <span class="material-symbols-outlined text-4xl text-[var(--cc-text-muted)] opacity-50">bar_chart_off</span>
                    <p class="text-sm font-medium text-[var(--cc-text)]">Belum ada data untuk divisualkan</p>
                    <p class="text-xs text-[var(--cc-text-muted)]">Coba ubah filter untuk melihat ringkasan pipeline.</p>
                </div>
            </div>
            @endif
        </div>

        <div class="cc-card p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-[var(--cc-text)]">Sales Rep Contribution</h3>
                    <p class="text-xs text-[var(--cc-text-muted)]">Siapa yang memegang nilai pipeline terbesar</p>
                </div>
                <span class="material-symbols-outlined text-[22px] text-cc-cyan">groups</span>
            </div>
            @if($salesContribution->count() > 0)
            <div class="h-[280px]">
                <canvas id="opportunity-sales-chart"></canvas>
            </div>
            @else
            <div class="h-[280px] flex items-center justify-center rounded-lg border border-dashed border-[var(--cc-border)] text-center">
                <div class="space-y-2">
                    <span class="material-symbols-outlined text-4xl text-[var(--cc-text-muted)] opacity-50">leaderboard</span>
                    <p class="text-sm font-medium text-[var(--cc-text)]">Belum ada kontribusi sales yang tampil</p>
                    <p class="text-xs text-[var(--cc-text-muted)]">Data akan muncul mengikuti manager dan sales rep yang dipilih.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="cc-card p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-[var(--cc-text)]">Top 5 High Value Opportunities</h3>
                <p class="text-xs text-[var(--cc-text-muted)]">Deal prioritas dengan nilai terbesar dari hasil filter aktif</p>
            </div>
            <span class="material-symbols-outlined text-[22px] text-cc-cyan">workspace_premium</span>
        </div>

        @if($topOpportunities->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-3">
            @foreach($topOpportunities as $opportunity)
            <a href="{{ $opportunity['show_url'] }}"
               class="rounded-lg border border-[var(--cc-border)] bg-[var(--cc-surface-secondary)] p-4 hover:border-cyan-500/40 hover:bg-cyan-500/5 transition-colors min-h-[170px] flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[var(--cc-accent-dim)] text-xs font-bold text-cc-cyan">{{ $loop->iteration }}</span>
                    <span class="inline-flex px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ match($opportunity['stage']) {
                        'prospecting' => 'bg-blue-500/12 text-blue-500 border border-blue-500/20',
                        'proposal' => 'bg-indigo-500/12 text-indigo-500 border border-indigo-500/20',
                        'negotiation' => 'bg-amber-500/12 text-amber-600 border border-amber-500/20',
                        'won' => 'bg-emerald-500/12 text-emerald-600 border border-emerald-500/20',
                        'lost' => 'bg-red-500/12 text-red-600 border border-red-500/20',
                        default => 'bg-slate-500/12 text-slate-500 border border-slate-500/20',
                    } }}">{{ $opportunity['stage_label'] }}</span>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-[var(--cc-text)] leading-5">{{ $opportunity['title'] }}</h4>
                    <p class="text-xs text-[var(--cc-text-muted)]">{{ $opportunity['client_name'] }}</p>
                </div>
                <div class="mt-auto flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-[var(--cc-text-muted)]">Nilai</p>
                        <p class="text-sm font-bold text-[var(--cc-text)]">{{ $opportunity['estimated_value_fmt'] }}</p>
                    </div>
                    <span class="text-xs font-semibold text-cc-cyan">Detail</span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="rounded-lg border border-dashed border-[var(--cc-border)] py-12 text-center">
            <span class="material-symbols-outlined text-4xl text-[var(--cc-text-muted)] opacity-50">format_list_bulleted</span>
            <p class="mt-2 text-sm font-medium text-[var(--cc-text)]">Belum ada opportunity prioritas</p>
            <p class="text-xs text-[var(--cc-text-muted)]">Daftar ini akan terisi saat hasil filter memiliki data.</p>
        </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="cc-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm resizable-table dark-table" data-table-id="opportunities-table" x-data="opportunityTable()">
                <thead>
                    <tr class="border-b border-[var(--cc-border)]">
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('title')">Opportunity <span x-text="icon('title')"></span></th>
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('company_name')">{{ __('ui.client') }} <span x-text="icon('company_name')"></span></th>
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('sales_name')">{{ __('ui.sales_rep') }} <span x-text="icon('sales_name')"></span></th>
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('manager_name')">{{ __('ui.sales_manager') }} <span x-text="icon('manager_name')"></span></th>
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('stage')">Stage <span x-text="icon('stage')"></span></th>
                        <th class="px-5 py-3 text-right cursor-pointer select-none" @click="sort('estimated_value')">{{ __('ui.value') }} <span x-text="icon('estimated_value')"></span></th>
                        <th class="px-5 py-3 text-left cursor-pointer select-none" @click="sort('created_at')">{{ __('ui.created_at') }} <span x-text="icon('created_at')"></span></th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--cc-border)]">
                    <template x-for="row in sortedRows()" :key="row.id">
                    <tr class="transition-colors">
                        <td class="px-5 py-3.5">
                            <a :href="row.show_url" class="text-cc-cyan font-medium hover:underline" x-text="row.title"></a>
                            <div class="text-[11px] text-cc-muted font-mono" x-text="row.opp_number"></div>
                        </td>
                        <td class="px-5 py-3.5">
                            <a x-show="row.client_url" :href="row.client_url" class="text-cc-cyan hover:underline" x-text="row.company_name"></a>
                            <span x-show="!row.client_url" class="text-cc-muted">-</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a x-show="row.sales_url" :href="row.sales_url" class="text-cc-cyan hover:underline" x-text="row.sales_name"></a>
                            <span x-show="!row.sales_url" class="text-cc-muted">-</span>
                        </td>
                        <td class="px-5 py-3.5 text-cc-muted" x-text="row.manager_name"></td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-0.5 rounded-lg text-[11px] font-bold" :class="stageClass(row.stage)" x-text="row.stage_label"></span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-medium text-[var(--cc-text)]" x-text="row.estimated_value_fmt"></td>
                        <td class="px-5 py-3.5 text-cc-muted" x-text="row.created_at_fmt"></td>
                        <td class="px-5 py-3.5 text-right">
                            <a :href="row.show_url" class="text-cc-cyan hover:underline text-xs font-semibold">Detail</a>
                        </td>
                    </tr>
                    </template>
                    <template x-if="rows.length === 0">
                    <tr><td colspan="8" class="px-5 py-10 text-center text-cc-muted">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">inbox</span>
                        Belum ada opportunity
                    </td></tr>
                    </template>
                </tbody>
            </table>
        </div>
        @if($opportunities->hasPages())
        <div class="px-5 py-3 border-t border-[var(--cc-border)]">{{ $opportunities->links() }}</div>
        @endif
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stageCanvas = document.getElementById('opportunity-stage-chart');
        const salesCanvas = document.getElementById('opportunity-sales-chart');
        const textColor = () => document.documentElement.classList.contains('light') ? '#5b6578' : '#94a3b8';
        const gridColor = () => document.documentElement.classList.contains('light') ? 'rgba(66, 79, 110, 0.08)' : 'rgba(255,255,255,0.05)';

        if (stageCanvas && window.Chart) {
            new window.Chart(stageCanvas, {
                type: 'bar',
                data: {
                    labels: @json($stageSummary->pluck('label')->values()),
                    datasets: [
                        {
                            label: 'Nilai Pipeline',
                            data: @json($stageSummary->pluck('total_value')->map(fn ($value) => round($value / 1000000, 1))->values()),
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.68)',
                                'rgba(99, 102, 241, 0.68)',
                                'rgba(245, 158, 11, 0.68)',
                                'rgba(16, 185, 129, 0.68)',
                                'rgba(239, 68, 68, 0.68)'
                            ],
                            borderRadius: 8,
                            borderSkipped: false,
                        },
                        {
                            label: 'Jumlah Deal',
                            data: @json($stageSummary->pluck('count')->values()),
                            type: 'line',
                            borderColor: 'rgba(148, 163, 184, 0.95)',
                            backgroundColor: 'rgba(148, 163, 184, 0.18)',
                            borderWidth: 2,
                            tension: 0.35,
                            pointRadius: 4,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor() } },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.96)',
                            titleColor: '#cbd5e1',
                            bodyColor: '#e2e8f0',
                            callbacks: {
                                label(context) {
                                    if (context.datasetIndex === 0) {
                                        return 'Nilai Pipeline: Rp ' + context.raw + ' Jt';
                                    }
                                    return 'Jumlah Deal: ' + context.raw;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor() }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor() },
                            ticks: {
                                color: textColor(),
                                callback: (value) => 'Rp ' + value + ' Jt'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { display: false },
                            ticks: { color: textColor() }
                        }
                    }
                }
            });
        }

        if (salesCanvas && window.Chart) {
            new window.Chart(salesCanvas, {
                type: 'bar',
                data: {
                    labels: @json($salesContribution->pluck('name')->values()),
                    datasets: [
                        {
                            label: 'Nilai Opportunity',
                            data: @json($salesContribution->pluck('total_value')->map(fn ($value) => round($value / 1000000, 1))->values()),
                            backgroundColor: 'rgba(34, 197, 94, 0.72)',
                            borderRadius: 8,
                            borderSkipped: false,
                        },
                        {
                            label: 'Jumlah Opportunity',
                            data: @json($salesContribution->pluck('count')->values()),
                            type: 'line',
                            borderColor: 'rgba(14, 165, 233, 0.95)',
                            backgroundColor: 'rgba(14, 165, 233, 0.16)',
                            borderWidth: 2,
                            tension: 0.35,
                            pointRadius: 4,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor() } },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.96)',
                            titleColor: '#cbd5e1',
                            bodyColor: '#e2e8f0',
                            callbacks: {
                                label(context) {
                                    if (context.datasetIndex === 0) {
                                        return 'Nilai Opportunity: Rp ' + context.raw + ' Jt';
                                    }
                                    return 'Jumlah Opportunity: ' + context.raw;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor(), maxRotation: 0, autoSkip: true }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor() },
                            ticks: {
                                color: textColor(),
                                callback: (value) => 'Rp ' + value + ' Jt'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { display: false },
                            ticks: { color: textColor() }
                        }
                    }
                }
            });
        }
    });

    function opportunityTable() {
        return {
            sortBy: 'created_at',
            sortDir: 'desc',
            rows: @json($opportunityRows),
            sort(field) {
                if (this.sortBy === field) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    return;
                }
                this.sortBy = field;
                this.sortDir = field === 'estimated_value' || field === 'created_at' ? 'desc' : 'asc';
            },
            icon(field) {
                if (this.sortBy !== field) return '';
                return this.sortDir === 'asc' ? 'ASC' : 'DESC';
            },
            stageClass(stage) {
                return {
                    prospecting: 'bg-blue-500/12 text-blue-500 dark:text-blue-400 border border-blue-500/20',
                    proposal: 'bg-indigo-500/12 text-indigo-500 dark:text-indigo-400 border border-indigo-500/20',
                    negotiation: 'bg-amber-500/12 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                    won: 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                    lost: 'bg-red-500/12 text-red-600 dark:text-red-400 border border-red-500/20',
                }[stage] || 'bg-slate-500/12 text-slate-500 border border-slate-500/20';
            },
            sortedRows() {
                return [...this.rows].sort((a, b) => {
                    const av = a[this.sortBy] ?? '';
                    const bv = b[this.sortBy] ?? '';

                    if (typeof av === 'number' || typeof bv === 'number') {
                        return this.sortDir === 'asc' ? Number(av) - Number(bv) : Number(bv) - Number(av);
                    }

                    return this.sortDir === 'asc'
                        ? String(av).localeCompare(String(bv))
                        : String(bv).localeCompare(String(av));
                });
            }
        };
    }
</script>
@endpush
@endsection
