@extends('layouts.app')
@section('title', 'Dashboard — General Manager')
@section('content')
<!-- KPI Row 1 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Revenue Hari Ini',$dailyRevenue??0],['Revenue Minggu Ini',$weeklyRevenue??0],['Revenue Bulan Ini',$monthlyRevenue??0],['Revenue Tahun Ini',$yearlyRevenue??0]] as $i=>$kpi)
    <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $i===2?'border-blue':'border-navy' }}">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $kpi[0] }}</p>
        <p class="text-xl font-bold text-navy mt-1">{{ formatIDR($kpi[1]) }}</p>
    </div>
    @endforeach
</div>

<!-- KPI Row 2 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-500 uppercase">Total Booking Aktif</p>
        <p class="text-xl font-bold text-green-600 mt-1">{{ $totalBookings ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">Total Client</p>
        <p class="text-xl font-bold text-blue-600 mt-1">{{ $totalClients ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
        <p class="text-xs text-gray-500 uppercase">Fleet Available</p>
        <p class="text-xl font-bold text-yellow-600 mt-1">{{ $totalFleet ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-500 uppercase">Outstanding Invoice</p>
        <p class="text-xl font-bold text-red-600 mt-1">{{ $outstandingInvoice ?? 0 }}</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-navy">Revenue Trend</h3>
            <div class="flex gap-1">
                <button onclick="loadRevenue('daily')" class="px-2 py-1 text-xs bg-navy text-white rounded">Harian</button>
                <button onclick="loadRevenue('weekly')" class="px-2 py-1 text-xs bg-gray-200 rounded">Mingguan</button>
                <button onclick="loadRevenue('monthly')" class="px-2 py-1 text-xs bg-gray-200 rounded">Bulanan</button>
                <button onclick="loadRevenue('yearly')" class="px-2 py-1 text-xs bg-gray-200 rounded">Tahunan</button>
            </div>
        </div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Revenue per Sales</h3>
        <canvas id="salesChart" height="200"></canvas>
    </div>
</div>

<!-- Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Top 5 Clients</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="py-2">Company</th><th>Industry</th><th class="text-right">Bookings</th></tr></thead>
            <tbody>
            @forelse($topClients ?? [] as $c)
            <tr class="border-b"><td class="py-2">{{ $c->company_name }}</td><td>{{ $c->industry }}</td><td class="text-right">{{ $c->bookings_count }}</td></tr>
            @empty
            <tr><td colspan="3" class="py-2 text-gray-400">No data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Recent Bookings</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="py-2">No.</th><th>Client</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recentBookings ?? [] as $b)
            <tr class="border-b hover:bg-gray-50 cursor-pointer" onclick="BlueERP.popup('Booking {{ $b->booking_number }}','<p>Status: {{ $b->status }}</p>')">
                <td class="py-2">{{ $b->booking_number }}</td><td>{{ $b->client->company_name ?? '-' }}</td>
                <td><span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ $b->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="3" class="py-2 text-gray-400">No data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
let revChart;
function loadRevenue(period) {
    fetch('/api/revenue?period='+period).then(r=>r.json()).then(data => {
        const labels = data.map(d=>d.date), values = data.map(d=>d.total);
        if(revChart) revChart.destroy();
        revChart = new Chart(document.getElementById('revenueChart'), {
            type:'line', data:{labels, datasets:[{label:'Revenue',data:values,borderColor:'#185FA5',backgroundColor:'rgba(24,95,165,0.1)',fill:true,tension:0.3}]},
            options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
        });
    });
}
fetch('/api/revenue/per-sales').then(r=>r.json()).then(data => {
    new Chart(document.getElementById('salesChart'), {
        type:'bar', data:{labels:data.map(d=>d.sales_name), datasets:[{label:'Revenue',data:data.map(d=>d.total_revenue),backgroundColor:'#042C53'}]},
        options:{responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
    });
});
loadRevenue('monthly');
</script>
@endpush
@endsection
