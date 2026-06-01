@extends('layouts.app')
@section('title', 'Bookings')
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-center justify-between">
    <form method="GET" class="flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search..." class="border rounded px-3 py-1.5 text-sm w-48">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','on_trip','completed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="bg-navy text-white px-4 py-1.5 rounded text-sm">Filter</button>
    </form>
    @if(auth()->user()->role==='sales')
    <a href="{{ route('bookings.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm hover:bg-blue/90">+ New Booking</a>
    @endif
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="px-4 py-3 text-left">No.</th><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-left">Vehicle</th><th class="px-4 py-3 text-left">Driver</th><th class="px-4 py-3 text-left">Pickup</th><th class="px-4 py-3 text-right">Price</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($bookings as $b)
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ $b->booking_number }}</td>
            <td class="px-4 py-3">{{ $b->client->company_name ?? '-' }}</td>
            <td class="px-4 py-3">{{ $b->vehicle->plate_number ?? '-' }}</td>
            <td class="px-4 py-3">{{ $b->driver->name ?? '-' }}</td>
            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($b->pickup_datetime)->format('d M Y H:i') }}</td>
            <td class="px-4 py-3 text-right">{{ formatIDR($b->price) }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $b->status==='completed'?'bg-green-100 text-green-700':($b->status==='cancelled'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700') }}">{{ $b->status }}</span></td>
            <td class="px-4 py-3 text-center">
                <a href="{{ route('bookings.show',$b) }}" class="text-blue hover:underline text-xs">View</a>
                @if(auth()->user()->role==='sales' && $b->sales_id===auth()->id())
                <a href="{{ route('bookings.edit',$b) }}" class="text-yellow-600 hover:underline text-xs ml-1">Edit</a>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">No bookings found</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $bookings->links() }}</div>
</div>
@endsection
