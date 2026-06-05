@extends('layouts.app')

@section('page-title', 'General Manager Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Row 1: Revenue KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('finance.index', ['filter' => 'today']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-md hover:bg-blue-50 transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Revenue Today</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
                    <p class="text-xs text-blue-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
                </div>
                <div class="text-3xl">📈</div>
            </div>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'week']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500 hover:shadow-md hover:bg-indigo-50 transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Revenue Week</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($weekRevenue) }}</p>
                    <p class="text-xs text-indigo-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
                </div>
                <div class="text-3xl">📊</div>
            </div>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'month']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 hover:shadow-md hover:bg-purple-50 transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Revenue Month</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
                    <p class="text-xs text-purple-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
                </div>
                <div class="text-3xl">💰</div>
            </div>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'year']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-green-500 hover:shadow-md hover:bg-green-50 transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm">Revenue Year</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($yearRevenue) }}</p>
                    <p class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
                </div>
                <div class="text-3xl">🎯</div>
            </div>
        </a>
    </div>

    {{-- Row 2: Operational KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500 hover:shadow-md hover:bg-yellow-50 transition-all">
            <p class="text-gray-500 text-sm">Active Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeBookings }}</p>
            <p class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View active bookings →</p>
        </a>

        <a href="{{ route('clients.index') }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-orange-500 hover:shadow-md hover:bg-orange-50 transition-all">
            <p class="text-gray-500 text-sm">Total Clients</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalClients }}</p>
            <p class="text-xs text-orange-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View all clients →</p>
        </a>

        <a href="{{ route('fleet.index') }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-red-500 hover:shadow-md hover:bg-red-50 transition-all">
            <p class="text-gray-500 text-sm">Total Fleet</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalFleet }}</p>
            <p class="text-xs text-red-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View fleet →</p>
        </a>

        <a href="{{ route('finance.index', ['status' => 'overdue']) }}" class="group block bg-white rounded-lg shadow p-6 border-l-4 border-pink-500 hover:shadow-md hover:bg-pink-50 transition-all">
            <p class="text-gray-500 text-sm">Invoice Overdue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $outstandingCount }}</p>
            <p class="text-xs text-pink-700 font-semibold mt-1">{{ \App\Helpers\FormatHelper::formatIDR($outstandingAmt) }}</p>
            <p class="text-xs text-pink-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View overdue invoices →</p>
        </a>
    </div>

    {{-- Revenue Chart with Toggle --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
            <div class="flex space-x-2">
                <button onclick="updateChart('daily')" class="period-btn px-3 py-1 rounded text-sm font-medium bg-gray-200 text-gray-800" data-period="daily">Daily</button>
                <button onclick="updateChart('weekly')" class="period-btn px-3 py-1 rounded text-sm font-medium bg-gray-200 text-gray-800" data-period="weekly">Weekly</button>
                <button onclick="updateChart('monthly')" class="period-btn px-3 py-1 rounded text-sm font-medium bg-blue-500 text-white" data-period="monthly">Monthly</button>
                <button onclick="updateChart('yearly')" class="period-btn px-3 py-1 rounded text-sm font-medium bg-gray-200 text-gray-800" data-period="yearly">Yearly</button>
            </div>
        </div>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    {{-- Revenue per Sales (clickable rows + clickable chart bars) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top 5 Sales Table --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Sales Performance</h3>
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr class="text-gray-500">
                        <th class="text-left py-2">#</th>
                        <th class="text-left py-2">Sales</th>
                        <th class="text-right py-2">Revenue</th>
                        <th class="text-right py-2">Bookings</th>
                    </tr>
                </thead>
                <tbody id="salesRevenueTable">
                    {{-- Populated via JS --}}
                </tbody>
            </table>
            {{-- Static Top 5 from server (always visible) --}}
            @if($topSales->count())
            <div class="mt-4 space-y-2">
                @foreach($topSales as $i => $sale)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
                        <a href="{{ route('sales.performance', ['user' => $sale->id]) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                            {{ $sale->name }}
                        </a>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900 text-sm">{{ \App\Helpers\FormatHelper::formatIDR($sale->total_revenue ?? 0) }}</div>
                        <div class="text-xs text-gray-500">{{ $sale->bookings_count }} bookings</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Top 5 Clients --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Clients by Revenue</h3>
            @if($topClients->count())
            <div class="space-y-2">
                @foreach($topClients as $i => $client)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
                        <div>
                            <a href="{{ route('clients.show', $client->id) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium hover:underline block">
                                {{ $client->company_name }}
                            </a>
                            <span class="text-xs text-gray-500">{{ $client->bookings_count }} bookings</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900 text-sm">{{ \App\Helpers\FormatHelper::formatIDR($client->total_spend ?? 0) }}</div>
                        <x-status-badge :status="$client->status" />
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <a href="{{ route('clients.index') }}" class="block mt-4 text-center text-blue-600 hover:text-blue-800 text-sm font-medium">View all clients →</a>
        </div>
    </div>

    {{-- Revenue per Sales Bar Chart (clickable bars) --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2 text-gray-900">Revenue by Sales <span class="text-sm text-gray-500 font-normal">(click bar to view performance)</span></h3>
        <canvas id="salesRevenueChart" height="80"></canvas>
    </div>

</div>

@push('scripts')
<script>
    let revenueChart, salesRevenueChart;
    let salesIdMap = {}; // map sales name → id for chart click

    function updateChart(period) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-800');
        });
        document.querySelector(`[data-period="${period}"]`).classList.add('bg-blue-500', 'text-white');
        document.querySelector(`[data-period="${period}"]`).classList.remove('bg-gray-200', 'text-gray-800');

        fetch(`/api/revenue?period=${period}`)
            .then(r => r.json())
            .then(data => {
                const labels = data.map(d => d.date || d.week || d.month || d.year);
                const values = data.map(d => parseInt(d.total));

                if (revenueChart) revenueChart.destroy();

                const ctx = document.getElementById('revenueChart').getContext('2d');
                revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Revenue',
                            data: values,
                            borderColor: '#185FA5',
                            backgroundColor: 'rgba(24, 95, 165, 0.1)',
                            fill: true, tension: 0.4, pointRadius: 5,
                            pointBackgroundColor: '#185FA5'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
                        },
                        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                window.location.href = '/finance?filter=' + period;
                            }
                        }
                    }
                });
            });
    }

    function loadRevenuePerSales() {
        fetch('/api/revenue/per-sales')
            .then(r => r.json())
            .then(data => {
                const labels = data.map(d => d.sales_name);
                const values = data.map(d => d.total_revenue);

                // Build id map
                data.forEach(d => { salesIdMap[d.sales_name] = d.sales_id; });

                if (salesRevenueChart) salesRevenueChart.destroy();

                const ctx = document.getElementById('salesRevenueChart').getContext('2d');
                salesRevenueChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Total Revenue',
                            data: values,
                            backgroundColor: ['#185FA5', '#378ADD', '#042C53', '#1e88e5', '#0d47a1'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
                        },
                        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } },
                        onClick: (event, elements) => {
                            if (elements.length > 0) {
                                const idx = elements[0].index;
                                const salesName = labels[idx];
                                const salesId = salesIdMap[salesName];
                                if (salesId) window.location.href = `/sales/${salesId}/performance`;
                            }
                        }
                    }
                });
            });
    }

    updateChart('monthly');
    loadRevenuePerSales();
</script>
@endpush
@endsection
