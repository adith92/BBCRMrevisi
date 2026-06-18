@extends('layouts.app')

@section('header_title', 'Operational Dashboard')

@section('content')
@php
    $pendingFleetOnly = $unassignedOpportunities->where('missing_fleets', '>', 0)->count();
    $pendingDriverOnly = $unassignedOpportunities->where('missing_drivers', '>', 0)->count();
    $activeConfirmed = $activeBookingList->where('status', 'confirmed')->count();
    $activeOnTrip = $activeBookingList->where('status', 'on_trip')->count();
    $priorityQueue = $unassignedOpportunities
        ->map(function ($opp) {
            $totalMissing = ($opp->missing_fleets ?? 0) + ($opp->missing_drivers ?? 0);
            $priority = $totalMissing >= 8 ? 'Critical' : ($totalMissing >= 4 ? 'High' : 'Medium');
            $priorityClass = $priority === 'Critical'
                ? 'bg-red-500/10 text-red-400 border-red-500/20'
                : ($priority === 'High'
                    ? 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                    : 'bg-sky-500/10 text-sky-400 border-sky-500/20');

            return (object) [
                'model' => $opp,
                'total_missing' => $totalMissing,
                'priority' => $priority,
                'priority_class' => $priorityClass,
            ];
        })
        ->sortByDesc('total_missing')
        ->values();
    $criticalCount = $priorityQueue->where('priority', 'Critical')->count();
    $highCount = $priorityQueue->where('priority', 'High')->count();
    $mediumCount = $priorityQueue->where('priority', 'Medium')->count();
@endphp
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    <div class="grid-stack-item" gs-id="w-operational-overview" gs-x="0" gs-y="0" gs-w="12" gs-h="3">
        <div class="grid-stack-item-content">
            <section class="cc-card rounded-[28px] border border-[var(--cc-border)] px-6 py-6 lg:px-7 lg:py-7 h-full overflow-hidden">
                <div class="flex h-full flex-col justify-between gap-6 lg:flex-row lg:items-start">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-indigo-400">
                            Command Center
                        </div>
                        <h1 class="mt-4 text-2xl font-semibold tracking-tight text-[var(--cc-text)] lg:text-3xl">
                            Operational Control Tower
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--cc-text-muted)]">
                            Ringkasan cepat untuk pergerakan booking aktif, kapasitas armada, dan antrean alokasi yang masih perlu tindakan hari ini.
                        </p>
                    </div>

                    <div class="grid w-full max-w-2xl grid-cols-2 gap-3 lg:grid-cols-4">
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-400">Fleet Ready</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $availableFleet }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">unit siap jalan</p>
                        </div>
                        <div class="rounded-2xl border border-sky-500/20 bg-sky-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-400">Trip Live</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $activeBookings }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">booking aktif</p>
                        </div>
                        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-400">Fleet Queue</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $pendingFleetOnly }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">butuh kendaraan</p>
                        </div>
                        <div class="rounded-2xl border border-fuchsia-500/20 bg-fuchsia-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-fuchsia-400">Driver Queue</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $pendingDriverOnly }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">butuh supir</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid-stack-item" gs-id="w-available-fleet" gs-x="0" gs-y="3" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-green-500 h-full transition-all hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Available Fleet</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $availableFleet }}</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View available →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-on-trip" gs-x="3" gs-y="3" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full transition-all hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">On Trip</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $onTripFleet }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View on trip →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-maintenance" gs-x="6" gs-y="3" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('maintenance.index') }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-yellow-500 h-full transition-all hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Maintenance</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $maintenanceFleet }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View maintenance →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-active-bookings" gs-x="9" gs-y="3" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-purple-500 h-full transition-all hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Active Bookings</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $activeBookings }}</p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View active →</p>
            </a>
        </div>
    </div>

    {{-- Active Trips Table --}}
    <div class="grid-stack-item" gs-id="w-active-trips" gs-x="0" gs-y="5" gs-w="12" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-[24px] shadow p-5 h-full overflow-auto border border-[var(--cc-border)]">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--cc-text)]">Active Trips Monitor</h3>
                        <p class="mt-1 text-sm text-[var(--cc-text-muted)]">Pantau perjalanan yang sedang confirmed atau on trip tanpa harus pindah halaman.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-400">
                            Confirmed {{ $activeConfirmed }}
                        </span>
                        <span class="rounded-full border border-violet-500/20 bg-violet-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-violet-400">
                            On Trip {{ $activeOnTrip }}
                        </span>
                        <a href="{{ route('bookings.index') }}" class="rounded-full border border-[var(--cc-border)] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--cc-text-muted)] transition hover:border-blue-500/30 hover:text-blue-400">
                            View All
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b" style="border-color:var(--cc-border)">
                            <tr style="color:var(--cc-text-muted)">
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
                            <tr class="border-b hover:bg-black/5 dark:hover:bg-gray-100/5 transition-colors" style="border-color:var(--cc-border)">
                                <td class="py-2">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono">
                                        {{ $booking->booking_number }}
                                    </a>
                                </td>
                                <td class="py-2 text-[var(--cc-text)]"><a href="{{ route('clients.show', $booking->client->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $booking->client->company_name }}</a></td>
                                <td class="py-2">
                                    <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono">
                                        {{ $booking->vehicle->plate_number }}
                                    </a>
                                </td>
                                <td class="py-2 text-[var(--cc-text)]">{{ $booking->driver->name }}</td>
                                <td class="py-2 text-[var(--cc-text-muted)]">{{ $booking->pickup_datetime->format('d M H:i') }}</td>
                                <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8">
                                    <div class="mx-auto max-w-md rounded-2xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)] px-6 py-6 text-center">
                                        <span class="material-symbols-outlined text-4xl text-emerald-400">route</span>
                                        <p class="mt-3 font-semibold text-[var(--cc-text)]">Belum ada trip aktif saat ini</p>
                                        <p class="mt-1 text-sm text-[var(--cc-text-muted)]">Begitu booking confirmed atau on trip masuk, daftar ini akan otomatis terisi.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Required Panel --}}
    <div class="grid-stack-item" gs-id="w-action-required" gs-x="0" gs-y="11" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-[24px] shadow p-5 h-full overflow-auto border border-red-500/20 bg-gradient-to-br from-red-500/[0.08] via-transparent to-transparent">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined rounded-2xl border border-red-500/20 bg-red-500/10 p-2 text-red-400">warning</span>
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--cc-text)]">Action Required Queue</h3>
                            <p class="mt-1 text-sm text-[var(--cc-text-muted)]">Daftar opportunity yang sudah berjalan tetapi alokasi kendaraan atau supirnya belum lengkap.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.2em] bg-red-500/10 text-red-400 border border-red-500/20">
                            {{ $unassignedOpportunities->count() }} Pending
                        </span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.2em] bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            Fleet {{ $pendingFleetOnly }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.2em] bg-fuchsia-500/10 text-fuchsia-400 border border-fuchsia-500/20">
                            Driver {{ $pendingDriverOnly }}
                        </span>
                    </div>
                </div>
                <div class="mb-5 grid gap-3 lg:grid-cols-[1.25fr_1fr]">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-red-400">Critical</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $criticalCount }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">missing besar, perlu cepat</p>
                        </div>
                        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-400">High</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $highCount }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">butuh tindak lanjut hari ini</p>
                        </div>
                        <div class="rounded-2xl border border-sky-500/20 bg-sky-500/10 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-400">Medium</p>
                            <p class="mt-2 text-2xl font-semibold text-[var(--cc-text)]">{{ $mediumCount }}</p>
                            <p class="mt-1 text-xs text-[var(--cc-text-muted)]">masih bisa dijadwalkan</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)] px-4 py-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-[var(--cc-text)]">Top Priority</h4>
                            <span class="text-[11px] uppercase tracking-[0.2em] text-[var(--cc-text-muted)]">Compact View</span>
                        </div>
                        <div class="space-y-2">
                            @forelse($priorityQueue->take(3) as $entry)
                                <a href="{{ route('fleet.index') }}?assign_opp={{ $entry->model->id }}" class="flex items-center justify-between gap-3 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-3 py-2.5 transition hover:-translate-y-0.5 hover:border-indigo-500/30">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--cc-text)]">{{ $entry->model->title }}</p>
                                        <p class="truncate text-xs text-[var(--cc-text-muted)]">{{ $entry->model->client->company_name ?? 'Tanpa client' }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-full border px-2 py-1 text-[10px] font-bold uppercase tracking-[0.16em] {{ $entry->priority_class }}">
                                            {{ $entry->priority }}
                                        </span>
                                        <span class="text-sm font-semibold text-[var(--cc-text)]">{{ $entry->total_missing }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-4 text-sm text-emerald-400">
                                    Semua alokasi sudah aman.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b" style="border-color:var(--cc-border)">
                            <tr style="color:var(--cc-text-muted)">
                                <th class="text-left py-2">Oportunitas</th>
                                <th class="text-left py-2">Client</th>
                                <th class="text-left py-2">Sales</th>
                                <th class="text-left py-2">Missing Allocation</th>
                                <th class="text-left py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($priorityQueue as $entry)
                            @php
                                $opp = $entry->model;
                                $missing = [];
                                if ($opp->missing_fleets > 0) $missing[] = 'Kendaraan (' . $opp->missing_fleets . ' unit)';
                                if ($opp->missing_drivers > 0) $missing[] = 'Supir (' . $opp->missing_drivers . ' orang)';
                            @endphp
                            <tr class="border-b transition-colors hover:bg-black/5 dark:hover:bg-gray-100/5" style="border-color:var(--cc-border)">
                                <td class="py-2.5">
                                    <a href="{{ route('opportunities.show', $opp->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ $opp->title }}
                                    </a>
                                    <span class="text-xs text-[var(--cc-text-muted)] block font-mono">{{ $opp->opp_number }}</span>
                                </td>
                                <td class="py-2.5 text-[var(--cc-text)]">
                                    @if($opp->client)
                                        <a href="{{ route('clients.show', $opp->client->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $opp->client->company_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-2.5 text-[var(--cc-text)]">{{ $opp->sales->name ?? '—' }}</td>
                                <td class="py-2.5">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.16em] {{ $entry->priority_class }}">
                                            {{ $entry->priority }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-xs text-red-400 font-medium">
                                            {{ implode(' & ', $missing) }} belum di-assign
                                        </span>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <a href="{{ route('fleet.index') }}?assign_opp={{ $opp->id }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 px-3 py-1.5 text-xs font-semibold transition-all">
                                        <span class="material-symbols-outlined text-[14px]">link</span> Assign Now
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-emerald-500 font-medium">
                                    🎉 Semua opportunity WON sudah memiliki alokasi kendaraan dan supir!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-dashboard-grid>
@endsection
