@extends('layouts.app')
@php
    $statusTone = ['available'=>'emerald','assigned'=>'blue','reserved'=>'amber','inactive'=>'slate'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Operasional</div>
        <h1 class="bb-display" style="margin-top:6px;">Supir</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Manajemen supir, ketersediaan, dan penugasan per pool.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','manager','operational']))
    <a href="{{ route('drivers.create') }}" class="bb-btn bb-btn-primary">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>Tambah Supir
    </a>
    @endif
</div>

{{-- KPI Stats --}}
@php
$kpis = [
    ['eyebrow'=>'Total Supir','value'=>number_format($stats['total']),'icon'=>'badge','accent'=>'blue'],
    ['eyebrow'=>'Tersedia','value'=>number_format($stats['available']),'icon'=>'check_circle','accent'=>'green'],
    ['eyebrow'=>'Bertugas','value'=>number_format($stats['assigned']),'icon'=>'drive_eta','accent'=>'cyan'],
    ['eyebrow'=>'Cuti/Libur','value'=>number_format($stats['leave']),'icon'=>'beach_access','accent'=>'violet'],
];
@endphp
@include('classic.partials.kpis', ['kpis'=>$kpis,'cols'=>4])

{{-- Status Breakdown + Pool Summary --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Distribusi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Status Supir</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($driverStatusSummary as $row)
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="bb-badge t-{{ $statusTone[$row['status']] ?? 'slate' }}">{{ ucfirst($row['label']) }}</span>
            <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);">{{ $row['count'] }}</span>
        </div>
        @endforeach
        </div>
    </div>
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Lokasi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Per Pool</h3>
        @php $maxPool = max(1, ($driverPoolSummary->max('count') ?? 1)); @endphp
        <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($driverPoolSummary as $pool)
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
    <h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Penugasan Supir Tertunda ({{ $pendingAssignments->count() }})</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Opportunity</th><th>Klien</th><th>Supir Dibutuhkan</th><th>Sudah Ditetapkan</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($pendingAssignments as $opp)
        <tr>
            <td>
                <a href="{{ route('opportunities.show', $opp->id) }}" style="color:var(--bb-accent);font-weight:600;text-decoration:none;">
                    {{ $opp->title ?? 'Opp #'.$opp->id }}
                </a>
            </td>
            <td style="color:var(--text-muted);">{{ $opp->client->company_name ?? '—' }}</td>
            <td class="bb-tnum" style="font-weight:700;">{{ $opp->required_drivers }}</td>
            <td>
                <span class="bb-tnum" style="font-weight:700;">{{ $opp->assignedDrivers->count() }}</span>
                @if($opp->missing_drivers > 0)
                <span class="bb-badge t-rose" style="margin-left:4px;font-size:10px;">kurang {{ $opp->missing_drivers }}</span>
                @endif
            </td>
            <td>
                @if($opp->driver_status === 'pending')
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

{{-- Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:180px;">
        <label class="bb-field-label">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, telepon, SIM…" class="bb-input">
    </div>
    <div>
        <label class="bb-field-label">Status</label>
        <select name="status" class="bb-select" style="width:150px;">
            <option value="">Semua Status</option>
            @foreach(['available'=>'Available','assigned'=>'Bertugas','reserved'=>'Reserved','inactive'=>'Cuti'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Cari</button>
</form>

{{-- Drivers Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Supir</th><th>Pool</th><th>Status</th><th>Penugasan</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($drivers as $driver)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="bb-avatar-sm">{{ strtoupper(substr($driver->name,0,2)) }}</div>
                    <div>
                        <div style="font-weight:600;color:var(--text-strong);">{{ $driver->name }}</div>
                        <div class="bb-body-sm" style="color:var(--text-muted);">{{ $driver->phone ?? '—' }}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-muted);">{{ $driver->pool?->name ?? '—' }}</td>
            <td><span class="bb-badge t-{{ $statusTone[strtolower($driver->status ?? '')] ?? 'slate' }}">{{ ucfirst($driver->status ?? '—') }}</span></td>
            <td style="color:var(--text-muted);font-size:13px;">
                {{ $driver->assignedOpportunity?->title ?? ($driver->assignedOpportunity ? 'Opp #'.$driver->assignedOpportunity->id : '—') }}
            </td>
            <td style="text-align:right;">
                <a href="{{ route('drivers.show', $driver) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada supir ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
