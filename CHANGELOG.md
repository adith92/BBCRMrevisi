# CHANGELOG — Golden Bird CRM

All notable changes to this project are documented in this file.

---

## [v8.3-workstream] — 2026-06-18

### Features
- **Set Sales Targets (KPI)**: GM/Manager bisa set target bulanan per Sales Representative untuk 6 produk KPI: Mobil Short, Bis Short, E-Voucher, Mobil Long, Bis Long, dan Supir.
- **Manager Dashboard Range Trend**: Revenue Trend tim mendukung range Hari, Minggu, Bulan, 3 Bulan, dan 6 Bulan.
- **Manager KPI Cards**: KPI Tim disajikan sebagai kartu visual per sales dengan target, revenue, progress, status, win rate, dan won count.
- **Pipeline per Sales**: breakdown pipeline manager memakai nilai pipeline per stage, bukan hanya jumlah deal.

### Fixes
- **Sales Dashboard Funnel**: menambahkan konteks bahwa funnel adalah jumlah opportunity milik sales per stage.
- **Quick Add Sidebar**: aksi Tambah Baru dibatasi sesuai role agar tidak menampilkan menu yang tidak tersambung.

### Chores
- Tambah migration baru untuk target produk di `sales_targets`.
- Test baseline terbaru: **202 passed (547 assertions)**.

---

## [v8.2] — 2026-06-16 / 2026-06-17

### Features
- **Fulfillment Lifecycle matang**: status armada & supir mengikuti stage — Proposal/Negotiation → `reserved`, Won → `assigned`, lainnya → `available`; field `assigned_opportunity_id` di Vehicle & Driver
- **Carry-over Assignment**: kendaraan & supir yang dipilih di Proposal ikut terbawa ke Negotiation dan tetap tampil di riwayat (fix eager-loading: `refresh()->load()`)
- **Pool Scoping alokasi**: role Pool hanya bisa memilih kendaraan & supir dari `pool_id` sendiri di tabel Pending Assignments
- **Pending Assignments**: titik masuk operasional/Pool untuk memenuhi unit & supir deal WON (khusus Mobil Long Term + supir)
- **requiredDriverQty()**: helper di model Opportunity untuk hitung kebutuhan supir terpisah dari jumlah kendaraan
- **Subscription manual billing**: route `subscriptions.billing.run` (POST + CSRF, role gm/finance/manager)
- **Pipeline — Nilai Rupiah saat drag**: `moveStage()` return `summary` (count + total per stage). Frontend update Rupiah live via `data-value-badge` + `data-col-value` attribute
- **Pipeline — Table View in-place**: toggle Kanban/Table tanpa redirect; pakai `#pipeline-table-view` + Alpine `x-show`

### Fixes
- Fix modal Assign/Fulfill dipisah dari modal Register Vehicle (sebelumnya nested → tersembunyi); regresi test `RoleBugFixTest`
- Fix UI kontras stage header pipeline (`.stage-pro`, `.stage-prop`, `.stage-neg` dll) untuk dark/light mode
- Fix menu Role Operations bisa diklik (z-index/event bubbling)
- Fix `MassiveVehicleBookingSeeder`: kolom Booking (`pickup_datetime`, `dropoff_datetime`, `destination`, `created_by`) dan Voucher (`voucher_code`, `title`, `denomination`, dll) disesuaikan dengan fillable model
- Fix 419 Page Expired: `render.yaml` SESSION_DRIVER file → cookie + SESSION_ENCRYPT + SESSION_SECURE_COOKIE
- Fix `$this->command?->` null-safe operator di seeder agar aman saat dipanggil tanpa CLI

### Chores
- Dockerfile CMD ditambah background seed `MassiveVehicleBookingSeeder` (auto-run tiap deploy)
- Semua dokumen lama (MASTERPROMPT v7.8, v8.0, v8.1) dihapus; v8.2 = sumber kebenaran tunggal
- `graph.json` (131 node, 115 edge) dibuat via graphify untuk pemetaan arsitektur
- Branch aktif: `main` + `checkpoint_design`
- Test baseline: **183 passed (492 assertions)** sebelum workstream KPI 18 Juni.

---

## [v8.1] — 2026-06-13

### Features
- **RBAC 2 lapis**: Middleware route + Laravel Policy (OpportunityPolicy, VehiclePolicy, DriverPolicy)
- **OpportunityPolicy**: Sales view+write miliknya; Manager view tim + update direct-report; GM view semua, read-only
- **VehiclePolicy / DriverPolicy**: pool scoping via `pool_id`
- **Pipeline Kanban 6 kolom**: Call/Meeting, Prospecting, Proposal, Negotiation, Won, Lost — drag bebas via SortableJS
- **History Timeline**: setiap perpindahan stage append ke JSON `history_timeline` + update `stage_changed_at`

### Fixes
- Fix carry-over kendaraan & supir saat pindah stage (eager-loading `fresh()` → `refresh()->load()`)
- Fix API available vehicle: `orWhere('assigned_opportunity_id', $oppId)` agar unit aktif tetap tampil
- Fix hierarchi Manager→Sales: `manager_id` di users, Policy cek `owner.manager_id === auth->id`

---

## [v8.0] — 2026-06-10

### Features
- **Multi-produk JSON**: field `products` (JSON array) di opportunities — menggantikan rencana tabel `opportunity_items`
- **6 Produk + kpi_key**: Mobil Short/Long Term, Bis Short/Long Term, E-Voucher, Supir — masing-masing punya `kpi_key`
- **KpiService**: recordWon() pecah nilai per kpi_key ke actual + actual_won di SalesTarget
- **SalesTarget**: 6 target produk + 1 total per Sales, per bulan/tahun
- **Approval Engine**: ApprovalService L1 Manager → L2 GM; diskon >5% atau deal >50jt → eskalasi GM
- **Migrasi BlueCRM fields**: tambah kolom `products`, `history_timeline`, `stage_changed_at`, `contract_duration_months` ke opportunities
- **Hapus role Director**: migrasi remap user director → gm; GM = pucuk pimpinan
- **Dashboard GridStack**: widget per-role dengan drag/resize, preferensi disimpan di `widget_preferences`

### Chores
- Seeder hierarki 1 GM → 5 Manager → 15 Sales (manager_id), plus pools, 6 produk fix, vehicles, drivers
- Route count: ≈53 → ≈56

---

## [v7.8] — 2026-06-07

### Features
- **Kanban Pipeline**: SortableJS drag-drop sales pipeline with health scores, deal cards, and live filters
- **Login Redesign v2**: Armada fleet image + 1-click demo login for all 6 roles (director, gm, manager, sales, operational, finance)
- **Command Center UX**: Alpine.js stores, ⌘K shortcuts, Chart.js 4 performance, FAB, toast notifications, konfetti
- **GM Dashboard**: Quick Shortcuts grid (16 modules), KPI cards, revenue charts, dark theme
- **Director Dashboard**: Executive summary, fleet health overview, revenue analytics
- **Analytics Route**: New analytics view with soft colors, 3D buttons, clickable elements

### Fixes
- **Async Database Seeding**: Changed `db:seed` to background process (`&`) to prevent Render health check timeout
- **SQLite Runtime Writable**: Fixed auth errors by ensuring `/var/www/html/database/` is writable at runtime
- **Login Error 500**: Fixed APP_URL missing `https://` prefix in render.yaml causing session/CSRF failures
- **Docker Build**: Changed `sqlite` to `sqlite-dev` apk; removed built-in `tokenizer` and `xml` extensions
- **Revenue Route**: Added missing `revenue.index` route + RoleAccessTest feature test
- **UI Test Status**: Fixed incorrect status value passed in role access test

### Chores
- **Cleanup**: Removed outdated deployment files (DEPLOYMENT.md, DEPLOY_CHECKLIST.md, Procfile, docker/, scripts/)
- **Rebrand**: Bluebird → Golden Bird CRM with updated logo and color palette
- **Alpine.store**: Migrated component state to Alpine.store for cross-component reactivity
- **Chart.js Performance**: Lazy loading, animation disable on mobile, responsive options

---

## [v7.7] — 2026-06-06

### Features
- Alpine.store global state management
- Chart.js 4 performance optimizations
- PHP 8.3 → 8.4 upgrade
- Improved deploy config (render.yaml + Dockerfile)

### Fixes
- Docker: sqlite-dev package, removed built-in PHP extensions from install list
- Health check: /up endpoint (Laravel default)

---

## [v7.5] — 2026-06-05

### Features
- **Command Center**: Dark/light mode toggle, keyboard shortcuts (⌘K), notification bell
- **Kanban Pipeline**: SortableJS drag-drop with deal cards, pipeline stages, health scores
- **Authentication**: 6-role RBAC (director, gm, manager, sales, operational, finance)
- **Vite + Tailwind CSS 4**: Modern build pipeline, CSS custom properties, responsive design
- **SQLite on Render**: Free tier deployment without external database

### Stack
- Laravel 12 + PHP 8.4 + Blade templates
- Vite + Tailwind CSS 4 + Alpine.js 3 + Chart.js 4 + SortableJS
- Docker multi-stage: node:22-alpine + php:8.4-fpm-alpine + supervisord
- Render free tier (auto-deploy from GitHub main)

---

## Previous Versions

See git log for full history:
```bash
git log --oneline
```
