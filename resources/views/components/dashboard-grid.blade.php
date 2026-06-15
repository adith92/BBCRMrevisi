{{--
    Reusable GridStack Dashboard Component
    Usage: <x-dashboard-grid :saved-layout="$savedLayout ?? null">
               <div class="grid-stack-item" gs-id="widget-name" gs-w="6" gs-h="4">
                   <div class="grid-stack-item-content">...</div>
               </div>
           </x-dashboard-grid>
--}}
@props(['savedLayout' => null])

<div x-data="dashboardGrid()" x-init="init()" class="relative">

    {{-- Toolbar --}}
    <div class="flex items-center justify-end gap-2 mb-4">
        <button @click="toggleEdit()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200"
                :class="editing
                    ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30'
                    : 'bg-[var(--cc-surface)] text-[var(--cc-text-muted)] border border-[var(--cc-border)] hover:border-[var(--cc-border-h)]'"
                :title="editing ? 'Lock Layout' : 'Edit Layout'">
            <span class="material-symbols-outlined text-[16px]" x-text="editing ? 'lock_open' : 'tune'"></span>
            <span x-text="editing ? '🔓 Editing — Click to Lock' : '🔧 Customize'"></span>
        </button>
        <button x-show="editing" @click="resetLayout()"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500/20 transition-all duration-200">
            <span class="material-symbols-outlined text-[14px]">restart_alt</span>
            Reset
        </button>
    </div>

    {{-- Editing indicator bar --}}
    <div x-show="editing" x-transition
         class="mb-3 px-4 py-2 rounded-lg border border-dashed border-amber-500/40 bg-amber-500/5 flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400">
        <span class="material-symbols-outlined text-[16px] animate-pulse">drag_indicator</span>
        <span>Drag, resize, atau susun ulang widget di bawah. Klik <strong>Lock</strong> untuk menyimpan posisi.</span>
    </div>

    {{-- Grid Container --}}
    <div id="dashboard-grid" class="grid-stack">
        {{ $slot }}
    </div>
</div>

@push('styles')
<style>
    /* GridStack custom styling */
    .grid-stack {
        min-height: 200px;
    }
    .grid-stack-item-content {
        border-radius: 12px;
        overflow: hidden;
    }
    .grid-stack > .grid-stack-item > .grid-stack-item-content {
        inset: 4px;
    }
    /* When editing, show drag handles */
    .gs-editing .grid-stack-item-content {
        outline: 2px dashed var(--cc-border-h);
        outline-offset: 2px;
        cursor: grab;
    }
    .gs-editing .grid-stack-item-content:active {
        cursor: grabbing;
    }
    /* Placeholder styling */
    .grid-stack-placeholder > .placeholder-content {
        background: var(--cc-accent-dim) !important;
        border: 2px dashed var(--cc-accent) !important;
        border-radius: 12px !important;
        opacity: 0.5;
    }
    /* Resize handle styling */
    .grid-stack > .grid-stack-item > .ui-resizable-se {
        width: 20px !important;
        height: 20px !important;
        background: var(--cc-accent);
        border-radius: 0 0 8px 0;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .gs-editing .grid-stack > .grid-stack-item > .ui-resizable-se,
    .gs-editing .grid-stack-item:hover > .ui-resizable-se {
        opacity: 0.7 !important;
    }
    /* Responsive: on small screens, force single column */
    @media (max-width: 767px) {
        .grid-stack {
            --gs-columns: 1 !important;
        }
        .grid-stack > .grid-stack-item {
            width: 100% !important;
            position: relative !important;
            left: 0 !important;
        }
    }
    /* Tablet: 2-column layout */
    @media (min-width: 768px) and (max-width: 1023px) {
        .grid-stack > .grid-stack-item {
            min-width: 50% !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function dashboardGrid() {
    return {
        grid: null,
        editing: false,
        saveTimer: null,
        savedLayout: @json($savedLayout),

        init() {
            this.$nextTick(() => {
                this.grid = GridStack.init({
                    column: 12,
                    cellHeight: 70,
                    margin: 6,
                    float: false,
                    animate: true,
                    staticGrid: true,
                    removable: false,
                    columnOpts: {
                        breakpoints: [
                            { w: 768, c: 1 },   // Mobile: 1 kolom
                            { w: 1024, c: 6 },  // Tablet: 6 kolom
                            { w: 1200, c: 12 }  // Desktop: 12 kolom
                        ]
                    }
                }, '#dashboard-grid');

                // Auto-save on drag/resize (only when editing)
                this.grid.on('change', () => {
                    if (this.editing) {
                        this.debouncedSave();
                    }
                });

                // Restore saved layout if available
                if (this.savedLayout && Array.isArray(this.savedLayout)) {
                    this.restoreLayout(this.savedLayout);
                }
            });
        },

        toggleEdit() {
            this.editing = !this.editing;
            const el = document.getElementById('dashboard-grid');

            if (this.editing) {
                this.grid.setStatic(false);
                el.closest('.relative').classList.add('gs-editing');
            } else {
                this.grid.setStatic(true);
                el.closest('.relative').classList.remove('gs-editing');
                this.saveLayout();
            }
        },

        debouncedSave() {
            if (this.saveTimer) clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => {
                this.saveLayout();
            }, 1500);
        },

        saveLayout() {
            if (this.grid.getColumn() !== 12) {
                console.log('Skipping layout save in mobile view');
                return;
            }

            const items = this.grid.getGridItems().map(el => {
                const node = el.gridstackNode;
                return {
                    id: el.getAttribute('gs-id') || el.id,
                    x: node.x, y: node.y,
                    w: node.w, h: node.h,
                };
            });

            fetch('{{ route("dashboard.saveLayout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ layout: items }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    CRM_Toast.show('✅ Layout tersimpan!', 'success', 1200);
                }
            })
            .catch(() => {
                CRM_Toast.show('⚠️ Gagal menyimpan layout', 'error', 3000);
            });
        },

        restoreLayout(layout) {
            if (!this.grid || !layout) return;
            layout.forEach(item => {
                const el = document.querySelector(`[gs-id="${item.id}"]`) || document.getElementById(item.id);
                if (el) {
                    this.grid.update(el, { x: item.x, y: item.y, w: item.w, h: item.h });
                } else {
                    console.warn(`[GridStack] Widget "${item.id}" tidak ditemukan, skip.`);
                }
            });
        },

        resetLayout() {
            if (!confirm('Reset layout ke posisi default?')) return;
            fetch('{{ route("dashboard.saveLayout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ layout: null }),
            })
            .then(() => {
                CRM_Toast.show('🔄 Layout direset. Memuat ulang...', 'info', 1500);
                setTimeout(() => window.location.reload(), 1200);
            });
        },
    };
}
</script>
@endpush
