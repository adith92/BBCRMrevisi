@extends('layouts.app')

@section('header_title', 'Finance & Invoices')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Finance'],
]" />

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-[var(--cc-bg-muted)] rounded-lg p-4 border-l-4 border-gray-400">
        <p class="text-xs text-[var(--cc-text-muted)]">Total Invoiced</p>
        <p class="text-lg font-bold text-[var(--cc-text)] mt-1">{{ \App\Helpers\FormatHelper::formatIDR($summary['total']) }}</p>
    </div>
    <a href="{{ route('finance.index', array_merge(request()->query(), ['status' => 'paid'])) }}"
       class="group block bg-green-50 rounded-lg p-4 border-l-4 border-green-500 hover:shadow-md transition-all">
        <p class="text-xs text-[var(--cc-text-muted)]">Paid</p>
        <p class="text-lg font-bold text-green-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($summary['paid']) }}</p>
        <p class="text-xs text-green-600 mt-1">{{ $summary['paid_count'] }} invoices</p>
    </a>
    <a href="{{ route('finance.index', array_merge(request()->query(), ['status' => 'sent'])) }}"
       class="group block bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500 hover:shadow-md transition-all">
        <p class="text-xs text-[var(--cc-text-muted)]">Pending</p>
        <p class="text-lg font-bold text-yellow-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($summary['pending']) }}</p>
    </a>
    <a href="{{ route('finance.index', array_merge(request()->query(), ['status' => 'overdue'])) }}"
       class="group block bg-red-50 rounded-lg p-4 border-l-4 border-red-500 hover:shadow-md transition-all">
        <p class="text-xs text-[var(--cc-text-muted)]">Overdue</p>
        <p class="text-lg font-bold text-red-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($summary['overdue']) }}</p>
        <p class="text-xs text-red-600 mt-1">{{ $summary['overdue_count'] }} invoices</p>
    </a>
</div>

{{-- Invoice List --}}
<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-wrap gap-2 justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            Invoices
            @if(request('status'))
                <span class="text-sm font-normal text-[var(--cc-text-muted)]">— filtered by: <strong>{{ request('status') }}</strong></span>
            @endif
            @if(request('filter'))
                <span class="text-sm font-normal text-[var(--cc-text-muted)]">— period: <strong>{{ request('filter') }}</strong></span>
            @endif
        </h2>
        {{-- Status filters --}}
        <div class="flex gap-2 text-sm flex-wrap">
            <a href="{{ route('finance.index', ['filter' => request('filter')]) }}"
               class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded">All</a>
            <a href="{{ route('finance.index', ['filter' => request('filter'), 'status' => 'paid']) }}"
               class="{{ request('status') === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded">Paid</a>
            <a href="{{ route('finance.index', ['filter' => request('filter'), 'status' => 'sent']) }}"
               class="{{ request('status') === 'sent' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded">Pending</a>
            <a href="{{ route('finance.index', ['filter' => request('filter'), 'status' => 'overdue']) }}"
               class="{{ request('status') === 'overdue' ? 'bg-red-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded">Overdue</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-[var(--cc-bg-muted)]">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="text-left py-3 px-4">Invoice #</th>
                    <th class="text-left py-3 px-4">Client</th>
                    <th class="text-left py-3 px-4">Sales</th>
                    <th class="text-left py-3 px-4">Due Date</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('invoices.show', $invoice->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-mono font-medium hover:underline">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('clients.show', $invoice->client_id) }}"
                           class="text-[var(--cc-text)] hover:text-blue-600 hover:underline">
                            {{ $invoice->client->company_name }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        @if($invoice->booking?->sales && auth()->user()->isGM())
                            <a href="{{ route('sales.performance', $invoice->booking->sales_id) }}" class="text-blue-600 hover:underline">
                                {{ $invoice->booking->sales->name }}
                            </a>
                        @else
                            <span class="text-[var(--cc-text-muted)] text-xs">{{ $invoice->booking?->sales?->name ?? '—' }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                        @if(\Carbon\Carbon::parse($invoice->due_date)->isPast() && $invoice->status !== 'paid')
                            <span class="text-red-500 text-xs ml-1">({{ \Carbon\Carbon::parse($invoice->due_date)->diffForHumans() }})</span>
                        @endif
                    </td>
                    <td class="py-3 px-4"><x-status-badge :status="$invoice->status" /></td>
                    <td class="py-3 px-4 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-[var(--cc-text-muted)]">No invoices found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>

    @include('finance.charts')
</div>
@endsection
