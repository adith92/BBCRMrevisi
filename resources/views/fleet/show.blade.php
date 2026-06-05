@extends('layouts.app')

@section('header_title', $vehicle->plate_number)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('fleet.index'), 'label' => 'Fleet'],
    ['url' => '#', 'label' => $vehicle->plate_number],
]" />

{{-- Hero --}}
<div class="bg-gradient-to-br from-blue-900 to-blue-700 rounded-lg shadow p-8 mb-6 text-white">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <p class="text-blue-200 text-sm uppercase tracking-widest mb-1">{{ ucfirst($vehicle->brand) }}</p>
            <h2 class="text-3xl font-bold">{{ $vehicle->plate_number }}</h2>
            <p class="text-blue-100 mt-2">{{ $vehicle->model }} · {{ $vehicle->capacity }} pax · {{ $vehicle->year }}</p>
            <p class="text-blue-200 text-sm mt-2">Pool: {{ $vehicle->pool?->name ?? 'Not assigned' }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <x-status-badge :status="$vehicle->status" />
            @if($activeBooking)
            <div class="bg-white/10 rounded-lg px-4 py-2 text-sm">
                <p class="text-blue-100">Currently assigned to:</p>
                <a href="{{ route('clients.show', $activeBooking->client_id) }}"
                   class="text-white font-semibold hover:underline">
                    {{ $activeBooking->client->company_name }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Active Booking Alert --}}
@if($activeBooking)
<div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
    <h3 class="font-semibold text-purple-900 mb-2">🚌 Currently On Trip</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-purple-600 text-xs">Booking</p>
            <a href="{{ route('bookings.show', $activeBooking->id) }}" class="font-semibold text-purple-900 hover:underline">
                {{ $activeBooking->booking_number }}
            </a>
        </div>
        <div>
            <p class="text-purple-600 text-xs">Client</p>
            <a href="{{ route('clients.show', $activeBooking->client_id) }}" class="font-semibold text-blue-600 hover:underline">
                {{ $activeBooking->client->company_name }}
            </a>
        </div>
        <div>
            <p class="text-purple-600 text-xs">Driver</p>
            <p class="font-semibold text-purple-900">{{ $activeBooking->driver->name }}</p>
        </div>
        <div>
            <p class="text-purple-600 text-xs">Sales</p>
            @if(auth()->user()->isGM())
                <a href="{{ route('sales.performance', $activeBooking->sales_id) }}" class="font-semibold text-blue-600 hover:underline">
                    {{ $activeBooking->sales->name }}
                </a>
            @else
                <p class="font-semibold text-purple-900">{{ $activeBooking->sales->name }}</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Next Maintenance Alert --}}
@if($nextMaintenance)
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-semibold text-yellow-900">🔧 Next Maintenance Scheduled</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ $nextMaintenance->type }} — {{ $nextMaintenance->description }}</p>
        </div>
        <div class="text-right">
            <p class="text-yellow-800 font-semibold">{{ \Carbon\Carbon::parse($nextMaintenance->scheduled_date)->format('d M Y') }}</p>
            <p class="text-xs text-yellow-600">{{ $nextMaintenance->vendor ?? 'No vendor' }}</p>
        </div>
    </div>
</div>
@endif

{{-- Booking History --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Booking History (Last 10)</h3>
        <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr class="text-gray-500">
                    <th class="text-left py-2">Booking #</th>
                    <th class="text-left py-2">Client</th>
                    <th class="text-left py-2">Sales</th>
                    <th class="text-left py-2">Pickup</th>
                    <th class="text-left py-2">Destination</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-2">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-blue-600 hover:underline">
                            {{ $booking->client->company_name }}
                        </a>
                    </td>
                    <td class="py-2">
                        @if(auth()->user()->isGM())
                            <a href="{{ route('sales.performance', $booking->sales_id) }}" class="text-blue-600 hover:underline">
                                {{ $booking->sales->name }}
                            </a>
                        @else
                            <span class="text-gray-700">{{ $booking->sales->name }}</span>
                        @endif
                    </td>
                    <td class="py-2 text-gray-600">{{ $booking->pickup_datetime->format('d M Y') }}</td>
                    <td class="py-2 text-gray-600">{{ $booking->destination }}</td>
                    <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                    <td class="py-2 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-4 text-center text-gray-500">No booking history</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Maintenance History --}}
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance History</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr class="text-gray-500">
                    <th class="text-left py-2">Type</th>
                    <th class="text-left py-2">Description</th>
                    <th class="text-left py-2">Vendor</th>
                    <th class="text-left py-2">Scheduled</th>
                    <th class="text-left py-2">Completed</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceLogs as $log)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 capitalize text-gray-700">{{ $log->type }}</td>
                    <td class="py-2 text-gray-600">{{ $log->description }}</td>
                    <td class="py-2 text-gray-600">{{ $log->vendor ?? '—' }}</td>
                    <td class="py-2 text-gray-600">{{ \Carbon\Carbon::parse($log->scheduled_date)->format('d M Y') }}</td>
                    <td class="py-2 text-gray-600">{{ $log->completed_date ? \Carbon\Carbon::parse($log->completed_date)->format('d M Y') : '—' }}</td>
                    <td class="py-2"><x-status-badge :status="$log->status" /></td>
                    <td class="py-2 text-right font-semibold">{{ $log->cost ? \App\Helpers\FormatHelper::formatIDR($log->cost) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-4 text-center text-gray-500">No maintenance records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
