@extends('layouts.app')
@section('title', 'New Booking')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <form method="POST" action="{{ route('bookings.store') }}">@csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Client</label>
                <select name="client_id" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->company_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Vehicle</label>
                <select name="vehicle_id" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->plate_number }} ({{ $v->model }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Driver</label>
                <select name="driver_id" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Destination</label>
                <input name="destination" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Pickup Date/Time</label>
                <input name="pickup_datetime" type="datetime-local" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Drop-off Date/Time</label>
                <input name="dropoff_datetime" type="datetime-local" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Price (IDR)</label>
                <input name="price" class="idr-input w-full border rounded px-3 py-2 text-sm" placeholder="Rp 0">
            </div>
            @if(auth()->user()->role !== 'sales')
            <div>
                <label class="block text-sm font-medium mb-1">Sales</label>
                <select name="sales_id" class="w-full border rounded px-3 py-2 text-sm">
                    @foreach($salesUsers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            @endif
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded text-sm">Create Booking</button>
            <a href="{{ route('bookings.index') }}" class="text-gray-500 hover:underline text-sm py-2">Cancel</a>
        </div>
    </form>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>initIDRMasking());</script>
@endsection
