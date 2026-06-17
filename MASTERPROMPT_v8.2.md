# 🧠 MASTERPROMPT v8.2 — Golden Bird CRM (BLUEPRINT REBUILD DARI 0)

> Dokumen ini cukup untuk membangun aplikasi dari NOL. Berisi schema penuh, business logic, RBAC, dan urutan build.
> Untuk AI manapun (Claude / Codex / Antigravity). Tanggal: 2026-06-16. Pengganti v8.1.
> Test baseline: **183 passed (492 assertions)**.

---

## 0. CARA PAKAI DOKUMEN INI
Bangun berurutan: **Sec.4 (stack) → Sec.7 (schema/migrasi) → Sec.8 (model) → Sec.9 (RBAC/policy) → Sec.10 (controller/route) → Sec.11 (service) → Sec.12 (view) → Sec.13 (seeder) → Sec.14 (test)**.
UI dashboard depan ikuti `UI_UX_LOCK.md` (TERKUNCI). Multi-produk = JSON `products` (BUKAN tabel `opportunity_items`).

---

## 1. IDENTITAS PROJECT
**Golden Bird CRM** — CRM B2B Fleet Management untuk Golden Bird Group (Golden Bird · Big Bird · Cititrans · Executive).
Inti: KPI/target penjualan, pipeline deal (Kanban), RBAC 2-lapis, fulfillment armada+supir untuk Long Term, finance & billing.
Remote: `github.com/adith92/BBCRMrevisi` · Branch `main` · Prod: Railway (`gbcrmbycodex-production.up.railway.app`).

---

## 2. AI PERSONA & RULES (WAJIB)
1. Baca: `HANDOFF.md` → `UI_UX_LOCK.md` → `MASTERPLAN_v8.2.md` → file ini → `LOGIC_MAP.md` → `SYSTEM_AUDIT.md`.
2. **UI/UX dashboard depan TERKUNCI**. Boleh ubah data/logic, dilarang ubah warna/layout/font/struktur.
3. Repo aktif HANYA `golden-bird-crm`. `RevisiTMPbyAG/` & `/master` ABAIKAN.
4. Otorisasi record-level WAJIB via **Policy** (bukan cuma middleware).
5. Multi-produk = JSON `products`. Jangan bikin tabel `opportunity_items`.
6. Jangan hidupkan role `director`. Jangan tambah library UI/CSS baru.
7. Verifikasi: `migrate:fresh --seed` + `php artisan test` (≥183 passed). Commit & komunikasi: Bahasa Indonesia.

---

## 3. AKTOR & ROLE
6 role: **gm · manager · sales · operational · pool · finance** (Director DIHAPUS → GM pucuk pimpinan).
- **GM**: global view, set semua KPI, approve diskon L2, read-only di pipeline.
- **Manager**: bina tim (`manager_id`), approve diskon L1, set KPI tim, view pipeline tim, update deal direct-report.
- **Sales**: eksekutor — buat/isi/geser kartu pipeline miliknya, kelola client miliknya.
- **Operational**: kelola fleet/booking/maintenance.
- **Pool Admin**: alokasi unit & supir pool-nya (`pool_id`), **blind to revenue**.
- **Finance**: invoice/payment/subscription/voucher.

---

## 4. TECH STACK
| Layer | Detail |
|-------|--------|
| Backend | Laravel 12, PHP ^8.3 |
| Auth | Laravel built-in + `RoleMiddleware` + Sanctum + Policies |
| Frontend | Blade + Tailwind + Chart.js + GridStack + Alpine.js |
| DB | SQLite (lokal), PostgreSQL (prod) |
| Deploy | Railway (auto dari `main`) |
| Dev deps | Pest/PHPUnit, Faker, Breeze, Pint, Sail |

---

## 5. ROLES & DEMO LOGINS (password: `password123`)
gm@goldenbird.co.id · manager1..5@ · sales1..15@ · ops@ · finance@goldenbird.co.id
Hierarki: GM → 5 Manager (`manager_id`) → 3 Sales/manager (15 sales). Pool & Operational di-scope via `pool_id`.

---

## 6. 6 PRODUK & KPI KEY (fix)
| Produk | kpi_key | Kategori | Fulfillment |
|--------|---------|----------|-------------|
| Mobil Short Term | `mobil_short` | short_term | sampai WON saja |
| Bis Short Term | `bis_short` | short_term | sampai WON saja |
| E-Voucher | `evoucher` | evoucher | — |
| Mobil Long Term | `mobil_long` | long_term | **alokasi unit+supir** |
| Bis Long Term | `bis_long` | long_term | sampai WON (bis) |
| Supir | `supir` | service | dialokasikan Pool |

---

## 7. DATABASE SCHEMA — 20+ TABEL (untuk migrasi)

> Semua tabel punya `id` + `timestamps` kecuali disebut lain. FK = foreignId constrained.

**users**: name, email(unique), password, role(enum: gm|manager|sales|operational|pool|finance), email_verified_at · +`manager_id`(FK users null), `sales_level`(junior|senior|key_account), `pool_id`(FK pools null), `billing_pin`(string null), `dashboard_settings`(json null)
**pools**: name, location, capacity(int), region, notes
**clients**: company_name, pic_name, phone, email, address, industry, status(enum), assigned_sales_id(FK users), tier, first_contact_date, company_size, notes
**drivers**: name, phone, license_number, status(string), `pool_id`(FK), `assigned_opportunity_id`(FK opportunities null), notes
**vehicles**: plate_number, brand(enum: goldenbird|bigbird|cititrans|executive), model, capacity(int), year, status(string), pool_id(FK), notes · +color, transmission, stnk_expiry, pajak_expiry, bbm_type, current_km, year_manufactured, `assigned_opportunity_id`(FK null), `fuel_indicator`(string), `insurance_expiry`(date)
**product_categories**: name, type(short_term|long_term|evoucher), description, is_active
**products**: product_category_id(FK), name, `kpi_key`, sku(unique), base_price(decimal), unit, min_pax, max_pax, duration_days, description, is_active
**opportunities**: opp_number(unique), client_id(FK), sales_id(FK), product_id(FK null), title, `stage`(call_meeting|prospecting|proposal|negotiation|won|lost), estimated_value, final_value, pax, discount_percent, discount_approved(bool), approved_by(FK null), expected_close_date, actual_close_date, lost_reason, notes, booking_id(FK null), subscription_id(null) · +`products`(json: array {product_id,name,category,kpi_key,qty,price,note}), `history_timeline`(json: array event stage), `stage_changed_at`(timestamp), `contract_duration_months`(int)
**activity_logs**: sales_id(FK), client_id(FK), opportunity_id(FK), type, subject, notes, activity_date(dateTime), duration_minutes, outcome, next_action, next_action_date
**sales_targets**: user_id(FK), period_year, period_month(tinyint), target_meetings/calls/visits/opportunities/won(int), target_revenue(decimal), actual_*(sama), unique(user_id,period_year,period_month)
**approval_requests**: opportunity_id(FK), requested_by(FK), current_approver_id(FK), type(discount|...), discount_percent, original_price, final_price, level(tinyint: 1=manager,2=gm), status(pending|approved|rejected), notes, rejection_reason, approved_at, rejected_at
**subscriptions**: sub_number, opportunity_id(FK), client_id(FK), vehicle_id(FK), driver_id(FK), product_id(FK), start_date, end_date, monthly_rate, billing_cycle, status, last_billed_at, next_billing_date, auto_renew(bool), notes
**vouchers**: voucher_code, client_id(FK), product_id(FK), title, denomination, purchase_price, valid_from, valid_until, status, used_at, used_by_booking_id(FK), issued_by(FK), notes
**vehicle_contracts**: vehicle_id, driver_id, client_id, start_date, end_date, contract_type, rate, status, notes
**bookings**: booking_number, client_id, sales_id, created_by, vehicle_id, driver_id, pickup_datetime, dropoff_datetime, destination, vehicle_type, price, status(enum), notes
**invoices**: invoice_number, booking_id(FK null), client_id, amount, status(draft|sent|paid|overdue|unpaid), due_date, paid_at, notes
**payments**: payment_number, invoice_id, amount, method(enum), payment_date, notes
**purchase_orders**: po_number, vendor, item_description, amount, status, notes
**maintenance_logs**: vehicle_id, type, description, cost, vendor, scheduled_date, completed_date, status, notes
**meeting_logs**: client_id, sales_id, meeting_date, notes, outcome, follow_up_date, status
**widget_preferences**: user_id(unique FK), widgets(json)

> Migrasi `remove_director_role`: remap user `director`→`gm` + perketat CHECK constraint (pgsql) / ENUM (mysql); SQLite cukup remap data.

---

## 8. MODEL & RELASI KUNCI
- **User**: hasMany(clients via assigned_sales_id, opportunities via sales_id, salesTargets), belongsTo(manager), hasMany(subordinates via manager_id), belongsTo(pool). Method: isGM/isManager/isSales/isOperational/isPool/isFinance; isDirector()→false.
- **Opportunity**: belongsTo(client, sales, product, approver, booking, subscription), hasMany(activityLogs, approvalRequests), **hasMany(assignedVehicles, assignedDrivers via assigned_opportunity_id)**. Cast `products`/`history_timeline`→array, `stage_changed_at`→datetime. Method: `requiredFleetQty()`, `requiredDriverQty()`, scopeByStage, scopeActive, accessor stageColor/stageLabel.
- **Vehicle/Driver**: belongsTo(pool, opportunity via assigned_opportunity_id). Cast assigned_opportunity_id→integer.
- **Product**: belongsTo(category), hasMany(opportunities), fillable incl `kpi_key`.
- **SalesTarget**: belongsTo(user), accessor achievement; getOrCreate(userId,year,month).

---

## 9. RBAC — DUA LAPIS

**Lapis 1 Middleware** (`role:...` di route). **Lapis 2 Policy** (auto-discovery).

`OpportunityPolicy`: view(GM/Manager semua; Sales hanya `sales_id===id`); create(Sales saja); update(GM=false; Manager hanya owner.manager_id===id; Sales hanya miliknya); delete(false).
`VehiclePolicy` & `DriverPolicy`: view(operational/pool dgn pool_id → hanya pool sama; lain → semua); create/update/delete(hanya operational/pool, pool sendiri).

Aturan turunan: Client write hanya Sales (route resource dipecah view vs write). KPI set target hanya gm/manager. Pool blind to revenue.

---

## 10. ROUTES INTI (≈56) → CONTROLLER
- `/dashboard` (index router per role) · `/dashboard/gm` (gm)
- `/pipeline` (PipelineController@index, kanban) · `opportunities` resource + `/{opp}/move-stage`, `/advance-stage`, `/quick-update`, `/discount`, `/360`, `/history` (OpportunityController)
- `clients` resource (view: gm/manager/sales/finance; write: sales)
- `products` resource (write gm/manager/finance) · `kpi` + `kpi/targets` (SalesTargetController)
- `fleet` resource + `/api/vehicles/available` · `drivers` resource + `/api/drivers/available` (Fleet/DriverController)
- `approvals` + `/{a}/approve` `/reject` (ApprovalController) · `bookings` `finance` `subscriptions` (+`subscriptions.billing.run` POST) `vouchers` `maintenance` `analytics` `revenue` resources
- API: `/api/breakdown/*`, `/api/widgets/save|reset`, `/api/search/global`, `/system/seed-demo`

---

## 11. SERVICES & BUSINESS LOGIC

### PipelineService
- `$allStages = [call_meeting, prospecting, proposal, negotiation, won, lost]`.
- `triggerWonActions()`: jika recurring (produk Long Term di `products`) → createSubscription; else createInvoice.

### Fulfillment lifecycle (OpportunityController @update / @advanceStage) ⭐
- Hitung `$targetFleetStatus`/`$targetDriverStatus`:
  - stage `won` → **`assigned`**
  - stage `proposal`/`negotiation` → **`reserved`**
  - lainnya → **`available`**
- Set `assigned_opportunity_id` ke opportunity; unit lama dilepas (`null`+`available`).
- **Carry-over**: gunakan `refresh()->load(assignedVehicles, assignedDrivers)` (BUKAN `fresh()`) agar relasi ikut di payload JSON. API available pakai `orWhere('assigned_opportunity_id', $oppId)` agar unit yang sedang di-assign deal ini tetap muncul.
- Jumlah supir = `requiredDriverQty()`; jumlah unit = `requiredFleetQty()`.
- **Pending Assignments**: WON Mobil Long Term + supir → operasional/Pool penuhi; Pool hanya pilih unit/supir pool sendiri.

### Approval (ApprovalService)
- needsApproval = diskon > 0. Level 1 Manager; diskon >5% → Level 2 GM (tertinggi). Deal value >50jt mulai L2.
- Perubahan angka deal pada stage sama → wajib ACC Manager.

### KpiService
- recordWon: pecah nilai per produk (`kpi_key`) ke actual + actual_won. incrementActivityCount/OpportunityCount.

### Subscription billing
- `runBilling` (route `subscriptions.billing.run`, POST+CSRF, role gm/finance/manager) untuk billing manual recurring.

---

## 12. VIEW & UI
- Dashboard per role: `dashboard/{gm,manager,sales,operational,finance}.blade.php` + `charts.blade.php`. **GM = TERKUNCI** (`UI_UX_LOCK.md`).
- Pipeline kanban: `pipeline/index.blade.php` (6 kolom, drag, modal Assign/Fulfill TERPISAH dari Register Vehicle), `create`, `show`.
- Design tokens & komponen di `resources/css/app.css` (`--cc-*`, `.kpi-card`, `.cc-card`). Font Inter. Warna pipeline gradasi biru `#1468a8`; Won hijau, Lost merah.
- i18n: `lang/id`, `lang/en` (key `ui.*`). Dark/Light mode.

---

## 13. SEEDER (urutan)
`DatabaseSeeder` → 1 GM + 5 Manager + 15 Sales (manager_id) + ops + finance; pools; 6 produk fix (`kpi_key`); 30 clients; vehicles/drivers (pool_id); bookings/invoices/payments. Lalu `DemoMassiveSeeder` (opportunities multi-stage + products JSON + history) & `MassiveVehicleBookingSeeder`.

---

## 14. QUICK START — Rebuild dari 0
```bash
git clone https://github.com/adith92/BBCRMrevisi.git golden-bird-crm
cd golden-bird-crm
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build && php artisan serve
# http://localhost:8000 · gm@goldenbird.co.id / password123
php artisan test   # baseline 183 passed
```

---

## 15. CHECKLIST COMMIT & LARANGAN
Sebelum commit: migrate:fresh --seed sukses · test ≥183 · Policy benar · fulfillment lifecycle+carry-over utuh · UI terkunci tidak tersentuh · LOGIC_MAP/SYSTEM_AUDIT diupdate jika RBAC berubah · jangan commit `/master`, `RevisiTMPbyAG/`, `public/build/* 2.*`.
Larangan: ubah UI dashboard depan tanpa izin · hidupkan director · tabel opportunity_items · library UI baru · otorisasi record-level tanpa Policy.
