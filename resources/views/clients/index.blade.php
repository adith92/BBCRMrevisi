@extends('layouts.app')

@section('header_title', 'Clients')

@section('content')
<div x-data="{ showCreateModal: false }" class="space-y-6">
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
        <div class="flex items-center justify-between w-full">
            <h2 class="text-xl font-semibold text-[var(--cc-text)]">
                All Clients
                <span class="text-sm font-normal text-[var(--cc-text-muted)] ml-2">({{ $clients->total() }} total)</span>
            </h2>
            @if(auth()->user()->isSales() || auth()->user()->isManager())
            <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-gray-900 text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Client
            </button>
            @endif
        </div>

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

    {{-- Create Client Modal --}}
    <div x-show="showCreateModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div class="cc-card w-full max-w-lg rounded-2xl shadow-xl overflow-hidden"
             @click.away="showCreateModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-4 border-b border-[var(--cc-border)] flex items-center justify-between">
                <h3 class="font-bold text-lg text-[var(--cc-text)]">Tambah Client Baru</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form action="{{ route('clients.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Nama Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" required placeholder="PT Contoh Indonesia" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Nama PIC <span class="text-rose-500">*</span></label>
                        <input type="text" name="pic_name" required placeholder="PIC Name" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">No. Telepon <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" required placeholder="08123456789" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Email PIC <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="pic@company.com" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Industri</label>
                        <input type="text" name="industry" placeholder="FMCG, Telco, dsb" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Alamat Kantor <span class="text-rose-500">*</span></label>
                    <input type="text" name="address" required placeholder="Jl. Sudirman No. 12" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                </div>
                
                @if(auth()->user()->isManager())
                <div>
                    <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Assign ke Sales</label>
                    <select name="assigned_sales_id" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500">
                        <option value="">Assign ke Saya Sendiri (Manager)</option>
                        @foreach($sales as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan mengenai client..." class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--cc-border)]">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-[var(--cc-border)] rounded-lg text-sm text-[var(--cc-text)] hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-gray-900 text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
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
</div>
@endsection
