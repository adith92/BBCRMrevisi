@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $statusTone = ['pending'=>'amber','confirmed'=>'blue','on_trip'=>'cyan','completed'=>'emerald','cancelled'=>'rose'];
    $statusIcon = ['pending'=>'schedule','confirmed'=>'verified','on_trip'=>'directions_car','completed'=>'check_circle','cancelled'=>'cancel'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Operasional</div>
        <h1 class="bb-display" style="margin-top:6px;">Dispatch & Booking</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Jadwal perjalanan, status, dan tren 7 hari terakhir.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','manager','sales','operational']))
    <a href="{{ route('bookings.create') }}" class="bb-btn bb-btn-primary">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>Booking Baru
    </a>
    @endif
</div>

{{-- Status Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
@foreach($statusSummary as $s)
<div class="bb-card" style="padding:16px 18px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">{{ $statusIcon[$s['status']] ?? 'circle' }}</span>
        <span class="bb-eyebrow" style="margin:0;">{{ $s['label'] }}</span>
    </div>
    <div class="bb-display" style="font-size:28px;line-height:1;">{{ number_format($s['count']) }}</div>
    @if($s['revenue'] > 0)
    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $rp($s['revenue']) }}</div>
    @endif
</div>
@endforeach
</div>

{{-- Booking Trend Chart --}}
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">7 Hari Terakhir</div>
    <h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Tren Booking Harian</h3>
    @php $maxTrend = max(1, $bookingTrend->max('count')); @endphp
    <div style="display:flex;align-items:flex-end;gap:8px;height:80px;">
    @foreach($bookingTrend as $t)
    @php $h = max(4, round(($t['count']/$maxTrend)*72)); @endphp
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
        <span class="bb-tnum" style="font-size:11px;color:var(--text-muted);">{{ $t['count'] ?: '' }}</span>
        <div style="width:100%;height:{{ $h }}px;background:var(--bb-accent);border-radius:4px 4px 0 0;opacity:.85;"></div>
        <span style="font-size:10px;color:var(--text-faint);">{{ $t['label'] }}</span>
    </div>
    @endforeach
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
        <label class="bb-field-label">Status</label>
        <select name="status" class="bb-select" style="width:160px;">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status')==='active')>Active (confirmed + on trip)</option>
            @foreach(['pending','confirmed','on_trip','completed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
    @if(request()->hasAny(['status','client_id']))
    <a href="{{ route('bookings.index') }}" class="bb-btn bb-btn-secondary" style="height:40px;">Reset</a>
    @endif
</form>

{{-- Bookings Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Booking</th><th>Klien</th><th>Kendaraan</th><th>Supir</th><th>Pickup</th><th>Status</th><th style="text-align:right;">Harga</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($bookings as $booking)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">#{{ $booking->id }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ $booking->sales?->name ?? '—' }}</div>
            </td>
            <td style="color:var(--text-muted);">{{ $booking->client?->company_name ?? '—' }}</td>
            <td style="color:var(--text-muted);">{{ $booking->vehicle?->plate_number ?? '—' }}</td>
            <td style="color:var(--text-muted);">{{ $booking->driver?->name ?? '—' }}</td>
            <td style="color:var(--text-muted);font-size:12px;">
                {{ $booking->pickup_datetime ? \Carbon\Carbon::parse($booking->pickup_datetime)->format('d M Y H:i') : '—' }}
            </td>
            <td><span class="bb-badge t-{{ $statusTone[$booking->status] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$booking->status)) }}</span></td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($booking->price ?? 0) }}</td>
            <td style="text-align:right;">
                <a href="{{ route('bookings.show', $booking) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada booking ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($bookings instanceof \Illuminate\Contracts\Pagination\Paginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $bookings->links() }}</div>
@endif

@endsection
