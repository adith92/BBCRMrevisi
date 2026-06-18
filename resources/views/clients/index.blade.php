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

        $industryMax = max(1, (int) collect($industryRevenue)->max('value'));
        $statusMax = max(1, (int) collect($statusBreakdown)->max('value'));
        $currentStatus = request('filter_status', 'all');
        $currentSort = request('sort_by', 'name_asc');
        $searchValue = request('search', '');
    @endphp

    @push('styles')
    <style>
        .portfolio-card {
            background: linear-gradient(135deg, color-mix(in srgb, var(--cc-card) 92%, transparent), color-mix(in srgb, var(--cc-surface) 88%, transparent));
            border: 1px solid var(--cc-border);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.12);
        }
        .summary-tile {
            background: linear-gradient(180deg, color-mix(in srgb, var(--cc-card) 96%, transparent), color-mix(in srgb, var(--cc-surface) 92%, transparent));
            border: 1px solid var(--cc-border);
        }
        .summary-kicker {
            color: var(--cc-text-muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .dashboard-panel {
            background: linear-gradient(180deg, color-mix(in srgb, var(--cc-card) 96%, transparent), color-mix(in srgb, var(--cc-surface) 93%, transparent));
            border: 1px solid var(--cc-border);
        }
        .insight-bar-bg {
            background: color-mix(in srgb, var(--cc-text-muted) 14%, transparent);
        }
        .toolbar-input {
            background: color-mix(in srgb, var(--cc-card) 96%, transparent);
            border: 1px solid var(--cc-border);
            color: var(--cc-text);
        }
        .toolbar-input::placeholder {
            color: var(--cc-text-faint);
        }
        .clients-table thead th {
            background: color-mix(in srgb, var(--cc-surface) 72%, transparent);
            color: var(--cc-text-muted);
        }
        .clients-table tbody tr:hover td {
            background: var(--cc-row-hover);
        }
    </style>
    @endpush

    <div class="portfolio-card rounded-[28px] p-6 md:p-8">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
                <div class="space-y-2">
                    <div class="summary-kicker">Client Portfolio</div>
                    <h2 class="text-3xl md:text-4xl font-black tracking-tight text-[var(--cc-text)]">All Clients</h2>
                    <p class="text-sm md:text-base text-[var(--cc-text-muted)] max-w-3xl">
                        Ringkasan hubungan client, kontribusi revenue, dan sinyal akun yang perlu follow-up.
                    </p>
                </div>
                @if(auth()->user()->isSales() || auth()->user()->isManager())
                <button @click="showCreateModal = true" class="btn-primary self-start">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Client
                </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="summary-tile rounded-2xl p-5">
                    <div class="summary-kicker">Total Client</div>
                    <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ number_format($summary['total_clients']) }}</div>
                    <div class="mt-2 text-sm text-[var(--cc-text-muted)]">{{ number_format($summary['active_clients']) }} aktif</div>
                </div>
                <div class="summary-tile rounded-2xl p-5">
                    <div class="summary-kicker">Active Revenue</div>
                    <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ formatMiliar($summary['active_revenue']) }}</div>
                    <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Dari deal won</div>
                </div>
                <div class="summary-tile rounded-2xl p-5">
                    <div class="summary-kicker">Accounts At Risk</div>
                    <div class="mt-3 text-3xl font-black text-[var(--cc-text)]">{{ number_format($summary['at_risk_clients']) }}</div>
                    <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Minim aktivitas atau tidak aktif</div>
                </div>
                <div class="summary-tile rounded-2xl p-5">
                    <div class="summary-kicker">Top Industry</div>
                    <div class="mt-3 text-2xl font-black text-[var(--cc-text)]">{{ $summary['top_industry'] }}</div>
                    <div class="mt-2 text-sm text-[var(--cc-text-muted)]">Kontributor portofolio tertinggi</div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="dashboard-panel rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div>
                            <div class="summary-kicker">Client Mix</div>
                            <h3 class="mt-1 text-xl font-bold text-[var(--cc-text)]">Revenue by Industry</h3>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse($industryRevenue as $row)
                        <div class="grid grid-cols-[120px_1fr_90px] gap-3 items-center">
                            <div class="text-sm font-semibold text-[var(--cc-text)] truncate">{{ $row['label'] }}</div>
                            <div class="insight-bar-bg h-3 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-sky-400 via-cyan-400 to-blue-500" style="width: {{ max(10, round(($row['value'] / $industryMax) * 100)) }}%"></div>
                            </div>
                            <div class="text-right text-sm font-semibold text-[var(--cc-text-muted)]">{{ formatMiliar($row['value']) }}</div>
                        </div>
                        @empty
                        <div class="text-sm text-[var(--cc-text-muted)]">Belum ada revenue industri yang bisa ditampilkan.</div>
                        @endforelse
                    </div>
                </div>

                <div class="dashboard-panel rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div>
                            <div class="summary-kicker">Relationship Health</div>
                            <h3 class="mt-1 text-xl font-bold text-[var(--cc-text)]">Client Status</h3>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach($statusBreakdown as $row)
                        <div class="grid grid-cols-[110px_1fr_52px] gap-3 items-center">
                            <div class="text-sm font-semibold text-[var(--cc-text)]">{{ $row['label'] }}</div>
                            <div class="insight-bar-bg h-3 rounded-full overflow-hidden">
                                <div class="h-full rounded-full
                                    @if($row['tone'] === 'emerald') bg-gradient-to-r from-emerald-400 to-cyan-400
                                    @elseif($row['tone'] === 'amber') bg-gradient-to-r from-amber-400 to-orange-400
                                    @elseif($row['tone'] === 'rose') bg-gradient-to-r from-rose-400 to-pink-400
                                    @else bg-gradient-to-r from-slate-400 to-slate-500 @endif"
                                    style="width: {{ max(8, round(($row['value'] / $statusMax) * 100)) }}%"></div>
                            </div>
                            <div class="text-right text-sm font-semibold text-[var(--cc-text-muted)]">{{ $row['value'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="cc-card rounded-2xl p-5 md:p-6">
                <form method="GET" action="{{ route('clients.index') }}" class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">
                    <div class="relative flex-1 max-w-xl">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[var(--cc-text-faint)] text-[20px]">search</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $searchValue }}"
                            placeholder="Cari company, PIC, sales, industry..."
                            class="toolbar-input w-full rounded-xl pl-12 pr-4 py-3 text-sm outline-none focus:border-sky-500" />
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select name="filter_status" class="dark-input min-w-[180px] px-4 py-3 text-sm" onchange="this.form.submit()">
                            <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="prospect" {{ $currentStatus === 'prospect' ? 'selected' : '' }}>Prospect</option>
                            <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        <select name="sort_by" class="dark-input min-w-[220px] px-4 py-3 text-sm" onchange="this.form.submit()">
                            <option value="name_asc" {{ $currentSort === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="name_desc" {{ $currentSort === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="transactions_desc" {{ $currentSort === 'transactions_desc' ? 'selected' : '' }}>Jumlah Transaksi Terbanyak</option>
                            <option value="value_desc" {{ $currentSort === 'value_desc' ? 'selected' : '' }}>Nilai Transaksi Terbesar</option>
                        </select>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm resizable-table dark-table clients-table" data-table-id="clients-table">
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
                                <td class="py-4 px-4">
                                    <a href="{{ route('clients.show', $client->id) }}" class="text-cc-cyan font-semibold hover:underline">
                                        {{ $client->company_name }}
                                    </a>
                                    <div class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $client->email }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-[var(--cc-text)]">{{ $client->pic_name }}</div>
                                    <div class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $client->phone }}</div>
                                </td>
                                <td class="py-4 px-4 text-[var(--cc-text-muted)]">{{ $client->industry ?? '—' }}</td>
                                <td class="py-4 px-4">
                                    @if($client->assignedSales)
                                        <a href="{{ route('sales.performance', $client->assignedSales->id) }}" class="text-cc-cyan hover:underline text-sm">
                                            {{ $client->assignedSales->name }}
                                        </a>
                                    @else
                                        <span class="text-[var(--cc-text-muted)] text-sm">Unassigned</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-[var(--cc-text-muted)]">
                                    {{ $client->won_opportunities_count ?? 0 }} won
                                </td>
                                <td class="py-4 px-4 text-[var(--cc-text)] font-semibold">
                                    {{ formatMiliar($client->won_opportunities_sum ?? 0) }}
                                </td>
                                <td class="py-4 px-4">
                                    <x-status-badge :status="$client->status" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-[var(--cc-text-muted)]">
                                    Tidak ada client yang cocok dengan filter ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">{{ $clients->links() }}</div>
            </div>
        </div>
    </div>

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
</div>
@endsection
