@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $kpis = [
        ['eyebrow'=>'Hari Ini','value'=>$rp($todayRevenue ?? 0),'icon'=>'today','accent'=>'blue'],
        ['eyebrow'=>'Minggu Ini','value'=>$rp($weekRevenue ?? 0),'icon'=>'date_range','accent'=>'cyan'],
        ['eyebrow'=>'Bulan Ini','value'=>$rp($monthRevenue ?? 0),'icon'=>'calendar_month','accent'=>'green'],
        ['eyebrow'=>'Tahun Ini','value'=>$rp($yearRevenue ?? 0),'icon'=>'event','accent'=>'violet'],
        ['eyebrow'=>'My Clients','value'=>number_format($myClients ?? 0),'icon'=>'corporate_fare','accent'=>'gold'],
        ['eyebrow'=>'Active Bookings','value'=>number_format($activeBookings ?? 0),'icon'=>'route','accent'=>'red'],
    ];
    $funnelLabels = ['Call/Meeting','Prospecting','Proposal','Negotiation','Won'];
    $fMax = max(1, max($salesFunnel ?? [1]));
    $rtMax = max(1, max($revenueTrend['data'] ?? [1]));
    $tPct = ($hasTarget ?? false) && ($targetRevenue ?? 0) > 0 ? min(100, round(($monthRevenue/$targetRevenue)*100)) : 0;
@endphp
@section('content')
<div style="margin-bottom:24px;">
    <div class="bb-eyebrow">Sales Officer</div>
    <h1 class="bb-display" style="margin-top:6px;">My Dashboard</h1>
    <p style="color:var(--text-muted);margin-top:6px;">Performa penjualan pribadi, funnel, dan pencapaian target.</p>
</div>

@include('classic.partials.kpis', ['kpis'=>$kpis, 'cols'=>6])

@if($hasTarget ?? false)
<div class="bb-card" style="padding:18px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <div><span class="bb-eyebrow">Target Bulan Ini</span> <span style="color:var(--text-strong);font-weight:600;margin-left:6px;">{{ $rp($monthRevenue ?? 0) }} / {{ $rp($targetRevenue ?? 0) }}</span></div>
        <span class="bb-tnum" style="font-weight:700;color:var(--bb-primary);">{{ $tPct }}%</span>
    </div>
    <div class="bb-progress" style="height:8px;"><span style="width:{{ $tPct }}%;"></span></div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">6 Bulan Terakhir</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:12px;">Revenue Trend</h3>
        <div style="display:flex;align-items:flex-end;gap:12px;height:190px;padding-top:10px;">
            @foreach(($revenueTrend['data'] ?? []) as $i => $val)
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end;">
                <div style="width:100%;border-radius:6px 6px 0 0;background:{{ $loop->last ? 'linear-gradient(180deg,var(--bb-tertiary),var(--bb-primary))' : 'var(--bb-muted-2)' }};height:{{ max(6, round(($val/$rtMax)*140)) }}px;"></div>
                <div style="font-size:10px;color:var(--text-faint);">{{ $revenueTrend['labels'][$i] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Pipeline</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:16px;">Sales Funnel</h3>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach($funnelLabels as $i => $label)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $label }}</span>
                    <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">{{ $salesFunnel[$i] ?? 0 }}</span>
                </div>
                <div class="bb-progress"><span style="width:{{ round((($salesFunnel[$i] ?? 0)/$fMax)*100) }}%;"></span></div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
