@extends('layouts.app')

@section('header_title', 'Detail Produk - ' . $product->name)

@section('content')
<div class="p-4 md:p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('products.index') }}" class="text-sm font-medium hover:underline text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Produk</a>
                <span class="text-slate-400 text-sm">/</span>
                <span class="text-sm font-medium" style="color:var(--cc-text)">Detail</span>
            </div>
            <h1 class="text-2xl font-bold" style="color:var(--cc-text)">{{ $product->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-300 dark:bg-slate-700/50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                Kembali
            </a>
            @if(auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isDirector())
            <a href="{{ route('products.edit', $product->id) }}"
               class="px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Detail Produk --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-panel cc-card rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-5 md:p-6">
                <h2 class="text-lg font-bold mb-4" style="color:var(--cc-text)">Informasi Produk</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">SKU</p>
                        <p class="font-mono font-medium" style="color:var(--cc-text)">{{ $product->sku }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Status</p>
                        @if($product->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                            Nonaktif
                        </span>
                        @endif
                    </div>
                    
                    <div class="md:col-span-2">
                        <p class="text-sm text-slate-500 mb-1">Nama Produk</p>
                        <p class="font-medium" style="color:var(--cc-text)">{{ $product->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500 mb-1">Kategori</p>
                        <p class="font-medium" style="color:var(--cc-text)">
                            {{ $product->category ? $product->category->name : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Tipe</p>
                        @if($product->category)
                        @php
                        $typeBadge = [
                            'short_term' => 'bg-blue-100 text-blue-700',
                            'long_term'  => 'bg-purple-100 text-purple-700',
                            'evoucher'   => 'bg-emerald-100 text-emerald-700',
                        ];
                        $typeLabel = [
                            'short_term' => 'Short Term',
                            'long_term'  => 'Long Term',
                            'evoucher'   => 'E-Voucher',
                        ];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $typeBadge[$product->category->type] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $typeLabel[$product->category->type] ?? $product->category->type }}
                        </span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-sm text-slate-500 mb-1">Deskripsi</p>
                        <p class="text-sm leading-relaxed" style="color:var(--cc-text-muted)">
                            {!! nl2br(e($product->description ?: 'Tidak ada deskripsi.')) !!}
                        </p>
                    </div>
                </div>
            </div>

            <div class="glass-panel cc-card rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-5 md:p-6">
                <h2 class="text-lg font-bold mb-4" style="color:var(--cc-text)">Spesifikasi & Harga</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Harga Dasar</p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            Rp {{ number_format((float)$product->base_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Unit</p>
                        <span class="text-sm font-medium px-2.5 py-1 rounded" style="background:var(--cc-sidebar);color:var(--cc-text)">
                            {{ strtoupper($product->unit) }}
                        </span>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500 mb-1">Kapasitas (Pax)</p>
                        <p class="font-medium" style="color:var(--cc-text)">
                            @if($product->min_pax || $product->max_pax)
                                {{ $product->min_pax ?: '1' }} - {{ $product->max_pax ?: 'Tak Terbatas' }} Pax
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Durasi (Hari)</p>
                        <p class="font-medium" style="color:var(--cc-text)">
                            {{ $product->duration_days ? $product->duration_days . ' Hari' : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: Opportunities --}}
        <div class="space-y-6">
            <div class="glass-panel cc-card rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-5 md:p-6">
                <h2 class="text-lg font-bold mb-4" style="color:var(--cc-text)">Opportunity Aktif</h2>
                <p class="text-xs mb-4" style="color:var(--cc-text-muted)">
                    Opportunity terbaru yang sedang berjalan menggunakan produk ini.
                </p>

                @if(isset($activeOpportunities) && $activeOpportunities->count() > 0)
                <div class="space-y-3">
                    @foreach($activeOpportunities as $opp)
                    <div class="p-3 rounded-lg border border-slate-100 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-black/10 transition-colors">
                        <div class="flex justify-between items-start mb-1">
                            <a href="{{ route('pipeline.index') }}?search={{ $opp->name }}" class="font-medium text-sm hover:text-blue-600 transition-colors" style="color:var(--cc-text)">
                                {{ $opp->name }}
                            </a>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase" style="background:var(--cc-sidebar);color:var(--cc-text)">
                                {{ $opp->stage }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2">
                            {{ $opp->client ? $opp->client->name : 'Klien Tidak Diketahui' }}
                        </p>
                        <div class="text-xs font-mono font-medium text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format((float)$opp->value, 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-sm text-slate-500 font-medium">Belum ada Opportunity aktif</p>
                </div>
                @endif
            </div>
            
            @if(auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isDirector())
            <div class="glass-panel cc-card rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-5 md:p-6 bg-red-50/30 dark:bg-red-900/10">
                <h2 class="text-lg font-bold text-red-600 mb-2">Zona Berbahaya</h2>
                <p class="text-xs text-slate-500 mb-4">
                    Menghapus produk akan berdampak pada data yang terkait. Pastikan produk ini tidak sedang digunakan.
                </p>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition-colors border border-red-200 cursor-pointer">
                        Hapus Produk
                    </button>
                </form>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
