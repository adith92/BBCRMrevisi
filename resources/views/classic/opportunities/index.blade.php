@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $stageTone = ['prospecting'=>'amber','proposal'=>'blue','negotiation'=>'violet','won'=>'emerald','lost'=>'rose'];
    $stageIcon = ['prospecting'=>'search','proposal'=>'description','negotiation'=>'handshake','won'=>'verified','lost'=>'cancel'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Penjualan</div>
        <h1 class="bb-display" style="margin-top:6px;">Opportunities</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Semua peluang penjualan, funnel, dan kontribusi tim.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('pipeline.index') }}" class="bb-btn bb-btn-secondary">
            <span class="material-symbols-outlined" style="font-size:18px;">view_kanban</span>Kanban Board
        </a>
        @if(in_array(Auth::user()->role ?? '', ['gm','manager','sales']))
        <a href="{{ route('opportunities.create') }}" class="bb-btn bb-btn-primary">
            <span class="material-symbols-outlined" style="font-size:18px;">add</span>Buat Opportunity
        </a>
        @endif
    </div>
</div>

{{-- Stage Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;" class="bb-stage-grid">
@foreach($stageSummary as $s)
<div class="bb-card" style="padding:16px 18px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
        <span class="material-symbols-outlined" style="font-size:18px;color:var(--bb-accent);">{{ $stageIcon[$s['stage']] ?? 'circle' }}</span>
        <span class="bb-eyebrow" style="margin:0;">{{ $s['label'] }}</span>
    </div>
    <div class="bb-display" style="font-size:28px;line-height:1;">{{ $s['count'] }}</div>
    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $s['total_value_fmt'] }}</div>
</div>
@endforeach
</div>

{{-- Top Opportunities + Sales Contribution --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    {{-- Top Opportunities --}}
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Tertinggi</div>
        <h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Top 5 Opportunities</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($topOpportunities as $top)
            <a href="{{ $top['show_url'] }}" style="display:flex;align-items:center;justify-content:space-between;text-decoration:none;padding:8px 10px;border-radius:6px;transition:background .15s;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                <div style="min-width:0;">
                    <div style="font-weight:600;color:var(--text-strong);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $top['title'] }}</div>
                    <div class="bb-body-sm" style="color:var(--text-muted);">{{ $top['client_name'] }}</div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;margin-left:12px;">
                    <span class="bb-badge t-{{ $stageTone[$top['stage']] ?? 'slate' }}" style="font-size:10px;">{{ $top['stage_label'] }}</span>
                    <span class="bb-tnum" style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ $top['estimated_value_fmt'] }}</span>
                </div>
            </a>
        @empty
            <p style="color:var(--text-faint);font-size:13px;">Belum ada data.</p>
        @endforelse
        </div>
    </div>

    {{-- Sales Contribution --}}
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Kontribusi</div>
        <h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Per Sales</h3>
        @php $maxVal = max(1, ($salesContribution->max('total_value') ?? 1)); @endphp
        <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse($salesContribution->take(6) as $sc)
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $sc['name'] }}</span>
                <span style="display:flex;gap:8px;align-items:center;">
                    <span style="color:var(--text-muted);font-size:12px;">{{ $sc['count'] }} opps</span>
                    <span class="bb-tnum" style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ $sc['total_value_fmt'] }}</span>
                </span>
            </div>
            <div class="bb-progress"><span style="width:{{ round(($sc['total_value']/$maxVal)*100) }}%;"></span></div>
        </div>
        @empty
            <p style="color:var(--text-faint);font-size:13px;">Belum ada data kontribusi.</p>
        @endforelse
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card bb-filter-bar" style="padding:16px;margin-bottom:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:end;">
    <div>
        <label class="bb-field-label">Stage</label>
        <select name="stage" class="bb-select">
            <option value="">Semua Stage</option>
            @foreach(['prospecting'=>'Prospecting','proposal'=>'Proposal','negotiation'=>'Negotiation','won'=>'Won','lost'=>'Lost'] as $v=>$l)
            <option value="{{ $v }}" @selected(request('stage')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="bb-field-label">Klien</label>
        <select name="client_id" class="bb-select">
            <option value="">Semua Klien</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" @selected(request('client_id')==(string)$c->id)>{{ $c->company_name }}</option>
            @endforeach
        </select>
    </div>
    @if(!empty($managers) && $managers->count())
    <div>
        <label class="bb-field-label">Manager</label>
        <select name="manager_id" class="bb-select">
            <option value="">Semua Manager</option>
            @foreach($managers as $m)
            <option value="{{ $m->id }}" @selected(request('manager_id')==(string)$m->id)>{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    @if(!empty($salesUsers) && $salesUsers->count())
    <div>
        <label class="bb-field-label">Sales</label>
        <select name="sales_id" class="bb-select">
            <option value="">Semua Sales</option>
            @foreach($salesUsers as $s)
            <option value="{{ $s->id }}" @selected(request('sales_id')==(string)$s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
</form>

{{-- Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>#</th><th>Judul</th><th>Klien</th><th>Sales</th><th>Stage</th><th style="text-align:right;">Nilai</th><th>Tanggal</th>
        </tr></thead>
        <tbody>
        @forelse($opportunityRows as $row)
            <tr onclick="window.location='{{ $row['show_url'] }}'" style="cursor:pointer;">
                <td class="bb-tnum" style="color:var(--text-muted);font-size:12px;">{{ $row['opp_number'] }}</td>
                <td>
                    <div style="font-weight:600;color:var(--text-strong);">{{ $row['title'] }}</div>
                    @if($row['manager_name'] !== '-')
                    <div class="bb-body-sm" style="color:var(--text-muted);">via {{ $row['manager_name'] }}</div>
                    @endif
                </td>
                <td>
                    @if($row['client_url'])
                    <a href="{{ $row['client_url'] }}" onclick="event.stopPropagation();" style="color:var(--bb-accent);text-decoration:none;font-weight:500;">{{ $row['company_name'] }}</a>
                    @else
                    <span style="color:var(--text-muted);">{{ $row['company_name'] }}</span>
                    @endif
                </td>
                <td style="color:var(--text-muted);">{{ $row['sales_name'] }}</td>
                <td><span class="bb-badge t-{{ $stageTone[$row['stage']] ?? 'slate' }}">{{ $row['stage_label'] }}</span></td>
                <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $row['estimated_value_fmt'] }}</td>
                <td style="color:var(--text-muted);font-size:12px;">{{ $row['created_at_fmt'] }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada opportunity ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($opportunities instanceof \Illuminate\Contracts\Pagination\Paginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $opportunities->links() }}</div>
@endif

@endsection
