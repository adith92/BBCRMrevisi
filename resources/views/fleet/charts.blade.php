{{-- Fleet Charts Partial — included at bottom of fleet/index.blade.php --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

    {{-- Fleet Utilization by Type --}}
    <div class="chart-card">
        <div class="flex items-center justify-between mb-3">
            <span class="chart-title">🚌 Utilization by Fleet Type <span class="chart-sub">bulan ini</span></span>
        </div>
        <div style="height:180px;position:relative">
            <canvas id="chart-fleet-util"></canvas>
        </div>
    </div>

    {{-- Maintenance Status --}}
    <div class="chart-card">
        <div class="flex items-center justify-between mb-3">
            <span class="chart-title">🔧 Maintenance Status <span class="chart-sub">semua unit</span></span>
        </div>
        <div style="height:180px;position:relative">
            <canvas id="chart-fleet-maint"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';

    // Utilization bar
    const ctxU = document.getElementById('chart-fleet-util');
    if (ctxU) new Chart(ctxU, {
        type: 'bar',
        data: {
            labels: ['Golden Bird', 'Big Bird', 'Cititrans', 'Executive'],
            datasets: [{
                label: 'Utilization %',
                data: [92, 84, 78, 73],
                backgroundColor: ['rgba(245,158,11,0.6)','rgba(16,185,129,0.6)','rgba(59,130,246,0.6)','rgba(139,92,246,0.6)'],
                borderColor:     ['rgba(245,158,11,0.9)','rgba(16,185,129,0.9)','rgba(59,130,246,0.9)','rgba(139,92,246,0.9)'],
                borderWidth: 1, borderRadius: 6, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `${c.raw}% utilization` } } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11, weight: '600' } } },
                y: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + '%' }, max: 100 },
            }
        }
    });

    // Maintenance doughnut
    const ctxM = document.getElementById('chart-fleet-maint');
    if (ctxM) new Chart(ctxM, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Scheduled Maint.', 'In Repair', 'Standby'],
            datasets: [{
                data: [142, 18, 8, 12],
                backgroundColor: ['#10b981','#f59e0b','#ef4444','#3b82f6'],
                borderColor: isDark() ? '#09090f' : '#f0f0fa',
                borderWidth: 3, hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            cutout: '65%',
            plugins: { legend: { position: 'right', labels: { color: tc(), font: { size: 11 }, boxWidth: 12 } } },
        }
    });

    new MutationObserver(() => Object.values(Chart.instances || {}).forEach(c => c.update()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
