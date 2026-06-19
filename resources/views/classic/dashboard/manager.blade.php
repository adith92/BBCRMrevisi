@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $kpis = [
        ['eyebrow'=>'Team Pipeline','value'=>$rp($teamPipelineValue ?? 0),'icon'=>'trending_up','accent'=>'blue','delta'=>'aktif','deltaUp'=>true],
        ['eyebrow'=>'Deals Won','value'=>number_format($teamWon ?? 0),'icon'=>'emoji_events','accent'=>'green','delta'=>'bulan ini','deltaUp'=>true],
        ['eyebrow'=>'Deals Lost','value'=>number_format($teamLost ?? 0),'icon'=>'cancel','accent'=>'red','delta'=>'bulan ini','deltaUp'=>false],
        ['eyebrow'=>'Team Members','value'=>number_format(($teamMembers ?? collect())->count()),'icon'=>'groups','accent'=>'violet','delta'=>'sales rep','deltaUp'=>true],
    ];
    $rtMax = max(1, max($revenueTrend['data'] ?? [1]));
@endphp
@section('content')
<div style="margin-bottom:24px;">
    <div class="bb-eyebrow">Sales Management</div>
    <h1 class="bb-display" style="margin-top:6px;">Manager Dashboard</h1>
    <p style="color:var(--text-muted);margin-top:6px;">Performa tim, pipeline, dan pencapaian target sales.</p>
</div>

@include('classic.partials.kpis', ['kpis'=>$kpis, 'cols'=>4])

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Top Performers</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:12px;">Sales Ranking</h3>
        @forelse(($topPerformers ?? []) as $i => $m)
        <div class="bb-list-row">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <span style="color:var(--text-faint);font-weight:700;width:14px;">{{ $i+1 }}</span>
                <div style="min-width:0;">
                    <div style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $m->name }}</div>
                    <div class="bb-body-sm" style="color:var(--text-muted);">Win rate {{ $m->win_rate ?? 0 }}% · {{ $m->won_count ?? 0 }} won</div>
                </div>
            </div>
            <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">{{ $rp($m->won_revenue ?? 0) }}</span>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Belum ada data.</p>
        @endforelse
    </div>

    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">6 Bulan Terakhir</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:12px;">Revenue Trend</h3>
        <div style="display:flex;align-items:flex-end;gap:12px;height:200px;padding-top:10px;">
            @foreach(($revenueTrend['data'] ?? []) as $i => $val)
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end;">
                <div style="width:100%;border-radius:6px 6px 0 0;background:{{ $loop->last ? 'linear-gradient(180deg,var(--bb-tertiary),var(--bb-primary))' : 'var(--bb-muted-2)' }};height:{{ max(6, round(($val/$rtMax)*150)) }}px;"></div>
                <div style="font-size:10px;color:var(--text-faint);text-align:center;">{{ $revenueTrend['labels'][$i] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
