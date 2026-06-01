@extends('layouts.app')
@section('title', 'Vehicle ' . ($fleet->plate_number ?? ''))
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mb-4">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Plate:</span> <strong>{{ $fleet->plate_number }}</strong></div>
        <div><span class="text-gray-500">Brand:</span> <span class="px-2 py-0.5 rounded text-xs bg-navy/10 text-navy">{{ ucfirst($fleet->brand) }}</span></div>
        <div><span class="text-gray-500">Model:</span> {{ $fleet->model }}</div>
        <div><span class="text-gray-500">Capacity:</span> {{ $fleet->capacity }} pax</div>
        <div><span class="text-gray-500">Year:</span> {{ $fleet->year }}</div>
        <div><span class="text-gray-500">Pool:</span> {{ $fleet->pool->name ?? '-' }}</div>
        <div><span class="text-gray-500">Status:</span> <span class="px-2 py-0.5 rounded text-xs {{ $fleet->status==='available'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ ucfirst(str_replace('_',' ',$fleet->status)) }}</span></div>
    </div>
    <a href="{{ route('fleet.index') }}" class="text-gray-500 hover:underline text-sm mt-4 block">← Back</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Maintenance History</h3>
        @forelse($maintenanceLogs ?? [] as $m)
        <div class="border-b py-2 text-sm"><strong>{{ ucfirst($m->type) }}</strong> — {{ Str::limit($m->description,40) }} <span class="text-gray-400">({{ $m->scheduled_date->format('d M Y') }})</span></div>
        @empty<p class="text-gray-400 text-sm">No maintenance records</p>@endforelse
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Recent Bookings</h3>
        @forelse($bookings ?? [] as $b)
        <div class="border-b py-2 text-sm"><strong>{{ $b->booking_number }}</strong> — {{ $b->client->company_name ?? '-' }} <span class="text-gray-400">({{ \Carbon\Carbon::parse($b->pickup_datetime)->format('d M') }})</span></div>
        @empty<p class="text-gray-400 text-sm">No bookings</p>@endforelse
    </div>
</div>
@endsection
