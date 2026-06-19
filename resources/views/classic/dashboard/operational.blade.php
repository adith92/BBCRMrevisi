@extends('layouts.app')
@php
    $kpis = [
        ['eyebrow'=>'Available','value'=>number_format($availableFleet ?? 0),'icon'=>'check_circle','accent'=>'green','delta'=>'siap jalan','deltaUp'=>true],
        ['eyebrow'=>'On Trip','value'=>number_format($onTripFleet ?? 0),'icon'=>'local_shipping','accent'=>'blue','delta'=>'aktif','deltaUp'=>true],
        ['eyebrow'=>'Maintenance','value'=>number_format($maintenanceFleet ?? 0),'icon'=>'build','accent'=>'gold','delta'=>'perbaikan','deltaUp'=>false],
        ['eyebrow'=>'Active Bookings','value'=>number_format($activeBookings ?? 0),'icon'=>'route','accent'=>'violet','delta'=>'berjalan','deltaUp'=>true],
    ];
@endphp
@section('content')
<div style="margin-bottom:24px;">
    <div class="bb-eyebrow">Operations</div>
    <h1 class="bb-display" style="margin-top:6px;">Operational Dashboard</h1>
    <p style="color:var(--text-muted);margin-top:6px;">Status armada, dispatch aktif, dan kebutuhan penugasan.</p>
</div>

@include('classic.partials.kpis', ['kpis'=>$kpis, 'cols'=>4])

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Dispatch</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:12px;">Active Bookings</h3>
        @forelse(($activeBookingList ?? []) as $b)
        <div class="bb-list-row">
            <div style="min-width:0;">
                <div style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $b->client->company_name ?? ('Booking #'.$b->id) }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ optional($b->start_date ?? $b->created_at)->format('d M Y') }}</div>
            </div>
            <span class="bb-badge" style="background:var(--status-info-bg);color:var(--status-info-fg);">{{ str_replace('_',' ',$b->status ?? '-') }}</span>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Tidak ada booking aktif.</p>
        @endforelse
    </div>

    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Butuh Penugasan</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:12px;">Unassigned Opportunities</h3>
        @forelse(($unassignedOpportunities ?? []) as $o)
        <div class="bb-list-row">
            <div style="min-width:0;">
                <div style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $o->client->company_name ?? 'Opportunity' }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">Sales: {{ $o->sales->name ?? '-' }}</div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                @if(($o->missing_fleets ?? 0) > 0)<span class="bb-badge" style="background:var(--status-warning-bg);color:var(--status-warning-fg);">{{ $o->missing_fleets }} unit</span>@endif
                @if(($o->missing_drivers ?? 0) > 0)<span class="bb-badge" style="background:var(--status-error-bg);color:var(--status-error-fg);">{{ $o->missing_drivers }} supir</span>@endif
            </div>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Semua sudah ditugaskan. 🎉</p>
        @endforelse
    </div>
</div>
@endsection
