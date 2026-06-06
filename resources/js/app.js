import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Chart = Chart;

/* ════════════════════════════════════════════════════════════
   1. DARK / LIGHT MODE
   ════════════════════════════════════════════════════════════ */
window.CRM_Theme = {
    init() {
        const saved = localStorage.getItem('crm-theme') || 'dark';
        this.apply(saved);
    },
    apply(mode) {
        const html = document.documentElement;
        html.classList.remove('dark', 'light');
        html.classList.add(mode);
        localStorage.setItem('crm-theme', mode);
        // update toggle icon
        const icon = document.getElementById('theme-icon');
        if (icon) icon.textContent = mode === 'dark' ? '☀️' : '🌙';
        const label = document.getElementById('theme-label');
        if (label) label.textContent = mode === 'dark' ? 'Light' : 'Dark';
    },
    toggle() {
        const current = document.documentElement.classList.contains('light') ? 'light' : 'dark';
        this.apply(current === 'dark' ? 'light' : 'dark');
    },
};

/* ════════════════════════════════════════════════════════════
   2. FOCUS / PRESENTATION MODE
   ════════════════════════════════════════════════════════════ */
window.CRM_Focus = {
    active: false,
    toggle() {
        this.active = !this.active;
        const sb = document.getElementById('sidebar');
        if (sb) sb.classList.toggle('collapsed', this.active);
        const btn = document.getElementById('focus-icon');
        if (btn) btn.textContent = this.active ? '⛶' : '⛶';
        CRM_Toast.show(this.active ? '⛶ Presentation mode ON — sidebar hidden' : '↩ Normal mode restored');
    },
};

/* ════════════════════════════════════════════════════════════
   3. TOAST NOTIFICATIONS
   ════════════════════════════════════════════════════════════ */
window.CRM_Toast = {
    show(msg, type = 'info', duration = 3200) {
        let el = document.getElementById('crm-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'crm-toast';
            el.style.cssText = `
                position:fixed;bottom:28px;left:50%;transform:translateX(-50%);
                padding:11px 20px;border-radius:12px;font-size:13px;font-weight:600;
                z-index:9999;pointer-events:none;max-width:420px;text-align:center;
                backdrop-filter:blur(16px);transition:opacity 0.25s,transform 0.25s;
                opacity:0;transform:translateX(-50%) translateY(8px);
            `;
            document.body.appendChild(el);
        }
        const colors = {
            info:    'background:rgba(0,229,255,0.15);color:#00e5ff;border:1px solid rgba(0,229,255,0.3)',
            success: 'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)',
            error:   'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)',
            warning: 'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)',
        };
        el.style.cssText += ';' + (colors[type] || colors.info);
        el.textContent = msg;
        el.style.opacity = '1';
        el.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(el._t);
        el._t = setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-50%) translateY(8px)';
        }, duration);
    },
};

/* ════════════════════════════════════════════════════════════
   4. COMMAND PALETTE
   ════════════════════════════════════════════════════════════ */
window.CRM_Palette = {
    open: false,
    query: '',
    selected: 0,
    results: [],

    // Static nav items always available
    navItems: [
        { icon: '🏠', label: 'Dashboard',      sub: 'Go to dashboard',       url: '/dashboard' },
        { icon: '🗂️',  label: 'Sales Pipeline', sub: 'Kanban board',          url: '/pipeline' },
        { icon: '🏢',  label: 'Clients',        sub: 'Manage clients',        url: '/clients' },
        { icon: '📅',  label: 'Activity Log',   sub: 'Sales activities',      url: '/activities' },
        { icon: '✅',  label: 'Approval Queue', sub: 'Pending approvals',     url: '/approvals' },
        { icon: '🚌',  label: 'Fleet Armada',   sub: 'Vehicles & drivers',    url: '/fleet' },
        { icon: '🗺️',  label: 'Dispatch',       sub: 'Bookings & routes',     url: '/bookings' },
        { icon: '📊',  label: 'Analytics',      sub: 'Reports & charts',      url: '/analytics' },
        { icon: '🔄',  label: 'Subscriptions',  sub: 'Recurring contracts',   url: '/subscriptions' },
        { icon: '🎟️',  label: 'E-Voucher',      sub: 'Voucher management',    url: '/vouchers' },
        { icon: '⚙️',  label: 'Settings',       sub: 'App settings',          url: '/settings' },
        { icon: '🌙',  label: 'Toggle Dark/Light', sub: 'Switch theme',       action: 'theme' },
        { icon: '⛶',   label: 'Focus Mode',     sub: 'Presentation mode',     action: 'focus' },
        { icon: '➕',  label: 'New Opportunity', sub: 'Create deal',           action: 'new-opp' },
    ],

    show() {
        this.open = true;
        this.query = '';
        this.selected = 0;
        this.results = [...this.navItems];
        this._render();
        setTimeout(() => document.getElementById('cmd-input')?.focus(), 50);
    },

    hide() {
        this.open = false;
        const el = document.getElementById('crm-cmd-palette');
        if (el) el.remove();
    },

    async search(q) {
        this.query = q;
        this.selected = 0;
        if (!q.trim()) {
            this.results = [...this.navItems];
            this._render(); return;
        }
        const ql = q.toLowerCase();
        // Filter nav items
        const nav = this.navItems.filter(i =>
            i.label.toLowerCase().includes(ql) || i.sub.toLowerCase().includes(ql)
        );
        // Fetch live data from server
        try {
            const res = await fetch(`/search/global?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.results = [...nav, ...(data.results || [])];
        } catch {
            this.results = nav;
        }
        this._render();
    },

    execute(item) {
        this.hide();
        if (item.action === 'theme')   { CRM_Theme.toggle(); return; }
        if (item.action === 'focus')   { CRM_Focus.toggle(); return; }
        if (item.action === 'new-opp') { document.getElementById('fab-quick-add')?.click(); return; }
        if (item.url) window.location.href = item.url;
    },

    moveDown() { this.selected = Math.min(this.selected + 1, this.results.length - 1); this._highlight(); },
    moveUp()   { this.selected = Math.max(this.selected - 1, 0); this._highlight(); },
    confirm()  { if (this.results[this.selected]) this.execute(this.results[this.selected]); },

    _highlight() {
        document.querySelectorAll('.cmd-result-item').forEach((el, i) => {
            el.classList.toggle('selected', i === this.selected);
        });
    },

    _render() {
        const list = document.getElementById('cmd-list');
        if (!list) return;
        list.innerHTML = this.results.length
            ? this.results.map((r, i) => `
                <div class="cmd-result-item ${i === this.selected ? 'selected' : ''}"
                     onclick="CRM_Palette.execute(CRM_Palette.results[${i}])">
                    <span class="cmd-icon">${r.icon}</span>
                    <span class="cmd-label">${r.label}</span>
                    <span class="cmd-sub">${r.sub || ''}</span>
                </div>`).join('')
            : `<div style="padding:20px;text-align:center;color:var(--cc-text-faint);font-size:13px;">No results for "${this.query}"</div>`;
    },

    mount() {
        if (document.getElementById('crm-cmd-palette')) return;
        const el = document.createElement('div');
        el.id = 'crm-cmd-palette';
        el.className = 'cmd-palette-overlay';
        el.innerHTML = `
            <div class="cmd-palette-box">
                <div style="display:flex;align-items:center;padding:0 16px;border-bottom:1px solid var(--cc-border)">
                    <span style="font-size:18px;margin-right:8px;color:var(--cc-text-muted)">🔍</span>
                    <input id="cmd-input" class="cmd-palette-input"
                           placeholder="Search clients, deals, pages... (⌘K)"
                           autocomplete="off" />
                    <span class="kbd-hint" style="flex-shrink:0">ESC</span>
                </div>
                <div id="cmd-list" style="max-height:340px;overflow-y:auto;"></div>
                <div style="padding:8px 16px;border-top:1px solid var(--cc-border);display:flex;gap:12px;font-size:11px;color:var(--cc-text-faint)">
                    <span>↑↓ navigate</span><span>↵ open</span><span>ESC close</span>
                </div>
            </div>`;
        document.body.appendChild(el);
        this._render();

        const input = document.getElementById('cmd-input');
        input?.addEventListener('input', e => this.search(e.target.value));
        input?.addEventListener('keydown', e => {
            if (e.key === 'ArrowDown')  { e.preventDefault(); this.moveDown(); }
            if (e.key === 'ArrowUp')    { e.preventDefault(); this.moveUp(); }
            if (e.key === 'Enter')      { e.preventDefault(); this.confirm(); }
            if (e.key === 'Escape')     this.hide();
        });
        el.addEventListener('click', e => { if (e.target === el) this.hide(); });
    },

    toggle() {
        if (this.open) { this.hide(); } else { this.mount(); this.show(); }
    },
};

/* ════════════════════════════════════════════════════════════
   5. NOTIFICATION DRAWER
   ════════════════════════════════════════════════════════════ */
window.CRM_Notif = {
    open: false,
    items: [
        { icon: '🎉', title: 'Deal Won! PT Gojek', body: 'Rp 4,8M closed by Sari Dewi', time: '2m ago', type: 'won', url: '/pipeline' },
        { icon: '⏳', title: '2 Approvals Pending', body: 'PT Unilever 15% disc. — needs GM sign', time: '15m ago', type: 'approval', url: '/approvals' },
        { icon: '⚠️', title: 'Deal Aging Alert', body: 'PT BCA stuck in Proposal for 14 days', time: '1h ago', type: 'aging', url: '/pipeline' },
        { icon: '🚌', title: 'Fleet Alert', body: 'Bus BB-0023 maintenance due tomorrow', time: '2h ago', type: 'fleet', url: '/fleet' },
        { icon: '📅', title: 'Follow-up Due Today', body: '5 activities scheduled — Andi Pratama', time: '3h ago', type: 'activity', url: '/activities' },
        { icon: '💰', title: 'Invoice Overdue', body: 'INV-240315-0012 PT Astra — 7 days late', time: '5h ago', type: 'finance', url: '/finance' },
    ],
    unread: 4,

    toggle() {
        this.open = !this.open;
        const drawer = document.getElementById('notif-drawer');
        if (!drawer) { this._mount(); } else { drawer.classList.toggle('open', this.open); }
        if (this.open) { this.unread = 0; this._updateBadge(); }
    },

    _updateBadge() {
        const badge = document.getElementById('notif-badge');
        if (badge) {
            badge.textContent = this.unread > 0 ? this.unread : '';
            badge.style.display = this.unread > 0 ? 'flex' : 'none';
        }
    },

    _mount() {
        const el = document.createElement('div');
        el.id = 'notif-drawer';
        el.className = 'notif-drawer' + (this.open ? ' open' : '');
        el.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <div style="font-size:15px;font-weight:700;color:var(--cc-text)">🔔 Notifications</div>
                <button onclick="CRM_Notif.toggle()" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--cc-text-muted)">✕</button>
            </div>
            ${this.items.map(n => `
                <div class="notif-item" onclick="window.location='${n.url}';CRM_Notif.toggle()">
                    <span class="notif-icon">${n.icon}</span>
                    <div style="flex:1;min-width:0">
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-body">${n.body}</div>
                        <div class="notif-time">${n.time}</div>
                    </div>
                </div>`).join('')}
            <div style="margin-top:12px;text-align:center">
                <a href="/notifications" style="font-size:12px;color:var(--cc-accent);text-decoration:none">View all →</a>
            </div>`;
        document.body.appendChild(el);
        // backdrop click closes
        document.addEventListener('click', e => {
            if (this.open && !el.contains(e.target) && !e.target.closest('#notif-btn')) {
                this.open = false;
                el.classList.remove('open');
            }
        });
        setTimeout(() => el.classList.add('open'), 10);
    },
};

/* ════════════════════════════════════════════════════════════
   6. WIN CELEBRATION KONFETTI 🎊
   ════════════════════════════════════════════════════════════ */
window.CRM_Confetti = {
    fire() {
        const colors = ['#00e5ff','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#ffffff'];
        const count = 120;
        for (let i = 0; i < count; i++) {
            const el = document.createElement('div');
            const size = Math.random() * 10 + 6;
            el.style.cssText = `
                position:fixed;
                left:${Math.random() * 100}vw;
                top:-20px;
                width:${size}px;
                height:${size * (Math.random() > 0.5 ? 0.4 : 1)}px;
                background:${colors[Math.floor(Math.random() * colors.length)]};
                border-radius:${Math.random() > 0.5 ? '50%' : '2px'};
                z-index:99999;
                opacity:1;
                animation:confetti-fall ${1.5 + Math.random() * 2}s ease-in ${Math.random() * 0.8}s forwards;
                pointer-events:none;
            `;
            document.body.appendChild(el);
            el.addEventListener('animationend', () => el.remove());
        }
        CRM_Toast.show('🎊 DEAL WON! Congratulations! 🏆', 'success', 4000);
    },
};

/* ════════════════════════════════════════════════════════════
   7. GLOBAL KEYBOARD SHORTCUTS
   ════════════════════════════════════════════════════════════ */
window.CRM_Keys = {
    init() {
        document.addEventListener('keydown', e => {
            const tag = document.activeElement?.tagName?.toLowerCase();
            const typing = ['input','textarea','select'].includes(tag);

            // ⌘K or Ctrl+K — command palette
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                CRM_Palette.toggle();
                return;
            }
            // ⌘B — focus/presentation mode
            if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
                e.preventDefault();
                CRM_Focus.toggle();
                return;
            }
            // Escape — close any open overlay
            if (e.key === 'Escape') {
                CRM_Palette.hide();
                const nd = document.getElementById('notif-drawer');
                if (nd) { CRM_Notif.open = false; nd.classList.remove('open'); }
                return;
            }

            // Single key shortcuts — only when NOT typing in a field
            if (typing || e.metaKey || e.ctrlKey || e.altKey) return;

            switch (e.key) {
                case 'n': case 'N':
                    e.preventDefault();
                    document.getElementById('fab-quick-add')?.click();
                    break;
                case 'f': case 'F':
                    e.preventDefault();
                    document.querySelector('[data-filter-toggle]')?.click();
                    CRM_Toast.show('🔎 Filter panel', 'info');
                    break;
                case 'w': case 'W':
                    e.preventDefault();
                    document.querySelector('[data-mark-won]')?.click();
                    break;
                case 'e': case 'E':
                    e.preventDefault();
                    document.querySelector('[data-inline-edit]')?.click();
                    CRM_Toast.show('✏️ Inline edit mode', 'info');
                    break;
                case 'a': case 'A':
                    e.preventDefault();
                    document.querySelector('[data-add-activity]')?.click();
                    CRM_Toast.show('📅 Add activity', 'info');
                    break;
                case 'v': case 'V':
                    e.preventDefault();
                    document.querySelector('[data-view-360]')?.click();
                    break;
                // Number nav shortcuts 1–7
                case '1': window.location.href = '/dashboard';     break;
                case '2': window.location.href = '/pipeline';      break;
                case '3': window.location.href = '/clients';       break;
                case '4': window.location.href = '/bookings';      break;
                case '5': window.location.href = '/analytics';     break;
                case '6': window.location.href = '/fleet';         break;
                case '7': window.location.href = '/approvals';     break;
                case '?':
                    CRM_Toast.show('⌨️ Shortcuts: ⌘K=search  N=new  E=edit  W=won  A=activity  1-7=nav  ⌘B=focus', 'info', 5000);
                    break;
            }
        });
    },
};

/* ════════════════════════════════════════════════════════════
   8. DASHBOARD SPARKLINES (mini Chart.js per KPI)
   ════════════════════════════════════════════════════════════ */
window.CRM_Sparkline = {
    render(canvasId, data, color = '#00e5ff') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{ data, borderColor: color, borderWidth: 2, fill: true,
                    backgroundColor: color.replace(')', ',0.08)').replace('rgb','rgba'),
                    pointRadius: 0, tension: 0.4 }]
            },
            options: {
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                animation: { duration: 600 },
                responsive: true, maintainAspectRatio: false,
            }
        });
    },
};

/* ════════════════════════════════════════════════════════════
   9. DEAL HEALTH SCORE
   ════════════════════════════════════════════════════════════ */
window.CRM_Health = {
    score(daysSinceActivity, stageDurationDays) {
        const total = daysSinceActivity + stageDurationDays * 0.5;
        if (total < 7)  return { cls: 'health-green',  emoji: '💚', label: 'Healthy' };
        if (total < 14) return { cls: 'health-yellow', emoji: '💛', label: 'Warming' };
        return              { cls: 'health-red',    emoji: '❤️', label: 'At Risk' };
    },
};

/* ════════════════════════════════════════════════════════════
   10. KANBAN BOARD DRAG-SCROLL
   ════════════════════════════════════════════════════════════ */
window.initBoardDragScroll = function() {
    const board = document.getElementById('kanban-scroll-x');
    if (!board) return;
    let isDown = false, startX, scrollLeft;
    board.addEventListener('mousedown', e => {
        if (e.target.closest('.kanban-card')) return;
        isDown = true; startX = e.pageX - board.offsetLeft; scrollLeft = board.scrollLeft;
        board.style.cursor = 'grabbing';
    });
    document.addEventListener('mouseup', () => { isDown = false; board.style.cursor = 'grab'; });
    board.addEventListener('mouseleave', () => { isDown = false; board.style.cursor = 'grab'; });
    board.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - board.offsetLeft;
        board.scrollLeft = scrollLeft - (x - startX) * 1.5;
    });
};

/* ════════════════════════════════════════════════════════════
   INIT
   ════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    CRM_Theme.init();
    CRM_Keys.init();
    CRM_Notif._updateBadge();
});

Alpine.start();
