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
        <p class="text-sm text-gray-600 mt-1">Available</p>
    </a>
    <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="group block bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $stats['on_trip'] }}</p>
        <p class="text-sm text-gray-600 mt-1">On Trip</p>
    </a>
    <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" class="group block bg-orange-50 rounded-lg p-4 border-l-4 border-orange-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-orange-700">{{ $stats['maintenance'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Maintenance</p>
    </a>
    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-gray-400 text-center">
        <p class="text-2xl font-bold text-gray-700">{{ $stats['inactive'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Inactive</p>
    </div>
</div>

{{-- Vehicle Grid --}}
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">
            All Vehicles
            <span class="text-sm font-normal text-gray-500 ml-2">({{ $vehicles->total() }} total)</span>
        </h2>
        {{-- Filter by status --}}
        <div class="flex gap-2 text-sm">
            <a href="{{ route('fleet.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded hover:opacity-90">All</a>
            <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="{{ request('status') === 'available' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded hover:opacity-90">Available</a>
            <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="{{ request('status') === 'on_trip' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded hover:opacity-90">On Trip</a>
            <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" class="{{ request('status') === 'maintenance' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded hover:opacity-90">Maintenance</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr class="text-gray-600">
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
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $vehicle->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-mono font-semibold hover:underline">
                            {{ $vehicle->plate_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <span class="capitalize text-gray-700">{{ $vehicle->brand }}</span>
                    </td>
                    <td class="py-3 px-4 text-gray-700">{{ $vehicle->model }}</td>
                    <td class="py-3 px-4 text-center text-gray-600">{{ $vehicle->capacity }} pax</td>
                    <td class="py-3 px-4 text-center text-gray-600">{{ $vehicle->year }}</td>
                    <td class="py-3 px-4 text-gray-500">{{ $vehicle->pool?->name ?? '—' }}</td>
                    <td class="py-3 px-4"><x-status-badge :status="$vehicle->status" /></td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}"
                           class="text-blue-600 hover:underline font-medium">
                            {{ $vehicle->bookings_count }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-gray-500">No vehicles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $vehicles->links() }}</div>
</div>
@endsection
