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
        @isset($salesUsers)
        <div>
            <label class="block dark-label mb-1">Sales</label>
            <select name="sales_id" class="dark-input text-sm px-3 py-2 min-w-36">
                <option value="">Semua Sales</option>
                @foreach($salesUsers as $s)
                <option value="{{ $s->id }}" @selected((string)request('sales_id')===(string)$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endisset
        @if(isset($managers) && $managers->count() > 0)
        <div>
            <label class="block dark-label mb-1">{{ __('ui.filter_manager') }}</label>
            <select name="manager_id" class="dark-input text-sm px-3 py-2 min-w-40">
                <option value="">{{ __('ui.all_managers') }}</option>
                @foreach($managers as $manager)
                <option value="{{ $manager->id }}" @selected((string)request('manager_id')===(string)$manager->id)>{{ $manager->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="btn-primary text-sm px-4 py-2">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
        </button>
        @if(request('stage') || request('sales_id') || request('manager_id'))
        <a href="{{ route('opportunities.index') }}" class="text-cc-muted hover:text-cc font-medium text-sm px-3 py-2">Reset</a>
        @endif
    </form>

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
