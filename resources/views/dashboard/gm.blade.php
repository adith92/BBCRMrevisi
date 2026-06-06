@extends('layouts.app')

@section('header_title', 'Command Center')

@push('styles')
<style>
.exec-summary-card {
    background: linear-gradient(135deg, rgba(19,19,36,0.9) 0%, rgba(15,15,28,0.95) 100%);
    border: 1px solid rgba(0,229,255,0.12);
    border-radius: 16px;
    position: relative;
    overflow: hidden;
}
.exec-summary-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(0,229,255,0.05) 0%, transparent 70%);
    pointer-events: none;
}
.fleet-bar {
    height: 6px;
    border-radius: 3px;
    background: rgba(255,255,255,0.06);
    overflow: hidden;
}
.fleet-bar-fill {
    height: 100%;
    border-radius: 3px;
}
.rank-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.rank-row:last-child { border-bottom: none; }
.rank-num {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    flex-shrink: 0;
}
.booking-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    transition: background 0.15s;
}
.booking-row:hover { background: rgba(255,255,255,0.03); }
.approval-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.approval-item:last-child { border-bottom: none; }
.priority-high { color: #ef4444; font-size: 10px; font-weight: 700; }
.priority-med { color: #f59e0b; font-size: 10px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- ===== COMMAND CENTER HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-black text-white tracking-tight">Bluebird CRM <span style="color:#00e5ff;">Command Center</span></h1>
            </div>
            <p class="text-xs" style="color:#475569;">Corporate Fleet · Sales Pipeline · Dispatch · Revenue Intelligence</p>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <span class="badge-demo">Live Demo</span>
                <span class="badge-demo">June 2026</span>
                <span class="badge-live flex items-center gap-1.5">
                    <span class="pulse-dot" style="width:5px;height:5px;"></span>
                    Director HQ
                </span>
                <span style="background:rgba(139,92,246,0.12);color:#a78bfa;border:1px solid rgba(139,92,246,0.2);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">API Ready</span>
                <span style="background:rgba(245,158,11,0.1);color:#fbbf24;border:1px solid rgba(245,158,11,0.2);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">Railway Deploy</span>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('approvals.index') }}" class="btn-primary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">fact_check</span>
                Approve Queue
            </a>
            <a href="{{ route('analytics.index') }}" class="btn-secondary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">query_stats</span>
                Reports
            </a>
        </div>
    </div>

    {{-- ===== KPI CARDS ROW ===== --}}
    <div id="widget-kpi-row" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

        {{-- KPI 1: Revenue --}}
        <div class="kpi-card kpi-cyan col-span-2 md:col-span-1 lg:col-span-1" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(0,229,255,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#00e5ff;">payments</span>
                </div>
                <span class="signal-up">▲ 18.4%</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">Rp 2,84 M</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Monthly Revenue</div>
            <canvas id="spark-revenue" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

        {{-- KPI 2: Bookings --}}
        <div class="kpi-card kpi-blue" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#60a5fa;">route</span>
                </div>
                <span class="signal-up">▲ 32</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">{{ $pendingDispatch ?? 248 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Active Bookings</div>
            <canvas id="spark-bookings" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

        {{-- KPI 3: Fleet --}}
        <div class="kpi-card kpi-emerald" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#34d399;">local_shipping</span>
                </div>
                <span class="signal-up">Healthy</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">{{ $availableVehicles ?? 72 }}%</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Fleet Utilization</div>
            <canvas id="spark-fleet" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

        {{-- KPI 4: Clients --}}
        <div class="kpi-card kpi-purple" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(139,92,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#a78bfa;">corporate_fare</span>
                </div>
                <span class="signal-up">▲ 12</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">128</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Corp. Clients</div>
            <canvas id="spark-clients" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

        {{-- KPI 5: Outstanding --}}
        <div class="kpi-card kpi-gold" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#fbbf24;">receipt_long</span>
                </div>
                <span class="signal-warn">Attention</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">Rp 420 Jt</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Outstanding Inv.</div>
            <canvas id="spark-invoice" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

        {{-- KPI 6: Approval --}}
        <div class="kpi-card kpi-red" style="position:relative;overflow:hidden;">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#f87171;">pending_actions</span>
                </div>
                <span class="signal-down">Urgent</span>
            </div>
            <div class="text-lg font-black text-white leading-tight">{{ $pendingPO ?? 14 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Pending Approval</div>
            <canvas id="spark-approvals" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </div>

    </div>

    {{-- ===== QUICK SHORTCUTS ===== --}}
    <div id="widget-quick-shortcuts">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-widest" style="color:#475569;">
                <span class="material-symbols-outlined text-[13px] align-middle mr-1" style="color:#0066ff;">bolt</span>
                Quick Shortcuts
            </h2>
            <span class="text-[10px]" style="color:#334155;">Akses cepat semua modul</span>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
            @php
            $shortcuts = [
                ['icon'=>'groups',         'label'=>'Clients',       'route'=>'clients.index',       'color'=>'#3385ff', 'bg'=>'rgba(0,82,204,0.12)'],
                ['icon'=>'local_shipping', 'label'=>'Pipeline',      'route'=>'pipeline.index',      'color'=>'#60a5fa', 'bg'=>'rgba(96,165,250,0.1)'],
                ['icon'=>'book_online',    'label'=>'Bookings',      'route'=>'bookings.index',      'color'=>'#34d399', 'bg'=>'rgba(52,211,153,0.1)'],
                ['icon'=>'directions_bus', 'label'=>'Fleet',         'route'=>'fleet.index',         'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,0.1)'],
                ['icon'=>'build',          'label'=>'Maintenance',   'route'=>'maintenance.index',   'color'=>'#f97316', 'bg'=>'rgba(249,115,22,0.1)'],
                ['icon'=>'receipt_long',   'label'=>'Finance',       'route'=>'finance.index',       'color'=>'#a78bfa', 'bg'=>'rgba(167,139,250,0.1)'],
                ['icon'=>'subscriptions',  'label'=>'Subscriptions', 'route'=>'subscriptions.index', 'color'=>'#38bdf8', 'bg'=>'rgba(56,189,248,0.1)'],
                ['icon'=>'redeem',         'label'=>'Vouchers',      'route'=>'vouchers.index',      'color'=>'#fb7185', 'bg'=>'rgba(251,113,133,0.1)'],
                ['icon'=>'query_stats',    'label'=>'Analytics',     'route'=>'analytics.index',     'color'=>'#00e5ff', 'bg'=>'rgba(0,229,255,0.08)'],
                ['icon'=>'fact_check',     'label'=>'Approvals',     'route'=>'approvals.index',     'color'=>'#4ade80', 'bg'=>'rgba(74,222,128,0.1)'],
                ['icon'=>'inventory_2',    'label'=>'Products',      'route'=>'products.index',      'color'=>'#e879f9', 'bg'=>'rgba(232,121,249,0.1)'],
                ['icon'=>'bar_chart',      'label'=>'KPI',           'route'=>'kpi.index',           'color'=>'#fde047', 'bg'=>'rgba(253,224,71,0.1)'],
                ['icon'=>'event_note',     'label'=>'Activities',    'route'=>'activities.index',    'color'=>'#94a3b8', 'bg'=>'rgba(148,163,184,0.08)'],
                ['icon'=>'description',    'label'=>'Opportunities', 'route'=>'opportunities.index', 'color'=>'#7dd3fc', 'bg'=>'rgba(125,211,252,0.1)'],
                ['icon'=>'contract',       'label'=>'V. Contracts',  'route'=>'vehicle-contracts.index', 'color'=>'#86efac', 'bg'=>'rgba(134,239,172,0.1)'],
                ['icon'=>'dashboard',      'label'=>'GM View',       'route'=>'dashboard.gm',        'color'=>'#c084fc', 'bg'=>'rgba(192,132,252,0.1)'],
            ];
            @endphp

            @foreach($shortcuts as $s)
            <a href="{{ route($s['route']) }}"
               class="group flex flex-col items-center gap-2 p-3 rounded-xl transition-all duration-150 hover:scale-105 active:scale-95"
               style="background:{{ $s['bg'] }}; border:1px solid {{ $s['color'] }}22;">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all group-hover:shadow-lg"
                     style="background:{{ $s['bg'] }}; border:1px solid {{ $s['color'] }}44;">
                    <span class="material-symbols-outlined text-[18px]" style="color:{{ $s['color'] }};">{{ $s['icon'] }}</span>
                </div>
                <span class="text-[10px] font-semibold text-center leading-tight" style="color:#94a3b8;">{{ $s['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ===== MAIN GRID: Executive + Fleet League ===== --}}
    <div id="widget-exec-summary" class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Executive Summary (2/3) --}}
        <div class="lg:col-span-2 exec-summary-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#00e5ff;">auto_awesome</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#00e5ff;">Executive Intelligence</span>
                </div>
                <span style="background:rgba(0,229,255,0.08);color:#00e5ff;border:1px solid rgba(0,229,255,0.15);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">AI Summary</span>
            </div>
            <h3 class="text-base font-bold text-white mb-3 leading-snug">
                Corporate fleet performance naik <span style="color:#10b981;">18.4%</span> bulan ini.
            </h3>
            <p class="text-sm leading-relaxed mb-4" style="color:#64748b;">
                Golden Bird menjadi kontributor revenue terbesar, didorong kontrak corporate dan airport executive transfer. Big Bird stabil dari charter perusahaan, sementara Cititrans membutuhkan peningkatan pipeline untuk rute bisnis. Finance perlu mempercepat follow-up outstanding invoice di atas 14 hari.
            </p>
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#334155;">Strategic Recommendations</div>
                @php
                $recs = [
                    ['icon'=>'group','color'=>'#00e5ff','text'=>'Prioritaskan 12 client corporate dengan potensi renewal'],
                    ['icon'=>'receipt_long','color'=>'#f59e0b','text'=>'Follow-up invoice overdue di atas 14 hari — Rp 420 Jt exposed'],
                    ['icon'=>'local_shipping','color'=>'#10b981','text'=>'Tambahkan fleet allocation untuk area Jakarta HQ'],
                    ['icon'=>'build','color'=>'#8b5cf6','text'=>'Percepat approval PO maintenance untuk unit high-demand'],
                    ['icon'=>'leaderboard','color'=>'#60a5fa','text'=>'Dorong sales terbaik untuk handle enterprise account'],
                ];
                @endphp
                @foreach($recs as $r)
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[14px] mt-0.5 flex-shrink-0" style="color:{{ $r['color'] }};">{{ $r['icon'] }}</span>
                    <span class="text-xs" style="color:#94a3b8;">{{ $r['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Fleet League (1/3) --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f59e0b;">emoji_events</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Fleet League</span>
                </div>
                <a href="{{ route('fleet.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">View All →</a>
            </div>
            @php
            $fleets = [
                ['name'=>'Golden Bird','pct'=>92,'color'=>'#f59e0b','badge'=>'High Performer','badgeColor'=>'rgba(245,158,11,0.12)','badgeText'=>'#fbbf24'],
                ['name'=>'Big Bird','pct'=>84,'color'=>'#10b981','badge'=>'Stable','badgeColor'=>'rgba(16,185,129,0.12)','badgeText'=>'#34d399'],
                ['name'=>'Cititrans','pct'=>78,'color'=>'#3b82f6','badge'=>'Needs Growth','badgeColor'=>'rgba(59,130,246,0.12)','badgeText'=>'#60a5fa'],
                ['name'=>'Exec. Transport','pct'=>73,'color'=>'#8b5cf6','badge'=>'Under Review','badgeColor'=>'rgba(139,92,246,0.12)','badgeText'=>'#a78bfa'],
            ];
            @endphp
            <div class="space-y-4">
                @foreach($fleets as $i => $f)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <div class="rank-num" style="background:rgba(255,255,255,0.05);color:#64748b;">{{ $i+1 }}</div>
                            <span class="text-xs font-semibold text-white">{{ $f['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span style="background:{{ $f['badgeColor'] }};color:{{ $f['badgeText'] }};font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;">{{ $f['badge'] }}</span>
                            <span class="text-xs font-bold text-white">{{ $f['pct'] }}%</span>
                        </div>
                    </div>
                    <div class="fleet-bar">
                        <div class="fleet-bar-fill" style="width:{{ $f['pct'] }}%;background:{{ $f['color'] }};opacity:0.8;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== BOTTOM GRID: Revenue Chart + Sales Ranking + Bookings + Approvals ===== --}}
    <div id="widget-revenue-chart" class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Revenue Chart (2/3) --}}
        <div class="lg:col-span-2 cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#3b82f6;">bar_chart</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Weekly Revenue Movement</span>
                </div>
                <span class="text-[10px] font-semibold" style="color:#475569;">Peak: Kamis — Corporate Airport Transfer</span>
            </div>
            <canvas id="revenueChart" height="180"></canvas>
        </div>

        {{-- Sales Ranking (1/3) --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#a78bfa;">military_tech</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Sales Ranking</span>
                </div>
                <a href="{{ route('kpi.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">KPI →</a>
            </div>
            @php
            $sellers = [
                ['name'=>'Andi Pratama','rev'=>'Rp 740 Jt','closing'=>'38%','medal'=>'🥇','color'=>'#f59e0b'],
                ['name'=>'Sari Dewi','rev'=>'Rp 615 Jt','closing'=>'34%','medal'=>'🥈','color'=>'#94a3b8'],
                ['name'=>'Reza Firmansyah','rev'=>'Rp 480 Jt','closing'=>'29%','medal'=>'🥉','color'=>'#cd7c2f'],
                ['name'=>'Maya Corp.','rev'=>'Rp 355 Jt','closing'=>'24%','medal'=>'4','color'=>'#475569'],
            ];
            @endphp
            <div class="space-y-1">
                @foreach($sellers as $s)
                <div class="rank-row">
                    <span class="text-base flex-shrink-0">{{ $s['medal'] }}</span>
                    <div class="flex-grow min-w-0">
                        <div class="text-xs font-semibold text-white truncate">{{ $s['name'] }}</div>
                        <div class="text-[10px]" style="color:#475569;">Closing {{ $s['closing'] }}</div>
                    </div>
                    <div class="text-xs font-bold flex-shrink-0" style="color:{{ $s['color'] }};">{{ $s['rev'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== BOTTOM ROW: Recent Bookings + Approval Queue ===== --}}
    <div id="widget-recent-books" class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Recent Bookings --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#10b981;">route</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Recent Bookings</span>
                </div>
                <a href="{{ route('bookings.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">View All →</a>
            </div>
            @php
            $bookings = [
                ['id'=>'GB-2026-0612','client'=>'Astra International','fleet'=>'Golden Bird','status'=>'confirmed','statusClass'=>'status-confirmed'],
                ['id'=>'BB-2026-0441','client'=>'Telkom Indonesia','fleet'=>'Big Bird','status'=>'On Trip','statusClass'=>'status-completed'],
                ['id'=>'CT-2026-0192','client'=>'Bank Mandiri','fleet'=>'Cititrans','status'=>'pending','statusClass'=>'status-pending'],
                ['id'=>'EX-2026-0088','client'=>'Pertamina','fleet'=>'Executive','status'=>'completed','statusClass'=>'status-completed'],
            ];
            @endphp
            <div class="space-y-1">
                @foreach($bookings as $b)
                <div class="booking-row">
                    <div class="flex-grow min-w-0">
                        <div class="text-xs font-bold text-white font-mono">{{ $b['id'] }}</div>
                        <div class="text-[10px]" style="color:#475569;">{{ $b['client'] }} — {{ $b['fleet'] }}</div>
                    </div>
                    <span class="status-badge {{ $b['statusClass'] }} flex-shrink-0">{{ $b['status'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Approval Queue --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f87171;">pending_actions</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Approval Queue</span>
                </div>
                <a href="{{ route('approvals.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">Approve →</a>
            </div>
            @php
            $approvals = [
                ['title'=>'Fleet Maintenance PO','dept'=>'Operational','priority'=>'High','icon'=>'build','iconColor'=>'#f87171'],
                ['title'=>'Corp. Contract Renewal','dept'=>'Sales','priority'=>'High','icon'=>'handshake','iconColor'=>'#f59e0b'],
                ['title'=>'Invoice Adjustment','dept'=>'Finance','priority'=>'Medium','icon'=>'receipt_long','iconColor'=>'#fbbf24'],
                ['title'=>'Enterprise Onboarding','dept'=>'Sales','priority'=>'Medium','icon'=>'person_add','iconColor'=>'#60a5fa'],
            ];
            @endphp
            <div class="space-y-1">
                @foreach($approvals as $a)
                <div class="approval-item">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.04);">
                        <span class="material-symbols-outlined text-[16px]" style="color:{{ $a['iconColor'] }};">{{ $a['icon'] }}</span>
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="text-xs font-semibold text-white truncate">{{ $a['title'] }}</div>
                        <div class="text-[10px]" style="color:#475569;">{{ $a['dept'] }}</div>
                    </div>
                    <span class="{{ $a['priority'] === 'High' ? 'priority-high' : 'priority-med' }} flex-shrink-0 uppercase tracking-wide">{{ $a['priority'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
    {{-- ════ CHARTS SECTION ════ --}}
    <div id="widget-charts-section">
        @include('dashboard.charts')
    </div>

</div>{{-- close outer space-y-5 --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue (Jt)',
                data: [320, 410, 285, 520, 475, 240, 190],
                backgroundColor: [
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                    'rgba(0,229,255,0.6)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.3)',
                    'rgba(59,130,246,0.3)',
                ],
                borderColor: [
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.8)',
                    'rgba(0,229,255,0.9)',
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                ],
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,15,28,0.95)',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#94a3b8',
                    bodyColor: '#00e5ff',
                    bodyFont: { weight: 'bold', size: 14 },
                    callbacks: {
                        label: ctx => `Rp ${ctx.raw} Jt`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: { color: '#475569', font: { size: 11, weight: '600' } }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: {
                        color: '#475569', font: { size: 11 },
                        callback: v => `${v} Jt`
                    }
                }
            }
        }
    });

    // ── KPI Sparklines ──
    const sparks = [
        { id: 'spark-revenue',   data: [210,240,285,310,295,340,380,395,420,440,460,484], color: '#00e5ff' },
        { id: 'spark-bookings',  data: [180,195,210,230,225,248,260,255,270,248,265,280], color: '#60a5fa' },
        { id: 'spark-fleet',     data: [65,68,70,72,69,71,74,72,75,73,72,74],            color: '#34d399' },
        { id: 'spark-clients',   data: [100,104,108,110,112,115,116,118,120,122,125,128], color: '#a78bfa' },
        { id: 'spark-invoice',   data: [280,310,340,360,395,420,410,430,440,420,415,420], color: '#fbbf24' },
        { id: 'spark-approvals', data: [8,10,12,9,11,14,12,15,13,14,16,14],              color: '#f87171' },
    ];
    sparks.forEach(s => {
        if (window.CRM_Sparkline) CRM_Sparkline.render(s.id, s.data, s.color);
    });
});
</script>
@endpush
