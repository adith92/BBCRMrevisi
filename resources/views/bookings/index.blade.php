@extends('layouts.app')

@section('header_title', 'Bookings')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Bookings'],
]" />

<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-wrap gap-2 justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            Bookings
            @if(request('status'))
                <span class="text-sm font-normal text-[var(--cc-text-muted)]">— {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
            @endif
        </h2>
        <div class="flex flex-wrap gap-2 items-center">
            {{-- Status filters --}}
            <div class="flex gap-1 text-xs">
                <a href="{{ route('bookings.index', \Illuminate\Support\Arr::except(request()->query(), ['status'])) }}"
                   class="{{ !request('status') ? 'bg-blue-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">All</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'active'])) }}"
                   class="{{ request('status') === 'active' ? 'bg-purple-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Active</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                   class="{{ request('status') === 'pending' ? 'bg-yellow-500 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Pending</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                   class="{{ request('status') === 'completed' ? 'bg-green-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Completed</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
                   class="{{ request('status') === 'cancelled' ? 'bg-red-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Cancelled</a>
            </div>

            @if(auth()->user()->isSales() || auth()->user()->isGM() || auth()->user()->isOperational())
                <a href="{{ route('bookings.create') }}" class="btn-3d">
                    ➕ New Booking
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mb-6">
        <div class="xl:col-span-4 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)]/45 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-[var(--cc-text)]">Status Booking</h3>
                <span class="material-symbols-outlined text-blue-400 text-[18px]">donut_large</span>
            </div>
            <div class="h-52"><canvas id="booking-status-chart"></canvas></div>
        </div>
        <div class="xl:col-span-5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)]/45 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-[var(--cc-text)]">Booking 7 Hari</h3>
                <span class="material-symbols-outlined text-emerald-400 text-[18px]">stacked_line_chart</span>
            </div>
            <div class="h-52"><canvas id="booking-trend-chart"></canvas></div>
        </div>
        <div class="xl:col-span-3 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)]/45 p-4">
            <h3 class="text-sm font-bold text-[var(--cc-text)] mb-3">Revenue by Status</h3>
            <div class="space-y-3">
                @forelse($statusSummary as $row)
                    <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => $row['status']])) }}" class="block rounded-lg border border-[var(--cc-border)] bg-[var(--cc-surface)] px-3 py-2 hover:border-blue-500/50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold text-[var(--cc-text)]">{{ $row['label'] }}</span>
                            <span class="text-[10px] text-[var(--cc-text-muted)]">{{ $row['count'] }} booking</span>
                        </div>
                        <div class="mt-1 text-sm font-mono font-bold text-emerald-400">{{ \App\Helpers\FormatHelper::formatIDR($row['revenue']) }}</div>
                    </a>
                @empty
                    <div class="rounded-lg border border-dashed border-[var(--cc-border)] p-4 text-center text-xs text-[var(--cc-text-muted)]">Belum ada booking.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[var(--cc-bg-muted)] border-b">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="px-4 py-3 text-left font-semibold">Booking #</th>
                    <th class="px-4 py-3 text-left font-semibold">Client</th>
                    <th class="px-4 py-3 text-left font-semibold">Sales</th>
                    <th class="px-4 py-3 text-left font-semibold">Vehicle</th>
                    <th class="px-4 py-3 text-left font-semibold">Pickup</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono font-semibold">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-[var(--cc-text)] hover:text-blue-600 hover:underline">
                            {{ $booking->client->company_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sales.performance', $booking->sales_id) }}" class="text-blue-600 hover:underline">
                            {{ $booking->sales->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        @if(auth()->user()->isGM() || auth()->user()->isOperational())
                            <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $booking->vehicle->plate_number }}
                            </a>
                        @else
                            <span class="font-mono text-[var(--cc-text)]">{{ $booking->vehicle->plate_number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[var(--cc-text-muted)]">{{ $booking->pickup_datetime->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :status="$booking->status"
                            :link="route('bookings.index', array_merge(request()->query(), ['status' => $booking->status]))" />
                    </td>
                    <td class="px-4 py-3 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-[var(--cc-text-muted)]">No bookings found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const textColor = getComputedStyle(document.documentElement).getPropertyValue('--cc-text-muted').trim() || '#94a3b8';
    const cardColor = getComputedStyle(document.documentElement).getPropertyValue('--cc-card').trim() || '#111827';
    const statusRows = @json($statusSummary ?? []);
    const trendRows = @json($bookingTrend ?? []);

    const statusCanvas = document.getElementById('booking-status-chart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusRows.map(row => row.label),
                datasets: [{
                    data: statusRows.map(row => row.count),
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#64748b'],
                    borderColor: cardColor,
                    borderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: { legend: { position: 'bottom', labels: { color: textColor } } }
            }
        });
    }

    const trendCanvas = document.getElementById('booking-trend-chart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'bar',
            data: {
                labels: trendRows.map(row => row.label),
                datasets: [{
                    label: 'Booking',
                    data: trendRows.map(row => row.count),
                    backgroundColor: 'rgba(59, 130, 246, 0.72)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { display: false } },
                    y: { ticks: { color: textColor, precision: 0 }, grid: { color: 'rgba(148,163,184,0.15)' } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
