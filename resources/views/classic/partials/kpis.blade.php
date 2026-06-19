{{-- Classic KPI grid. Expects $kpis = [['eyebrow','value','icon','accent','delta','deltaUp'], ...] --}}
<div style="display:grid;grid-template-columns:repeat({{ $cols ?? min(count($kpis),6) }},1fr);gap:16px;margin-bottom:20px;" class="bb-kpi-grid">
    @foreach($kpis as $k)
    <div class="bb-kpi">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div class="bb-eyebrow" style="color:var(--text-muted);">{{ $k['eyebrow'] }}</div>
            <div class="bb-chip" style="background:var(--accent-{{ $k['accent'] }}-bg);color:var(--accent-{{ $k['accent'] }}-fg);">
                <span class="material-symbols-outlined" style="font-size:19px;">{{ $k['icon'] }}</span>
            </div>
        </div>
        <div class="bb-tnum" style="font-family:var(--font-brand);font-weight:700;font-size:24px;color:var(--text-strong);margin-top:12px;line-height:1.1;">{{ $k['value'] }}</div>
        @if(!empty($k['delta']))
        <div style="margin-top:8px;font-size:12px;font-weight:600;color:{{ ($k['deltaUp'] ?? true) ? 'var(--status-success-fg)' : 'var(--status-error-fg)' }};display:flex;align-items:center;gap:4px;">
            <span class="material-symbols-outlined" style="font-size:15px;">{{ ($k['deltaUp'] ?? true) ? 'trending_up' : 'trending_down' }}</span>{{ $k['delta'] }}
        </div>
        @endif
    </div>
    @endforeach
</div>
