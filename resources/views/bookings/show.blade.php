@extends('layouts.app')

@section('header_title', $booking->booking_number)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('bookings.index'), 'label' => 'Bookings'],
    ['url' => '#', 'label' => $booking->booking_number],
]" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Header --}}
        <div class="cc-card rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-[var(--cc-text)] font-mono">{{ $booking->booking_number }}</h2>
                    <p class="text-[var(--cc-text-muted)] text-sm mt-1">Created: {{ $booking->created_at->format('d M Y H:i') }}</p>
                </div>
                <x-status-badge :status="$booking->status" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-[var(--cc-border)]/50 pt-6">
                {{-- Client Info --}}
                <div>
                    <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide mb-3">Client</p>
                    <a href="{{ route('clients.show', $booking->client_id) }}"
                       class="text-blue-500 hover:underline font-semibold block text-lg">
                        {{ $booking->client->company_name }}
                    </a>
                    <div class="mt-2 space-y-1 text-sm text-[var(--cc-text-muted)]">
                        <p>PIC: <span class="text-[var(--cc-text)] font-medium">{{ $booking->client->pic_name }}</span></p>
                        <p>📞 {{ $booking->client->phone }}</p>
                        <p>✉️ {{ $booking->client->email }}</p>
                    </div>
                </div>

                {{-- Sales & Driver --}}
                <div>
                    <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide mb-3">Sales & Driver</p>
                    <div class="space-y-2 text-sm">
                        <div>
                            <p class="text-[var(--cc-text-muted)]">Sales Person</p>
                            <a href="{{ route('sales.performance', $booking->sales_id) }}"
                               class="text-blue-500 hover:underline font-semibold">
                                {{ $booking->sales->name }}
                            </a>
                        </div>
                        <div>
                            <p class="text-[var(--cc-text-muted)]">Driver</p>
                            <p class="font-semibold text-[var(--cc-text)]">{{ $booking->driver->name }}</p>
                            <p class="text-xs text-[var(--cc-text-muted)]">📞 {{ $booking->driver->phone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Trip Details --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Trip Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-[var(--cc-text-muted)]">Pickup</p>
                    <p class="font-semibold text-[var(--cc-text)] mt-1">{{ $booking->pickup_datetime->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-[var(--cc-text-muted)]">Dropoff</p>
                    <p class="font-semibold text-[var(--cc-text)] mt-1">{{ $booking->dropoff_datetime->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-[var(--cc-text-muted)]">Vehicle</p>
                    @if(auth()->user()->isGM() || auth()->user()->isOperational())
                        <a href="{{ route('fleet.show', $booking->vehicle_id) }}"
                           class="text-blue-500 hover:underline font-semibold font-mono mt-1 block">
                            {{ $booking->vehicle->plate_number }}
                        </a>
                        <p class="text-[var(--cc-text-muted)] text-xs">{{ $booking->vehicle->model }} · {{ $booking->vehicle->capacity }} pax</p>
                    @else
                        <p class="font-semibold font-mono text-[var(--cc-text)] mt-1">{{ $booking->vehicle->plate_number }}</p>
                        <p class="text-[var(--cc-text-muted)] text-xs">{{ $booking->vehicle->model }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-[var(--cc-text-muted)]">Destination</p>
                    <p class="font-semibold text-[var(--cc-text)] mt-1">{{ $booking->destination }}</p>
                    <p class="text-[var(--cc-text-muted)] text-xs mt-0.5">
                        Duration: {{ $booking->pickup_datetime->diffInHours($booking->dropoff_datetime) }} hours
                    </p>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($booking->notes)
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-2">Notes</h3>
            <p class="text-[var(--cc-text-muted)] text-sm">{{ $booking->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Financial --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Financial</h3>
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-400 font-medium">Booking Price</p>
                <p class="text-3xl font-bold text-blue-500 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</p>
            </div>

            @if($booking->invoice)
            <div class="mt-4 pt-4 border-t border-[var(--cc-border)]/50">
                <p class="text-xs text-[var(--cc-text-muted)] mb-2">Invoice</p>
                @if(auth()->user()->isGM() || auth()->user()->isFinance())
                    <a href="{{ route('invoices.show', $booking->invoice->id) }}"
                       class="text-blue-500 hover:underline font-mono block font-semibold mb-2">
                        {{ $booking->invoice->invoice_number }}
                    </a>
                    <x-status-badge :status="$booking->invoice->status" />
                @else
                    <p class="font-mono text-[var(--cc-text)] text-sm mb-2">{{ $booking->invoice->invoice_number }}</p>
                    <x-status-badge :status="$booking->invoice->status" />
                @endif
            </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Quick Links</h3>
            <div class="space-y-2 text-sm">
                <a href="{{ route('clients.show', $booking->client_id) }}"
                   class="flex items-center gap-2 text-blue-500 hover:text-blue-400 hover:underline">
                    👥 View Client Profile
                </a>
                @if(auth()->user()->isGM() || auth()->user()->isOperational())
                <a href="{{ route('fleet.show', $booking->vehicle_id) }}"
                   class="flex items-center gap-2 text-blue-500 hover:text-blue-400 hover:underline">
                    🚌 View Vehicle Detail
                </a>
                @endif
                @if(auth()->user()->isGM())
                <a href="{{ route('sales.performance', $booking->sales_id) }}"
                   class="flex items-center gap-2 text-blue-500 hover:text-blue-400 hover:underline">
                    📈 View Sales Performance
                </a>
                @if($booking->invoice)
                <a href="{{ route('invoices.show', $booking->invoice->id) }}"
                   class="flex items-center gap-2 text-blue-500 hover:text-blue-400 hover:underline">
                    🧾 View Invoice
                </a>
                @endif
                @endif
            </div>
        </div>

        {{-- Back Button --}}
        <a href="{{ route('bookings.index') }}" class="block bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] py-2.5 rounded-xl hover:bg-[var(--cc-surface)] text-center font-semibold text-sm transition-all">
            ← Back to Bookings
        </a>
    </div>
</div>
@endsection
