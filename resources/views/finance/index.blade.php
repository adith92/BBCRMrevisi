@extends('layouts.app')
@section('title', 'Finance')
@section('content')
<div class="flex gap-2 mb-4">
    @foreach([['invoices','Invoices'],['payments','Payments'],['purchase-orders','Purchase Orders']] as [$k,$l])
    <a href="{{ route('finance.index',['tab'=>$k]) }}" class="px-4 py-2 rounded text-sm {{ ($tab ?? 'invoices')===$k ? 'bg-navy text-white' : 'bg-white shadow text-gray-600' }}">{{ $l }}</a>
    @endforeach
    <div class="ml-auto">
        <a href="{{ route('finance.create',['type'=>($tab ?? 'invoices')==='payments'?'payment':(($tab ?? '')==='purchase-orders'?'po':'invoice')]) }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New</a>
    </div>
</div>

@if(($tab ?? 'invoices') === 'invoices')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-left">Due Date</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
        @forelse($invoices as $inv)
        <tr class="border-b hover:bg-gray-50"><td class="px-4 py-3 font-medium">{{ $inv->invoice_number }}</td><td class="px-4 py-3">{{ $inv->client->company_name ?? '-' }}</td><td class="px-4 py-3 text-right">{{ formatIDR($inv->amount) }}</td><td class="px-4 py-3">{{ $inv->due_date->format('d M Y') }}</td><td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $inv->status==='paid'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ $inv->status }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('finance.show',[$inv->id,'type'=>'invoice']) }}" class="text-blue hover:underline text-xs">View</a></td></tr>
        @empty<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No invoices</td></tr>@endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $invoices->links() }}</div>
</div>

@elseif(($tab ?? '') === 'payments')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Payment</th><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-left">Method</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
        <tbody>
        @forelse($payments as $p)
        <tr class="border-b"><td class="px-4 py-3 font-medium">{{ $p->payment_number }}</td><td class="px-4 py-3">{{ $p->invoice->invoice_number ?? '-' }}</td><td class="px-4 py-3">{{ $p->invoice->client->company_name ?? '-' }}</td><td class="px-4 py-3 text-right">{{ formatIDR($p->amount) }}</td><td class="px-4 py-3">{{ ucfirst($p->method) }}</td><td class="px-4 py-3">{{ $p->payment_date->format('d M Y') }}</td></tr>
        @empty<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No payments</td></tr>@endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $payments->links() }}</div>
</div>

@else
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">PO</th><th class="px-4 py-3 text-left">Vendor</th><th class="px-4 py-3 text-left">Item</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
        <tbody>
        @forelse($purchaseOrders as $po)
        <tr class="border-b"><td class="px-4 py-3 font-medium">{{ $po->po_number }}</td><td class="px-4 py-3">{{ $po->vendor }}</td><td class="px-4 py-3">{{ Str::limit($po->item_description,40) }}</td><td class="px-4 py-3 text-right">{{ formatIDR($po->amount) }}</td><td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">{{ $po->status }}</span></td></tr>
        @empty<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No POs</td></tr>@endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $purchaseOrders->links() }}</div>
</div>
@endif
@endsection
