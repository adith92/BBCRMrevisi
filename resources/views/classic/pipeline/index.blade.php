@extends('layouts.app')
@php
    $rp = fn($n) => ($n>=1e9?'Rp '.rtrim(rtrim(number_format($n/1e9,2,',','.'),'0'),',').' M':($n>=1e6?'Rp '.rtrim(rtrim(number_format($n/1e6,1,',','.'),'0'),',').' Jt':'Rp '.number_format((float)$n,0,',','.')));
    $stageLabel = ['call_meeting'=>'Call/Meeting','prospecting'=>'Prospecting','proposal'=>'Proposal','negotiation'=>'Negotiation','won'=>'Won','lost'=>'Lost'];
    $stageTone  = ['call_meeting'=>'blue','prospecting'=>'amber','proposal'=>'blue','negotiation'=>'violet','won'=>'emerald','lost'=>'rose'];
    $stageAccent = ['call_meeting'=>'#3b82f6','prospecting'=>'#f59e0b','proposal'=>'#6366f1','negotiation'=>'#8b5cf6','won'=>'#10b981','lost'=>'#ef4444'];
@endphp

@push('styles')
<style>
.bb-kanban-shell { display:flex; flex-direction:column; height:calc(100vh - 130px); }
.bb-kanban-filters { flex-shrink:0; }
.bb-kanban-board { display:flex; gap:12px; overflow-x:auto; overflow-y:hidden; flex:1; padding-bottom:12px; }
.bb-kanban-col {
    flex:0 0 260px; display:flex; flex-direction:column;
    background:var(--surface-2); border-radius:10px; border:1px solid var(--border);
}
.bb-kanban-col-head {
    padding:12px 14px 10px; border-bottom:1px solid var(--border); flex-shrink:0;
    display:flex; align-items:center; justify-content:space-between;
}
.bb-kanban-col-accent { width:3px; border-radius:2px; height:18px; flex-shrink:0; margin-right:8px; }
.bb-kanban-cards { overflow-y:auto; flex:1; padding:10px; display:flex; flex-direction:column; gap:8px; }
.bb-kanban-card {
    background:var(--surface-1); border:1px solid var(--border); border-radius:8px;
    padding:12px 14px; cursor:pointer; transition:box-shadow .15s, transform .1s;
}
.bb-kanban-card:hover { box-shadow:0 2px 12px rgba(0,0,0,.12); transform:translateY(-1px); }
.bb-kanban-card.dragging { opacity:.5; transform:rotate(2deg); }
.bb-kanban-col.drag-over { outline:2px dashed var(--bb-accent); outline-offset:2px; }
.bb-kanban-empty { padding:20px; text-align:center; color:var(--text-faint); font-size:12px; }
.bb-kanban-val { font-size:11px; font-weight:700; color:var(--text-strong); font-variant-numeric:tabular-nums; }
.bb-kanban-col-sum { font-size:11px; color:var(--text-muted); font-variant-numeric:tabular-nums; }
</style>
@endpush

@section('content')

<div class="bb-kanban-shell" x-data="pipelineBoard()">

{{-- Page Head --}}
<div class="bb-page-head" style="margin-bottom:14px;">
    <div>
        <div class="bb-eyebrow">Penjualan</div>
        <h1 class="bb-display" style="margin-top:6px;">Pipeline Board</h1>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('opportunities.index') }}" class="bb-btn bb-btn-secondary">
            <span class="material-symbols-outlined" style="font-size:18px;">table_rows</span>Tabel
        </a>
        @if(in_array(Auth::user()->role ?? '', ['gm','manager','sales']))
        <button class="bb-btn bb-btn-primary" @click="showCreate=true">
            <span class="material-symbols-outlined" style="font-size:18px;">add</span>Opportunity
        </button>
        @endif
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bb-card bb-kanban-filters" style="padding:12px 16px;margin-bottom:12px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
    @if(!empty($salesUsers) && $salesUsers->count())
    <div>
        <label class="bb-field-label">Sales</label>
        <select name="filter_sales" class="bb-select" style="width:160px;">
            <option value="">Semua Sales</option>
            @foreach($salesUsers as $s)
            <option value="{{ $s->id }}" @selected(request('filter_sales')==(string)$s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div>
        <label class="bb-field-label">Bulan</label>
        <select name="filter_month" class="bb-select" style="width:140px;">
            <option value="all" @selected($filterMonth==='all')>Semua</option>
            <option value="previous" @selected($filterMonth==='previous')>Bulan Lalu</option>
            <option value="current" @selected($filterMonth==='current')>Bulan Ini</option>
            <option value="next" @selected($filterMonth==='next')>Bulan Depan</option>
        </select>
    </div>
    <div>
        <label class="bb-field-label">Urutkan</label>
        <select name="sort_by" class="bb-select" style="width:150px;">
            @foreach(['updated'=>'Terbaru diupdate','newest'=>'Terbaru dibuat','value_desc'=>'Nilai tertinggi','value_asc'=>'Nilai terendah','close_date'=>'Close date'] as $v=>$l)
            <option value="{{ $v }}" @selected($sortBy===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bb-btn bb-btn-primary" style="height:40px;"><span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>Terapkan</button>
</form>

{{-- Kanban Board --}}
<div class="bb-kanban-board">
@foreach($stages as $stage)
@php
    $colItems = $opportunities->getCollection()->where('stage', $stage)->values();
    $colCount = $colItems->count();
    $colVal = $colItems->sum(fn($o) => (float)($o->estimated_value ?? 0));
    $accent = $stageAccent[$stage] ?? '#6b7280';
@endphp
<div class="bb-kanban-col"
     x-data="{ dragOver: false }"
     @dragover.prevent="dragOver=true"
     @dragleave="dragOver=false"
     @drop.prevent="drop($event, '{{ $stage }}'); dragOver=false"
     :class="{ 'drag-over': dragOver }">

    <div class="bb-kanban-col-head">
        <div style="display:flex;align-items:center;gap:0;">
            <div class="bb-kanban-col-accent" style="background:{{ $accent }};"></div>
            <span style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ $stageLabel[$stage] ?? ucfirst($stage) }}</span>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;">
            <span style="font-size:13px;font-weight:700;color:var(--text-strong);">{{ $colCount }}</span>
            <span class="bb-kanban-col-sum">{{ $rp($colVal) }}</span>
        </div>
    </div>

    <div class="bb-kanban-cards" id="col-{{ $stage }}">
        @forelse($colItems as $opp)
        <div class="bb-kanban-card"
             draggable="true"
             data-id="{{ $opp->id }}"
             data-stage="{{ $opp->stage }}"
             @dragstart="dragStart($event, {{ $opp->id }}, '{{ $opp->stage }}')"
             @dragend="dragEnd($event)"
             @click="openCard({{ $opp->id }})">
            <div style="font-weight:600;font-size:13px;color:var(--text-strong);margin-bottom:4px;line-height:1.35;">
                {{ $opp->title ?? ($opp->product->name ?? 'Opp #'.$opp->id) }}
            </div>
            <div class="bb-body-sm" style="color:var(--text-muted);margin-bottom:8px;">
                {{ $opp->client->company_name ?? '—' }}
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span class="bb-badge t-{{ $stageTone[$opp->stage] ?? 'slate' }}" style="font-size:10px;">{{ $stageLabel[$opp->stage] ?? $opp->stage }}</span>
                <span class="bb-kanban-val">{{ $rp($opp->estimated_value ?? 0) }}</span>
            </div>
            @if($opp->sales)
            <div class="bb-body-sm" style="color:var(--text-faint);margin-top:6px;border-top:1px solid var(--border);padding-top:6px;">
                <span class="material-symbols-outlined" style="font-size:13px;vertical-align:middle;">person</span>
                {{ $opp->sales->name }}
            </div>
            @endif
        </div>
        @empty
        <div class="bb-kanban-empty">
            <span class="material-symbols-outlined" style="font-size:24px;display:block;margin-bottom:4px;opacity:.4;">inbox</span>
            Kosong
        </div>
        @endforelse
    </div>
</div>
@endforeach
</div>

</div>{{-- end kanban-shell --}}

@push('scripts')
<script>
function pipelineBoard() {
    return {
        showCreate: false,
        draggingId: null,
        draggingStage: null,

        dragStart(e, id, stage) {
            this.draggingId = id;
            this.draggingStage = stage;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        },
        dragEnd(e) {
            e.target.classList.remove('dragging');
        },
        async drop(e, targetStage) {
            if (!this.draggingId || targetStage === this.draggingStage) return;
            const id = this.draggingId;
            const fromStage = this.draggingStage;
            this.draggingId = null;
            this.draggingStage = null;

            try {
                const res = await fetch(`/opportunities/${id}/move-stage`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ stage: targetStage }),
                });
                if (res.ok) {
                    window.location.reload();
                } else {
                    alert('Gagal memindahkan opportunity. Coba lagi.');
                }
            } catch(err) {
                console.error(err);
            }
        },
        openCard(id) {
            window.location.href = `/opportunities/${id}`;
        },
    };
}
</script>
@endpush

@endsection
