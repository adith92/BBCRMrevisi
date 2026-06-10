@extends('layouts.app')

@section('header_title', 'Clients')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Clients'],
]" />

@php
    if (!function_exists('formatMiliar')) {
        function formatMiliar($value) {
            if (!$value) return 'Rp 0';
            if ($value >= 1000000000) {
                $formatted = number_format($value / 1000000000, 1, '.', '');
                if (str_ends_with($formatted, '.0')) {
                    $formatted = substr($formatted, 0, -2);
                }
                return 'Rp ' . $formatted . ' Miliar';
            }
            if ($value >= 1000000) {
                $formatted = number_format($value / 1000000, 1, '.', '');
                if (str_ends_with($formatted, '.0')) {
                    $formatted = substr($formatted, 0, -2);
                }
                return 'Rp ' . $formatted . ' Juta';
            }
            return 'Rp ' . number_format($value, 0, ',', '.');
        }
    }
@endphp

<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            All Clients
            <span class="text-sm font-normal text-[var(--cc-text-muted)] ml-2">({{ $clients->total() }} total)</span>
        </h2>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            {{-- Status Filter --}}
            <select id="filter-status-select"
                    class="rounded-xl border border-[var(--cc-border)] bg-[var(--cc-card)] px-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500"
                    onchange="applyClientFilters()">
                <option value="all" {{ request('filter_status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="active" {{ request('filter_status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('filter_status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>

            {{-- Sorting --}}
            <select id="sort-by-select"
                    class="rounded-xl border border-[var(--cc-border)] bg-[var(--cc-card)] px-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500"
                    onchange="applyClientFilters()">
                <option value="name_asc" {{ request('sort_by', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                <option value="transactions_desc" {{ request('sort_by') === 'transactions_desc' ? 'selected' : '' }}>Jumlah Transaksi Terbanyak</option>
                <option value="value_desc" {{ request('sort_by') === 'value_desc' ? 'selected' : '' }}>Nilai Transaksi Terbesar</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm resizable-table dark-table" data-table-id="clients-table">
            <thead>
                <tr>
                    <th class="text-left py-3 px-4">Company</th>
                    <th class="text-left py-3 px-4">PIC</th>
                    <th class="text-left py-3 px-4">Industry</th>
                    <th class="text-left py-3 px-4">Sales</th>
                    <th class="text-left py-3 px-4">Jumlah Transaksi</th>
                    <th class="text-left py-3 px-4">Nilai Transaksi</th>
                    <th class="text-left py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr class="transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('clients.show', $client->id) }}"
                           class="text-cc-cyan font-medium hover:underline">
                            {{ $client->company_name }}
                        </a>
                        <div class="text-xs text-[var(--cc-text-muted)]">{{ $client->email }}</div>
                    </td>
                    <td class="py-3 px-4">
                        <a href="mailto:{{ $client->email }}" class="text-cc-cyan hover:underline">
                            {{ $client->pic_name }}
                        </a>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $client->industry ?? '—' }}</td>
                    <td class="py-3 px-4">
                        @if($client->assignedSales)
                            <a href="{{ route('sales.performance', $client->assignedSales->id) }}"
                               class="text-cc-cyan hover:underline text-sm">
                                {{ $client->assignedSales->name }}
                            </a>
                        @else
                            <span class="text-[var(--cc-text-muted)] text-sm">Unassigned</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                        {{ $client->won_opportunities_count ?? 0 }} won
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)] font-medium">
                        {{ formatMiliar($client->won_opportunities_sum ?? 0) }}
                    </td>
                    <td class="py-3 px-4">
                        <x-status-badge :status="$client->status" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-8 text-center text-[var(--cc-text-muted)]">No clients found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</div>

<script>
function applyClientFilters() {
    const status = document.getElementById('filter-status-select').value;
    const sort = document.getElementById('sort-by-select').value;
    const url = new URL(window.location.href);
    url.searchParams.set('filter_status', status);
    url.searchParams.set('sort_by', sort);
    window.location.href = url.toString();
}
</script>
@endsection
