@extends('layouts.app')
@section('title', 'Dashboard — Operational')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500"><p class="text-xs text-gray-500 uppercase">Available</p><p class="text-xl font-bold text-green-600">{{ $availableFleet ?? 0 }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500"><p class="text-xs text-gray-500 uppercase">On Trip</p><p class="text-xl font-bold text-blue-600">{{ $onTripFleet ?? 0 }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500"><p class="text-xs text-gray-500 uppercase">Maintenance</p><p class="text-xl font-bold text-yellow-600">{{ $maintenanceFleet ?? 0 }}</p></div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500"><p class="text-xs text-gray-500 uppercase">Total Driver</p><p class="text-xl font-bold text-purple-600">{{ $totalDrivers ?? 0 }}</p></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Fleet Status</h3>
        <canvas id="fleetChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Pool Availability</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Pool</th><th>Vehicles</th><th>Capacity</th></tr></thead><tbody>
        @forelse($pools ?? [] as $p)
        <tr class="border-b"><td class="py-2">{{ $p->name }}</td><td>{{ $p->vehicles_count }}</td><td>{{ $p->capacity }}</td></tr>
        @empty<tr><td colspan="3" class="py-2 text-gray-400">No pools</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h3 class="font-semibold text-navy mb-3">Active Bookings</h3>
    <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">No.</th><th>Client</th><th>Vehicle</th><th>Driver</th><th>Pickup</th><th>Status</th></tr></thead><tbody>
    @forelse($activeBookings ?? [] as $b)
    <tr class="border-b"><td class="py-2">{{ is_array($b) ? $b['booking_number'] : $b->booking_number }}</td><td>{{ is_array($b) ? $b['client_name'] : ($b->client->company_name ?? '-') }}</td><td>{{ is_array($b) ? $b['vehicle'] : ($b->vehicle->plate_number ?? '-') }}</td><td>{{ is_array($b) ? $b['driver'] : ($b->driver->name ?? '-') }}</td><td>{{ is_array($b) ? \Carbon\Carbon::parse($b['pickup_datetime'])->format('d M H:i') : \Carbon\Carbon::parse($b->pickup_datetime)->format('d M H:i') }}</td><td><span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">{{ is_array($b) ? $b['status'] : $b->status }}</span></td></tr>
    @empty<tr><td colspan="6" class="py-2 text-gray-400">No active bookings</td></tr>@endforelse
    </tbody></table>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-navy mb-3">Maintenance Schedule</h3>
    <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Vehicle</th><th>Type</th><th>Vendor</th><th>Date</th><th>Status</th></tr></thead><tbody>
    @forelse($maintenanceSchedule ?? [] as $m)
    <tr class="border-b"><td class="py-2">{{ $m->vehicle->plate_number ?? '-' }}</td><td>{{ $m->type }}</td><td>{{ $m->vendor ?? '-' }}</td><td>{{ $m->scheduled_date->format('d M Y') }}</td><td><span class="px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700">{{ $m->status }}</span></td></tr>
    @empty<tr><td colspan="5" class="py-2 text-gray-400">No scheduled maintenance</td></tr>@endforelse
    </tbody></table>
</div>
@push('scripts')
<script>
new Chart(document.getElementById('fleetChart'),{type:'doughnut',data:{labels:['Available','On Trip','Maintenance'],datasets:[{data:[{{ $availableFleet ?? 0 }},{{ $onTripFleet ?? 0 }},{{ $maintenanceFleet ?? 0 }}],backgroundColor:['#22C55E','#185FA5','#F59E0B']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}}});
</script>
@endpush
@endsection
