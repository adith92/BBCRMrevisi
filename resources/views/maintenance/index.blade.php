@extends('layouts.app')

@section('header_title', 'Maintenance')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Maintenance'],
]" />

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('maintenance.index', ['status' => 'scheduled']) }}" class="group block bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-yellow-700">{{ $stats['scheduled'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Scheduled</p>
    </a>
    <a href="{{ route('maintenance.index', ['status' => 'in_progress']) }}" class="group block bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $stats['in_progress'] }}</p>
        <p class="text-sm text-gray-600 mt-1">In Progress</p>
    </a>
    <a href="{{ route('maintenance.index', ['status' => 'completed']) }}" class="group block bg-green-50 rounded-lg p-4 border-l-4 border-green-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-green-700">{{ $stats['completed'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Completed</p>
    </a>
    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-gray-400 text-center">
        <p class="text-lg font-bold text-gray-700">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_cost']) }}</p>
        <p class="text-sm text-gray-600 mt-1">Total Cost</p>
    </div>
</div>

{{-- Maintenance Logs --}}
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Maintenance Logs</h2>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('maintenance.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded">All</a>
            <a href="{{ route('maintenance.index', ['status' => 'scheduled']) }}" class="{{ request('status') === 'scheduled' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded">Scheduled</a>
            <a href="{{ route('maintenance.index', ['status' => 'in_progress']) }}" class="{{ request('status') === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded">In Progress</a>
            <a href="{{ route('maintenance.index', ['status' => 'completed']) }}" class="{{ request('status') === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded">Completed</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr class="text-gray-600">
                    <th class="text-left py-3 px-4">Vehicle</th>
                    <th class="text-left py-3 px-4">Type</th>
                    <th class="text-left py-3 px-4">Description</th>
                    <th class="text-left py-3 px-4">Vendor</th>
                    <th class="text-left py-3 px-4">Scheduled</th>
                    <th class="text-left py-3 px-4">Completed</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $log->vehicle_id) }}"
                           class="text-blue-600 hover:underline font-mono font-semibold">
                            {{ $log->vehicle->plate_number }}
                        </a>
                        <div class="text-xs text-gray-400">{{ $log->vehicle->model }}</div>
                    </td>
                    <td class="py-3 px-4 capitalize text-gray-700">{{ $log->type }}</td>
                    <td class="py-3 px-4 text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
                    <td class="py-3 px-4 text-gray-500">{{ $log->vendor ?? '—' }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ \Carbon\Carbon::parse($log->scheduled_date)->format('d M Y') }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $log->completed_date ? \Carbon\Carbon::parse($log->completed_date)->format('d M Y') : '—' }}</td>
                    <td class="py-3 px-4"><x-status-badge :status="$log->status" /></td>
                    <td class="py-3 px-4 text-right font-semibold">{{ $log->cost ? \App\Helpers\FormatHelper::formatIDR($log->cost) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-gray-500">No maintenance records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>

@if($upcomingPOs->count())
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Purchase Orders</h3>
    <div class="space-y-3">
        @foreach($upcomingPOs as $po)
        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div>
                <p class="font-mono font-semibold text-gray-900 text-sm">{{ $po->po_number }}</p>
                <p class="text-xs text-gray-500">{{ $po->vendor }} — {{ Str::limit($po->item_description, 50) }}</p>
            </div>
            <div class="text-right">
                <p class="font-semibold text-gray-900">{{ \App\Helpers\FormatHelper::formatIDR($po->amount) }}</p>
                <x-status-badge :status="$po->status" />
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
