@extends('layouts.app')
@section('title', 'Dashboard — Sales')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Revenue Hari Ini',$dailyRevenue??0],['Revenue Minggu Ini',$weeklyRevenue??0],['Revenue Bulan Ini',$monthlyRevenue??0],['Revenue Tahun Ini',$yearlyRevenue??0]] as $i=>$kpi)
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue">
        <p class="text-xs text-gray-500 uppercase">{{ $kpi[0] }}</p>
        <p class="text-xl font-bold text-blue mt-1">{{ formatIDR($kpi[1]) }}</p>
    </div>
    @endforeach
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-500 uppercase">My Active Bookings</p>
        <p class="text-xl font-bold text-green-600">{{ $activeBookings ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 uppercase">My Clients</p>
        <p class="text-xl font-bold text-blue-600">{{ $totalClients ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
        <p class="text-xs text-gray-500 uppercase">Pending Confirmation</p>
        <p class="text-xl font-bold text-yellow-600">{{ $pendingBookings ?? 0 }}</p>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">My Revenue Trend</h3>
        <canvas id="myRevenueChart" height="180"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Upcoming Follow-ups</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Client</th><th>Date</th><th>Status</th></tr></thead><tbody>
        @forelse($upcomingFollowUps ?? [] as $f)
        <tr class="border-b"><td class="py-2">{{ $f->client->company_name ?? '-' }}</td><td>{{ \Carbon\Carbon::parse($f->follow_up_date)->format('d M') }}</td><td>{{ $f->status }}</td></tr>
        @empty<tr><td colspan="3" class="py-2 text-gray-400">No follow-ups</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-navy mb-3">My Recent Bookings</h3>
    <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">No.</th><th>Client</th><th>Vehicle</th><th>Date</th><th>Status</th></tr></thead><tbody>
    @forelse($recentBookings ?? [] as $b)
    <tr class="border-b hover:bg-gray-50"><td class="py-2">{{ $b->booking_number }}</td><td>{{ $b->client->company_name ?? '-' }}</td><td>{{ $b->vehicle->plate_number ?? '-' }}</td><td>{{ \Carbon\Carbon::parse($b->pickup_datetime)->format('d M Y') }}</td><td><span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ $b->status }}</span></td></tr>
    @empty<tr><td colspan="5" class="py-2 text-gray-400">No bookings</td></tr>@endforelse
    </tbody></table>
</div>
@push('scripts')
<script>
fetch('/api/revenue?period=monthly').then(r=>r.json()).then(data => {
    new Chart(document.getElementById('myRevenueChart'),{type:'line',data:{labels:data.map(d=>d.date),datasets:[{label:'Revenue',data:data.map(d=>d.total),borderColor:'#185FA5',backgroundColor:'rgba(24,95,165,0.1)',fill:true,tension:0.3}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
});
</script>
@endpush
@endsection
