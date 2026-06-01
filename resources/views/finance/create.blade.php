@extends('layouts.app')
@section('title', 'New ' . ucfirst($type))
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('finance.store') }}?type={{ $type }}">@csrf
        @if($type === 'invoice')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Booking</label><select name="booking_id" required class="w-full border rounded px-3 py-2 text-sm">@foreach($bookings as $b)<option value="{{ $b->id }}">{{ $b->booking_number }} - Rp {{ number_format($b->price,0,',','.') }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Client</label><select name="client_id" required class="w-full border rounded px-3 py-2 text-sm">@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->company_name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Amount (IDR)</label><input name="amount" class="idr-input w-full border rounded px-3 py-2 text-sm" placeholder="Rp 0"></div>
            <div><label class="block text-sm font-medium mb-1">Due Date</label><input name="due_date" type="date" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
        </div>
        @elseif($type === 'payment')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Invoice</label><select name="invoice_id" required class="w-full border rounded px-3 py-2 text-sm">@foreach($invoices as $i)<option value="{{ $i->id }}">{{ $i->invoice_number }} - {{ $i->client->company_name ?? '' }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Method</label><select name="method" class="w-full border rounded px-3 py-2 text-sm">@foreach(['transfer','cash','giro'] as $m)<option value="{{ $m }}">{{ ucfirst($m) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Amount (IDR)</label><input name="amount" class="idr-input w-full border rounded px-3 py-2 text-sm" placeholder="Rp 0"></div>
            <div><label class="block text-sm font-medium mb-1">Payment Date</label><input name="payment_date" type="date" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
        </div>
        @else
        <div class="grid grid-cols-1 gap-4">
            <div><label class="block text-sm font-medium mb-1">Vendor</label><input name="vendor" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Description</label><textarea name="item_description" rows="2" required class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
            <div><label class="block text-sm font-medium mb-1">Amount (IDR)</label><input name="amount" class="idr-input w-full border rounded px-3 py-2 text-sm" placeholder="Rp 0"></div>
            <div><label class="block text-sm font-medium mb-1">Status</label><select name="status" class="w-full border rounded px-3 py-2 text-sm">@foreach(['pending','approved','received'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
        </div>
        @endif
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded text-sm">Save</button>
            <a href="{{ route('finance.index') }}" class="text-gray-500 hover:underline text-sm py-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
