@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $statusTone = ['active'=>'emerald','prospect'=>'amber','inactive'=>'slate'];
    $irMax = max(1, ($industryRevenue ?? collect())->max('value') ?? 1);
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Penjualan</div>
        <h1 class="bb-display" style="margin-top:6px;">Clients</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Ringkasan klien korporat, kontribusi revenue, dan status follow-up.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','manager','sales']))
    <a href="{{ route('clients.create') }}" class="bb-btn bb-btn-primary"><span class="material-symbols-outlined" style="font-size:18px;">add</span>Klien Baru</a>
    @endif
</div>

{{-- Summary --}}
@php
$kpis = [
    ['eyebrow'=>'Total Klien','value'=>number_format($summary['total_clients'] ?? 0),'icon'=>'groups','accent'=>'blue'],
    ['eyebrow'=>'Active Revenue','value'=>$rp($summary['active_revenue'] ?? 0),'icon'=>'payments','accent'=>'green'],
    ['eyebrow'=>'Active Clients','value'=>number_format($summary['active_clients'] ?? 0),'icon'=>'verified','accent'=>'cyan'],
    ['eyebrow'=>'Top Industry','value'=>$summary['top_industry'] ?? '-','icon'=>'factory','accent'=>'violet'],
];
@endphp
@include('classic.partials.kpis', ['kpis'=>$kpis, 'cols'=>4])

{{-- Breakdown + industry --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Distribusi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Client Status</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($statusBreakdown as $row)
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span class="bb-badge t-{{ $row['tone'] }}">{{ $row['label'] }}</span>
                <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);">{{ number_format($row['value']) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Kontribusi</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Revenue by Industry</h3>
        @forelse($industryRevenue ?? [] as $ir)
        <div style="margin-bottom:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $ir['label'] }}</span>
                <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">{{ $rp($ir['value']) }}</span>
            </div>
            <div class="bb-progress"><span style="width:{{ round(($ir['value']/$irMax)*100) }}%;"></span></div>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Belum ada data revenue.</p>
        @endforelse
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card bb-filter-bar" style="padding:16px;margin-bottom:16px;display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end;">
    <div>
        <label class="bb-field-label">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama perusahaan, PIC, email, sales…" class="bb-input">
    </div>
    <div>
        <label class="bb-field-label">Status</label>
        <select name="filter_status" class="bb-select">
            <option value="">Semua Status</option>
            @foreach(['active'=>'Active','prospect'=>'Prospect','inactive'=>'Inactive'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('filter_status')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="bb-field-label">Urutkan</label>
        <select name="sort_by" class="bb-select">
            @foreach(['name_asc'=>'Nama A-Z','name_desc'=>'Nama Z-A','transactions_desc'=>'Transaksi','value_desc'=>'Nilai'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('sort_by','name_asc')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
</form>

{{-- Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Perusahaan</th><th>Industri</th><th>Sales</th><th>Status</th><th style="text-align:right;">Won</th><th style="text-align:right;">Revenue</th>
        </tr></thead>
        <tbody>
        @forelse($clients as $client)
            <tr onclick="window.location='{{ route('clients.show', $client) }}'" style="cursor:pointer;">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="bb-avatar-sm">{{ strtoupper(substr($client->company_name,0,2)) }}</div>
                        <div style="min-width:0;">
                            <div style="font-weight:600;color:var(--text-strong);">{{ $client->company_name }}</div>
                            <div class="bb-body-sm" style="color:var(--text-muted);">{{ $client->pic_name ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--text-muted);">{{ $client->industry ?? '—' }}</td>
                <td style="color:var(--text-muted);">{{ $client->assignedSales->name ?? '—' }}</td>
                <td><span class="bb-badge t-{{ $statusTone[$client->status] ?? 'slate' }}">{{ ucfirst($client->status) }}</span></td>
                <td class="bb-tnum" style="text-align:right;font-weight:600;color:var(--text-strong);">{{ number_format($client->won_opportunities_count ?? 0) }}</td>
                <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($client->won_opportunities_sum ?? 0) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada klien ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($clients instanceof \Illuminate\Contracts\Pagination\Paginator || $clients instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $clients->links() }}</div>
@endif

@endsection
