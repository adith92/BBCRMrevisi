@extends('layouts.app')

@section('header_title', 'GM Dashboard')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Welcome Header --}}
    <div class="bg-gradient-to-r from-[#003887] via-[#1e4fa8] to-secondary text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute right-4 bottom-0 top-0 opacity-10 pointer-events-none flex items-center">
            <span class="material-symbols-outlined text-[120px]">insights</span>
        </div>
        <div class="relative z-10">
            <h2 class="text-2xl font-bold">Welcome back, General Manager!</h2>
            <p class="text-blue-100 text-sm mt-1">Real-time analytical dispatch metrics and consolidated B2B enterprise performance.</p>
        </div>
    </div>

    {{-- 6 KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Revenue Bulan Ini</p>
                <p class="text-3xl font-extrabold text-[#003887]">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue ?? 0) }}</p>
                <p class="text-[10px] text-slate-400">Total pembayaran terkonfirmasi</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-700 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">payments</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Fleet</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $totalFleet ?? 0 }}</p>
                <p class="text-[10px] text-slate-400">Unit armada terdaftar</p>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-700 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">local_shipping</span>
            </div>
        </div>

        <a href="{{ route('bookings.index', ['status'=>'active']) }}" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:border-blue-300 transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1">
                    Active Bookings <span class="material-symbols-outlined text-xs">open_in_new</span>
                </p>
                <p class="text-3xl font-extrabold text-blue-800">{{ $activeBookings ?? 0 }}</p>
                <p class="text-[10px] text-blue-500 font-semibold">Click untuk inspect →</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-800 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">distance</span>
            </div>
        </a>

        <a href="{{ route('clients.index') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:border-purple-300 transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Clients</p>
                <p class="text-3xl font-extrabold text-purple-700">{{ $totalClients ?? 0 }}</p>
                <p class="text-[10px] text-slate-400">Klien B2B aktif</p>
            </div>
            <div class="p-3 bg-purple-50 text-purple-700 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">business</span>
            </div>
        </a>

        <a href="{{ route('pipeline.index') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:border-emerald-300 transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sales Pipeline</p>
                <p class="text-3xl font-extrabold text-emerald-700">{{ $pipelineCount ?? 0 }}</p>
                <p class="text-[10px] text-slate-400">Opportunity aktif</p>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-700 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">handshake</span>
            </div>
        </a>

        <a href="{{ route('invoices.index', ['status'=>'overdue']) }}" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:border-red-300 transition-all group flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-xs font-bold text-red-500 uppercase tracking-wider">Invoice Overdue</p>
                <p class="text-3xl font-extrabold text-red-600">{{ $outstandingCount ?? 0 }}</p>
                <p class="text-[10px] text-red-400 font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($outstandingAmt ?? 0) }}</p>
            </div>
            <div class="p-3 bg-red-50 text-red-600 rounded-xl group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[28px]">warning</span>
            </div>
        </a>
    </div>

    {{-- Revenue Chart --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-900">Revenue Trend</h3>
                <p class="text-xs text-slate-400">Klik bar/periode untuk detail</p>
            </div>
            <div class="flex gap-2">
                @foreach(['daily'=>'Harian','weekly'=>'Mingguan','monthly'=>'Bulanan','yearly'=>'Tahunan'] as $key=>$label)
                <button onclick="updateChart('{{ $key }}')" data-period="{{ $key }}"
                    class="period-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $key==='monthly' ? 'bg-[#003887] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    {{-- Top Sales & Top Clients --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-4">Top Sales Performance</h3>
            @forelse($topSales ?? [] as $i => $sale)
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-blue-100 text-[#003887] text-xs font-extrabold flex items-center justify-center">{{ $i+1 }}</span>
                    <a href="#" class="text-sm font-semibold text-slate-800 hover:text-[#003887]">{{ $sale->name }}</a>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-900">{{ \App\Helpers\FormatHelper::formatIDR($sale->total_revenue ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400">{{ $sale->bookings_count ?? 0 }} bookings</div>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Belum ada data sales</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-900 mb-4">Top Clients by Revenue</h3>
            @forelse($topClients ?? [] as $i => $client)
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold flex items-center justify-center">{{ $i+1 }}</span>
                    <div>
                        <a href="{{ route('clients.show', $client->id) }}" class="text-sm font-semibold text-slate-800 hover:text-[#003887] block">{{ $client->company_name }}</a>
                        <span class="text-[10px] text-slate-400">{{ $client->bookings_count ?? 0 }} bookings</span>
                    </div>
                </div>
                <div class="text-sm font-bold text-slate-900">{{ \App\Helpers\FormatHelper::formatIDR($client->total_spend ?? 0) }}</div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Belum ada data klien</p>
            @endforelse
            <a href="{{ route('clients.index') }}" class="block mt-3 text-center text-xs font-semibold text-[#003887] hover:underline">Lihat semua klien →</a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let revenueChart;
function updateChart(period) {
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('bg-[#003887]','text-white');
        btn.classList.add('bg-slate-100','text-slate-600');
    });
    const active = document.querySelector(`[data-period="${period}"]`);
    if (active) { active.classList.add('bg-[#003887]','text-white'); active.classList.remove('bg-slate-100','text-slate-600'); }
    fetch(`/api/revenue?period=${period}`)
        .then(r => r.json())
        .then(data => {
            const labels = data.map(d => d.date || d.week || d.month || d.year);
            const values = data.map(d => parseInt(d.total));
            if (revenueChart) revenueChart.destroy();
            revenueChart = new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: { labels, datasets: [{ label: 'Revenue', data: values, borderColor: '#003887', backgroundColor: 'rgba(0,56,135,0.08)', fill: true, tension: 0.4, pointRadius: 5, pointBackgroundColor: '#003887' }] },
                options: { responsive: true,
                    plugins: { tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } } },
                    scales: { y: { ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt' } } }
                }
            });
        }).catch(() => {});
}
updateChart('monthly');
</script>
@endpush
