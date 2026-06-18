# 🚌 GOLDEN BIRD CRM — v8.2

> **Enterprise B2B Fleet Management & CRM System**  
> Dark/Light Dual Theme · Kanban Pipeline · RBAC 2-Lapis · Fleet Fulfillment · Finance & Billing

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CDN-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat-square&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)](https://chartjs.org)

---

## Tentang Project

**Golden Bird CRM** adalah sistem CRM B2B Fleet Management untuk Golden Bird Group (Golden Bird · Big Bird · Cititrans · Executive Transport). Dibangun di atas Laravel 12 dengan RBAC 2-lapis, pipeline Kanban 6 stage, fulfillment armada & supir, dan dashboard per-role.

### Tech Stack

| Layer | Detail |
|-------|--------|
| Backend | Laravel 12, PHP 8.4 |
| Database | PostgreSQL (production / Railway), SQLite (local dev) |
| Frontend | Blade + Tailwind CDN + Alpine.js + Chart.js + GridStack |
| Auth | Laravel session auth + RoleMiddleware + Laravel Policies |
| Deploy | Railway (auto-deploy dari branch `main`) |
| Test | Pest/PHPUnit — baseline: **202 passed (547 assertions)** |

---

## Quick Start (Local Dev)

```bash
# 1. Clone
git clone https://github.com/adith92/BBCRMrevisi.git
cd golden-bird-crm

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database + seed (SQLite lokal)
touch database/database.sqlite
php artisan migrate --seed

# 5. Serve
php artisan serve
# → http://localhost:8000
```

---

## Demo Accounts (password: `password123`)

| Role | Email |
|------|-------|
| GM | `gm@goldenbird.co.id` |
| Manager (×5) | `manager1@goldenbird.co.id` s/d `manager5@goldenbird.co.id` |
| Sales (×15) | `sales1@goldenbird.co.id` s/d `sales15@goldenbird.co.id` |
| Operational | `ops@goldenbird.co.id` |
| Finance | `finance@goldenbird.co.id` |

> Pool role di-assign via `pool_id`. Tidak ada role `director` — digantikan GM.

---

## 6 Role & Akses

| Role | Scope | Akses Utama |
|------|-------|-------------|
| `gm` | Global | Full view, approve semua, set KPI, read-only di pipeline |
| `manager` | Tim | Approve diskon/deal tim, view pipeline tim, set KPI tim |
| `sales` | Pribadi | Buat/isi/geser opportunity miliknya, kelola client miliknya |
| `operational` | Fleet global | Kelola fleet, booking, maintenance, fulfillment |
| `pool` | Pool sendiri | Alokasi unit & supir dari pool miliknya saja |
| `finance` | Keuangan | Invoice, payment, subscription, billing |

---

## Fitur Utama (v8.2)

- **Kanban Pipeline 6 stage**: Call/Meeting → Prospecting → Proposal → Negotiation → Won / Lost
- **Multi-produk per deal**: JSON `products` (qty, harga, note) — 6 kpi_key: mobil_short, bis_short, evoucher, mobil_long, bis_long, supir
- **Fulfillment lifecycle**: status armada mengikuti stage (available → reserved → assigned)
- **Carry-over assignment**: kendaraan & supir terpilih ikut terbawa antar stage
- **Pending Assignments**: titik masuk operasional/pool untuk penuhi unit deal WON
- **Approval Engine**: diskon L1 Manager → L2 GM
- **Subscription billing**: manual (route POST) + cron harian `subscriptions:bill` (23:00 UTC)
- **Dashboard per-role**: Command Center GM (terkunci), Manager, Sales, Operational, Finance
- **Dark/Light theme**, GridStack widget, global search, i18n ID/EN

---

## Struktur Dokumen (untuk developer/AI)

Baca berurutan:
1. `CLAUDE.md` — konteks project & rules
2. `UI_UX_LOCK.md` — dashboard GM TERKUNCI
3. `HANDOFF.md` — status operasional terbaru
4. `MASTERPLAN_v8.2.md` — visi, ERD, business rules
5. `MASTERPROMPT_v8.2.md` — blueprint rebuild dari 0
6. `LOGIC_MAP.md` — peta RBAC & workflow
7. `SYSTEM_AUDIT.md` — route map, bug list, risk

---

## Deployment

```bash
# Push ke Railway (auto-deploy)
git add -A
git commit -m "feat/fix: deskripsi"
git push origin main
```

- **Production URL**: https://gbcrmbycodex-production.up.railway.app
- **Branch aktif**: `main`
- **DB Production**: PostgreSQL (Railway)

---

## Test

```bash
php artisan test
# Ekspektasi: ≥202 passed (547 assertions)

# Re-seed lokal
php artisan migrate:fresh --seed
```

---

## Aturan Penting

- **Jangan ubah UI/UX** dashboard GM (`gm.blade.php`) — lihat `UI_UX_LOCK.md`
- **Multi-produk = JSON `products`** — jangan bikin tabel `opportunity_items`
- **Jangan hidupkan role `director`** — sudah dihapus permanen
- **Jangan modifikasi** `routes/web.php` yang existing atau `database/migrations` yang sudah ada
- **Commit message** dalam Bahasa Indonesia

---

## License

MIT License
