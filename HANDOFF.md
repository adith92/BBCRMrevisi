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

## ✅ Status sekarang: Sesi terakhir — 14 Juni 2026

### Commit terakhir (sudah di-push ke `main`)
```
220540c Tambah tautan detail klien pada tampilan dasbor
b8281e6 Fix: Menyesuaikan warna teks legenda dan sumbu grafik untuk mendukung dark mode
5792b1e Fix: Menunda render grafik di dashboard setelah inisialisasi GridStack
8b5dfc3 feat: implementasi rincian KPI, filter dashboard GM, diagram interaktif, dan perbaikan otorisasi controller
2040a35 fix: beresin 6 temuan audit (bug routes, relasi mati, scoping, docs)
0641aaa fix: global search query error and restore short term / bus products with conditional WON assignment
172d69a feat: implement optional driver linking, pool role, and fleet long-term restriction
b1fd077 fix: adjust plate number text color contrast on fleet cards
ebc688e feat: implement logic synchronization and RBAC boundaries from BLUECRM
01d5dd7 Revert UI changes causing login break
```

### Yang dikerjakan sesi ini (commit `220540c`):
**Menambahkan tautan detail klien (link ke `clients.show`) pada 2 tampilan dashboard:**

| File | Perubahan |
|------|-----------|
| `resources/views/dashboard/operational.blade.php` | Baris 77: Nama klien di tabel booking aktif dibungkus `<a href="{{ route('clients.show', $booking->client->id) }}" ...>`. Baris 131-139: Nama klien di tabel unassigned won opportunities dibungkus dengan link serupa (termasuk penanganan null). |
| `resources/views/dashboard/manager.blade.php` | Baris 140: Nama klien di sidebar aktivitas terbaru dibungkus link ke `clients.show`. |
| `resources/views/dashboard/finance.blade.php` | Sudah memiliki link ke `clients.show` (tidak diubah, hanya diverifikasi konsistensinya). |

### Konfigurasi git yang diubah:
- `http.postBuffer` diset ke `524288000` (500 MB) untuk mengatasi masalah timeout saat push.

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
