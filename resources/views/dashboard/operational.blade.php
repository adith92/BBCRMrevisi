@extends('layouts.app')

@section('header_title', 'Operational Dashboard')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-green-500 hover:shadow-md hover:bg-green-50 transition-all">
            <p class="text-gray-500 text-sm">Available Fleet</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $availableFleet }}</p>
            <p class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View available →</p>
        </a>

        <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-md hover:bg-blue-50 transition-all">
            <p class="text-gray-500 text-sm">On Trip</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $onTripFleet }}</p>
            <p class="text-xs text-blue-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View on trip →</p>
        </a>

        <a href="{{ route('maintenance.index') }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-yellow-500 hover:shadow-md hover:bg-yellow-50 transition-all">
            <p class="text-gray-500 text-sm">Maintenance</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $maintenanceFleet }}</p>
            <p class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View maintenance →</p>
        </a>

        <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-purple-500 hover:shadow-md hover:bg-purple-50 transition-all">
            <p class="text-gray-500 text-sm">Active Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeBookings }}</p>
            <p class="text-xs text-purple-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View active →</p>
        </a>
    </div>

    <div class="cc-card rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Active Trips</h3>
            <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr class="text-gray-500">
                        <th class="text-left py-2">Booking #</th>
                        <th class="text-left py-2">Client</th>
                        <th class="text-left py-2">Vehicle</th>
                        <th class="text-left py-2">Driver</th>
                        <th class="text-left py-2">Pickup</th>
                        <th class="text-left py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeBookingList as $booking)
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="py-2">
                            <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $booking->booking_number }}
                            </a>
                        </td>
                        <td class="py-2 text-gray-700">{{ $booking->client->company_name }}</td>
                        <td class="py-2">
                            <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $booking->vehicle->plate_number }}
                            </a>
                        </td>
                        <td class="py-2 text-gray-700">{{ $booking->driver->name }}</td>
                        <td class="py-2 text-gray-600">{{ $booking->pickup_datetime->format('d M H:i') }}</td>
                        <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-4 text-center text-gray-500">No active trips right now</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
