@extends('layouts.app')

@section('header_title', 'Drivers Management')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Drivers'],
]" />

{{-- Driver Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <!-- Available -->
    <a href="{{ route('drivers.index', ['status' => 'available']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-emerald-200 dark:border-emerald-900/30 bg-gradient-to-br from-emerald-50 to-emerald-100/30 dark:from-emerald-950/20 dark:to-emerald-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(16,185,129,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(16,185,129,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-emerald-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-emerald-500/20">check_circle</span>
        </div>
        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $stats['available'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-emerald-800/60 dark:text-emerald-400/60 mt-1">Available</p>
    </a>

    <!-- Assigned -->
    <a href="{{ route('drivers.index', ['status' => 'assigned']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-blue-200 dark:border-blue-900/30 bg-gradient-to-br from-blue-50 to-blue-100/30 dark:from-blue-950/20 dark:to-blue-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(59,130,246,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(59,130,246,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-blue-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-blue-500/20">assignment_ind</span>
        </div>
        <p class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">{{ $stats['assigned'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-blue-800/60 dark:text-blue-400/60 mt-1">Assigned</p>
    </a>

    <!-- Reserved -->
    <a href="{{ route('drivers.index', ['status' => 'reserved']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-orange-200 dark:border-orange-900/30 bg-gradient-to-br from-orange-50 to-orange-100/30 dark:from-orange-950/20 dark:to-orange-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(249,115,22,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(249,115,22,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-orange-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-orange-500/20">hourglass_empty</span>
        </div>
        <p class="text-3xl font-black text-orange-600 dark:text-orange-400 tracking-tight">{{ $stats['reserved'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-orange-800/60 dark:text-orange-400/60 mt-1">Reserved</p>
    </a>

    <!-- Inactive -->
    <div class="group relative block overflow-hidden rounded-2xl p-5 border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-50 to-slate-100/30 dark:from-slate-900/40 dark:to-slate-850/10 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(100,116,139,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(100,116,139,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-slate-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-slate-500/20">cancel</span>
        </div>
        <p class="text-3xl font-black text-slate-600 dark:text-slate-400 tracking-tight">{{ $stats['inactive'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-800/60 dark:text-slate-400/60 mt-1">Inactive</p>
    </div>
</div>

{{-- Drivers List --}}
<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            All Drivers
            <span class="text-sm font-normal text-[var(--cc-text-muted)] ml-2">({{ $drivers->total() }} total)</span>
        </h2>
        
        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
            {{-- Filter by status --}}
            <div class="flex gap-1 text-sm bg-[var(--cc-bg-muted)] rounded-lg p-1 border border-[var(--cc-border)]">
                <a href="{{ route('drivers.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">All</a>
                <a href="{{ route('drivers.index', ['status' => 'available']) }}" class="{{ request('status') === 'available' ? 'bg-green-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">Available</a>
                <a href="{{ route('drivers.index', ['status' => 'assigned']) }}" class="{{ request('status') === 'assigned' ? 'bg-blue-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">Assigned</a>
                <a href="{{ route('drivers.index', ['status' => 'reserved']) }}" class="{{ request('status') === 'reserved' ? 'bg-orange-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">Reserved</a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm resizable-table" data-table-id="drivers-table">
            <thead class="border-b bg-[var(--cc-bg-muted)]">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="text-left py-3 px-4">Name</th>
                    <th class="text-left py-3 px-4">Phone</th>
                    <th class="text-left py-3 px-4">License Number</th>
                    <th class="text-left py-3 px-4">Pool</th>
                    <th class="text-left py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="py-3 px-4">
                        <span class="font-bold text-[var(--cc-text)]">{{ $driver->name }}</span>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $driver->phone_number }}</td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $driver->license_number }}</td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $driver->pool?->name ?? '—' }}</td>
                    <td class="py-3 px-4"><x-status-badge :status="$driver->status" /></td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-[var(--cc-text-muted)]">No drivers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $drivers->links() }}</div>
</div>

@endsection
