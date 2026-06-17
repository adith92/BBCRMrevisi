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
8. `VERIFIKASI_TAHAP_A.md` — cara verifikasi runtime.

> **v8.2 = sumber kebenaran tunggal.** Semua dokumen versi lama (v7.8, v8.0, v8.1) sudah DIHAPUS agar rapi. Jika ragu, validasi langsung ke kode.

---

## ✅ Status sekarang: Sesi terakhir — 16 Juni 2026

### Commit terakhir (sudah di-push ke `main`)
```
a326196 fix: pisahkan modal assign dari register vehicle
143bd6e fix: perbaiki tombol assign fulfill fleet
ce9523b fix: perbaiki billing subscription dan hitungan alokasi supir
56faa68 docs: update HANDOFF.md and create LOGIC_MAP.md reflecting latest architecture and workflows
2c50188 feat: implement pool role fleet & driver assignment and fulfillment logic for won opportunities
```

### Yang dikerjakan sesi ini:
**Perbaikan Bug & Sinkronisasi Workflow Alokasi Pool:**

| Modul | Perubahan |
|------|-----------|
| **Fleet / Assign-Fulfill Modal** | Root cause terakhir ditemukan: modal Assign/Fulfill masih berada di dalam wrapper modal **Register Vehicle** (`showCreateModal`). Akibatnya klik Assign/Fulfill sudah mengubah state, tetapi modal tetap tersembunyi sampai Register Vehicle dibuka. Fix: modal Assign dipisahkan dari wrapper Register Vehicle dan ditambah test regresi agar tidak nested lagi. |
| **Fleet / Alpine State** | Sebelumnya juga diperbaiki reaktivitas Alpine (`showAssignModal`) dengan deep clone data opportunity, pencegahan event bubbling, dan render modal menggunakan `x-show` agar state tidak tertahan oleh proxy. |
| **Pool Logic (RBAC)** | Menerapkan `pool_id` pada role Pool. User pool (misal: Pool Jakarta / Pool Surabaya) sekarang **hanya dapat memilih kendaraan dan supir** yang berasal dari pool mereka sendiri saat melakukan alokasi pada tabel *Pending Assignments*. |
| **Operational & Long Term** | Menyempurnakan alur opportunity yang `WON` khusus untuk produk **Mobil Long Term** dan integrasi supirnya. Pending Assignment tetap menjadi titik masuk operasional/pool untuk memenuhi unit dan supir. |
| **Finance / Subscription** | Menambahkan route POST manual billing `subscriptions.billing.run` dan memperbaiki aksi billing agar memakai CSRF + pengecekan role yang valid. |
| **Dashboard Allocation Count** | Hitungan kebutuhan supir diperbaiki agar memakai helper `requiredDriverQty()` dan tidak tercampur dengan jumlah kendaraan. |

### Konfigurasi tambahan:
- Struktur otorisasi pada Controller (terutama Operational & Fleet) telah dirapihkan sehingga tidak memunculkan Error 403.
- `tests/Feature/RoleBugFixTest.php` sekarang punya regresi khusus untuk memastikan modal Assign/Fulfill tidak lagi berada di dalam modal Register Vehicle.
- Test terakhir yang dicatat pada sesi fix: `php artisan test` → **183 passed (492 assertions)**.
- Catatan working tree saat update dokumen ini: ada artefak build duplikat belum terlacak (`public/build/assets/app-DtbFpjiz 2.js`, `public/build/manifest 2.json`). Jangan commit otomatis sebelum dipastikan memang dibutuhkan.

---

## 🔒 Aturan main (TIDAK BOLEH dilanggar)
1. **UI/UX dashboard depan terkunci** — lihat `UI_UX_LOCK.md`. Boleh ganti data hardcoded → dinamis, TIDAK boleh ubah warna/layout/font/struktur.
2. Semua perubahan di layer data/controller/service. Field form boleh nambah, layout jangan.
3. Tidak ada library/framework UI baru.
4. Folder `RevisiTMPbyAG/` ABAIKAN — repo aktif hanya `golden-bird-crm/`.

---

## 🎯 Keputusan yang sudah dikunci (jangan ditawar ulang)
- **Multi-produk per deal** → tabel relasional `opportunity_items` (qty, harga, note per produk).
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
2. Lakukan cleanup artefak duplikat `public/build/* 2.*` hanya setelah user setuju.
3. Validasi workflow Fleet/Pool lewat browser/manual test: klik Assign/Fulfill dari kartu Pending Assignment tanpa membuka Register Vehicle lebih dulu.
4. Jika lanjut ke dokumen masterplan/masterprompt, sinkronkan dulu `MASTERPROMPT_v7.8.md` dengan `HANDOFF.md` + `LOGIC_MAP.md`.

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
