# 🚌 BLUEBIRD CRM — Command Center v7.5

> **Enterprise B2B Fleet Management & CRM System**  
> Award-worthy UX · Dark/Light Dual Theme · Full Keyboard Navigation · Real-time Charts

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat-square&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)](https://chartjs.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 TABLE OF CONTENTS

1. [🎯 Project Overview](#overview)
2. [✨ What's New in v7.5](#whats-new)
3. [🚀 Quick Start](#quick-start)
4. [🔑 Demo Accounts](#demo-accounts)
5. [🎨 UI/UX Features](#ui-ux)
6. [⌨️ Keyboard Shortcuts](#shortcuts)
7. [📊 Dashboard & Charts](#charts)
8. [🗂️ Kanban Pipeline](#kanban)
9. [🏗️ Architecture](#architecture)
10. [🐳 Docker / Deployment](#deployment)
11. [📁 File Structure](#file-structure)

---

## <a name="overview"></a>🎯 PROJECT OVERVIEW

**Bluebird CRM** adalah sistem CRM enterprise untuk manajemen armada B2B perusahaan transportasi (BigBird, GoldenBird, Cititrans, Executive). Dibangun di atas Laravel 12 dengan UX-first philosophy — setiap pixel dirancang untuk produktivitas maksimal tim sales.

### 📐 System Stats v7.5

| Kategori | Detail |
|---|---|
| 🏗️ Framework | Laravel 12 + PHP 8.2 |
| 💾 Database | SQLite (WAL mode, ~50k–1M rows) |
| 🎨 CSS System | CSS Custom Properties dual-theme |
| ⚡ JS Modules | 10 global CRM_* modules |
| 🖼️ Views | 25+ Blade components |
| 🧪 Seeder | Scale 1–20 (50k–1M rows) |
| 🚢 Deploy | Docker + Render.com ready |

---

## <a name="whats-new"></a>✨ WHAT'S NEW IN v7.5

### 🎨 Theme System — Dual Dark/Light

- **🌑 Dark Mode** (default): Deep space `#09090f` background, glass card effect `rgba(19,19,36,0.85)`
- **☀️ Light Mode**: Near-white `#f0f0fa` dengan frosted-glass sidebar, bukan cream/kuning
- **⚡ Instant toggle** — zero reload via CSS Custom Properties + JS `CRM_Theme`
- **💾 Persistent** — preference disimpan di localStorage

```
html.dark  → --cc-bg: #09090f  | --cc-card: rgba(19,19,36,0.85)
html.light → --cc-bg: #f0f0fa  | --cc-card: rgba(255,255,255,0.82)
```

### ⌨️ Command Palette ⌘K

- **Spotlight-style** overlay dengan fuzzy search
- Navigate ke semua halaman via keyboard
- Server search `/search/global?q=` + fallback client-side
- `⌘K` buka, `↑↓` navigasi, `Enter` execute, `Esc` tutup

### 🔔 Notification Center

- Bell icon dengan unread badge di topbar
- Slide-in drawer dari kanan
- Item dengan icon, waktu relatif, mark-as-read
- `CRM_Notif.toggle()` via JS

### 🎊 Konfetti Won!

- Pure CSS animation — **120 partikel** warna-warni
- Auto-trigger saat deal dipindah ke stage **Won**
- Self-cleanup via `animationend` event
- `CRM_Confetti.fire()` bisa dipanggil manual

### ➕ FAB Quick-Add Deal

- Floating Action Button ⊕ fixed bottom-right
- Modal Alpine.js dengan 4 field: title, client, value, close date
- Auto-focus pada title input saat buka
- Link "Full form →" ke halaman create lengkap
- Hanya tampil untuk role: director, gm, manager, sales

### 💚 Deal Health Score

- Visual badge per Kanban card: 💚 Sehat / 💛 Perlu Perhatian / ❤️ Bahaya
- Formula: `daysSince + (stageDays × 0.5)`
- `< 7` = green, `< 14` = yellow, `≥ 14` = red
- `CRM_Health.score()` via global JS

### 📊 7-Day KPI Charts

4 chart visual di dashboard:
1. **📈 Revenue & Deals** — Dual-axis: line (revenue) + bar (deals closed)
2. **🗂️ Pipeline Distribution** — Donut chart 5 stage + legend
3. **🏅 Sales Leaderboard** — Horizontal bar chart ranking tim sales
4. **📋 Activity Breakdown** — Doughnut + progress bar per tipe aktivitas

Semua chart **theme-aware** via `MutationObserver` → auto-rerender saat dark/light toggle.

### 📐 Multi-View Kanban Toggle

- **Board** 🗂️ (default), **List** 📋, **Table** 📊
- State disimpan di localStorage per user
- Smooth CSS transition saat switch view

### 🌐 Global Keyboard Shortcuts (12 total)

| Key | Aksi |
|---|---|
| `⌘K` | Command Palette |
| `⌘B` | Focus / Presentation Mode |
| `⌘D` | Toggle Dark/Light |
| `N` | New Opportunity |
| `E` | Inline Edit |
| `W` | Mark Won |
| `A` | Add Activity |
| `V` | 360° View |
| `F` | Filter Panel |
| `1-7` | Jump to Nav |
| `?` | Show Shortcuts Help |
| `Esc` | Close Modals |

### 🌱 Scalable Demo Seeder

```bash
DEMO_SCALE=1  php artisan db:seed   # ~50,000 rows
DEMO_SCALE=5  php artisan db:seed   # ~250,000 rows
DEMO_SCALE=20 php artisan db:seed   # ~1,000,000 rows
```

SQLite WAL mode aktif otomatis untuk performa optimal.

### 🖥️ SPA Shell Layout

- `html/body overflow:hidden` — hanya `#content-area` yang scroll
- Sidebar collapsible dengan animasi `cubic-bezier(0.16,1,0.3,1)`
- Focus Mode: sembunyikan sidebar untuk presentasi full-screen
- Topbar center search bar (desktop) + mobile-responsive

---

## <a name="quick-start"></a>🚀 QUICK START

```bash
# 1. Clone
git clone https://github.com/neochemical/golden-bird-crm.git
cd golden-bird-crm

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database + seed
touch database/database.sqlite
php artisan migrate
php artisan db:seed                    # default scale=1

# 5. Build assets
npm run build

# 6. Serve
php artisan serve
# → http://localhost:8000
```

---

## <a name="demo-accounts"></a>🔑 DEMO ACCOUNTS

| 👤 Role | 📧 Email | 🔐 Password | 🎯 Access |
|---|---|---|---|
| 👑 **Director** | director@bluebird.id | password | Full system + approval |
| 🏢 **GM** | gm@bluebird.id | password | Dashboard + reports + pipeline |
| 📊 **Manager** | manager@bluebird.id | password | Team management + KPI |
| 💼 **Sales** | sales@bluebird.id | password | Opportunities + activities |
| 🔍 **Finance** | finance@bluebird.id | password | Revenue + approvals |
| ⚙️ **Admin** | admin@bluebird.id | password | System settings |

---

## <a name="ui-ux"></a>🎨 UI/UX FEATURES

### 🎨 Design System

```css
/* Dark Mode (default) */
--cc-bg:         #09090f          /* Deep space */
--cc-card:       rgba(19,19,36,0.85)  /* Glass card */
--cc-sidebar:    rgba(14,14,26,0.92)  /* Sidebar */
--cc-accent:     #00e5ff          /* Cyan accent */
--cc-accent-2:   #7c3aed          /* Purple secondary */

/* Light Mode */
--cc-bg:         #f0f0fa          /* Near-white */
--cc-card:       rgba(255,255,255,0.82)
--cc-sidebar:    rgba(248,248,255,0.92)
```

### 💎 Glass Morphism Effect

Semua card menggunakan `backdrop-filter: blur(20px)` dengan border subtle untuk efek kaca premium.

### 🔤 Typography

- Font: **Inter** (Google Fonts) — weight 400–900
- Icons: **Material Symbols Outlined** (adjustable fill + weight)
- Emoji: Digunakan secara strategis sebagai visual cue

### 📱 Responsive Design

- Desktop: Sidebar + topbar + content
- Tablet: Sidebar collapsible
- Mobile: Sidebar overlay dengan hamburger menu

---

## <a name="shortcuts"></a>⌨️ KEYBOARD SHORTCUTS

Tekan `?` kapan saja untuk melihat overlay shortcuts. Semua shortcuts dihandle via global `CRM_Keys.init()`.

```
⌘K  → Command Palette (Spotlight-style search)
⌘B  → Focus/Presentation Mode (sembunyikan sidebar)
⌘D  → Toggle Dark ↔ Light mode
N   → New Opportunity (buka FAB quick-add)
E   → Inline Edit (deal yang sedang aktif)
W   → Mark Won (konfetti! 🎊)
A   → Add Activity Log
V   → 360° Client View
F   → Toggle Filter Panel
1-7 → Jump langsung ke menu navigasi
?   → Show/Hide Shortcuts Overlay
Esc → Close semua modal/overlay
```

---

## <a name="charts"></a>📊 DASHBOARD & CHARTS

### 📈 Revenue & Deals 7-Day Timeline

Dual-axis chart: line chart revenue + bar chart deals closed. Setiap hari ditampilkan dengan badge jumlah deal.

### 🗂️ Pipeline Distribution

Donut chart dengan 5 stage: Prospecting → Proposal → Negotiation → Won → Lost. Legend interaktif di kanan.

### 🏅 Sales Leaderboard

Horizontal bar chart ranking tim sales berdasarkan revenue bulan ini. Medal 🥇🥈🥉 untuk top 3.

### 📋 Activity Breakdown

Doughnut chart + progress bar per tipe: 📞 Call, 📧 Email, 🤝 Meeting, 📄 Proposal, 🔄 Follow-up.

### ⚡ Theme-Aware Charts

```javascript
// Auto-rerender saat dark/light toggle
const observer = new MutationObserver(() => {
    Object.values(Chart.instances).forEach(c => c.update());
});
observer.observe(document.documentElement, { attributes: true });
```

---

## <a name="kanban"></a>🗂️ KANBAN PIPELINE

### 🎯 Deal Health Score Per Card

```
💚 Sehat      → Risk score < 7   (aktif dalam 7 hari)
💛 Perhatian  → Risk score < 14  (aktif dalam 14 hari)
❤️ Bahaya     → Risk score ≥ 14  (sudah lama tidak ada aktivitas)
```

### 🖱️ Drag & Drop

SortableJS — drag antar kolom stage. Saat drop ke **Won** → konfetti otomatis! 🎊

### 📐 Multi-View Toggle

```
[🗂️ Board] [📋 List] [📊 Table]
```

State tersimpan di localStorage, persist antar session.

### 🔗 Clickable Data

- Client name → Client 360° view
- Deal title → Deal detail
- Stage badge → Filter by stage
- Health badge → Filter by health status

---

## <a name="architecture"></a>🏗️ ARCHITECTURE

### 🧩 JS Module System

```javascript
window.CRM_Theme    // Dark/light toggle + persistence
window.CRM_Focus    // Presentation/focus mode
window.CRM_Toast    // Toast notifications (info/success/error/warning)
window.CRM_Palette  // Command palette ⌘K
window.CRM_Notif    // Notification center drawer
window.CRM_Confetti // Konfetti animation (120 particles)
window.CRM_Keys     // Global keyboard shortcuts
window.CRM_Sparkline// Mini sparkline charts (canvas)
window.CRM_Health   // Deal health score calculator
```

### 📁 Key Files

```
resources/
├── css/app.css                         ← CSS custom properties + all components
├── js/app.js                           ← 10 CRM_* global modules
└── views/
    ├── layouts/app.blade.php           ← SPA shell (sidebar + topbar + FAB)
    ├── components/
    │   ├── topbar.blade.php            ← Search + notif + theme toggle
    │   ├── sidebar.blade.php           ← Navigation + role badge
    │   ├── fab.blade.php               ← Floating action button
    │   └── flash.blade.php             ← Flash messages
    ├── dashboard/
    │   ├── gm.blade.php                ← GM dashboard + charts
    │   ├── director.blade.php          ← Director dashboard
    │   ├── manager.blade.php           ← Manager dashboard
    │   ├── sales.blade.php             ← Sales rep dashboard
    │   └── charts.blade.php            ← Chart.js partials (reusable)
    └── pipeline/
        └── index.blade.php             ← Kanban board + health score
```

### 🗄️ Database Models

```
User → roles: director, gm, manager, sales, finance, admin
Client → company_name, industry, status, tier
Opportunity → stage (prospecting→won/lost), estimated_value, health
ActivityLog → type (call/email/meeting/proposal/followup)
SalesTarget → monthly targets per user
Product → fleet types (bus charter, airport transfer, etc.)
ApprovalRequest → multi-level approval workflow
Subscription → recurring fleet contracts
```

---

## <a name="deployment"></a>🐳 DOCKER / DEPLOYMENT

### 🚀 Render.com (Recommended)

```bash
# render.yaml sudah dikonfigurasi
# Push ke GitHub → auto-deploy via Render
git push origin main
```

### 🐳 Docker

```bash
# Build
docker build -t bluebird-crm .

# Run
docker run -p 8000:8000 \
  -e APP_KEY=base64:xxx \
  -e DEMO_SCALE=1 \
  bluebird-crm
```

### ⚙️ Environment Variables

```env
APP_NAME="Bluebird CRM"
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://your-domain.com

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

DEMO_SCALE=1          # 1=50k rows, 5=250k rows, 20=1M rows
```

---

## <a name="file-structure"></a>📁 FILE STRUCTURE

```
golden-bird-crm/
├── 🐳 Dockerfile                      ← Multi-stage production build
├── ⚙️ render.yaml                      ← Render.com deployment config
├── 📦 nixpacks.toml                    ← Nixpacks build config
├── 🔧 CLAUDE.md                        ← Claude Code instructions
├── 🛠️ DEPLOYMENT.md                    ← Deployment guide
├── 📜 scripts/
│   └── init.sh                        ← Container init script
├── app/
│   ├── Http/Controllers/              ← DashboardController, OpportunityController, etc.
│   └── Models/                        ← User, Client, Opportunity, ActivityLog, etc.
├── database/
│   ├── migrations/                    ← 11 tables
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── DemoMassiveSeeder.php      ← Scalable seeder (50k–1M rows)
├── resources/
│   ├── css/app.css                    ← CSS Custom Properties dual-theme system
│   ├── js/app.js                      ← 10 CRM_* global JS modules
│   └── views/                         ← 25+ Blade components
├── routes/web.php                     ← All routes
└── vite.config.js                     ← Asset bundling
```

---

## 🏆 CHANGELOG

### v7.5 — UX Command Center Overhaul (Juni 2026)

**🎨 Theme & Design**
- ✅ Dual theme system: Dark (deep space) + Light (near-white glass)
- ✅ CSS Custom Properties — semua warna via variabel, zero hardcode
- ✅ Glass morphism effect pada semua card (`backdrop-filter: blur`)
- ✅ Smooth theme toggle tanpa reload halaman
- ✅ Near-white light mode `#f0f0fa` — bukan cream/kuning

**⌨️ Keyboard & Navigation**
- ✅ Command Palette ⌘K — Spotlight-style search
- ✅ 12 global keyboard shortcuts via `CRM_Keys`
- ✅ Shortcuts help overlay (tekan `?`)
- ✅ Focus/Presentation Mode ⌘B

**📊 Charts & Visualization**
- ✅ 7-day Revenue & Deals dual-axis chart
- ✅ Pipeline Distribution donut chart
- ✅ Sales Leaderboard horizontal bar
- ✅ Activity Breakdown doughnut + progress bars
- ✅ Theme-aware Chart.js (auto re-render on toggle)
- ✅ Day-by-day timeline labels dengan deal count badges

**🗂️ Kanban Pipeline**
- ✅ Deal Health Score visual badge per card (💚💛❤️)
- ✅ Konfetti animation saat Won (120 partikel pure CSS)
- ✅ Multi-view toggle: Board / List / Table
- ✅ Client name clickable → 360° view
- ✅ `CRM_Health.score()` global calculator

**🔔 Notifications & Feedback**
- ✅ Notification center bell drawer
- ✅ Toast notifications 4 tipe (info/success/error/warning)
- ✅ FAB Quick-Add Deal dengan Alpine.js modal

**🏗️ Architecture & Performance**
- ✅ SPA Shell Layout — hanya content-area yang scroll
- ✅ Sidebar collapsible dengan smooth CSS animation
- ✅ Scalable demo seeder `DEMO_SCALE` 1–20
- ✅ SQLite WAL mode untuk performa bulk insert
- ✅ Chunked seeder (200–500 rows/batch) — no PHP OOM

### v6.0 — Foundation

- Initial Laravel 12 setup
- Role-based authentication (6 roles)
- Kanban drag & drop (SortableJS)
- Basic dashboard per role
- Docker + Render deployment

---

## 👨‍💻 DEVELOPMENT

```bash
# Development server
npm run dev
php artisan serve

# Re-seed database
php artisan migrate:fresh --seed

# Scale up demo data
DEMO_SCALE=5 php artisan migrate:fresh --seed

# Build for production
npm run build
```

---

## 📄 LICENSE

MIT License — Free to use, modify, and distribute.

---

<div align="center">

**🚌 Bluebird CRM v7.5** — Built with ❤️ for Indonesia's fleet industry

[![Made with Laravel](https://img.shields.io/badge/Made%20with-Laravel-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Powered by Alpine.js](https://img.shields.io/badge/Powered%20by-Alpine.js-8BC0D0?style=flat-square)](https://alpinejs.dev)

</div>
