# 🗺️ MASTERPLAN v8.2 — Golden Bird CRM (FINAL)

> Upgrade dari v8.1. Merangkum SEMUA business workflow, logic, rule & mindmap dari project AKTUAL.
> Tanggal: 2026-06-16 · Repo: `golden-bird-crm` · Remote: `github.com/adith92/BBCRMrevisi` · Prod: Railway (`gbcrmbycodex-production.up.railway.app`).
> Baca berurutan: `HANDOFF.md` → `UI_UX_LOCK.md` → file ini → `MASTERPROMPT_v8.2.md` → `LOGIC_MAP.md` → `SYSTEM_AUDIT.md`.
> Status test terakhir: **202 passed (547 assertions)**.

---

## 🆕 APA YANG BERUBAH DI v8.2 (vs v8.1)

1. **Fulfillment lifecycle matang** — status armada & supir mengikuti stage:
   - Proposal / Negotiation → **`reserved`**
   - Won → **`assigned`**
   - Stage lain / dilepas → **`available`**
   (OpportunityController:287-288, 483-484)
2. **Carry-over assignment** — kendaraan & supir yang dipilih di Proposal ikut terbawa ke Negotiation & tampil di riwayat (fix eager-loading: `refresh()->load()` bukan `fresh()`; API available pakai `orWhere assigned_opportunity_id`).
3. **Pool scoping pada alokasi** — user Pool hanya bisa memilih kendaraan & supir dari `pool_id`-nya sendiri di tabel **Pending Assignments**.
4. **Pending Assignments** = titik masuk operasional/Pool untuk memenuhi unit & supir deal WON (khusus Mobil Long Term + supirnya).
5. **Driver qty** dihitung via helper `Opportunity::requiredDriverQty()` (tidak tercampur jumlah kendaraan).
6. **Subscription manual billing** — route `subscriptions.billing.run` (POST) + CSRF + cek role.
7. **Fix modal Assign/Fulfill** — dipisah dari modal Register Vehicle (sebelumnya nested → tersembunyi); ada regresi test `RoleBugFixTest`.
8. **Fix UI kontras** note box pipeline; menu Role Operations bisa diklik (z-index/event bubbling).

> Skema DB TIDAK berubah sejak v8.0 (migration terakhir `2026_06_12_170003`). Perubahan v8.2 di layer logic/controller/view/policy. Route 53 → 56.

> ⚠️ **Koreksi standar multi-produk:** implementasi AKTUAL memakai kolom **JSON `products`** di `opportunities` (bukan tabel `opportunity_items`). Rencana tabel relasional dibatalkan. Ikuti kode: JSON `products`.

---

## 📌 VISI PRODUK
CRM B2B Fleet Management Golden Bird Group (Golden Bird · Big Bird · Cititrans · Executive).
Pantau performa & target penjualan (KPI), lacak pipeline deal real-time, RBAC dinamis 2-lapis, dan **fulfillment armada/supir** dengan lifecycle status untuk produk Long Term.

---

## 🧠 MINDMAP SISTEM

```
GOLDEN BIRD CRM
├── 1. SALES ENGINE
│   ├── Clients (perusahaan + PIC)               → Sales kelola; atasan view
│   ├── Pipeline/Opportunities (Kanban 6 stage)  → Sales geser; Manager/GM view; Manager approval
│   │   ├── Multi-produk per deal (JSON `products`: qty/harga/note)
│   │   ├── History timeline per stage (JSON `history_timeline`)
│   │   ├── Carry-over kendaraan/supir antar stage
│   │   └── 6 produk: mobil/bis short, evoucher, mobil/bis long, supir (kpi_key)
│   └── KPI/SalesTargets (6 target produk + total, rupiah, dinamis)
│
├── 2. APPROVAL ENGINE
│   ├── Diskon: L1 Manager → L2 GM (tertinggi)
│   └── Perubahan angka deal di stage sama → ACC Manager
│
├── 3. FULFILLMENT ENGINE (Long Term) ⭐ v8.2
│   ├── Lifecycle status: available → reserved (Proposal/Nego) → assigned (Won)
│   ├── Pending Assignments → operasional/Pool penuhi unit & supir
│   ├── Carry-over assignment antar stage
│   ├── requiredDriverQty() hitung kebutuhan supir
│   └── Short Term & Bis → transaksi sampai WON, TANPA alokasi unit fisik
│
├── 4. OPERATIONS (Pool-scoped via Policy)
│   ├── Fleet (Vehicle: status, fuel_indicator, insurance_expiry, pool_id, assigned_opportunity_id)
│   ├── Drivers (status, pool_id, assigned_opportunity_id)
│   └── Pools · Maintenance · Purchase Orders
│
├── 5. FINANCE
│   └── Invoices · Payments · Subscriptions (recurring LT + manual billing) · Vouchers · billing_pin
│
└── 6. PLATFORM
    ├── Dashboard per role (Command Center GM = TERKUNCI) + GridStack widget
    ├── Analytics · Revenue breakdown API · Global search · i18n ID/EN · Dark/Light
    └── RBAC: middleware route + Laravel Policies
```

---

## 🎭 RBAC v8.2 — DUA LAPIS

6 role: **gm · manager · sales · operational · pool · finance** (Director dihapus → GM).
- **Lapis 1 — Middleware route** (`role:...`).
- **Lapis 2 — Policy** (`OpportunityPolicy`, `VehiclePolicy`, `DriverPolicy`).

### Opportunity
| Aksi | GM | Manager | Sales |
|------|----|---------|-------|
| view | ✅ semua | ✅ semua | ✅ hanya miliknya |
| create | ❌ | ❌ | ✅ |
| update | ❌ read-only | ✅ hanya direct report (`manager_id`) | ✅ hanya miliknya |
| delete | ❌ | ❌ | ❌ |

### Vehicle & Driver (pool scoping)
| Aksi | Operational/Pool dgn `pool_id` | Role lain |
|------|-------------------------------|-----------|
| view | ✅ hanya pool sama | ✅ semua |
| create/update/delete | ✅ hanya pool sendiri | ❌ (selain operational/pool) |

> Pool Admin = **blind to revenue**; saat alokasi, hanya pilih unit/supir pool sendiri.

---

## 🔄 BUSINESS WORKFLOW

### A. Pipeline (6 stage)
`Call/Meeting → Prospecting → Proposal → Negotiation → Won / Lost`
- Deal baru mulai Call/Meeting (pilih Call/Meeting + note). Kanban bebas pindah; transisi tercatat di `history_timeline`.
- Multi-produk via JSON `products`. WON → 100% Actual Value & KPI per `kpi_key`.

### B. Approval (Manager gerbang)
- Diskon (L1 Manager → L2 GM). Revisi angka deal stage sama → ACC Manager. Geser stage tanpa approval.

### C. Fulfillment ⭐ (alur v8.2)
1. Sales pilih kendaraan & supir mulai tahap Proposal → status jadi **`reserved`**, `assigned_opportunity_id` di-set.
2. Pilihan **carry-over** ke Negotiation (tidak hilang).
3. **WON** → status unit & supir jadi **`assigned`**; muncul di **Pending Assignments** untuk operasional/Pool eksekusi (khusus Mobil Long Term + supir).
4. Pool hanya bisa alokasi unit/supir dari pool sendiri. `requiredDriverQty()` tentukan jumlah supir.
5. Short Term & Bis → sampai WON saja, tanpa alokasi unit fisik via sistem.

### D. KPI
- Tiap Sales: 6 target produk + total. GM agregasi tim. Manager set tim sendiri; GM set semua.

---

## 🏆 SUDAH DIBANGUN (per v8.2)
- ✅ RBAC 2 lapis + pool scoping + GM read-only pipeline + Manager direct-report only.
- ✅ Pipeline Kanban 6 stage, multi-produk JSON, history timeline, carry-over assignment.
- ✅ Approval engine (Manager→GM).
- ✅ **Fulfillment lifecycle** available→reserved→assigned + Pending Assignments + requiredDriverQty.
- ✅ 6 produk + kpi_key + KpiService.
- ✅ Hierarki 1 GM → 5 Manager → 15 Sales.
- ✅ Dashboard per role + GridStack + Command Center GM (TERKUNCI).
- ✅ Finance: Invoice/Payment/Subscription (+manual billing)/Voucher/billing_pin.
- ✅ Test 202 passed.

---

## 🚧 BACKLOG / CATATAN
- Cleanup artefak build duplikat (`public/build/* 2.*`) jika muncul lagi sebelum commit.
- Verifikasi fulfillment manual via browser (klik Assign/Fulfill dari Pending Assignment tanpa buka Register Vehicle).
- Dokumen lama (v7.8, v8.0, v8.1) sudah dihapus; v8.2 = sumber kebenaran tunggal.
- Cek temuan di `SYSTEM_AUDIT.md` (CSRF kanban/API, booking_number race).

---

## 📊 DATABASE — 20 Model
User, Client, Opportunity, Product, ProductCategory, SalesTarget, ApprovalRequest, ActivityLog, MeetingLog, Booking, Invoice, Payment, Subscription, Voucher, Vehicle, Driver, Pool, MaintenanceLog, PurchaseOrder, VehicleContract, WidgetPreference.
Field kunci: opportunities(`products` JSON, `history_timeline` JSON, `stage`, `stage_changed_at`, `contract_duration_months`); products(`kpi_key`); users(`manager_id`, `pool_id`, `sales_level`, `billing_pin`, `dashboard_settings`); vehicles/drivers(`assigned_opportunity_id`, `pool_id`, `status`); vehicles(`fuel_indicator`, `insurance_expiry`).

---

## 🧬 ERD RINGKAS (relasi antar entitas)

```
User (manager_id→User, pool_id→Pool)
 ├─< Client (assigned_sales_id)
 ├─< Opportunity (sales_id)        Opportunity ─ belongsTo Client, Product, approver(User)
 │     ├─< ActivityLog                          ├─< ApprovalRequest (requested_by, current_approver_id → User)
 │     ├─ products (JSON: produk+qty+harga+note)├─< assignedVehicles (Vehicle.assigned_opportunity_id)
 │     ├─ history_timeline (JSON event stage)   └─< assignedDrivers  (Driver.assigned_opportunity_id)
 │     └─ → Subscription / Invoice (saat WON)
 ├─< SalesTarget (period_year, period_month)
 └─ Pool ─< Vehicle, Driver

Booking ─ belongsTo Client, Vehicle, Driver, sales(User) ─< Invoice ─< Payment
Subscription ─ belongsTo Opportunity, Client, Vehicle, Driver, Product
Voucher ─ belongsTo Client, Product, issued_by(User)
```

---

## 📜 BUSINESS RULES (lengkap, untuk validasi build)

1. **Deal baru** wajib stage `call_meeting`; Sales pilih Call atau Meeting + note.
2. **Stage** boleh pindah bebas (kanban); tiap transisi append ke `history_timeline` + update `stage_changed_at`.
3. **Multi-produk** disimpan di JSON `products` (array of {product_id, name, category, kpi_key, qty, price, note}). `estimated_value` = SUM(qty×price).
4. **WON** → nilai 100% jadi Actual Value, masuk KPI per `kpi_key`; trigger Subscription (jika ada produk Long Term) atau Invoice.
5. **Fulfillment status armada/supir**: `available` → `reserved` (Proposal/Negotiation) → `assigned` (Won) → `available` (dilepas). Tautkan via `assigned_opportunity_id`. Carry-over wajib terjaga antar stage.
6. **Pending Assignments**: hanya WON Mobil Long Term + supir yang masuk antrian fulfillment. Short Term & Bis tidak alokasi unit fisik.
7. **Pool scoping**: operasional/Pool dgn `pool_id` hanya lihat/pilih/kelola Vehicle & Driver pool-nya.
8. **Approval**: diskon >0 → ApprovalRequest (L1 Manager, >5% → L2 GM). Revisi angka deal di stage sama → ACC Manager. Geser stage tanpa approval.
9. **RBAC pipeline**: Sales view+write miliknya; Manager view tim + update direct-report; GM view semua, read-only. Client write hanya Sales.
10. **KPI**: 6 target produk + total/Sales. GM = agregasi tim. Manager set tim sendiri; GM set semua.
11. **Subscription**: recurring Long Term, ada manual billing (`subscriptions.billing.run`).

---

## 🛠️ ROADMAP BUILD DARI 0 (fase)

1. **Fondasi**: Laravel 12 + auth Breeze + RoleMiddleware + 6 role + seeder hierarki 5×3.
2. **Master data**: pools, clients, 6 produk (kpi_key), vehicles, drivers.
3. **Pipeline**: opportunities (6 stage, JSON products + history_timeline), kanban view, ownership Policy.
4. **Approval**: ApprovalService (L1 Manager → L2 GM) + diskon + perubahan angka.
5. **Fulfillment**: assigned_opportunity_id, lifecycle status, carry-over, Pending Assignments, pool scoping (Vehicle/Driver Policy).
6. **KPI**: SalesTarget 6 produk + total, KpiService recordWon.
7. **Finance**: Invoice/Payment/Subscription (+manual billing)/Voucher.
8. **Dashboard & platform**: dashboard per role (GM TERKUNCI), GridStack, analytics, global search, i18n, dark/light.
9. **Test**: target ≥202 passed.

> Detail schema & logic implementasi ada di `MASTERPROMPT_v8.2.md` (Sec.7–13).

---

## 🔗 DOKUMEN PENDAMPING
`HANDOFF.md` · `UI_UX_LOCK.md` · `LOGIC_MAP.md` · `SYSTEM_AUDIT.md` · `MASTERPROMPT_v8.2.md` · `CLAUDE.md`
