@extends('layouts.app')

@section('page-title', 'Sales Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Revenue KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('finance.index', ['filter' => 'today']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-md hover:bg-blue-50 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Today</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
            <p class="text-xs text-blue-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'week']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500 hover:shadow-md hover:bg-indigo-50 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Week</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($weekRevenue) }}</p>
            <p class="text-xs text-indigo-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'month']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 hover:shadow-md hover:bg-purple-50 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Month</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
            <p class="text-xs text-purple-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'year']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-green-500 hover:shadow-md hover:bg-green-50 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Year</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($yearRevenue) }}</p>
            <p class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>
    </div>

    {{-- Operational KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500 hover:shadow-md hover:bg-yellow-50 transition-all">
            <p class="text-gray-500 text-sm">Active Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeBookings }}</p>
            <p class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
        </a>

        <a href="{{ route('clients.index') }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-orange-500 hover:shadow-md hover:bg-orange-50 transition-all">
            <p class="text-gray-500 text-sm">My Clients</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $myClients }}</p>
            <p class="text-xs text-orange-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View my clients →</p>
        </a>

        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <a href="{{ route('bookings.create') }}" class="block w-full bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-semibold hover:bg-blue-700 transition-colors">
                ➕ New Booking
            </a>
        </div>
    </div>

    {{-- My Performance Link --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">My Performance</h3>
                <p class="text-gray-500 text-sm mt-1">View detailed breakdown of your bookings and revenue</p>
            </div>
            <a href="{{ route('sales.performance', ['user' => auth()->id()]) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium transition-colors">
                View Performance →
            </a>
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Bookings</h3>
            <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr class="text-gray-500">
                    <th class="text-left py-2">Booking #</th>
                    <th class="text-left py-2">Client</th>
                    <th class="text-left py-2">Vehicle</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    <td class="py-2">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-2">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-gray-800 hover:text-blue-600">
                            {{ $booking->client->company_name }}
                        </a>
                    </td>
                    <td class="py-2 text-gray-600">{{ $booking->vehicle->plate_number }}</td>
                    <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                    <td class="py-2 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-500">No recent bookings</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
