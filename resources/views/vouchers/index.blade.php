@extends('layouts.app')

@section('header_title', 'Manajemen Voucher')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'E-Voucher'],
]" />

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3">
    ❌ {{ session('error') }}
</div>
@endif

{{-- Quick Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="cc-card rounded-lg shadow p-4 border-l-4 border-green-500">
        <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide">Total Tersedia</p>
        <p class="text-2xl font-bold text-[var(--cc-text)] mt-1">{{ number_format($stats['available']) }}</p>
        <p class="text-xs text-green-600 mt-0.5">voucher aktif</p>
    </div>

    <div class="cc-card rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide">Sudah Digunakan</p>
        <p class="text-2xl font-bold text-[var(--cc-text)] mt-1">{{ number_format($stats['used']) }}</p>
        <p class="text-xs text-blue-600 mt-0.5">voucher terpakai</p>
    </div>

    <div class="cc-card rounded-lg shadow p-4 border-l-4 border-purple-500">
        <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide">Nilai Tersedia</p>
        <p class="text-lg font-bold text-[var(--cc-text)] mt-1">Rp {{ number_format((float)$stats['value_available'], 0, ',', '.') }}</p>
        <p class="text-xs text-purple-600 mt-0.5">total denominasi</p>
    </div>

    <div class="cc-card rounded-lg shadow p-4 border-l-4 border-gray-400">
        <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide">Kedaluwarsa</p>
        <p class="text-2xl font-bold text-[var(--cc-text)] mt-1">{{ number_format($stats['expired']) }}</p>
        <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">voucher expired</p>
    </div>
</div>

<div class="cc-card rounded-lg shadow p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-[var(--cc-text)]">E-Voucher</h2>
            <p class="text-sm text-[var(--cc-text-muted)] mt-0.5">Kelola voucher transportasi klien</p>
        </div>
        @can('role:gm,finance,manager')
        <a href="{{ route('vouchers.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
            🎫 Buat Voucher
        </a>
        @endcan
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b border-[var(--cc-border)] pb-4">
        @php
        $tabs = [
            ''          => 'Semua',
            'available' => 'Tersedia',
            'used'      => 'Digunakan',
            'expired'   => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
        ];
        @endphp
        @foreach($tabs as $val => $label)
        <a href="{{ route('vouchers.index', ['status' => $val]) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
           {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[var(--cc-bg-muted)] border-b">
                <tr class="text-[var(--cc-text-muted)] text-left">
                    <th class="py-3 px-4">Kode Voucher</th>
                    <th class="py-3 px-4">Judul</th>
                    <th class="py-3 px-4">Client</th>
                    <th class="py-3 px-4 text-right">Denominasi</th>
                    <th class="py-3 px-4">Periode Berlaku</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                @php
                $isExpiringSoon = $voucher->status === 'available' && $voucher->valid_until->diffInDays(today()) <= 7 && $voucher->valid_until->isFuture();
                @endphp
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="py-3 px-4">
                        {{-- Prominent code for scanning --}}
                        <a href="{{ route('vouchers.show', $voucher) }}"
                           class="inline-flex items-center gap-2 group">
                            <span class="font-mono font-bold text-[var(--cc-text)] text-base tracking-widest group-hover:text-blue-600 transition-colors">
                                {{ $voucher->voucher_code }}
                            </span>
                            <span class="text-gray-400 text-xs">📱</span>
                        </a>
                        <div class="text-xs text-gray-400 mt-0.5">Issued: {{ $voucher->issuedBy?->name ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text)]">{{ $voucher->title }}</td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                        {{ $voucher->client?->company_name ?? '<em class="text-gray-400">Umum</em>' }}
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-[var(--cc-text)]">
                        Rp {{ number_format((float)$voucher->denomination, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-xs text-[var(--cc-text-muted)]">
                        <div>{{ $voucher->valid_from->format('d M Y') }}</div>
                        <div class="text-gray-400">s/d {{ $voucher->valid_until->format('d M Y') }}
                            @if($isExpiringSoon)
                            <span class="text-amber-500 font-medium">⚠ Segera</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        @php
                        $badge = match($voucher->status) {
                            'available' => 'bg-green-100 text-green-700',
                            'used'      => 'bg-blue-100 text-blue-700',
                            'expired'   => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                            'cancelled' => 'bg-red-100 text-red-600',
                            default     => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                        };
                        $statusLabel = match($voucher->status) {
                            'available' => 'Tersedia',
                            'used'      => 'Digunakan',
                            'expired'   => 'Kedaluwarsa',
                            'cancelled' => 'Dibatalkan',
                            default     => ucfirst($voucher->status),
                        };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('vouchers.show', $voucher) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs hover:underline">Detail</a>
                            @if($voucher->status === 'available')
                            <form method="POST" action="{{ route('vouchers.expire', $voucher) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Expire voucher {{ $voucher->voucher_code }}?')"
                                        class="text-[var(--cc-text-muted)] hover:text-red-600 text-xs hover:underline">
                                    Expire
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-400">
                        <div class="text-3xl mb-2">🎫</div>
                        <div>Belum ada voucher</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vouchers->appends(request()->query())->links() }}
    </div>
</div>
@endsection
