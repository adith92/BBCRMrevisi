@extends('layouts.app')
@section('title', 'Dashboard — Finance')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500"><p class="text-xs text-gray-500 uppercase">Revenue Bulan Ini</p><p class="text-xl font-bold text-green-600">{{ formatIDR($monthlyRevenue ?? 0) }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500"><p class="text-xs text-gray-500 uppercase">Pending Invoice</p><p class="text-xl font-bold text-yellow-600">{{ $pendingInvoices ?? 0 }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500"><p class="text-xs text-gray-500 uppercase">Paid This Month</p><p class="text-xl font-bold text-blue-600">{{ $paidThisMonth ?? 0 }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500"><p class="text-xs text-gray-500 uppercase">Outstanding</p><p class="text-xl font-bold text-red-600">{{ formatIDR($outstanding ?? 0) }}</p></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Revenue Trend (Aggregate)</h3>
        <canvas id="finRevChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Pending Payments</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Invoice</th><th>Client</th><th class="text-right">Amount</th><th>Due</th></tr></thead><tbody>
        @forelse($pendingPayments ?? [] as $p)
        <tr class="border-b"><td class="py-2">{{ $p->invoice_number }}</td><td>{{ $p->client->company_name ?? '-' }}</td><td class="text-right">{{ formatIDR($p->amount) }}</td><td>{{ $p->due_date->format('d M') }}</td></tr>
        @empty<tr><td colspan="4" class="py-2 text-gray-400">No pending</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Recent Invoices</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">No.</th><th>Client</th><th class="text-right">Amount</th><th>Status</th></tr></thead><tbody>
        @forelse($recentInvoices ?? [] as $inv)
        <tr class="border-b"><td class="py-2">{{ $inv->invoice_number }}</td><td>{{ $inv->client->company_name ?? '-' }}</td><td class="text-right">{{ formatIDR($inv->amount) }}</td><td><span class="px-2 py-0.5 rounded text-xs {{ $inv->status==='paid'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $inv->status }}</span></td></tr>
        @empty<tr><td colspan="4" class="py-2 text-gray-400">No invoices</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Recent Transactions</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Payment</th><th>Method</th><th class="text-right">Amount</th><th>Date</th></tr></thead><tbody>
        @forelse($recentTransactions ?? [] as $t)
        <tr class="border-b"><td class="py-2">{{ $t->payment_number }}</td><td>{{ $t->method }}</td><td class="text-right">{{ formatIDR($t->amount) }}</td><td>{{ $t->payment_date->format('d M') }}</td></tr>
        @empty<tr><td colspan="4" class="py-2 text-gray-400">No transactions</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@push('scripts')
<script>
fetch('/api/revenue?period=monthly').then(r=>r.json()).then(data => {
    new Chart(document.getElementById('finRevChart'),{type:'bar',data:{labels:data.map(d=>d.date),datasets:[{label:'Revenue',data:data.map(d=>d.total),backgroundColor:'#185FA5'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
});
</script>
@endpush
@endsection
