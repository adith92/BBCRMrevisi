@extends('layouts.app')

@section('header_title', 'Subscription Billing')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Subscription Billing'],
]" />

{{-- Due Today Warning --}}
@if($dueTodayCount > 0)
<div class="mb-4 flex items-center gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-lg px-4 py-3">
    <span class="text-xl">⚠️</span>
    <div>
        <span class="font-semibold">{{ $dueTodayCount }} kontrak</span> jatuh tempo hari ini dan belum ditagih.
        @can('role:gm,finance,manager')
        <a href="{{ route('subscriptions.billing.run') }}"
           onclick="return confirm('Jalankan billing sekarang?')"
           class="ml-2 underline font-semibold hover:text-amber-900">Proses Sekarang</a>
        @endcan
    </div>
</div>
@endif

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

<div class="bg-white rounded-lg shadow p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Subscription Billing</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola kontrak berlangganan kendaraan</p>
        </div>
        @can('role:gm,finance,manager')
        <a href="{{ route('subscriptions.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
            ➕ Tambah Kontrak
        </a>
        @endcan
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
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
           {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Client Filter --}}
    <form method="GET" action="{{ route('subscriptions.index') }}" class="mb-4 flex gap-3 items-end">
        <input type="hidden" name="status" value="{{ $status }}">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter Client</label>
            <select name="client_id" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                <option value="">— Semua Client —</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>
                    {{ $c->company_name }}
                </option>
                @endforeach
            </select>
        </div>
        @if($clientId)
        <a href="{{ route('subscriptions.index', ['status' => $status]) }}"
           class="text-sm text-gray-500 hover:text-gray-700 underline pb-2">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-gray-600 text-left">
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
                <tr class="border-b hover:bg-gray-50 transition-colors {{ $isOverdue ? 'bg-amber-50' : '' }}">
                    <td class="py-3 px-4">
                        <a href="{{ route('subscriptions.show', $sub) }}"
                           class="text-blue-600 hover:underline font-mono font-medium text-xs">
                            {{ $sub->sub_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-gray-900">{{ $sub->client->company_name ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-4 text-gray-600">
                        {{ $sub->vehicle ? $sub->vehicle->plate_number . ' (' . $sub->vehicle->brand . ')' : '—' }}
                    </td>
                    <td class="py-3 px-4 text-gray-600">
                        {{ $sub->product->name ?? '—' }}
                    </td>
                    <td class="py-3 px-4 text-right font-medium text-gray-900">
                        Rp {{ number_format((float)$sub->monthly_rate, 0, ',', '.') }}
                        <div class="text-xs text-gray-400 font-normal">
                            /{{ $sub->billing_cycle === 'monthly' ? 'bulan' : ($sub->billing_cycle === 'quarterly' ? '3 bulan' : 'tahun') }}
                        </div>
                    </td>
                    <td class="py-3 px-4 text-xs text-gray-600">
                        <div>{{ $sub->start_date?->format('d M Y') ?? '—' }}</div>
                        <div class="text-gray-400">s/d {{ $sub->end_date?->format('d M Y') ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-4">
                        @if($sub->next_billing_date)
                        <span class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $sub->next_billing_date->format('d M Y') }}
                            @if($isOverdue) <span class="text-red-500">⚠</span> @endif
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @php
                        $badge = match($sub->status) {
                            'active'     => 'bg-green-100 text-green-700',
                            'paused'     => 'bg-yellow-100 text-yellow-700',
                            'terminated' => 'bg-red-100 text-red-700',
                            'expired'    => 'bg-gray-100 text-gray-500',
                            default      => 'bg-gray-100 text-gray-500',
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
                            <form method="POST" action="{{ route('subscriptions.terminate', $sub) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Terminasi kontrak {{ $sub->sub_number }}?')"
                                        class="text-red-600 hover:text-red-800 text-xs hover:underline">
                                    Terminasi
                                </button>
                            </form>
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
</div>
@endsection
