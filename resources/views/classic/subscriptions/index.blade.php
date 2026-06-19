@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $statusTone = ['active'=>'emerald','paused'=>'amber','terminated'=>'rose','pending'=>'blue'];
    $statusIcon = ['active'=>'check_circle','paused'=>'pause_circle','terminated'=>'cancel','pending'=>'schedule'];
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Keuangan</div>
        <h1 class="bb-display" style="margin-top:6px;">Subscription</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Kelola berlangganan, billing, dan riwayat penagihan.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','finance','manager']))
    <a href="{{ route('subscriptions.create') }}" class="bb-btn bb-btn-primary">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>Tambah Subscription
    </a>
    @endif
</div>

{{-- Summary KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
    <div class="bb-card" style="padding:16px 18px;">
        <div class="bb-eyebrow">Active MRR</div>
        <div class="bb-display" style="font-size:24px;line-height:1;margin-top:6px;">{{ $rp($billingSummary['active_mrr'] ?? 0) }}</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">schedule</span>
            <span class="bb-eyebrow" style="margin:0;">Next Billing</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;">{{ $billingSummary['next_billing_count'] ?? 0 }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">dalam 7 hari</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">event</span>
            <span class="bb-eyebrow" style="margin:0;">Expiring Soon</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;">{{ $billingSummary['expiring_soon_count'] ?? 0 }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">dalam 30 hari</div>
    </div>
    <div class="bb-card" style="padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <span class="material-symbols-outlined" style="font-size:16px;color:var(--bb-accent);">cancel</span>
            <span class="bb-eyebrow" style="margin:0;">Terminated</span>
        </div>
        <div class="bb-display" style="font-size:20px;line-height:1;">{{ $billingSummary['terminated_count'] ?? 0 }}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">bulan ini</div>
    </div>
</div>

{{-- Product Revenue + Billing Timeline --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="bb-two-col">
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Top 4</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Revenue by Product</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($productRevenue as $prod)
        <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-weight:600;font-size:13px;color:var(--text-strong);">{{ $prod['label'] }}</span>
                <span class="bb-tnum" style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ $rp($prod['value'] ?? 0) }}</span>
            </div>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Tidak ada data produk.</p>
        @endforelse
        </div>
    </div>
    <div class="bb-card" style="padding:20px;">
        <div class="bb-eyebrow">Next 3</div><h3 class="bb-h3" style="margin-top:4px;margin-bottom:14px;">Upcoming Billings</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
        @forelse($billingTimeline as $billing)
        <div style="padding:10px 12px;background:var(--surface-2);border-radius:6px;border-left:3px solid var(--bb-accent);">
            <div style="font-weight:600;font-size:12px;color:var(--text-strong);">{{ $billing->sub_number ?? 'Sub #'.$billing->id }}</div>
            <div class="bb-body-sm" style="color:var(--text-muted);">{{ $billing->client?->company_name ?? '—' }}</div>
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
                <span style="font-size:11px;color:var(--text-faint);">{{ $billing->next_billing_date?->format('d M Y') ?? '—' }}</span>
                <span class="bb-tnum" style="font-weight:700;font-size:11px;color:var(--text-strong);">{{ $rp($billing->monthly_rate ?? 0) }}</span>
            </div>
        </div>
        @empty
        <p style="color:var(--text-faint);font-size:13px;">Tidak ada billing yang akan datang.</p>
        @endforelse
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:180px;">
        <label class="bb-field-label">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Sub number, klien, produk…" class="bb-input">
    </div>
    <div>
        <label class="bb-field-label">Status</label>
        <select name="status" class="bb-select" style="width:140px;">
            <option value="">Semua Status</option>
            <option value="active" @selected($status==='active')>Active</option>
            <option value="paused" @selected($status==='paused')>Paused</option>
            <option value="terminated" @selected($status==='terminated')>Terminated</option>
        </select>
    </div>
    <div>
        <label class="bb-field-label">Klien</label>
        <select name="client_id" class="bb-select" style="width:150px;">
            <option value="">Semua Klien</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" @selected((int)$clientId===$c->id)>{{ $c->company_name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
    @if(request()->hasAny(['search','status','client_id']))
    <a href="{{ route('subscriptions.index') }}" class="bb-btn bb-btn-secondary" style="height:40px;">Reset</a>
    @endif
</form>

{{-- Subscriptions Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Subscription</th><th>Klien</th><th>Produk</th><th>Kendaraan / Supir</th><th>Monthly Rate</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($subscriptions as $sub)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">{{ $sub->sub_number ?? 'Sub #'.$sub->id }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">
                    @if($sub->start_date){{ $sub->start_date->format('d M Y') }} @if($sub->end_date)→ {{ $sub->end_date->format('d M Y') }}@endif @else — @endif
                </div>
            </td>
            <td style="color:var(--text-muted);">{{ $sub->client?->company_name ?? '—' }}</td>
            <td style="color:var(--text-muted);">{{ $sub->product?->name ?? '—' }}</td>
            <td style="color:var(--text-muted);font-size:12px;">
                <div>{{ $sub->vehicle?->plate_number ?? '—' }}</div>
                <div class="bb-body-sm">{{ $sub->driver?->name ?? '—' }}</div>
            </td>
            <td class="bb-tnum" style="font-weight:700;color:var(--text-strong);">{{ $rp($sub->monthly_rate ?? 0) }}</td>
            <td><span class="bb-badge t-{{ $statusTone[$sub->status] ?? 'slate' }}">{{ ucfirst($sub->status ?? '—') }}</span></td>
            <td style="text-align:right;">
                <a href="{{ route('subscriptions.show', $sub) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada subscription ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($subscriptions instanceof \Illuminate\Contracts\Pagination\Paginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $subscriptions->links() }}</div>
@endif

@endsection
