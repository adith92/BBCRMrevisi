{{-- Subscriptions Charts Partial --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

    {{-- MRR Growth --}}
    <div class="chart-card">
        <span class="chart-title">📈 MRR Growth <span class="chart-sub">6 bulan</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-sub-mrr"></canvas>
        </div>
    </div>

    {{-- Subscription Status --}}
    <div class="chart-card">
        <span class="chart-title">🔄 Subscription Status <span class="chart-sub">aktif vs churn</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-sub-status"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';

    // MRR area chart
    const ctxM = document.getElementById('chart-sub-mrr');
    if (ctxM) new Chart(ctxM, {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [{
                label: 'MRR (Jt)', data: [420, 485, 510, 560, 598, 645],
                borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)',
                fill: true, tension: 0.45, borderWidth: 2.5,
                pointRadius: 4, pointBackgroundColor: '#10b981',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `MRR Rp ${c.raw} Jt` } } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11 } } },
                y: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'Jt' } },
            }
        }
    });

    // Status doughnut
    const ctxS = document.getElementById('chart-sub-status');
    if (ctxS) new Chart(ctxS, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Trial', 'Churned', 'Paused'],
            datasets: [{ data: [68, 12, 8, 4], backgroundColor: ['#10b981','#3b82f6','#ef4444','#f59e0b'], borderColor: isDark() ? '#09090f' : '#f0f0fa', borderWidth: 3, hoverOffset: 5 }]
        },
        options: { responsive: true, maintainAspectRatio: false, resizeDelay: 100, cutout: '62%', animation: { duration: 400 },
            plugins: { legend: { position: 'right', labels: { color: tc(), font: { size: 11 }, boxWidth: 12 } } } }
    });

    new MutationObserver(() => Object.values(Chart.instances || {}).forEach(c => c.update()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
