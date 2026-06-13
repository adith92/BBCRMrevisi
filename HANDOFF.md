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

## ✅ Status sekarang: Tahap A SELESAI (belum diverifikasi runtime)
Yang sudah dikerjakan:
- Role **Director dihapus total** → wewenang pindah ke **GM** (approver tertinggi = GM/level 2).
- **Hierarki**: 1 GM → 5 Manager → 15 Sales (via `manager_id`).
- **6 produk fix** + kolom `kpi_key`: mobil_short, bis_short, evoucher, mobil_long, bis_long, supir.
- **Guard Client**: create/edit hanya role `sales`; GM/Manager/Finance view-only.
- Test diperbarui ke desain baru.
- 2 migrasi baru: `2026_06_09_000001` (kpi_key), `2026_06_09_000002` (remove director).

⚠️ **WAJIB jalankan dulu** sebelum lanjut: `php artisan migrate:fresh --seed` lalu `php artisan test`. Detail di `VERIFIKASI_TAHAP_A.md`.

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

## ➡️ Langkah berikutnya: TAHAP B
1. Tambah stage `call_meeting` di `PipelineService` ($allStages + $transitions) & migration comment opportunities.
2. Deal baru: stage dikunci ke `call_meeting`; tambah pilihan Call/Meeting + note.
3. Scope view pipeline: Sales=sendiri, Manager=semua tim, GM=semua (view only).
4. Guard write pipeline: hanya Sales pemilik (`sales_id === auth id`). GM/Manager → 403 untuk store/update/moveStage.
5. Terapkan warna pipeline gradasi biru di `resources/views/pipeline/index.blade.php`.

Lalu Tahap C (opportunity_items + stage history), D (KPI per produk), E (test). Detail penuh di `RENCANA_IMPLEMENTASI_Sales_Pipeline.md`.

---

## 🚀 Commit/deploy
```bash
cd golden-bird-crm
git add -A
git commit -m "Tahap A: hapus director, hierarki 5x3, 6 produk, guard client, UI lock + handoff"
git push origin main
```
Catatan: jangan commit folder `/master` (sudah di .gitignore).
