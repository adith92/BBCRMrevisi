@extends('layouts.app')

@section('header_title', 'Finance Dashboard')

@section('content')
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- KPI Cards --}}
    <div class="grid-stack-item" gs-id="w-today-revenue" gs-x="0" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('finance.index', ['filter' => 'today']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Today Revenue</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-month-revenue" gs-x="3" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('finance.index', ['filter' => 'month']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-green-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Month Revenue</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-pending-invoice" gs-x="6" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('finance.index', ['status' => 'sent']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-yellow-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Pending Invoice</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $pendingInvoice }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View pending →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-outstanding" gs-x="9" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('finance.index', ['status' => 'overdue']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-red-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Outstanding</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-2">{{ \App\Helpers\FormatHelper::formatIDR($outstanding) }}</p>
                <p class="text-xs text-red-600 dark:text-red-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $overdueCount }} overdue →</p>
            </a>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="grid-stack-item" gs-id="w-finance-summary" gs-x="0" gs-y="2" gs-w="12" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full">
                <h3 class="text-base font-semibold text-[var(--cc-text)]">Financial Summary</h3>
                <p class="text-[var(--cc-text-muted)] mt-2 text-sm">Total Paid This Month: <strong class="text-green-600 dark:text-green-400">{{ \App\Helpers\FormatHelper::formatIDR($paidThisMonth) }}</strong></p>
            </div>
        </div>
    </div>

    {{-- Overdue Invoices --}}
    @if($overdueInvoices->count())
    <div class="grid-stack-item" gs-id="w-overdue" gs-x="0" gs-y="4" gs-w="12" gs-h="4">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full overflow-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-[var(--cc-text)]">⚠️ Overdue Invoices</h3>
                    <a href="{{ route('finance.index', ['status' => 'overdue']) }}" class="text-red-600 dark:text-red-400 hover:underline text-xs font-medium">View all →</a>
                </div>
                <div class="space-y-2">
                    @foreach($overdueInvoices as $inv)
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-500/10 rounded-lg border border-red-100 dark:border-red-500/20">
                        <div>
                            <a href="{{ route('invoices.show', $inv->id) }}" class="text-red-700 dark:text-red-400 hover:underline font-medium">
                                {{ $inv->invoice_number }}
                            </a>
                            <span class="text-[var(--cc-text-muted)] text-sm ml-2">—</span>
                            <a href="{{ route('clients.show', $inv->client_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm ml-1">
                                {{ $inv->client->company_name }}
                            </a>
                        </div>
                        <span class="font-semibold text-red-700 dark:text-red-400">{{ \App\Helpers\FormatHelper::formatIDR($inv->amount) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</x-dashboard-grid>
@endsection
