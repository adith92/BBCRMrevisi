# Changelog — 15 Juni 2026

Rangkuman seluruh perubahan yang dikerjakan pada tanggal 15 Juni 2026, dikelompokkan berdasarkan area fungsional.

---

## 🚀 Deployment & Infrastructure (Dini Hari)

| Waktu | Perubahan |
|-------|-----------|
| 03:25 | Setup auto-deploy CapRover via GitHub Actions |
| 04:10 | Fix pembuatan tarball deploy |
| 04:12 | Fix indentasi YAML pada GitHub Actions workflow |
| 04:13 | Fix path tarball agar dibuat di luar workspace |
| 04:18 | Retry deploy CapRover |

---

## 🔐 Auth & Seeder (Siang)

| Waktu | Perubahan |
|-------|-----------|
| 14:17 | Seragamkan semua password seeder menjadi `password123` dan hapus diskrepansi pada login form |
| 14:42 | Jalankan `db:seed` otomatis saat container startup agar data demo selalu tersedia |

---

## 📊 Sales Pipeline — Fitur Baru

| Waktu | Perubahan |
|-------|-----------|
| 15:27 | Tambah field **Final Value** dan **Contract Duration (Months)** pada modal Won stage |
| 15:32 | Fix generate `opp_number` agar mengabaikan format seeder dan mengambil sequence yang benar |
| 16:04 | Tampilkan semua kendaraan yang tersedia (hapus filter brand yang membatasi) |
| 16:05 | Ganti elemen `<select>` pada modal Products dengan custom Alpine.js dropdown bergaya DavidUI |
| 21:24 | Konsolidasi alokasi Armada & Supir: buat opsional di tahap Proposal/Negotiation, filter hanya Mobil Long Term |
| 21:33 | Batasi jumlah armada sesuai quantity produk; perbaiki bug uncheck pada supir; izinkan assignment di Proposal/Negotiation |

---

## 🎨 UI/UX — Tampilan & Navigasi

| Waktu | Perubahan |
|-------|-----------|
| 15:58 | Ganti tombol Cancel/Close dengan styling custom **Go Back** |
| 17:21 | Implementasi **sidebar collapsible** dan animasi custom BackButton |
| 17:23 | Ganti breadcrumb styling dengan ikon SVG dan warna indigo |
| 17:36 | Tambah halaman **Show** dan **Edit** yang hilang untuk Products |
| 17:41 | Tambah modal penjelasan (info icon) untuk metrik tim di Dashboard |

---

## 📈 Dashboard — Widget & GridStack

| Waktu | Perubahan |
|-------|-----------|
| 18:43 | Tambah komponen React: **FleetLeague** dan **WeeklyRevenueChart** |
| 19:14 | Optimasi GridStack: responsive breakpoints, auto-save dengan debounce, bersihkan duplikasi inisialisasi |

---

## 🐛 Bug Fix — Pipeline & Operasional (Malam)

### Hak Akses & Data Klien
| Waktu | Perubahan |
|-------|-----------|
| 21:47 | Buka akses **detail klien** untuk semua Sales yang di-assign (bukan hanya PIC), sehingga jumlah Won/Deals di halaman Client dan Detail-nya sinkron |
| 21:56 | Hapus filter `sales_id` pada dropdown Perusahaan di Pipeline agar semua klien aktif tampil |

### Modal Operasional
| Waktu | Perubahan |
|-------|-----------|
| 21:47 | Tambah modal popup untuk **Register Driver** dan **Register Vehicle** pada halaman Operations (sebelumnya tombol tidak berfungsi / crash) |
| 21:47 | Hapus link **Armada** dan **Maintenance** dari menu "Tambah Baru" di sidebar (route tidak ada, menyebabkan error) |

### Filter & Status Supir/Armada
| Waktu | Perubahan |
|-------|-----------|
| 21:56 | Fix case-sensitivity pada status driver (`Available` vs `available`) dan vehicle (`Rent_out` vs `booked`) di OpportunityController |
| 22:10 | Pertahankan pilihan Supir & Armada saat pindah stage (Proposal → Negotiation): API kini menyertakan unit yang sudah ter-assign ke Opportunity yang sedang diedit |
| 22:10 | Fix warna teks pada textbox **"Details / Note"** yang tidak terbaca (putih di atas putih) — sekarang menggunakan background solid sesuai tema |

### Business Rule: Status Lifecycle
| Waktu | Perubahan |
|-------|-----------|
| 22:13 | **Aturan baru diterapkan:** Supir/Armada yang dipilih di tahap **Proposal** kini langsung berstatus `reserved`/`booked` (sebelumnya tetap `available`). Status berubah ke `assigned` hanya saat **Won**. Pembatalan ke tahap sebelumnya (Prospecting/Call/Lost) mengembalikan status ke `available` |

---

## File yang Dimodifikasi (Ringkasan)

| File | Jenis Perubahan |
|------|-----------------|
| `app/Http/Controllers/PipelineController.php` | Eager load drivers/vehicles, hapus filter sales_id pada clients |
| `app/Http/Controllers/OpportunityController.php` | Status lifecycle (reserved/booked/assigned), case fix, validation |
| `app/Http/Controllers/FleetController.php` | API availability + opportunity_id parameter |
| `app/Http/Controllers/ClientController.php` | Buka akses detail klien untuk assigned sales |
| `resources/views/pipeline/index.blade.php` | UI fixes: textbox contrast, checklist persistence, dropdown, fleet limit |
| `resources/views/components/sidebar.blade.php` | Hapus broken links (Armada & Maintenance) |
| `resources/views/fleet/index.blade.php` | Tambah modal Register Driver & Register Vehicle |
| `resources/views/dashboard/*.blade.php` | Widget baru, GridStack optimization |
| `.github/workflows/deploy.yml` | CapRover auto-deploy setup |
| `database/seeders/*` | Seragamkan password |

---

> **Total Commits Hari Ini:** 24 commits  
> **Kategori:** 5 Infrastructure · 2 Auth/Seeder · 6 Fitur Baru · 5 UI/UX · 6 Bug Fix
