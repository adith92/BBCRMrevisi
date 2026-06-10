@extends('layouts.app')

@section('header_title', $client->company_name)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('clients.index'), 'label' => 'Clients'],
    ['url' => '#', 'label' => $client->company_name],
]" />

{{-- Hero --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[var(--cc-text)]">{{ $client->company_name }}</h2>
            <p class="text-[var(--cc-text-muted)] mt-1">{{ $client->industry }} · {{ $client->address }}</p>
        </div>
        <x-status-badge :status="$client->status" />
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-[var(--cc-border)]">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">PIC Name</p>
            <a href="mailto:{{ $client->email }}" class="font-semibold text-cc-cyan hover:underline">{{ $client->pic_name }}</a>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Phone</p>
            <p class="font-semibold text-[var(--cc-text)]">{{ $client->phone }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Email</p>
            <p class="font-semibold text-[var(--cc-text)] text-sm">{{ $client->email }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Assigned Sales</p>
            @if($client->assignedSales)
                <a href="{{ route('sales.performance', $client->assignedSales->id) }}"
                   class="font-semibold text-cc-cyan hover:underline">
                    {{ $client->assignedSales->name }}
                </a>
            @else
                <p class="text-[var(--cc-text-faint)]">Unassigned</p>
            @endif
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <a href="{{ route('finance.index', ['status' => 'paid']) }}" class="kpi-card kpi-emerald block group">
        <p class="kpi-label">Total Paid</p>
        <p class="kpi-value text-emerald-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_spend']) }}</p>
        <p class="text-[10px] font-bold mt-2 text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">View paid invoices →</p>
    </a>

    <div class="kpi-card kpi-gold">
        <p class="kpi-label">Pending</p>
        <p class="kpi-value text-amber-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_pending']) }}</p>
    </div>

    <div class="kpi-card kpi-red">
        <p class="kpi-label">Overdue</p>
        <p class="kpi-value text-red-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_overdue']) }}</p>
    </div>
</div>

{{-- Invoice Summary --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-[var(--cc-text)] mb-4">Invoice History</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm dark-table resizable-table">
            <thead>
                <tr>
                    <th class="text-left">Invoice #</th>
                    <th class="text-left">Due Date</th>
                    <th class="text-left">Status</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->invoices->sortByDesc('created_at')->take(10) as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="text-cc-cyan hover:underline font-mono">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="text-[var(--cc-text-muted)]">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                    <td><x-status-badge :status="$invoice->status" /></td>
                    <td class="text-right font-semibold text-[var(--cc-text)]">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-[var(--cc-text-muted)]">No invoices yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Meeting Logs --}}
@if($client->meetingLogs->count())
<div class="cc-card rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold text-[var(--cc-text)] mb-4">Meeting Log</h3>
    <div class="space-y-3">
        @foreach($client->meetingLogs->sortByDesc('meeting_date')->take(5) as $meeting)
        <div class="border-l-2 border-blue-400 pl-4 py-2 bg-[var(--cc-surface)] rounded-r-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-[var(--cc-text)]">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}</p>
                    <p class="text-[13px] text-[var(--cc-text-muted)] mt-1">{{ $meeting->outcome }}</p>
                    @if($meeting->notes)
                        <p class="text-xs text-[var(--cc-text-faint)] mt-1 italic">{{ $meeting->notes }}</p>
                    @endif
                </div>
                <x-status-badge :status="$meeting->status" />
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
