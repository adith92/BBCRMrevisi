@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
@endphp
@section('content')

<div class="bb-page-head">
    <div>
        <div class="bb-eyebrow">Katalog</div>
        <h1 class="bb-display" style="margin-top:6px;">Produk</h1>
        <p style="color:var(--text-muted);margin-top:6px;">Kelola katalog produk, harga, dan kategori.</p>
    </div>
    @if(in_array(Auth::user()->role ?? '', ['gm','manager']))
    <a href="{{ route('products.create') }}" class="bb-btn bb-btn-primary">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>Produk Baru
    </a>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="bb-card" style="padding:14px 16px;margin-bottom:14px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:180px;">
        <label class="bb-field-label">Cari</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, SKU…" class="bb-input">
    </div>
    <div>
        <label class="bb-field-label">Kategori</label>
        <select name="type" class="bb-select" style="width:160px;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->type }}" @selected(request('type')===$cat->type)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Cari</button>
    @if(request()->hasAny(['q','type']))
    <a href="{{ route('products.index') }}" class="bb-btn bb-btn-secondary" style="height:40px;">Reset</a>
    @endif
</form>

{{-- Products Table --}}
<div class="bb-table-wrap">
    <table class="bb-table">
        <thead><tr>
            <th>Produk</th><th>SKU</th><th>Kategori</th><th style="text-align:right;">Harga</th><th>Unit</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($products as $product)
        <tr>
            <td>
                <div style="font-weight:600;color:var(--text-strong);">{{ $product->name }}</div>
                <div class="bb-body-sm" style="color:var(--text-muted);">{{ $product->description ? Str::limit($product->description, 40) : '—' }}</div>
            </td>
            <td style="color:var(--text-muted);font-size:12px;font-family:monospace;font-weight:500;">{{ $product->sku }}</td>
            <td style="color:var(--text-muted);font-size:12px;">{{ $product->category?->name ?? '—' }}</td>
            <td class="bb-tnum" style="text-align:right;font-weight:700;color:var(--text-strong);">{{ $rp($product->base_price ?? 0) }}</td>
            <td style="color:var(--text-muted);font-size:12px;">{{ ucfirst($product->unit ?? '—') }}</td>
            <td>
                <span class="bb-badge t-{{ $product->is_active ? 'emerald' : 'slate' }}">
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td style="text-align:right;">
                <a href="{{ route('products.show', $product) }}" class="bb-btn bb-btn-secondary" style="padding:4px 12px;font-size:12px;">Detail</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text-faint);padding:32px;">Tidak ada produk ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($products instanceof \Illuminate\Contracts\Pagination\Paginator)
<div class="bb-pagination" style="margin-top:16px;">{{ $products->links() }}</div>
@endif

@endsection
