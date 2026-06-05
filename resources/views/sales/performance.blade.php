@extends('layouts.app')

@section('header_title', $user->name . ' — Performance')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => $user->name . ' Performance'],
]" />

{{-- Hero --}}
<div class="bg-gradient-to-br from-blue-900 to-indigo-800 rounded-lg shadow p-8 mb-6 text-white">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <p class="text-blue-200 text-sm uppercase tracking-widest mb-1">Sales Performance</p>
            <h2 class="text-3xl font-bold">{{ $user->name }}</h2>
            <p class="text-blue-200 mt-2">{{ $user->email }}</p>
        </div>
        <div class="text-right">
            <p class="text-blue-200 text-sm">Total Revenue</p>
            <p class="text-4xl font-bold">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_revenue']) }}</p>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('bookings.index', ['sales_id' => $user->id, 'status' => 'completed']) }}"
       class="group block bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-green-700">{{ $stats['completed'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Completed</p>
        <p class="text-xs text-green-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
    </a>
    <a href="{{ route('bookings.index', ['sales_id' => $user->id, 'status' => 'active']) }}"
       class="group block bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500 hover:shadow-md transition-all text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $stats['active'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Active</p>
        <p class="text-xs text-blue-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View active →</p>
    </a>
    <div class="bg-purple-50 rounded-lg shadow p-4 border-l-4 border-purple-500 text-center">
        <p class="text-2xl font-bold text-purple-700">{{ $stats['total_bookings'] }}</p>
        <p class="text-sm text-gray-600 mt-1">Total Bookings</p>
    </div>
    <div class="bg-indigo-50 rounded-lg shadow p-4 border-l-4 border-indigo-500 text-center">
        <p class="text-lg font-bold text-indigo-700">{{ \App\Helpers\FormatHelper::formatIDR($stats['avg_per_booking']) }}</p>
        <p class="text-sm text-gray-600 mt-1">Avg / Booking</p>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
        <div class="flex gap-2 text-sm">
            @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $p => $label)
                <a href="{{ route('sales.performance', ['user' => $user->id, 'period' => $p]) }}"
                   class="{{ $period === $p ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-3 py-1 rounded font-medium text-sm">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
    <canvas id="perfChart" height="80"></canvas>
</div>

{{-- Assigned Clients + Recent Bookings --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Assigned Clients --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Assigned Clients</h3>
            <a href="{{ route('clients.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View all →</a>
        </div>
        <div class="space-y-2">
            @forelse($clients->take(8) as $client)
            <div class="flex items-center justify-between py-2 border-b last:border-0">
                <div>
                    <a href="{{ route('clients.show', $client->id) }}"
                       class="text-blue-600 hover:underline font-medium text-sm">
                        {{ $client->company_name }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $client->industry }}</p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$client->status" />
                    <p class="text-xs text-gray-500 mt-1">{{ $client->bookings_count }} bookings</p>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">No assigned clients</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Bookings</h3>
            <a href="{{ route('bookings.index', ['sales_id' => $user->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm">View all →</a>
        </div>
        <div class="space-y-2">
            @forelse($bookings->take(8) as $booking)
            <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                <div>
                    <a href="{{ route('bookings.show', $booking->id) }}"
                       class="text-blue-600 hover:underline font-mono font-medium">
                        {{ $booking->booking_number }}
                    </a>
                    <div class="text-xs text-gray-500 mt-0.5">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-blue-500 hover:underline">
                            {{ $booking->client->company_name }}
                        </a>
                        · {{ $booking->pickup_datetime->format('d M') }}
                    </div>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$booking->status" />
                    <p class="text-xs font-semibold text-gray-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</p>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">No bookings yet</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
const chartData = @json($chartData);
const labels = chartData.map(d => d.label);
const values = chartData.map(d => parseFloat(d.value));

const ctx = document.getElementById('perfChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Revenue',
            data: values,
            backgroundColor: 'rgba(24, 95, 165, 0.7)',
            borderColor: '#185FA5',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
        },
        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
    }
});
</script>
@endpush
@endsection
