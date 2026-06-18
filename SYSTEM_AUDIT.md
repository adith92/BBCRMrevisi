# 🔍 SYSTEM AUDIT — Golden Bird CRM (Technical Flow Map)

> Reverse-engineered dari source code, bukan dokumentasi. Tanggal audit: 2026-06-13.
> Branch: `checkpoint_design` / `main` (pasca commit `33f9317` "Tahap A").
> Dokumen ini untuk **developer / AI**. Versi bahasa awam ada di chat / dokumen terpisah.
> Tag `[VERIFY]` = perlu diverifikasi manual. Tag `[BUG]` = terkonfirmasi rusak dari kode.

---

## 0. Stack & Entry Points

| Layer | Detail |
|---|---|
| Framework | Laravel 12, PHP 8.4 |
| Routing | `routes/web.php` (semua, termasuk grup `api`), `routes/auth.php`, `routes/console.php` |
| Middleware bootstrap | `bootstrap/app.php` — alias `role`, `trustProxies(*)`, CSRF except, `SetLocale` append |
| Auth | Laravel session auth (`web` guard), bukan token/Sanctum |
| Health check | `/up` |
| Scheduler | `subscriptions:bill` daily `23:00` UTC (`routes/console.php`) |

**CSRF dimatikan untuk:** `api/*`, `opportunities/*/move-stage`, `opportunities/*/quick-update` (`bootstrap/app.php:30-34`). Kanban PATCH mengandalkan session auth saja.

---

## 1. Route → Controller Map (per modul)

### Auth (`routes/auth.php`)
| URI | Method | Handler | Guard |
|---|---|---|---|
| `/login` | GET/POST | `Auth\AuthenticatedSessionController@create/store` | guest |
| `/logout` | POST | `@destroy` | auth |

`create()` mem-passing **seluruh `User::all()`** (name, email, role, manager_name) ke view login untuk fitur demo 1-klik. → lihat §6 Security.

### Dashboard
| URI | Handler | Role |
|---|---|---|
| `/dashboard` | `DashboardController@index` → `match(role)` dispatch | auth |
| `/dashboard/gm` | `@gm` | `role:gm` |
| `/dashboard/save-layout` | `@saveLayout` | auth |

`index()` dispatch: `gm→gm()`, `manager→manager()`, `sales→sales()`, `operational|pool→operational()`, `finance→finance()`, `default→gm()`. (`DashboardController.php:26-38`)

### Pipeline / Opportunity (modul inti)
| URI | Method | Handler | Role | Catatan |
|---|---|---|---|---|
| `/pipeline` | GET | `PipelineController@index` | gm,manager,sales | scoping internal |
| `opportunities` | resource | `OpportunityController` | gm,manager,sales | |
| `…/{o}/advance-stage` | POST | `@advanceStage` | gm,manager,sales | tapi internal cek `role===sales && owner` |
| `…/{o}/discount` | POST | `@storeDiscount` | sales,manager,gm | **[BUG]** method tidak ada → 500 |
| `…/{o}/move-stage` | PATCH | `@moveStage` | gm,manager,sales | kanban; CSRF off; cek owner |
| `…/{o}/quick-update` | PATCH | `@quickUpdate` | gm,manager,sales | CSRF off |
| `…/{o}/360` | GET | `@view360` | gm,manager,sales | |
| `/api/opportunities/by-client/{client}` | GET | `@byClient` | gm,manager,sales | **[BUG]** method tidak ada → 500 |
| `/api/opportunities/{o}/history` | GET | `@getHistory` | gm,manager,sales | |

### Clients (split permission)
| URI | Handler | Role |
|---|---|---|
| `clients` index/show | `ClientController` | gm,manager,sales,finance |
| `clients` create/store/edit/update/destroy | `ClientController` | **sales only** |

Dua deklarasi `Route::resource('clients')` terpisah — read group vs write group. Write `destroy` juga sales-only.

### Bookings / Fleet / Drivers / Maintenance
| URI | Handler | Role |
|---|---|---|
| `bookings` resource | `BookingController` | gm,manager,sales,operational |
| `bookings.index` | `@index` | **withoutMiddleware** (semua auth) |
| `fleet` resource (param=`vehicle`) | `FleetController` | gm,manager,operational,sales |
| `drivers` resource | `DriverController` | gm,manager,operational,sales |
| `maintenance` resource | `MaintenanceController` | gm,manager,operational |

### Finance / Revenue / Subscriptions / Products
| URI | Handler | Role |
|---|---|---|
| `/finance`, `/finance/invoices/{invoice}` | `FinanceController@index/show` | gm,manager,finance |
| `/revenue` | `RevenueController@index` | gm,manager,finance |
| `subscriptions` resource | `SubscriptionController` | gm,manager,finance |
| `…/{s}/terminate` | `@terminate` (butuh billing_pin) | gm,finance |
| `products` resource | `ProductController` | gm,manager,finance |
| `products.index` | `@index` | **withoutMiddleware** (semua auth) |
| `/api/products/search` | `@apiSearch` | auth |

### KPI / Activities / Analytics / Sales
| URI | Handler | Role |
|---|---|---|
| `/kpi` | `SalesTargetController@index` | gm,manager,sales |
| `/kpi/targets` | `@store` | gm,manager |
| `activities` resource (no edit/update) | `ActivityLogController` | gm,manager,sales |
| `/analytics`, `/crosssell`, `/pipeline`, `/sales` | `AnalyticsController` | gm,manager |
| `/sales/{user}/performance` | `SalesController@performance` | **NO role mw** — cek internal |

### API breakdown / widgets / search / system
| URI | Handler | Role |
|---|---|---|
| `/api/revenue`, `/revenue/breakdown` | `RevenueController` / `Api\RevenueBreakdownController` | auth |
| `/api/breakdown/{clients,bookings,fleet,drivers,opportunities}` | `Api\DashboardApiController` | auth |
| `/api/revenue/per-sales` | `RevenueController@getRevenuePerSales` | gm,manager |
| `/api/search/global` | `SearchController@global` | auth |
| `/api/widgets/{save,reset}` | `WidgetController` | auth |
| `/api/activities/upcoming` | `ActivityLogController@apiUpcoming` | gm,manager,sales |
| `/api/vehicles/available`, `/drivers/available` | `FleetController@apiAvailable / apiDriversAvailable` | gm,manager,sales |
| `/system/seed-demo` | `SystemController@seedDemo` | gm |
| `/approvals`, `/settings` | closure → redirect "coming soon" | auth (stub) |

---

## 2. Model Graph (ERD teks)

```
User (role: gm|manager|sales|operational|finance|pool)
 ├─ belongsTo  User            manager()        (self, manager_id)
 ├─ hasMany    User            subordinates()   (manager_id)
 ├─ belongsTo  Pool            pool()           (pool_id)
 ├─ hasMany    Client          clients()        (assigned_sales_id)
 ├─ hasMany    Booking         bookings()       (sales_id)
 ├─ hasMany    Opportunity     opportunities()  (sales_id)
 ├─ hasMany    ActivityLog     activityLogs()   (sales_id)
 ├─ hasMany    MeetingLog      meetingLogs()     (sales_id)
 └─ hasMany    SalesTarget     salesTargets()

Client
 ├─ belongsTo  User            assignedSales()  (assigned_sales_id)
 ├─ hasMany    Booking, Invoice, MeetingLog, Opportunity, ActivityLog, Subscription
 └─ hasMany    Voucher         vouchers()       ⚠️ [BUG] model Voucher TIDAK ADA

Opportunity   (opp_number auto: OPP-YYYYMM-#### via boot::creating)
 ├─ belongsTo  Client, User(sales), Product, User(approver), Booking, Subscription
 ├─ hasMany    ActivityLog, ApprovalRequest
 ├─ hasMany    Vehicle         assignedVehicles() (assigned_opportunity_id)
 ├─ hasMany    Driver          assignedDrivers()  (assigned_opportunity_id)
 ├─ scope      byStage(), active() (notIn won/lost)
 └─ accessor   stageColor, stageLabel
     stages: call_meeting → prospecting → proposal → negotiation → won|lost

Booking       (booking_number: BB-YYYYMMDD-### di controller)
 ├─ belongsTo  Client, User(sales), User(createdBy), Vehicle, Driver
 └─ hasOne     Invoice

Vehicle       (GLOBAL SCOPE 'pool': operational+pool_id → filter pool_id)
 ├─ belongsTo  Pool, Opportunity(assignedOpportunity)
 ├─ hasMany    Booking, MaintenanceLog, Subscription
 └─ hasMany    VehicleContract contracts()      ⚠️ [BUG] model VehicleContract TIDAK ADA

Driver        (GLOBAL SCOPE 'pool': sama seperti Vehicle)
 ├─ belongsTo  Pool, Opportunity(assignedOpportunity)
 ├─ hasMany    Booking
 └─ hasMany    VehicleContract contracts()      ⚠️ [BUG] model TIDAK ADA

Subscription  (sub_number auto: SUB-YYYYMM-#### via boot::creating)
 └─ belongsTo  Client, Vehicle, Driver, Product, Opportunity

Invoice ─ belongsTo Booking, Client; hasMany Payment
Payment ─ belongsTo Invoice
Product ─ belongsTo ProductCategory(category, product_category_id); hasMany Opportunity
ProductCategory ─ hasMany Product
SalesTarget ─ belongsTo User  (actuals = ACCESSOR dinamis, lihat §3 KPI)
ApprovalRequest ─ belongsTo Opportunity, User(requester), User(currentApprover)
ActivityLog ─ belongsTo User(sales), Client, Opportunity  (boot::created → KpiService no-op)
Pool ─ hasMany Vehicle
MaintenanceLog ─ belongsTo Vehicle
MeetingLog ─ belongsTo Client, User(sales)
PurchaseOrder ─ (standalone, tak ada relasi)
```

**Dead code:** `app/Models/AllModels.php` mendefinisikan ulang `Invoice, Payment, PurchaseOrder, Pool, MaintenanceLog, MeetingLog` — TIDAK di-autoload (nama file ≠ nama kelas PSR-4), tapi landmine duplicate-class kalau pernah di-`require`. Versi di AllModels.php juga stale (Pool tanpa `region`).

---

## 3. Alur Bisnis per Modul

### 3.1 Pipeline / Opportunity (jantung sistem)

**Create** (`store`, `OpportunityController.php:88`): `abort_if(!isSales)` → **hanya Sales** boleh buat opportunity. `sales_id = auth()->id()`. `opp_number` auto via model boot.

**Pindah stage** — dua jalur:
1. `advanceStage` (form POST, `:401`) — full: validasi `final_value`+`contract_duration_months` wajib jika `won`; assign/release Vehicle+Driver (`lockForUpdate`, tolak kalau unit sudah dibooking sales lain); buat ActivityLog; jika `won` → `PipelineService::triggerWonActions()`. Dibungkus DB transaction + rollback.
2. `moveStage` (kanban PATCH JSON, `:553`) — ringan: update stage, ActivityLog, jika `won` → triggerWonActions. **Return summary per-stage** (count + SUM estimated_value) untuk update UI realtime.

**Guard kedua jalur:** `role !== 'sales' || sales_id !== user->id` → 403. Artinya **GM/manager TIDAK bisa drag** walau route role mengizinkan masuk halaman (lihat §6).

**Transition rule** (`PipelineService`): `$allStages` Trello-style bebas, tapi `canTransition()` masih pakai `$transitions` map (any→any tetap lolos). Stage `won`/`lost` set `actual_close_date = now()`.

**Won actions** (`PipelineService::triggerWonActions`):
- `isRecurring()` true (type==recurring ATAU products[].category mengandung "Long Term") → buat **Subscription** (monthly_rate = estimated_value, end +1th, active).
- else → buat **Invoice** (`INV-`+uniqid, due +30d, status draft).
- Amount = `estimated_value ?? final_value ?? 0`. ⚠️ Urutan: estimated didahulukan walau `won` sudah set `final_value`.

### 3.2 Booking (dispatch)
`store` (`BookingController.php:44`): validasi client/vehicle/driver/pickup/dropoff (dropoff after pickup)/price. `sales_id` = auth jika sales, else `input('sales_id', auth)`. `booking_number = BB-YYYYMMDD-` + `(Booking::count()+1)` zero-pad 3 → ⚠️ **race-condition / collision** kalau hapus booking (count turun). `status='pending'`, `vehicle_type = Vehicle->brand`.
**Scoping index:** non-GM/operational/finance → hanya `sales_id = self`. `show`: sales non-owner → 403.

### 3.3 Revenue & Finance
- `FinanceController@index`: operational → 403. sales → invoice via `whereHas('booking', sales_id=self)`. Summary 6 agregat (total/paid/pending/overdue + count) dengan scoping sama.
- `RevenueController@index`: sales → `where('sales_id')`; finance → cabang lain (`:27`). `getRevenuePerSales` `abort_if(!isGM)` → **GM-only** walau route izinkan manager.
- Invoice dibuat dari 2 sumber: (a) `triggerWonActions` one-time, (b) `processMonthlyBilling` subscription.

### 3.4 Subscription billing (cron)
`SubscriptionController::processMonthlyBilling()` (static, `:142`) dipanggil command `subscriptions:bill` (daily 23:00 UTC, `ProcessSubscriptions.php`):
- Ambil `status=active AND next_billing_date <= today`.
- **Idempotency:** cek Invoice dengan notes `like %SUB-BILLING/{sub}/{Ym}%` → skip kalau ada.
- Amount = monthly_rate × (quarterly→3 / yearly→12 / else 1).
- Invoice `INV-YYYYMM-####` (seq dari last), status `sent`, due +14d.
- Advance `next_billing_date` per cycle; jika lewat `end_date` & `!auto_renew` → status `expired`.
- `runBilling()` HTTP handler ADA tapi **tanpa route** (dead method).

`terminate` (`:116`): butuh `pin` 6-digit, dicek `Hash::check(pin, user->billing_pin)`. Set status `terminated`, end_date today. Role gm,finance.

### 3.5 Fleet & Driver (pool scoping)
Global scope `pool` di model Vehicle & Driver: jika `isOperational() && pool_id !== null` → filter `pool_id`. Controller tambah cabang `isOperational() || isPool()` di index/create/edit. Finance → read-only/redirect (`FleetController:20`, `DriverController:18`). Assignment unit ke opportunity hanya saat WON (lihat 3.1).

### 3.6 KPI / SalesTarget (penting: dinamis)
`KpiService` semua method **no-op** (komentar "Calculated dynamically"). Nilai aktual KPI dihitung **real-time via accessor** di `SalesTarget`:
- `actualWon` = count Opportunity won di bulan/tahun period.
- `actualRevenue` = SUM `COALESCE(final_value, estimated_value, 0)` won period.
- `actualMeetings/Calls/Visits` = count ActivityLog by type+activity_date.
- `actualOpportunities` = count Opportunity created period.
→ Kolom `actual_*` di tabel `sales_targets` **diabaikan** (accessor override). `getOrCreate(user,year,month)` bikin row target default 0.
`SalesTargetController@store`: manager hanya boleh set target untuk subordinate / dirinya (`:87-89`).

### 3.7 Activity Log
`store`: `sales_id = auth()->id()` paksa. `destroy/show`: non-owner sales → 403. Scoping index: sales→self, manager→subordinate sales (`manager_id`), gm→semua. Model `boot::created` panggil `KpiService::incrementActivityCount` (no-op).

---

## 4. RBAC Matrix (role × modul × izin) — sesuai kode

Sumber: route `role:` middleware + scoping query internal. `gm` = pucuk pimpinan (eks-director).

| Modul | gm | manager | sales | operational | finance | pool |
|---|---|---|---|---|---|---|
| Dashboard | ✅ gm() | ✅ manager() (team) | ✅ sales() (own) | ✅ operational() | ✅ finance() | ✅ operational() |
| Pipeline (lihat) | ✅ semua | ✅ team | ✅ own | ❌ | ❌ | ❌ |
| Pipeline (drag/move) | ⚠️ route ya, **kode 403** | ⚠️ route ya, **kode 403** | ✅ owner saja | ❌ | ❌ | ❌ |
| Opportunity create | ❌ (abort !sales) | ❌ | ✅ | ❌ | ❌ | ❌ |
| Clients (lihat) | ✅ | ✅ team | ✅ own | ❌ | ✅ | ❌ |
| Clients (tulis/hapus) | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Bookings | ✅ all | ✅ all | ✅ own | ✅ all | ✅(index) | — |
| Fleet | ✅ | ✅ | ✅(view) | ✅ pool-scoped | ⛔redirect | ✅ pool-scoped |
| Drivers | ✅ | ✅ | ✅ | ✅ pool-scoped | ⛔ | ✅ pool-scoped |
| Maintenance | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Finance/Invoice | ✅ | ✅ | ✅ own-booking | ⛔403 | ✅ | ❌ |
| Revenue | ✅ | ✅ | (api self) | ❌ | ✅ | ❌ |
| `revenue/per-sales` | ✅ **GM-only** | ⚠️route ya/**kode 403** | ❌ | ❌ | ❌ | ❌ |
| Subscriptions | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Sub terminate | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Products | ✅ | ✅ | ✅(index only) | ✅(index) | ✅ | — |
| KPI (lihat) | ✅ all | ✅ team | ✅ own | ❌ | ❌ | ❌ |
| KPI set target | ✅ | ✅ subordinate | ❌ | ❌ | ❌ | ❌ |
| Activities | ✅ all | ✅ team | ✅ own | ❌ | ❌ | ❌ |
| Analytics | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| seed-demo | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

Legend: ✅ izin & jalan · ⛔ sengaja diblok dalam kode · ❌ tak ada akses (403 dari middleware) · ⚠️ route mengizinkan tapi kode internal lebih ketat (mismatch).

---

## 5. Business Rules Tercatat

1. **Opportunity hanya dibuat Sales**, `sales_id` = pembuat. Nomor `OPP-YYYYMM-####` auto-increment per bulan.
2. **Stage drag hanya oleh Sales pemilik** — bukan manager/GM (guard di `advanceStage`+`moveStage`).
3. **WON wajib** `final_value` + `contract_duration_months`; memicu Subscription (Long Term) atau Invoice (one-time).
4. **Assign Vehicle/Driver hanya saat WON**; `lockForUpdate` cegah double-book; tolak kalau unit milik opp lain & status ≠ available.
5. **Subscription billing idempotent** per `{sub}/{Ym}` via marker di `notes`. Cycle quarterly/yearly multiplier 3/12.
6. **Terminate subscription** butuh `billing_pin` 6-digit (hashed) milik user.
7. **KPI aktual dihitung real-time** dari Opportunity/ActivityLog (kolom DB `actual_*` diabaikan).
8. **Client auto-assign** ke pembuat kalau `assigned_sales_id` kosong.
9. **Pool scoping** untuk operational/pool: hanya lihat kendaraan & supir pool-nya.
10. **Demo seed hierarki:** 1 GM → 5 Manager → @3 Sales (15 sales), + 1 ops + 1 finance. sales_level: junior/senior/key_account. Semua password `password123`.

---

## 6. Temuan / Risiko (untuk dev)

### [BUG] Route → method hilang (500 saat dipanggil)
- `POST opportunities/{o}/discount` → `OpportunityController@storeDiscount` **tidak ada**.
- `GET api/opportunities/by-client/{client}` → `OpportunityController@byClient` **tidak ada**.
  Kemungkinan terhapus saat "Tahap A" (bareng `ApprovalController` & `ApprovalService` yang juga sudah hilang). `route:list` tetap load (Laravel tak validasi method saat registrasi), error baru muncul saat request.

### [BUG] Relasi Eloquent ke model tak ada (fatal saat diakses)
- `Client::vouchers()` → `App\Models\Voucher` tidak ada.
- `Vehicle::contracts()`, `Driver::contracts()` → `App\Models\VehicleContract` tidak ada.
  Aman selama tak pernah dipanggil; fatal `Class not found` begitu di-eager-load / dipakai.

### [BUG] Kanban summary tidak ter-scope
`moveStage` return `Opportunity::selectRaw(...)->groupBy('stage')` **tanpa filter sales_id** → untuk user Sales, angka & Rupiah kolom setelah drag jadi **total global semua sales**, padahal index pipeline-nya cuma data sendiri. Inkonsistensi tampilan. (`OpportunityController.php:~620`)

### [RISK] Mismatch route-role vs guard internal (defense in depth OK, tapi membingungkan)
- Pipeline move: route izinkan gm,manager,sales — kode hanya sales owner.
- `revenue/per-sales`: route gm,manager — kode `abort_if(!isGM)`.
  Tidak berbahaya (kode lebih ketat), tapi route menyesatkan & bisa bikin UI nampilkan tombol yang ujungnya 403.

### [RISK] Kebocoran data di halaman login (by design demo)
`AuthenticatedSessionController@create` kirim **semua email+role+manager** user ke guest. Wajar untuk demo 1-klik, **tapi jangan dipakai untuk produksi nyata** tanpa mematikan ini.

### [RISK] CSRF off untuk kanban + semua api
`api/*`, `move-stage`, `quick-update` exempt CSRF. Karena tetap butuh session auth, risiko terbatas, tapi endpoint mutasi (`move-stage`, `quick-update`, `widgets/*` POST) idealnya tetap CSRF-protected.

### [RISK] `booking_number` pakai `Booking::count()+1`
Bukan sequence aman — hapus/race bisa bikin nomor duplikat. Sama pola dengan beberapa nomor lain, tapi Opportunity/Subscription pakai `orderByDesc(...last seq)` yang lebih aman.

### [NOTE] Dokumentasi stale
- `CLAUDE.md` sudah disinkronkan pasca-Tahap A: 6 role aktual (gm, manager, sales, operational, pool, finance), akun `gm@`, `manager1..5@`, `sales1..15@`, `ops@`, `finance@`, branch `main` sebagai aktif utama.
- Kode punya 6 stage (`call_meeting` → `prospecting` → `proposal` → `negotiation` → `won`/`lost`). Dokumen v7.8 sudah dihapus; v8.2 = sumber kebenaran.

### [VERIFY] Hal yang perlu cek runtime
- Apakah Railway benar menjalankan `schedule:run` supaya `subscriptions:bill` aktif? (cek via `railway logs`)
- Apakah view mana pun memanggil `->vouchers` / `->contracts` (kalau ya → halaman 500).
- Apakah ada tombol UI yang nge-hit `discount` / `by-client` (kalau ya → 500).
- `/approvals` & `/settings` masih stub redirect — apakah memang belum dibutuhkan, padahal `ApprovalRequest` model + tabel masih ada.

---

## 7. Lifecycle Ringkas (happy path)

```
Sales buat Opportunity (OPP-YYYYMM-####, stage=call_meeting)
   → drag kanban prospecting→proposal→negotiation (moveStage, ActivityLog tiap pindah)
   → WON: isi final_value + durasi, assign Vehicle+Driver (lock), 
          triggerWonActions:
             ├─ Long Term  → Subscription (SUB-YYYYMM-####, active)
             │     → cron harian subscriptions:bill → Invoice (INV-YYYYMM-####, sent)
             │           → Payment (manual/finance)
             └─ one-time   → Invoice (INV-uniqid, draft)
   Booking (BB-YYYYMMDD-###) jalur paralel untuk dispatch armada → Invoice → Payment
KPI (SalesTarget) menghitung actual_* real-time dari Opportunity+ActivityLog.
```
