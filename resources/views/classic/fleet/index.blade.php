@extends('layouts.app')
@php
    $statusTone = ['available'=>'emerald','rent_out'=>'blue','assigned'=>'blue','booked'=>'amber','reserved'=>'amber','hold'=>'violet','maintenance'=>'rose','inactive'=>'slate'];
    $statusIcon = ['available'=>'check_circle','rent_out'=>'directions_car','assigned'=>'directions_car','booked'=>'event','reserved'=>'schedule','hold'=>'pause_circle','maintenance'=>'build','inactive'=>'block'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Operasional</div>
        <h1 class="bb-display" style="margin-top:6px;">Armada</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Manajemen kendaraan, status, dan penugasan per pool.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','manager']))
    <a href="{{ route('fleet.create') }}" class="bb-btn bb-btn-primary">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>Tambah Kendaraan
    </a>
    @endif
</div>

{{-- KPI Stats --}}
@php
$kpis = [
    ['eyebrow'=>'Total Armada','value'=>number_format($stats['total']),'icon'=>'directions_car','accent'=>'blue'],
    ['eyebrow'=>'Tersedia','value'=>number_format($stats['available']),'icon'=>'check_circle','accent'=>'green'],
    ['eyebrow'=>'Terpakai','value'=>number_format($stats['rented']),'icon'=>'airport_shuttle','accent'=>'cyan'],
    ['eyebrow'=>'Maintenance','value'=>number_format($stats['maintenance']),'icon'=>'build','accent'=>'red'],
];
@endphp
@include('classic.partials.kpis', ['kpis'=>$kpis,'cols'=>4])

{{-- Status Breakdown + Pool Summary --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Distribusi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Status Kendaraan</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($fleetStatusSummary as $row)
        @if($row['count'] > 0)
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">{{ $statusIcon[$row['status']] ?? 'circle' }}</span>
                <span class="bb-badge t-{{ $statusTone[$row['status']] ?? 'slate' }}">{{ $row['label'] }}</span>
            </div>
            <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);">{{ $row['count'] }}</span>
        </div>
        @endif
        @endforeach
        </div>
    </div>
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Lokasi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Per Pool</h3>
        @php $maxPool = max(1, ($fleetPoolSummary->max('count') ?? 1)); @endphp
        <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($fleetPoolSummary as $pool)
        <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-weight:600;font-size:13px;color:var(--text-strong);">{{ $pool['pool'] }}</span>
                <span class="bb-tnum" style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ $pool['count'] }}</span>
            </div>
            <div class="bb-progress"><span style="width:{{ round(($pool['count']/$maxPool)*100) }}%;"></span></div>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Tidak ada data pool.</p>
        @endforelse
        </div>
    </div>
</div>

{{-- Pending Assignments --}}
@if($pendingAssignments->count())
<div class="bb-card" style="padding:20px;margin-bottom:20px;border-left:3px solid var(--bb-accent);">
    <div class="bb-eyebrow" style="color:var(--bb-accent);">Perlu Perhatian</div>
    <h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Penugasan Tertunda ({{ $pendingAssignments->count() }})</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Opportunity</th><th>Klien</th><th>Armada</th><th>Supir</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($pendingAssignments as $opp)
        <tr>
            <td>
                <a href="{{ route('opportunities.show', $opp->id) }}" style="color:var(--bb-accent);font-weight:600;text-decoration:none;">
                    {{ $opp->title ?? 'Opp #'.$opp->id }}
                </a>
            </td>
            <td style="color:var(--text-muted);">{{ $opp->client->company_name ?? '—' }}</td>
            <td>
                <span class="bb-tnum" style="font-weight:700;">{{ $opp->assignedVehicles->count() }}</span>
                <span style="color:var(--text-muted);font-size:12px;"> / {{ $opp->required_fleets }} required</span>
                @if($opp->missing_fleets > 0)
                <span class="bb-badge t-rose" style="margin-left:4px;font-size:10px;">-{{ $opp->missing_fleets }}</span>
                @endif
            </td>
            <td>
                <span class="bb-tnum" style="font-weight:700;">{{ $opp->assignedDrivers->count() }}</span>
                <span style="color:var(--text-muted);font-size:12px;"> / {{ $opp->required_drivers }} required</span>
                @if($opp->missing_drivers > 0)
                <span class="bb-badge t-rose" style="margin-left:4px;font-size:10px;">-{{ $opp->missing_drivers }}</span>
                @endif
            </td>
            <td>
                @if($opp->fleet_status === 'pending' || $opp->driver_status === 'pending')
                <span class="bb-badge t-amber">Pending</span>
                @else
                <span class="bb-badge t-emerald">Fulfilled</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- Fleet Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:180px;">
        <label class="bb-field-label">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Plat, model, catatan…" class="bb-input">
    </div>
    <div>
        <label class="bb-field-label">Status</label>
        <select name="status" class="bb-select" style="width:150px;">
            <option value="">Semua Status</option>
            @foreach(['available'=>'Available','rent_out'=>'Terpakai','booked'=>'Booked','hold'=>'Hold','maintenance'=>'Maintenance'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Cari</button>
</form>

{{-- Fleet Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Plat / Model</th><th>Pool</th><th>Status</th><th>Penugasan</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($vehicles as $vehicle)
        <tr>
            <td>
                <div style="font-weight:700;color:var(--text-strong);">{{ $vehicle->plate_number }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ $vehicle->model ?? '—' }} · {{ ucfirst($vehicle->brand ?? '') }}</div>
            </td>
            <td style="color:var(--text-muted);">{{ $vehicle->pool?->name ?? '—' }}</td>
            <td><span class="bb-badge t-{{ $statusTone[strtolower($vehicle->status ?? '')] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$vehicle->status ?? '—')) }}</span></td>
            <td style="color:var(--text-muted);font-size:13px;">
                {{ $vehicle->assignedOpportunity?->title ?? ($vehicle->assignedOpportunity ? 'Opp #'.$vehicle->assignedOpportunity->id : '—') }}
            </td>
            <td style="text-align:right;">
                <a href="{{ route('fleet.show', $vehicle) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada kendaraan ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
