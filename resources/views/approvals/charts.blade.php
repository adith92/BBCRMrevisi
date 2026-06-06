{{-- Approvals Charts Partial --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

    {{-- Approval Funnel --}}
    <div class="chart-card">
        <span class="chart-title">✅ Approval Funnel <span class="chart-sub">bulan ini</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-appr-funnel"></canvas>
        </div>
    </div>

    {{-- Avg resolution time by type --}}
    <div class="chart-card">
        <span class="chart-title">⏱️ Avg Resolution Time <span class="chart-sub">per kategori</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-appr-time"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';

    // Funnel (horizontal bar descending)
    const ctxF = document.getElementById('chart-appr-funnel');
    if (ctxF) new Chart(ctxF, {
        type: 'bar',
        data: {
            labels: ['Submitted', 'Under Review', 'Approved', 'Rejected'],
            datasets: [{
                data: [48, 22, 18, 8],
                backgroundColor: ['rgba(59,130,246,0.6)','rgba(245,158,11,0.6)','rgba(16,185,129,0.6)','rgba(239,68,68,0.6)'],
                borderColor:     ['#3b82f6','#f59e0b','#10b981','#ef4444'],
                borderWidth: 1, borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11 } } },
                y: { grid: { display: false }, ticks: { color: tc(), font: { size: 11, weight: '600' } } },
            }
        }
    });

    // Resolution time bar
    const ctxT = document.getElementById('chart-appr-time');
    if (ctxT) new Chart(ctxT, {
        type: 'bar',
        data: {
            labels: ['Fleet PO', 'Contract', 'Invoice', 'Discount', 'Onboarding'],
            datasets: [{
                label: 'Avg Hours',
                data: [4.2, 18.5, 6.8, 9.1, 24.3],
                backgroundColor: 'rgba(139,92,246,0.55)',
                borderColor: '#8b5cf6',
                borderWidth: 1, borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400 },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `${c.raw}h avg` } } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 10 } } },
                y: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'h' } },
            }
        }
    });

    new MutationObserver(() => Object.values(Chart.instances || {}).forEach(c => c.update()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
