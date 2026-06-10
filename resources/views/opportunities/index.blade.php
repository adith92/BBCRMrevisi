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
        <a href="{{ route('opportunities.create') }}" class="btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span> Opportunity Baru
        </a>
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
        <button type="submit" class="btn-primary text-sm px-4 py-2">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
        </button>
        @if(request('stage') || request('sales_id'))
        <a href="{{ route('opportunities.index') }}" class="text-cc-muted hover:text-cc font-medium text-sm px-3 py-2">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="cc-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm resizable-table dark-table" data-table-id="opportunities-table">
                <thead>
                    <tr class="border-b border-[var(--cc-border)]">
                        <th class="px-5 py-3 text-left">Opportunity</th>
                        <th class="px-5 py-3 text-left">Client</th>
                        <th class="px-5 py-3 text-left">Sales</th>
                        <th class="px-5 py-3 text-left">Stage</th>
                        <th class="px-5 py-3 text-right">Estimasi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--cc-border)]">
                    @forelse($opportunities as $op)
                    @php
                        $stageColors = [
                            'prospecting' => 'bg-blue-500/12 text-blue-500 dark:text-blue-400 border border-blue-500/20',
                            'proposal'    => 'bg-indigo-500/12 text-indigo-500 dark:text-indigo-400 border border-indigo-500/20',
                            'negotiation' => 'bg-amber-500/12 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                            'won'         => 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                            'lost'        => 'bg-red-500/12 text-red-600 dark:text-red-400 border border-red-500/20',
                        ];
                    @endphp
                    <tr class="transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('opportunities.show', $op->id) }}" class="text-cc-cyan font-medium hover:underline">
                                {{ $op->title ?? $op->product->name ?? 'Opportunity #'.$op->id }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-cc-muted">{{ $op->client->company_name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-cc-muted">{{ $op->sales->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ $stageColors[$op->stage] ?? 'bg-slate-500/12 text-slate-500 border border-slate-500/20' }}">{{ ucfirst($op->stage) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-medium text-[var(--cc-text)]">{{ \App\Helpers\FormatHelper::formatIDR($op->estimated_value ?? 0) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('opportunities.show', $op->id) }}" class="text-cc-cyan hover:underline text-xs font-semibold">Detail →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-cc-muted">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">inbox</span>
                        Belum ada opportunity
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opportunities->hasPages())
        <div class="px-5 py-3 border-t border-[var(--cc-border)]">{{ $opportunities->links() }}</div>
        @endif
    </div>
</div>
@endsection
