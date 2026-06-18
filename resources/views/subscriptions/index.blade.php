@extends('layouts.app')

@section('header_title', 'Subscription Billing')

@section('content')
@php
    $canManageSubscriptions = auth()->user()->isGM()
        || auth()->user()->isFinance()
        || auth()->user()->isManager();
    $maxProductRevenue = max(1, (int) collect($productRevenue)->max('value'));
@endphp

@push('styles')
<style>
    .billing-hero {
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--cc-card) 82%, #2563eb 18%), var(--cc-card)),
            var(--cc-card);
        border: 1px solid color-mix(in srgb, var(--cc-border) 68%, #3b82f6 32%);
    }
    .billing-summary-tile {
        border: 1px solid var(--cc-border);
        background: color-mix(in srgb, var(--cc-card) 88%, var(--cc-surface) 12%);
    }
    .billing-summary-tile:hover {
        border-color: var(--cc-border-h);
    }
    .billing-meter {
        height: 12px;
        border-radius: 999px;
        background: color-mix(in srgb, var(--cc-surface) 88%, transparent);
        overflow: hidden;
    }
    .billing-meter > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0ea5e9, #4f46e5);
    }
    .billing-table thead th {
        background: color-mix(in srgb, var(--cc-card) 74%, var(--cc-surface) 26%);
    }
</style>
@endpush

<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Subscription Billing'],
]" />

{{-- Due Today Warning --}}
@if($dueTodayCount > 0)
<div class="mb-4 flex items-center gap-3 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl px-4 py-3">
    <span class="text-xl">⚠️</span>
    <div>
        <span class="font-semibold">{{ $dueTodayCount }} kontrak</span> jatuh tempo hari ini dan belum ditagih.
        @if($canManageSubscriptions)
        <form action="{{ route('subscriptions.billing.run') }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                    onclick="return confirm('Jalankan billing sekarang?')"
                    class="ml-2 underline font-semibold hover:text-amber-300 bg-transparent border-0 p-0 cursor-pointer">
                Proses Sekarang
            </button>
        </form>
        @endif
    </div>
</div>
@endif

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl px-4 py-3">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl px-4 py-3">
    ❌ {{ session('error') }}
</div>
@endif

<div class="space-y-6">
    <section class="billing-hero rounded-2xl shadow p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-[var(--cc-text-muted)]">Finance Control</div>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[var(--cc-text)]">Subscription Billing</h2>
                <p class="mt-2 text-sm text-[var(--cc-text-muted)]">Kelola kontrak aktif, billing berikutnya, dan kontrak yang perlu tindakan cepat.</p>
            </div>
            @if($canManageSubscriptions)
            <a href="{{ route('subscriptions.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 text-gray-900 px-4 py-2.5 rounded-xl hover:bg-indigo-500 transition-all text-sm font-semibold shadow-lg shadow-indigo-600/20">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Kontrak
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
            <div class="billing-summary-tile rounded-2xl p-5">
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">MRR Aktif</div>
                <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">Rp {{ number_format((float) $billingSummary['active_mrr'], 0, ',', '.') }}</div>
                <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Kontrak aktif bulanan</div>
            </div>
            <div class="billing-summary-tile rounded-2xl p-5">
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">Next Billing</div>
                <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ $billingSummary['next_billing_count'] }}</div>
                <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Jatuh tempo dalam 7 hari</div>
            </div>
            <div class="billing-summary-tile rounded-2xl p-5">
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">Expired Soon</div>
                <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ $billingSummary['expiring_soon_count'] }}</div>
                <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Kontrak aktif yang segera selesai</div>
            </div>
            <div class="billing-summary-tile rounded-2xl p-5">
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">Terminated</div>
                <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ $billingSummary['terminated_count'] }}</div>
                <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Terminasi bulan berjalan</div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <div class="cc-card rounded-2xl p-5">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">Billing Pipeline</div>
                <h3 class="mt-1 text-lg font-bold text-[var(--cc-text)]">Monthly Rate by Product</h3>
            </div>
            <div class="mt-5 space-y-4">
                @forelse($productRevenue as $row)
                <div class="grid grid-cols-[minmax(0,150px)_1fr_120px] gap-3 items-center text-sm">
                    <div class="truncate text-[var(--cc-text)] font-semibold">{{ $row['label'] }}</div>
                    <div class="billing-meter">
                        <span style="width: {{ max(8, round(($row['value'] / $maxProductRevenue) * 100)) }}%;"></span>
                    </div>
                    <div class="text-right text-[var(--cc-text-muted)] font-semibold">Rp {{ number_format((float) $row['value'], 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="text-sm text-[var(--cc-text-muted)]">Belum ada data produk subscription.</div>
                @endforelse
            </div>
        </div>

        <div class="cc-card rounded-2xl p-5">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--cc-text-muted)]">Follow-up</div>
                <h3 class="mt-1 text-lg font-bold text-[var(--cc-text)]">Next Billing Timeline</h3>
            </div>
            <div class="mt-5 space-y-4">
                @forelse($billingTimeline as $subscription)
                @php
                    $isDueToday = $subscription->next_billing_date && $subscription->next_billing_date->isToday();
                    $isOverdue = $subscription->next_billing_date && $subscription->next_billing_date->isPast() && !$subscription->next_billing_date->isToday();
                @endphp
                <div class="rounded-2xl border border-[var(--cc-border)] p-4 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="font-semibold text-[var(--cc-text)] truncate">{{ $subscription->client->company_name ?? 'Tanpa Client' }}</div>
                        <div class="text-sm text-[var(--cc-text-muted)] mt-1">{{ $subscription->sub_number }} · {{ $subscription->product->name ?? 'Tanpa Produk' }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-sm font-semibold text-[var(--cc-text)]">{{ $subscription->next_billing_date?->format('d M Y') ?? '—' }}</div>
                        <div class="mt-1">
                            @if($isOverdue)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Overdue</span>
                            @elseif($isDueToday)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Hari Ini</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Upcoming</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-sm text-[var(--cc-text-muted)]">Belum ada jadwal billing aktif.</div>
                @endforelse
            </div>
        </div>
    </section>

    <div class="cc-card rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-[var(--cc-text)]">Subscription Billing</h2>
                <p class="text-sm text-[var(--cc-text-muted)] mt-0.5">Daftar kontrak, billing berikutnya, dan status subscription</p>
            </div>
            @if($canManageSubscriptions)
            <a href="{{ route('subscriptions.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 text-gray-900 px-4 py-2.5 rounded-xl hover:bg-indigo-500 transition-all text-sm font-semibold shadow-lg shadow-indigo-600/20">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Kontrak
            </a>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mb-6 border-b border-[var(--cc-border)]/50 pb-4">
            @php
            $tabs = [
                ''            => 'Semua',
                'active'      => 'Aktif',
                'paused'      => 'Ditangguhkan',
                'terminated'  => 'Terminasi',
                'expired'     => 'Kedaluwarsa',
            ];
            @endphp
            @foreach($tabs as $val => $label)
            <a href="{{ route('subscriptions.index', array_merge(request()->query(), ['status' => $val])) }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
               {{ $status === $val ? 'bg-indigo-600 text-gray-900 shadow-md shadow-indigo-600/10' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)] border border-[var(--cc-border)]/50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('subscriptions.index') }}" class="mb-4 flex flex-col xl:flex-row gap-3 xl:items-end xl:justify-between">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="flex-1 flex flex-col xl:flex-row gap-3 xl:items-end">
                <div class="w-full xl:max-w-md">
                    <label class="block text-xs font-semibold text-[var(--cc-text-muted)] mb-1">Search</label>
                    <div class="dark-input flex items-center gap-2 px-4 py-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[var(--cc-text-muted)]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subscription, client, product..." class="w-full bg-transparent border-0 p-0 text-sm text-[var(--cc-text)] placeholder:text-[var(--cc-text-faint)] focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[var(--cc-text-muted)] mb-1">Filter Client</label>
                    <select name="client_id"
                            class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 min-w-[220px]">
                        <option value="" class="bg-[var(--cc-surface)]">— Semua Client —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" class="bg-[var(--cc-surface)]" {{ $clientId == $c->id ? 'selected' : '' }}>
                            {{ $c->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-[var(--cc-card)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-4 py-2.5 text-sm font-semibold hover:border-indigo-500 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">tune</span> Terapkan
                </button>
                @if($clientId || request('search'))
                <a href="{{ route('subscriptions.index', ['status' => $status]) }}"
                   class="text-sm text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] underline">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="billing-table w-full text-sm resizable-table" data-table-id="subscriptions-table">
                <thead class="bg-[var(--cc-bg-muted)] border-b border-[var(--cc-border)]/50">
                    <tr class="text-[var(--cc-text-muted)] text-left">
                        <th class="py-3 px-4">Sub #</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Kendaraan</th>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4 text-right">Monthly Rate</th>
                        <th class="py-3 px-4">Mulai / Selesai</th>
                        <th class="py-3 px-4">Next Billing</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    @php
                    $isOverdue = $sub->status === 'active' && $sub->next_billing_date && $sub->next_billing_date->isPast();
                    @endphp
                    <tr class="border-b border-[var(--cc-border)]/50 hover:bg-[var(--cc-row-hover)] transition-colors {{ $isOverdue ? 'bg-amber-500/5' : '' }}">
                        <td class="py-3 px-4">
                            <a href="{{ route('subscriptions.show', $sub) }}"
                               class="text-blue-500 hover:underline font-mono font-medium text-xs">
                                 {{ $sub->sub_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-[var(--cc-text)]">{{ $sub->client->company_name ?? '—' }}</div>
                        </td>
                        <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                            {{ $sub->vehicle ? $sub->vehicle->plate_number . ' (' . $sub->vehicle->brand . ')' : '—' }}
                        </td>
                        <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                            {{ $sub->product->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-[var(--cc-text)]">
                            Rp {{ number_format((float) $sub->monthly_rate, 0, ',', '.') }}
                            <div class="text-xs text-[var(--cc-text-muted)] font-normal">
                                /{{ $sub->billing_cycle === 'monthly' ? 'bulan' : ($sub->billing_cycle === 'quarterly' ? '3 bulan' : 'tahun') }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-xs text-[var(--cc-text-muted)]">
                            <div>{{ $sub->start_date?->format('d M Y') ?? '—' }}</div>
                            <div class="text-[var(--cc-text-muted)]/70">s/d {{ $sub->end_date?->format('d M Y') ?? '—' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($sub->next_billing_date)
                            <span class="text-xs {{ $isOverdue ? 'text-red-400 font-semibold' : 'text-[var(--cc-text-muted)]' }}">
                                {{ $sub->next_billing_date->format('d M Y') }}
                                @if($isOverdue) <span class="text-red-400">⚠</span> @endif
                            </span>
                            @else
                            <span class="text-[var(--cc-text-muted)] text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @php
                            $badge = match($sub->status) {
                                'active'     => 'bg-green-100 text-green-700',
                                'paused'     => 'bg-yellow-100 text-yellow-700',
                                'terminated' => 'bg-red-100 text-red-700',
                                'expired'    => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                                default      => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                            };
                            $label = match($sub->status) {
                                'active'     => 'Aktif',
                                'paused'     => 'Ditangguhkan',
                                'terminated' => 'Terminasi',
                                'expired'    => 'Kedaluwarsa',
                                default      => ucfirst($sub->status),
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('subscriptions.show', $sub) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs hover:underline">Detail</a>
                                @if($sub->status === 'active')
                                <button type="button"
                                        onclick="openTerminationModal('{{ route('subscriptions.terminate', $sub) }}', '{{ $sub->sub_number }}')"
                                        class="text-red-600 hover:text-red-800 text-xs hover:underline">
                                    Terminasi
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-gray-400">
                            <div class="text-3xl mb-2">📋</div>
                            <div>Belum ada kontrak berlangganan</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $subscriptions->appends(request()->query())->links() }}
        </div>

        @include('subscriptions.charts')
    </div>
</div>

<!-- Modal PIN Terminasi -->
<div id="termination-pin-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeTerminationModal()"></div>

    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full mx-4 overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="termination-modal-box">
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-gradient-to-r from-red-500/10 to-transparent">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-gray-900">Terminasi Kontrak</h3>
                    <p class="text-xs text-slate-400 font-medium font-mono" id="termination-sub-number">GB-2026-0001</p>
                </div>
            </div>
            <button type="button" onclick="closeTerminationModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form id="termination-form" method="POST" action="" class="p-6 space-y-5">
            @csrf
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed text-center">
                Silakan masukkan <strong>PIN 6-digit</strong> Anda untuk mengonfirmasi terminasi kontrak ini.
            </p>

            <div class="flex justify-center gap-2" id="pin-input-container">
                @for ($i = 0; $i < 6; $i++)
                <input type="password"
                       maxlength="1"
                       pattern="[0-9]"
                       inputmode="numeric"
                       required
                       class="w-12 h-12 text-center text-2xl font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
                       data-index="{{ $i }}"
                       autocomplete="off">
                @endfor
            </div>

            <input type="hidden" name="pin" id="full-pin-value">

            <div id="pin-error-msg" class="text-xs text-red-500 text-center font-semibold hidden">
                PIN harus berupa 6 digit angka!
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTerminationModal()" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-gray-900 rounded-xl font-semibold text-sm shadow-lg shadow-red-500/20 hover:shadow-red-600/30 transition-all">
                    Konfirmasi Terminasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTerminationModal(actionUrl, subNumber) {
        const modal = document.getElementById('termination-pin-modal');
        const modalBox = document.getElementById('termination-modal-box');
        const form = document.getElementById('termination-form');
        const subNumberText = document.getElementById('termination-sub-number');
        const errorMsg = document.getElementById('pin-error-msg');

        form.action = actionUrl;
        subNumberText.textContent = subNumber;

        const inputs = document.querySelectorAll('#pin-input-container input');
        inputs.forEach(input => input.value = '');
        document.getElementById('full-pin-value').value = '';
        errorMsg.classList.add('hidden');

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
            inputs[0].focus();
        }, 20);
    }

    function closeTerminationModal() {
        const modal = document.getElementById('termination-pin-modal');
        const modalBox = document.getElementById('termination-modal-box');

        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('#pin-input-container input');
        const form = document.getElementById('termination-form');
        const errorMsg = document.getElementById('pin-error-msg');

        inputs.forEach((input, index) => {
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('input', function() {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateFullPin();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (input.value === '' && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                    } else {
                        input.value = '';
                    }
                    updateFullPin();
                }
            });

            input.addEventListener('paste', function(e) {
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                if (/^\d{6}$/.test(pasteData)) {
                    pasteData.split('').forEach((char, idx) => {
                        if (inputs[idx]) {
                            inputs[idx].value = char;
                        }
                    });
                    inputs[5].focus();
                    updateFullPin();
                    e.preventDefault();
                }
            });
        });

        function updateFullPin() {
            const pinVal = Array.from(inputs).map(inp => inp.value).join('');
            document.getElementById('full-pin-value').value = pinVal;
        }

        form.addEventListener('submit', function(e) {
            const pinVal = document.getElementById('full-pin-value').value;
            if (pinVal.length !== 6 || !/^\d{6}$/.test(pinVal)) {
                e.preventDefault();
                errorMsg.classList.remove('hidden');
                errorMsg.textContent = 'PIN harus berupa 6 digit angka!';
            }
        });
    });
</script>
@endpush

@endsection
