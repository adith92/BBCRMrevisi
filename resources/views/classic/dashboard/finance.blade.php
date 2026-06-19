@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $kpis = [
        ['eyebrow'=>'Revenue Hari Ini','value'=>$rp($todayRevenue ?? 0),'icon'=>'payments','accent'=>'green'],
        ['eyebrow'=>'Revenue Bulan Ini','value'=>$rp($monthRevenue ?? 0),'icon'=>'account_balance','accent'=>'blue'],
        ['eyebrow'=>'Invoice Lunas','value'=>number_format($paidThisMonth ?? 0),'icon'=>'task_alt','accent'=>'cyan','delta'=>'bulan ini','deltaUp'=>true],
        ['eyebrow'=>'Outstanding','value'=>$rp($outstanding ?? 0),'icon'=>'request_quote','accent'=>'gold','delta'=>($pendingInvoice ?? 0).' invoice','deltaUp'=>false],
        ['eyebrow'=>'Pending','value'=>number_format($pendingInvoice ?? 0),'icon'=>'pending','accent'=>'violet'],
        ['eyebrow'=>'Overdue','value'=>number_format($overdueCount ?? 0),'icon'=>'warning','accent'=>'red','delta'=>'jatuh tempo','deltaUp'=>false],
    ];
@endphp
@section('content')
<div style="margin-bottom:24px;">
    <div class="bb-eyebrow">Finance &amp; Billing</div>
    <h1 class="bb-display" style="margin-top:6px;">Finance Dashboard</h1>
    <p style="color:var(--text-muted);margin-top:6px;">Penerimaan, outstanding, dan invoice jatuh tempo.</p>
</div>

@include('classic.partials.kpis', ['kpis'=>$kpis, 'cols'=>6])

<div class="bb-card" style="padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <div><div class="bb-eyebrow">Perlu Tindakan</div><h3 class="bb-h3" style="margin-top:4px;">Invoice Jatuh Tempo</h3></div>
        <a href="{{ route('finance.index') }}" class="bb-btn bb-btn-ghost"><span class="material-symbols-outlined" style="font-size:18px;">list</span>Semua Invoice</a>
    </div>
    @forelse(($overdueInvoices ?? []) as $inv)
    <div class="bb-list-row">
        <div style="min-width:0;">
            <div style="font-weight:600;color:var(--text-strong);font-size:13px;">{{ $inv->invoice_number ?? ('INV-'.$inv->id) }}</div>
            <div class="bb-body-sm" style="color:var(--text-muted);">{{ $inv->client->company_name ?? '-' }} · jatuh tempo {{ optional($inv->due_date)->format('d M Y') }}</div>
        </div>
        <span class="bb-tnum" style="font-weight:700;color:var(--status-error-fg);font-size:13px;">{{ $rp($inv->amount ?? 0) }}</span>
    </div>
    @empty
    <p style="color:var(--text-faint);font-size:13px;">Tidak ada invoice jatuh tempo. 🎉</p>
    @endforelse
</div>
@endsection
