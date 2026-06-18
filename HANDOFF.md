# HANDOFF — Golden Bird CRM (Baca ini dulu)

> Untuk AI manapun (Codex / Antigravity / dll) yang melanjutkan project ini.
> Stack: Laravel 12 + Blade + Tailwind + Chart.js. DB: SQLite (lokal), Postgres (prod).

---

## 📚 Urutan baca dokumen (WAJIB)
1. `CLAUDE.md` — konteks project.
2. `UI_UX_LOCK.md` — 🔒 tampilan dashboard depan TERKUNCI. Jangan ubah UI/UX.
3. `HANDOFF.md` — status operasional terbaru dan catatan sesi terakhir.
4. `MASTERPLAN_v8.2.md` — **FINAL**: visi, mindmap, ERD, business rules, roadmap build dari 0.
5. `MASTERPROMPT_v8.2.md` — **FINAL**: blueprint rebuild dari 0 (schema penuh, model, RBAC, logic, build order).
6. `LOGIC_MAP.md` — peta hak akses (RBAC 2-lapis) & workflow dari kode aktual.
7. `SYSTEM_AUDIT.md` — peta teknis (route→controller, ERD, bug/risk).
8. `UpgradePlan.md` — backlog upgrade produk yang sudah dikumpulkan dari review terakhir.

> **v8.2 = sumber kebenaran tunggal.** Semua dokumen versi lama (v7.8, v8.0, v8.1) sudah DIHAPUS agar rapi. Jika ragu, validasi langsung ke kode.

---

## ✅ Status sekarang: Sesi terakhir — 18 Juni 2026

### Commit terakhir (sudah di-push ke `main`)
```
a9f12d6 feat: tambah target KPI produk dan rapikan dashboard role
a326196 fix: pisahkan modal assign dari register vehicle
143bd6e fix: perbaiki tombol assign fulfill fleet
ce9523b fix: perbaiki billing subscription dan hitungan alokasi supir
56faa68 docs: update HANDOFF.md and create LOGIC_MAP.md reflecting latest architecture and workflows
```

### Yang dikerjakan sesi 16–17 Juni 2026:
**Perbaikan Bug & Sinkronisasi Workflow Alokasi Pool (16 Juni):**

| Modul | Perubahan |
|------|-----------|
| **Fleet / Assign-Fulfill Modal** | Modal Assign/Fulfill dipisahkan dari wrapper modal Register Vehicle (`showCreateModal`) — sebelumnya nested sehingga tersembunyi. Ditambah test regresi `RoleBugFixTest`. |
| **Fleet / Alpine State** | Reaktivitas Alpine (`showAssignModal`) diperbaiki dengan deep clone, pencegahan event bubbling, dan `x-show` agar state tidak tertahan proxy. |
| **Pool Logic (RBAC)** | User pool hanya dapat memilih kendaraan dan supir dari pool sendiri saat alokasi di Pending Assignments. |
| **Operational & Long Term** | Alur WON khusus Mobil Long Term + supir disempurnakan. Pending Assignment = titik masuk operasional/pool. |
| **Finance / Subscription** | Route POST manual billing `subscriptions.billing.run` ditambahkan + CSRF + pengecekan role. |
| **Dashboard Allocation Count** | Hitungan supir memakai helper `requiredDriverQty()`, tidak tercampur jumlah kendaraan. |

**Perbaikan tambahan (17 Juni) — sudah di-push:**

| Modul | Perubahan |
|------|-----------|
| **Pipeline Kanban — Nilai Rupiah drag** | `moveStage()` sekarang return `summary` (count + total per stage dari SQL). Frontend update count DAN nilai Rupiah via `data-value-badge` + `data-col-value` attribute. Sebelumnya Rupiah tidak berubah saat drag. |
| **Pipeline — Table View toggle** | Toggle Kanban/Table sekarang in-place (pakai `#pipeline-table-view` + `x-show`) — sebelumnya redirect ke `/opportunities`. |
| **Pipeline — Stage header colors** | Warna header kolom pipeline diperbaiki untuk dark/light mode contrast (`.stage-pro`, `.stage-prop`, `.stage-neg` dll via `html.dark`/`html.light`). |
| **MassiveVehicleBookingSeeder** | Fix kolom Booking (`pickup_datetime`, `dropoff_datetime`, `destination`, `created_by`) dan Voucher (`voucher_code`, `title`, `denomination`, dll) agar match fillable model. |
| **Dockerfile** | CMD ditambah background seed `MassiveVehicleBookingSeeder` agar auto-run tiap deploy. |
| **Session / CSRF** | `render.yaml`: `SESSION_DRIVER` file → `cookie` + `SESSION_ENCRYPT=true` + `SESSION_SECURE_COOKIE=true` (fix 419 Page Expired di Render). |

**Upgrade dashboard & KPI (18 Juni) — sudah di-push:**

| Modul | Perubahan |
|------|-----------|
| **KPI Target GM/Manager** | Halaman KPI sekarang punya form Set Sales Targets per Sales Representative + bulan target + 6 target produk: Mobil Short, Bis Short, E-Voucher, Mobil Long, Bis Long, Supir. Total target dihitung otomatis dan disimpan ke `sales_targets`. |
| **Manager Dashboard** | Revenue Trend tim punya range Hari/Minggu/Bulan/3 Bulan/6 Bulan. Pipeline per Sales memakai nilai pipeline + stage breakdown. KPI Tim diganti ke kartu visual per sales dengan target, revenue, progress, status, win rate, dan won count. |
| **Sales Dashboard** | Kartu Monthly Target menampilkan sumber target KPI, dan Opportunities Funnel diberi konteks bahwa datanya adalah jumlah opportunity milik sales per stage. |
| **Quick Add Sidebar** | Menu Tambah Baru dibatasi sesuai role agar tidak menampilkan aksi yang tidak tersambung untuk role yang tidak boleh membuat data tersebut. |

### Konfigurasi tambahan:
- Test terakhir yang dicatat: `php artisan test` → **202 passed (547 assertions)**.
- `VERIFIKASI_TAHAP_A.md` sudah dipensiunkan karena isinya verifikasi awal Tahap A dan tidak lagi menjadi panduan runtime utama.

---

## 🔒 Aturan main (TIDAK BOLEH dilanggar)
1. **UI/UX dashboard depan terkunci** — lihat `UI_UX_LOCK.md`. Boleh ganti data hardcoded → dinamis, TIDAK boleh ubah warna/layout/font/struktur.
2. Semua perubahan di layer data/controller/service. Field form boleh nambah, layout jangan.
3. Tidak ada library/framework UI baru.
4. Folder `RevisiTMPbyAG/` ABAIKAN — repo aktif hanya `golden-bird-crm/`.

---

## 🎯 Keputusan yang sudah dikunci (jangan ditawar ulang)
- **Multi-produk per deal** → kolom **JSON `products`** di tabel `opportunities` (array of {product_id, name, category, kpi_key, qty, price, note}). ⚠️ BUKAN tabel `opportunity_items` — rencana tabel relasional sudah DIBATALKAN.
- **6 target KPI per produk + 1 total** → 6+6 kolom di `sales_targets` (rupiah).
- **Akses pipeline**: Sales = buat/isi/geser kartu. GM = view only (tidak bisa geser). Manager = view semua tim + approval.
- **Approval Manager** untuk: (a) diskon, (b) perubahan ANGKA pada deal di stage yang sama. Geser stage tidak butuh approval.
- **6 stage**: Call/Meeting → Prospecting → Proposal → Negotiation → Won / Lost.
- **Deal baru** selalu mulai di stage Call/Meeting.
- **Warna pipeline**: gradasi biru brand `#1468a8` (makin gelap = makin dekat closing); Won hijau, Lost merah. (Halaman pipeline BUKAN bagian yang dikunci.)

---

## 📂 Struktur Dashboard Views
```
resources/views/dashboard/
├── all.blade.php           ← routing semua role
├── charts.blade.php        ← komponen chart reusable
├── director.blade.php      ← (legacy, director sudah dihapus)
├── finance.blade.php       ← dashboard finance
├── gm.blade.php            ← Command Center GM (terbesar, 42KB)
├── manager.blade.php       ← dashboard manager
├── operational.blade.php   ← dashboard operational
└── sales.blade.php         ← dashboard sales (funnel, revenue)
```

---

## ➡️ Langkah berikutnya
1. Jika user melaporkan bug Assign/Fulfill masih muncul di production, cek dulu apakah server sudah deploy commit `a326196` dan jalankan clear cache/view (`php artisan optimize:clear`) di environment server.
2. Jika muncul artefak build duplikat baru (`public/build/* 2.*`), bersihkan sebelum commit kecuali memang dibutuhkan deploy.
3. Validasi workflow Fleet/Pool lewat browser/manual test: klik Assign/Fulfill dari kartu Pending Assignment tanpa membuka Register Vehicle lebih dulu.
4. Jika lanjut ke dokumen masterplan/masterprompt, baca `MASTERPLAN_v8.2.md` + `MASTERPROMPT_v8.2.md` — v8.2 adalah sumber kebenaran tunggal.

---

## 🧰 Skill yang disarankan untuk sesi berikutnya
- **debugger** — untuk debugging error yang muncul saat implementasi.
- **systematic-debugging** — pendekatan terstruktur jika ada bug kompleks.
- **code-reviewer** — review kode sebelum commit.
- **concise-planning** — buat checklist implementasi yang jelas dan atomic.
- **github** — manajemen PR/issue via CLI.

---

## 🚀 Commit/deploy
```bash
cd golden-bird-crm
git add -A
git commit -m "deskripsi perubahan dalam bahasa Indonesia"
git push origin main
```
Catatan: jangan commit folder `/master` (sudah di .gitignore).

---

## 🔗 Info tambahan
- **Remote:** `https://github.com/adith92/BBCRMrevisi.git`
- **Branch aktif:** `main`
- **Production URL:** https://gbcrmbycodex-production.up.railway.app
- **Bahasa komunikasi:** Bahasa Indonesia (sesuai preferensi user)
