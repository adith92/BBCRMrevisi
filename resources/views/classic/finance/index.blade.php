@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $statusTone = ['paid'=>'emerald','sent'=>'amber','overdue'=>'rose','draft'=>'slate'];
    $statusIcon = ['paid'=>'check_circle','sent'=>'mail','overdue'=>'schedule','draft'=>'description'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Keuangan</div>
        <h1 class="bb-display" style="margin-top:6px;">Invoices</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Kelola invoice, pembayaran, dan ringkasan pendapatan.</p>
    </div>
</div>

{{-- Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
    <div class="bb-card" style="padding:16px 18px;">
        <div class="bb-eyebrow">Total Revenue</div>
        <div class="bb-display" style="font-size:24px;line-height:1;margin-top:6px;">{{ $rp($summary['total'] ?? 0) }}</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">check_circle</span>
            <span class="bb-eyebrow" style="margin:0;">Sudah Bayar</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;">{{ $summary['paid_count'] ?? 0 }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $rp($summary['paid'] ?? 0) }}</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">mail</span>
            <span class="bb-eyebrow" style="margin:0;">Menunggu</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;">{{ number_format($summary['pending'] ?? 0) }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $rp(0) }}</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;border-left:3px solid var(--rose);">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--rose);">schedule</span>
            <span class="bb-eyebrow" style="margin:0;color:var(--rose);">Overdue</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;color:var(--rose);">{{ $summary['overdue_count'] ?? 0 }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $rp($summary['overdue'] ?? 0) }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
        <label class="bb-field-label">Periode</label>
        <select name="filter" class="bb-select" style="width:140px;">
            <option value="all" @selected($filter==='all')>Semua</option>
            <option value="today" @selected($filter==='today')>Hari Ini</option>
            <option value="week" @selected($filter==='week')>Minggu Ini</option>
            <option value="month" @selected($filter==='month')>Bulan Ini</option>
            <option value="year" @selected($filter==='year')>Tahun Ini</option>
        </select>
    </div>
    <div>
        <label class="bb-field-label">Status</label>
        <select name="status" class="bb-select" style="width:140px;">
            <option value="">Semua Status</option>
            <option value="paid" @selected($status==='paid')>Paid</option>
            <option value="sent" @selected($status==='sent')>Sent</option>
            <option value="overdue" @selected($status==='overdue')>Overdue</option>
            <option value="draft" @selected($status==='draft')>Draft</option>
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
    @if(request()->hasAny(['filter','status']))
    <a href="{{ route('finance.index') }}" class="bb-btn bb-btn-secondary" style="height:40px;">Reset</a>
    @endif
</form>

{{-- Invoices Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Invoice</th><th>Klien</th><th>Booking</th><th>Tanggal</th><th style="text-align:right;">Nilai</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($invoices as $invoice)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">{{ $invoice->invoice_number ?? '#'.$invoice->id }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ $invoice->booking?->sales?->name ?? '—' }}</div>
            </td>
            <td style="color:var(--text-muted);">{{ $invoice->client?->company_name ?? '—' }}</td>
            <td style="color:var(--text-muted);font-size:12px;">
                @if($invoice->booking)
                <a href="{{ route('bookings.show', $invoice->booking) }}" style="color:var(--bb-accent);text-decoration:none;">#{{ $invoice->booking->id }}</a>
                @else
                —
                @endif
            </td>
            <td style="color:var(--text-muted);font-size:12px;">
                {{ $invoice->created_at ? $invoice->created_at->format('d M Y') : '—' }}
            </td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($invoice->amount ?? 0) }}</td>
            <td><span class="bb-badge t-{{ $statusTone[$invoice->status] ?? 'slate' }}">{{ ucfirst($invoice->status ?? '—') }}</span></td>
            <td style="text-align:right;">
                <a href="{{ route('finance.show', $invoice) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada invoice ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($invoices instanceof \Illuminate\Contracts\Pagination\Paginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $invoices->links() }}</div>
@endif

@endsection
