@extends('layouts.app')
@section('title', 'Booking ' . $booking->booking_number)
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Client:</span> <strong>{{ $booking->client->company_name ?? '-' }}</strong></div>
        <div><span class="text-gray-500">Sales:</span> {{ $booking->sales->name ?? '-' }}</div>
        <div><span class="text-gray-500">Vehicle:</span> {{ $booking->vehicle->plate_number ?? '-' }} ({{ $booking->vehicle->model ?? '' }})</div>
        <div><span class="text-gray-500">Driver:</span> {{ $booking->driver->name ?? '-' }}</div>
        <div><span class="text-gray-500">Pickup:</span> {{ \Carbon\Carbon::parse($booking->pickup_datetime)->format('d M Y H:i') }}</div>
        <div><span class="text-gray-500">Drop-off:</span> {{ \Carbon\Carbon::parse($booking->dropoff_datetime)->format('d M Y H:i') }}</div>
        <div><span class="text-gray-500">Destination:</span> {{ $booking->destination }}</div>
        <div><span class="text-gray-500">Price:</span> <strong class="text-navy">{{ formatIDR($booking->price) }}</strong></div>
        <div><span class="text-gray-500">Status:</span> <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ $booking->status }}</span></div>
        @if($booking->notes)<div class="col-span-2"><span class="text-gray-500">Notes:</span> {{ $booking->notes }}</div>@endif
    </div>
    <div class="mt-4 flex gap-3">
        @if(auth()->user()->role==='sales' && $booking->sales_id===auth()->id())
        <a href="{{ route('bookings.edit',$booking) }}" class="bg-yellow-500 text-white px-4 py-2 rounded text-sm">Edit</a>
        @endif
        <a href="{{ route('bookings.index') }}" class="text-gray-500 hover:underline text-sm py-2">← Back</a>
    </div>
</div>
@endsection
