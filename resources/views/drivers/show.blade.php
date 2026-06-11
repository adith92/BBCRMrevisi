@extends('layouts.app')

@section('header_title', 'Driver Details')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('drivers.index'), 'label' => 'Drivers'],
    ['url' => '#', 'label' => $driver->name],
]" />

<div class="cc-card rounded-lg shadow p-6">
    <div class="flex items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            {{ $driver->name }}
        </h2>
        <x-status-badge :status="$driver->status" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <p class="text-[var(--cc-text-muted)] font-bold uppercase tracking-widest text-xs mb-1">Phone Number</p>
            <p class="text-[var(--cc-text)]">{{ $driver->phone_number }}</p>
        </div>
        <div>
            <p class="text-[var(--cc-text-muted)] font-bold uppercase tracking-widest text-xs mb-1">License Number</p>
            <p class="text-[var(--cc-text)]">{{ $driver->license_number }}</p>
        </div>
        <div>
            <p class="text-[var(--cc-text-muted)] font-bold uppercase tracking-widest text-xs mb-1">Pool</p>
            <p class="text-[var(--cc-text)]">{{ $driver->pool?->name ?? '—' }}</p>
        </div>
    </div>
</div>
@endsection
