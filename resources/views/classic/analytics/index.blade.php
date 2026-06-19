@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Intelligence</div>
        <h1 class="bb-display" style="margin-top:6px;">Analytics</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Analisis pipeline, top performers, dan aktivitas tim.</p>
    </div>
</div>

{{-- Pipeline by Stage --}}
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">Penjualan</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Pipeline Value by Stage</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Stage</th><th>Count</th><th style="text-align:right;">Total Value</th></tr></thead>
        <tbody>
        @php
            $stageLabels = ['call_meeting'=>'Call/Meeting','prospecting'=>'Prospecting','proposal'=>'Proposal','negotiation'=>'Negotiation'];
            $stageTones = ['call_meeting'=>'blue','prospecting'=>'amber','proposal'=>'blue','negotiation'=>'violet'];
        @endphp
        @forelse($pipelineByStage as $stage=>$data)
        <tr>
            <td><span class="bb-badge t-{{ $stageTones[$stage] ?? 'slate' }}">{{ $stageLabels[$stage] ?? ucfirst($stage) }}</span></td>
            <td class="bb-tnum" style="font-weight:700;">{{ $data->count ?? 0 }}</td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($data->total_value ?? 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center;color:var(--text-faint);padding:16px;">No pipeline data.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Top Clients by Revenue --}}
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">Klien</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Top 10 Clients by Revenue</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Client</th><th style="text-align:right;">Revenue</th></tr></thead>
        <tbody>
        @forelse($topClients as $client)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">{{ $client->company_name }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ $client->contact_person ?? '—' }}</div>
            </td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($client->total_revenue ?? 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="2" style="text-align:center;color:var(--text-faint);padding:16px;">No client data.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Top Sales by Won Deals --}}
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">Tim Penjualan</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Top 10 Sales by Won Deals</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Salesman</th><th>Won Count</th><th style="text-align:right;">Won Revenue</th></tr></thead>
        <tbody>
        @forelse($topSales as $sales)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">{{ $sales->name }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ ucfirst($sales->role) }}</div>
            </td>
            <td class="bb-tnum" style="font-weight:700;">{{ $sales->won_count ?? 0 }}</td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($sales->won_revenue ?? 0) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center;color:var(--text-faint);padding:16px;">No sales data.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Activity Summary Last 30 Days --}}
@if($activitySummary->count())
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">Aktivitas</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Last 30 Days</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;">
    @php
        $activityIcons = ['call'=>'phone','meeting'=>'person','visit'=>'place','opportunity'=>'star','comment'=>'message'];
    @endphp
    @foreach($activitySummary as $type=>$summary)
    <div class="bb-card" style="padding:14px 16px;background:var(--surface-2);">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;">{{ $activityIcons[$type] ?? 'check' }}</span>
            <span class="bb-eyebrow" style="margin:0;">{{ ucfirst($type) }}</span>
        </div>
        <div class="bb-display" style="font-size:24px;line-height:1;">{{ $summary->count ?? 0 }}</div>
    </div>
    @endforeach
    </div>
</div>
@endif

@endsection
