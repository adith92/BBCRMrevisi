@extends('layouts.app')

@section('header_title', 'Pipeline Analytics')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-secondary text-[28px]">monitoring</span>
        <div>
            <h2 class="text-xl font-bold text-cc">Pipeline Analytics</h2>
            <p class="text-xs text-cc-muted">Konversi &amp; performa funnel penjualan</p>
        </div>
    </div>
    @include('components.analytics-nav')
    {{-- Win rate banner --}}
    <div class="bg-gradient-to-r from-[var(--color-secondary)] via-[#1e4fa8] to-secondary text-gray-900 rounded-2xl p-6 shadow-xl flex items-center justify-between">
        <div>
            <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider">Overall Win Rate</p>
            <p class="text-4xl font-extrabold mt-1">{{ $overallWinRate }}%</p>
        </div>
        <span class="material-symbols-outlined text-[64px] opacity-20">trophy</span>
    </div>

    {{-- Stage cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $stageMeta = [
                'call_meeting' => ['phone_in_talk','purple'],
                'prospecting'  => ['search','indigo'],
                'proposal'     => ['description','blue'],
                'negotiation'  => ['gavel','amber'],
                'won'          => ['check_circle','emerald'],
                'lost'         => ['cancel','red'],
            ];
        @endphp
        @foreach($stages as $stage)
        @php [$icon,$color] = $stageMeta[$stage] ?? ['circle','slate']; $data = $stageData[$stage] ?? null; @endphp
        <a href="/opportunities?stage={{ $stage }}" class="cc-card rounded-2xl shadow-sm border border-cc p-5 block hover:border-blue-500/50 hover:shadow-md transition group cursor-pointer">
            <div class="flex items-center justify-between mb-3">
                <span class="material-symbols-outlined text-{{ $color }}-600 group-hover:scale-110 transition-transform">{{ $icon }}</span>
                <span class="text-2xl font-extrabold text-cc">{{ $counts[$stage] ?? 0 }}</span>
            </div>
            <p class="text-xs font-bold text-cc-muted capitalize group-hover:text-blue-500 transition-colors">{{ str_replace('_', ' ', $stage) }}</p>
            <p class="text-[11px] text-cc-muted mt-1">{{ \App\Helpers\FormatHelper::formatIDR($data->total_value ?? 0) }}</p>
        </a>
        @endforeach
    </div>

    {{-- Chart Section --}}
    <div class="cc-card rounded-2xl shadow-sm border border-cc p-6">
        <h3 class="text-base font-bold text-cc mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">bar_chart</span>
            Funnel Volume & Value by Stage
        </h3>
        <div style="height:320px;position:relative">
            <canvas id="chart-pipeline-stages"></canvas>
        </div>
        <p class="text-[10px] text-cc-muted mt-2 text-center italic">Tips: Klik pada batang diagram untuk melihat daftar oportunitas pada stage tersebut.</p>
    </div>

    {{-- Conversion rates --}}
    <div class="cc-card rounded-2xl shadow-sm border border-cc p-6">
        <h3 class="text-base font-bold text-cc mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">conversion_path</span>
            Conversion Rates
        </h3>
        <div class="space-y-4">
            @foreach($conversionRates as $key => $rate)
            <div>
                <div class="flex justify-between text-xs font-semibold text-cc-muted mb-1">
                    <span>{{ ucfirst(str_replace('_',' → ',str_replace('_to_',' to ',$key))) }}</span>
                    <span class="text-secondary font-bold">{{ $rate }}%</span>
                </div>
                <div class="w-full bg-cc-card rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-[var(--color-secondary)] to-secondary h-full rounded-full" style="width: {{ min($rate,100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';
    
    const ctx = document.getElementById('chart-pipeline-stages');
    if (!ctx) return;

    const labels = [
        'Call Meeting',
        'Prospecting',
        'Proposal',
        'Negotiation',
        'Won',
        'Lost'
    ];
    
    const countsData = [
        {{ $counts['call_meeting'] ?? 0 }},
        {{ $counts['prospecting'] ?? 0 }},
        {{ $counts['proposal'] ?? 0 }},
        {{ $counts['negotiation'] ?? 0 }},
        {{ $counts['won'] ?? 0 }},
        {{ $counts['lost'] ?? 0 }}
    ];

    const valuesData = [
        {{ round(($stageData['call_meeting']->total_value ?? 0) / 1000000, 1) }},
        {{ round(($stageData['prospecting']->total_value ?? 0) / 1000000, 1) }},
        {{ round(($stageData['proposal']->total_value ?? 0) / 1000000, 1) }},
        {{ round(($stageData['negotiation']->total_value ?? 0) / 1000000, 1) }},
        {{ round(($stageData['won']->total_value ?? 0) / 1000000, 1) }},
        {{ round(($stageData['lost']->total_value ?? 0) / 1000000, 1) }}
    ];

    const backgroundColors = [
        'rgba(167, 139, 250, 0.62)',
        'rgba(99, 102, 241, 0.62)',
        'rgba(59, 130, 246, 0.62)',
        'rgba(245, 158, 11, 0.62)',
        'rgba(16, 185, 129, 0.62)',
        'rgba(239, 68, 68, 0.62)'
    ];

    const borderColors = [
        'rgba(167, 139, 250, 0.9)',
        'rgba(99, 102, 241, 0.9)',
        'rgba(59, 130, 246, 0.9)',
        'rgba(245, 158, 11, 0.9)',
        'rgba(16, 185, 129, 0.9)',
        'rgba(239, 68, 68, 0.9)'
    ];

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah Deal',
                    data: countsData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Nilai Deal (Jt Rp)',
                    data: valuesData,
                    type: 'line',
                    borderColor: 'rgba(255,255,255,0.4)',
                    backgroundColor: 'rgba(255,255,255,0.05)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 4,
                    yAxisID: 'y2'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: tc() }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,15,28,0.95)',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#94a3b8',
                    bodyColor: '#e2e8f0',
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 1) {
                                label += 'Rp ' + context.raw + ' Jt';
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gc(), drawBorder: false },
                    ticks: { color: tc(), font: { size: 11, weight: '600' } }
                },
                y: {
                    position: 'left',
                    grid: { color: gc(), drawBorder: false },
                    ticks: { color: tc() },
                    title: { display: true, text: 'Jumlah Deal', color: tc() }
                },
                y2: {
                    position: 'right',
                    grid: { display: false },
                    ticks: { color: tc(), callback: v => v + ' Jt' },
                    title: { display: true, text: 'Nilai Deal (Juta Rp)', color: tc() }
                }
            },
            onClick: (evt, activeElements) => {
                if (activeElements.length > 0) {
                    const idx = activeElements[0].index;
                    const stageMap = {
                        'Call Meeting': 'call_meeting',
                        'Prospecting': 'prospecting',
                        'Proposal': 'proposal',
                        'Negotiation': 'negotiation',
                        'Won': 'won',
                        'Lost': 'lost'
                    };
                    const clickedLabel = labels[idx];
                    const stageKey = stageMap[clickedLabel];
                    if (stageKey) {
                        window.location.href = `/opportunities?stage=${stageKey}`;
                    }
                }
            }
        }
    });

    // Handle theme change trigger
    new MutationObserver(() => {
        chart.options.scales.x.grid.color = gc();
        chart.options.scales.x.ticks.color = tc();
        chart.options.scales.y.grid.color = gc();
        chart.options.scales.y.ticks.color = tc();
        chart.options.scales.y2.ticks.color = tc();
        chart.update();
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
@endsection
