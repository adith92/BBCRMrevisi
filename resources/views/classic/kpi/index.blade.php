@extends('layouts.app')
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Penjualan</div>
        <h1 class="bb-display" style="margin-top:6px;">KPI & Target</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Pantau target penjualan, deals, dan metrik kinerja tim.</p>
    </div>
</div>

{{-- Filter: Year, Month, User --}}
<div class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
        <label class="bb-field-label">Tahun</label>
        <select name="year" class="bb-select" style="width:100px;" onchange="window.location.href=`?year=${this.value}&month={{ $month }}@if($selectedTargetUserId)&user_id={{ $selectedTargetUserId }}@endif`">
            @php $currentYear = now()->year; @endphp
            @foreach(range($currentYear-2, $currentYear+1) as $y)
            <option value="{{ $y }}" @selected($year==$y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="bb-field-label">Bulan</label>
        <select name="month" class="bb-select" style="width:120px;" onchange="window.location.href=`?year={{ $year }}&month=${this.value}@if($selectedTargetUserId)&user_id={{ $selectedTargetUserId }}@endif`">
            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $i=>$m)
            <option value="{{ $i+1 }}" @selected($month==($i+1))>{{ $m }}</option>
            @endforeach
        </select>
    </div>
    @if($assignableUsers->count())
    <div>
        <label class="bb-field-label">User</label>
        <select name="user_id" class="bb-select" style="width:160px;" onchange="window.location.href=`?year={{ $year }}&month={{ $month }}&user_id=${this.value}`">
            @foreach($assignableUsers as $u)
            <option value="{{ $u->id }}" @selected($selectedTargetUserId==$u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
</div>

{{-- User Info Card --}}
@if($selectedTarget)
<div class="bb-card" style="padding:16px 18px;margin-bottom:14px;border-left:3px solid var(--bb-accent);">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div class="bb-eyebrow">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</div>
            <h3 class="bb-h3" style="margin-top:4px;">{{ $selectedTarget->user?->name ?? 'Unknown' }}</h3>
            <div class="bb-body-sm" style="color:var(--text-muted);margin-top:4px;">Role: <strong>{{ ucfirst($selectedTarget->user?->role ?? '—') }}</strong></div>
        </div>
        @if(in_array(Auth::user()->role ?? '', ['gm','manager']))
        <a href="{{ route('kpi.targets') }}?year={{ $year }}&month={{ $month }}&user_id={{ $selectedTargetUserId }}" class="bb-btn bb-btn-primary">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>Edit Targets
        </a>
        @endif
    </div>
</div>

{{-- Target Summary Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:20px;">
    @php
        $targetFields = [
            ['key'=>'target_revenue','label'=>'Revenue Target','unit'=>'Rp'],
            ['key'=>'target_opportunities','label'=>'Opportunities','unit'=>''],
            ['key'=>'target_won','label'=>'Won Deals','unit'=>''],
            ['key'=>'target_meetings','label'=>'Meetings','unit'=>''],
            ['key'=>'target_calls','label'=>'Calls','unit'=>''],
            ['key'=>'target_visits','label'=>'Visits','unit'=>''],
        ];
    @endphp
    @foreach($targetFields as $field)
    <div class="bb-card" style="padding:14px 16px;">
        <div class="bb-eyebrow" style="font-size:11px;">{{ $field['label'] }}</div>
        <div class="bb-display" style="font-size:18px;line-height:1;margin-top:4px;">
            @if($field['key']==='target_revenue' && $selectedTarget->{$field['key']})
                Rp {{ number_format($selectedTarget->{$field['key']}, 0, ',', '.') }}
            @else
                {{ $selectedTarget->{$field['key']} ?? '—' }}
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Active Deals Table --}}
<div class="bb-card" style="padding:20px;margin-bottom:20px;">
    <div class="bb-eyebrow">Pipeline</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Active Deals</h3>
    <div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr><th>Title</th><th>Client</th><th>Product</th><th>Estimated</th><th>Status</th></tr></thead>
        <tbody>
        @php
            $userDeals = collect($deals)->where('salesId', $selectedTargetUserId)->where('stage', 'Active')->take(5);
        @endphp
        @forelse($userDeals as $deal)
        <tr>
            <td><div style="font-weight:600;color:var(--text-strong);">{{ $deal['title'] }}</div></td>
            <td style="color:var(--text-muted);">{{ $deal['clientName'] ?? '—' }}</td>
            <td style="color:var(--text-muted);font-size:12px;">{{ $deal['productName'] ?? '—' }}</td>
            <td class="bb-tnum" style="font-weight:700;">Rp {{ number_format($deal['estimatedValue'], 0, ',', '.') }}</td>
            <td><span class="bb-badge t-blue">Active</span></td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--text-faint);padding:16px;">No active deals.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@else

{{-- No User Selected --}}
<div class="bb-card" style="padding:40px;text-align:center;">
    <span class="material-symbols-outlined" style="font-size:48px;display:block;margin-bottom:16px;opacity:.4;">assignment</span>
    <p style="color:var(--text-faint);margin:0;">Pilih salesman untuk melihat target dan deal mereka.</p>
</div>

@endif

@endsection
