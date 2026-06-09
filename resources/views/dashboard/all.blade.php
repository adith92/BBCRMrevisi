<!-- resources/views/dashboard/operational.blade.php -->
@extends('layouts.app')

@section('header_title', 'Operational Dashboard')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm">Available Fleet</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $availableFleet }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm">On Trip</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $onTripFleet }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm">Maintenance</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $maintenanceFleet }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm">Active Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $activeBookings }}</p>
        </div>
    </div>

    <div class="cc-card rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">Fleet Management</h3>
        <p class="text-gray-500">Manage vehicles, pool, and maintenance in dedicated modules</p>
    </div>
</div>
@endsection

<!-- resources/views/dashboard/finance.blade.php -->
@extends('layouts.app')

@section('header_title', 'Finance Dashboard')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm">Today Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm">Month Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm">Pending Invoice</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $pendingInvoice }}</p>
        </div>
        <div class="cc-card rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm">Outstanding</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Helpers\FormatHelper::formatIDR($outstanding) }}</p>
        </div>
    </div>

    <div class="cc-card rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold">Financial Summary</h3>
        <p class="text-gray-500 mt-2">Total Paid This Month: {{ \App\Helpers\FormatHelper::formatIDR($paidThisMonth) }}</p>
    </div>
</div>
@endsection
