@extends('layouts.app')

@section('header_title', 'Fleet Management')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Fleet'],
]" />

{{-- Fleet Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="group block bg-green-50 rounded-lg p-4 border-l-4 border-green-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-green-700">{{ $stats['available'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1">Available</p>
    </a>
    <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="group block bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $stats['on_trip'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1">On Trip</p>
    </a>
    <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" class="group block bg-orange-50 rounded-lg p-4 border-l-4 border-orange-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-orange-700">{{ $stats['maintenance'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1">Maintenance</p>
    </a>
    <div class="bg-[var(--cc-bg-muted)] rounded-lg p-4 border-l-4 border-gray-400 text-center">
        <p class="text-2xl font-bold text-[var(--cc-text)]">{{ $stats['inactive'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1">Inactive</p>
    </div>
</div>

{{-- Vehicle Grid --}}
<div class="cc-card rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            All Vehicles
            <span class="text-sm font-normal text-[var(--cc-text-muted)] ml-2">({{ $vehicles->total() }} total)</span>
        </h2>
        {{-- Filter by status --}}
        <div class="flex gap-2 text-sm">
            <a href="{{ route('fleet.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded hover:opacity-90">All</a>
            <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="{{ request('status') === 'available' ? 'bg-green-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded hover:opacity-90">Available</a>
            <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="{{ request('status') === 'on_trip' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded hover:opacity-90">On Trip</a>
            <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" class="{{ request('status') === 'maintenance' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-[var(--cc-text)]' }} px-3 py-1 rounded hover:opacity-90">Maintenance</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-[var(--cc-bg-muted)]">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="text-left py-3 px-4">Plate Number</th>
                    <th class="text-left py-3 px-4">Brand</th>
                    <th class="text-left py-3 px-4">Model</th>
                    <th class="text-center py-3 px-4">Capacity</th>
                    <th class="text-center py-3 px-4">Year</th>
                    <th class="text-left py-3 px-4">Pool</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-center py-3 px-4">Bookings</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $vehicle->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-mono font-semibold hover:underline">
                            {{ $vehicle->plate_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <span class="capitalize text-[var(--cc-text)]">{{ $vehicle->brand }}</span>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text)]">{{ $vehicle->model }}</td>
                    <td class="py-3 px-4 text-center text-[var(--cc-text-muted)]">{{ $vehicle->capacity }} pax</td>
                    <td class="py-3 px-4 text-center text-[var(--cc-text-muted)]">{{ $vehicle->year }}</td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $vehicle->pool?->name ?? '—' }}</td>
                    <td class="py-3 px-4"><x-status-badge :status="$vehicle->status" /></td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}"
                           class="text-blue-600 hover:underline font-medium">
                            {{ $vehicle->bookings_count }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-[var(--cc-text-muted)]">No vehicles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $vehicles->links() }}</div>

    @include('fleet.charts')
</div>
@endsection
