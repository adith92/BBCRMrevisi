# HANDOFF — Golden Bird CRM (Baca ini dulu)

> Untuk AI manapun (Codex / Antigravity / dll) yang melanjutkan project ini.
> Stack: Laravel 12 + Blade + Tailwind + Chart.js. DB: SQLite (lokal), Postgres (prod).

---

## 📚 Urutan baca dokumen (WAJIB)
1. `CLAUDE.md` — konteks project.
2. `UI_UX_LOCK.md` — 🔒 tampilan dashboard depan TERKUNCI. Jangan ubah UI/UX.
3. `MASTERPLAN_v8.0.md` — **dokumen FINAL**: visi, mindmap, RBAC, business workflow, apa yang sudah dibangun.
4. `MASTERPROMPT_v8.0.md` — **dokumen FINAL**: persona AI, rules bisnis, model DB, rebuild dari 0, deploy.
5. `LOGIC_MAP.md` — peta hak akses & workflow dari kode aktual.
6. `RENCANA_IMPLEMENTASI_Sales_Pipeline.md` — keputusan & roadmap Tahap A–E (referensi historis).
7. `VERIFIKASI_TAHAP_A.md` — cara verifikasi runtime.

> v8.0 adalah sumber kebenaran terbaru. Jika ada konflik dengan dokumen lama (v7.8), ikuti v8.0.

---

## ✅ Status sekarang: Sesi terakhir — 16 Juni 2026

### Commit terakhir (sudah di-push ke `main`)
```
e16ccbe fix: alpine js reactivity bug for assign modal
2c50188 feat: implement pool role fleet & driver assignment and fulfillment logic for won opportunities
563cc13 Fix Alpine.js reactivity and event bubbling for Assign Modal
c000671 Fix Alpine.js reactivity issue where async openAssignModal swallowed DOM updates
6c6f751 Fix Alpine initialization race condition and script tag placement causing Assign Remaining button to fail
8cc1b45 Fix Alpine.js rendering and allocation functionality on Fleet index page
0a39a3e Fix 403 authorization for operational role, add Approval Pending tab with sorting, auto activity logs, and display assignments on Opportunity detail page
3b2b4c5 fix: sync fleet ops filters with status calculations
1956079 fix: ensure relations are returned on update and sync operational target status to reserved
395c3b8 fix: resolve fleet index syntax error, fix eager loading keys, add vehicle/driver details to history, and fix title contrast in dark mode
```

### Yang dikerjakan sesi ini:
**Perbaikan Bug & Implementasi Workflow Alokasi Pool:**

| Modul | Perubahan |
|------|-----------|
| **Fleet / Assign Vehicle** | Memperbaiki tuntas bug reaktivitas Alpine.js (`showAssignModal`) pada halaman Fleet yang menyebabkan tombol Assign / Fulfill tidak memunculkan modal alokasi. Diatasi dengan deep cloning proksi objek reaktif dan `Alpine.nextTick`. |
| **Pool Logic (RBAC)** | Menerapkan `pool_id` pada role Pool. User pool (misal: Pool Jakarta / Pool Surabaya) sekarang **hanya dapat memilih kendaraan dan supir** yang berasal dari pool mereka sendiri saat melakukan alokasi pada tabel *Pending Assignments*. |
| **Operational & Long Term** | Menyempurnakan alur opportunity yang `WON` khusus untuk produk **Mobil Long Term** dan integrasi supirnya. Menambahkan tab *Approval Pending* pada module operational serta fitur sinkronisasi dengan status *reserved*. |

### Konfigurasi tambahan:
- Struktur otorisasi pada Controller (terutama Operational & Fleet) telah dirapihkan sehingga tidak memunculkan Error 403.
- Telah dibuatkan dokumen `LOGIC_MAP.md` untuk memetakan alur hak akses dan workflow dari bisnis saat ini.

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

## ➡️ Langkah berikutnya: TAHAP B
1. Tambah stage `call_meeting` di `PipelineService` ($allStages + $transitions) & migration comment opportunities.
2. Deal baru: stage dikunci ke `call_meeting`; tambah pilihan Call/Meeting + note.
3. Scope view pipeline: Sales=sendiri, Manager=semua tim, GM=semua (view only).
4. Guard write pipeline: hanya Sales pemilik (`sales_id === auth id`). GM/Manager → 403 untuk store/update/moveStage.
5. Terapkan warna pipeline gradasi biru di `resources/views/pipeline/index.blade.php`.

Lalu Tahap C (opportunity_items + stage history), D (KPI per produk), E (test). Detail penuh di `RENCANA_IMPLEMENTASI_Sales_Pipeline.md`.

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
