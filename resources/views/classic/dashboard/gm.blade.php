@extends('layouts.app')

@php
    $rpShort = function ($n) {
        $n = (float) $n;
        if ($n >= 1.0e9) return 'Rp ' . rtrim(rtrim(number_format($n / 1.0e9, 2, ',', '.'), '0'), ',') . ' M';
        if ($n >= 1.0e6) return 'Rp ' . rtrim(rtrim(number_format($n / 1.0e6, 1, ',', '.'), '0'), ',') . ' Jt';
        if ($n >= 1.0e3) return 'Rp ' . number_format($n / 1.0e3, 0, ',', '.') . ' Rb';
        return 'Rp ' . number_format($n, 0, ',', '.');
    };
    $kpis = [
        ['eyebrow'=>'Revenue MTD','value'=>$rpShort($totalMonthlyBooked ?? 0),'icon'=>'account_balance_wallet','accent'=>'blue','delta'=>'+12,4%','deltaUp'=>true],
        ['eyebrow'=>'Bookings','value'=>number_format($activeBookings ?? $completedBookings ?? 0),'icon'=>'event_available','accent'=>'cyan','delta'=>'+9','deltaUp'=>true],
        ['eyebrow'=>'Fleet Aktif','value'=>($utilizationRate ?? 0).'%','icon'=>'local_shipping','accent'=>'green','delta'=>'util','deltaUp'=>true],
        ['eyebrow'=>'Clients','value'=>number_format($activeClients ?? 0),'icon'=>'corporate_fare','accent'=>'violet','delta'=>'+6','deltaUp'=>true],
        ['eyebrow'=>'Outstanding','value'=>$rpShort($outstandingInvoices ?? 0),'icon'=>'request_quote','accent'=>'gold','delta'=>'overdue','deltaUp'=>false],
        ['eyebrow'=>'Approval','value'=>number_format($pendingDispatch ?? 0),'icon'=>'verified','accent'=>'red','delta'=>'perlu aksi','deltaUp'=>false],
    ];
    $wMax = max(1, max($weeklyRevenue ?? [1]));
@endphp

@section('content')

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
    <div>
        <div class="bb-eyebrow">Bluebird CRM · Director HQ</div>
        <h1 class="bb-display" style="margin-top:6px;">Command Center</h1>
        <p style="color:var(--text-muted);margin-top:6px;max-width:560px;">Pandangan menyeluruh atas revenue, pipeline, armada, dan persetujuan lintas tim dalam satu layar.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('bookings.index') }}" class="bb-btn bb-btn-ghost"><span class="material-symbols-outlined" style="font-size:18px;">approval</span>Approve Queue</a>
        @if(in_array(Auth::user()->role ?? '', ['gm','manager']))
        <a href="{{ route('analytics.index') }}" class="bb-btn bb-btn-primary"><span class="material-symbols-outlined" style="font-size:18px;">summarize</span>Reports</a>
        @endif
    </div>
</div>

{{-- KPI grid --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:16px;margin-bottom:20px;" class="bb-kpi-grid">
    @foreach($kpis as $k)
    <div class="bb-kpi">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div class="bb-eyebrow" style="color:var(--text-muted);">{{ $k['eyebrow'] }}</div>
            <div class="bb-chip" style="background:var(--accent-{{ $k['accent'] }}-bg);color:var(--accent-{{ $k['accent'] }}-fg);">
                <span class="material-symbols-outlined" style="font-size:19px;">{{ $k['icon'] }}</span>
            </div>
        </div>
        <div class="bb-tnum" style="font-family:var(--font-brand);font-weight:700;font-size:26px;color:var(--text-strong);margin-top:12px;line-height:1.1;">{{ $k['value'] }}</div>
        <div style="margin-top:8px;font-size:12px;font-weight:600;color:{{ $k['deltaUp'] ? 'var(--status-success-fg)' : 'var(--status-error-fg)' }};display:flex;align-items:center;gap:4px;">
            <span class="material-symbols-outlined" style="font-size:15px;">{{ $k['deltaUp'] ? 'trending_up' : 'trending_down' }}</span>{{ $k['delta'] }}
        </div>
    </div>
    @endforeach
</div>

{{-- Revenue trend + Fleet league --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <div><div class="bb-eyebrow">Tren 7 Hari</div><h3 class="bb-h3" style="margin-top:4px;">Revenue Trend</h3></div>
            <span class="bb-badge" style="background:var(--status-success-bg);color:var(--status-success-fg);"><span class="material-symbols-outlined" style="font-size:13px;">north_east</span>+12,4%</span>
        </div>
        <div style="display:flex;align-items:flex-end;gap:14px;height:200px;padding-top:10px;">
            @foreach(($weeklyRevenue ?? []) as $i => $val)
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end;">
                <div class="bb-tnum" style="font-size:12px;font-weight:700;color:var(--text-strong);">{{ $val }}</div>
                <div style="width:100%;border-radius:6px 6px 0 0;background:{{ $loop->last ? 'linear-gradient(180deg,var(--bb-tertiary),var(--bb-primary))' : 'var(--bb-muted-2)' }};height:{{ max(6, round(($val / $wMax) * 150)) }}px;"></div>
                <div style="font-size:11px;color:var(--text-faint);">{{ $weeklyLabels[$i] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Per Sub-Brand</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:16px;">Fleet League</h3>
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach(($fleetLeague ?? []) as $idx => $fl)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="display:flex;align-items:center;gap:8px;"><span style="color:var(--text-faint);font-size:12px;font-weight:700;width:14px;">{{ $idx+1 }}</span><span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $fl['name'] }}</span></div>
                    <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">{{ $fl['pct'] }}%</span>
                </div>
                <div class="bb-progress"><span style="width:{{ $fl['pct'] }}%;background:{{ $fl['color'] }};"></span></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Sales ranking + Pipeline --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Top Performers</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:16px;">Sales Ranking</h3>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @php $sMax = max(1, max($salesLeaderboardData ?? [1])); @endphp
            @foreach(($salesLeaderboardLabels ?? []) as $i => $name)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="display:flex;align-items:center;gap:8px;"><span style="color:var(--text-faint);font-size:12px;font-weight:700;width:14px;">{{ $i+1 }}</span><span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $name }}</span></div>
                    <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">Rp {{ number_format($salesLeaderboardData[$i] ?? 0, 1, ',', '.') }}M</span>
                </div>
                <div class="bb-progress"><span style="width:{{ round((($salesLeaderboardData[$i] ?? 0) / $sMax) * 100) }}%;"></span></div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Deals per Stage</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:16px;">Pipeline</h3>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach(($pipelineLabels ?? []) as $i => $label)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $label }}</span>
                    <span class="bb-tnum" style="font-weight:700;color:var(--text-strong);font-size:13px;">{{ $pipelineDistribution[$i]['count'] ?? 0 }}</span>
                </div>
                <div class="bb-progress"><span style="width:{{ $pipelinePct[$i] ?? 0 }}%;background:{{ $pipelineColors[$i] ?? 'var(--bb-primary)' }};"></span></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
@media (max-width: 1100px){ .bb-kpi-grid{grid-template-columns:repeat(3,1fr)!important;} .bb-two-col{grid-template-columns:1fr!important;} }
@media (max-width: 640px){ .bb-kpi-grid{grid-template-columns:repeat(2,1fr)!important;} }
</style>
@endpush
@endsection
