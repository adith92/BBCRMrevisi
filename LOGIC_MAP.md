# LOGIC MAP — Golden Bird CRM (Peta Hak Akses & Workflow)

> **Versi:** Update 16 Juni 2026
> **Dokumen ini:** Memetakan alur logika bisnis, hak akses (RBAC), dan *workflow* dari sistem Golden Bird CRM berdasarkan kode yang berjalan.

---

## 1. Role-Based Access Control (RBAC) & Boundary

Sistem ini memiliki **6 Role Utama**, masing-masing dengan batasan (boundary) tersendiri.

| Role | Boundary (Batasan Scope) | Fitur / Akses Utama |
|---|---|---|
| **`gm`** (General Manager) | Global (Melihat seluruh data di sistem) | • Memiliki wewenang Penuh, *kecuali* mengubah Opportunity (hanya View-Only).<br>• Menyetujui semua form Approval.<br>• Bisa merubah KPI/Target tiap tim. |
| **`manager`** (Manager) | Tim (Hanya tim di bawah naungannya) | • Melakukan Approval diskon / perubahan angka deal di tim-nya.<br>• View Dashboard, Analytics, dan Pipeline milik seluruh tim-nya. |
| **`sales`** (Sales Officer) | Pribadi (Hanya data miliknya) | • Mengelola *Opportunity* dari awal sampai WON/LOST.<br>• **Pemilik Absolut** Pipeline-nya sendiri (GM/Manager tidak bisa geser stage). |
| **`operational`** | Fleet Management (Operasional Harian) | • Tidak bisa melihat harga/deal.<br>• Melihat *Pending Assignments* untuk mengelola penugasan.<br>• Bisa melakukan alokasi dan membuat activity log armada. |
| **`pool`** (Pool Officer) | Pool Spesifik (Data khusus Pool-nya) | • Mengelola alokasi armada dan supir yang **hanya berasal dari Pool miliknya** (misal Pool Jakarta).<br>• Tidak bisa mengatur kendaraan/supir pool lain. |
| **`finance`** (Finance) | Keuangan / Invoicing | • Mengelola data billing, payment, subscriptions, tax.<br>• View Dashboard spesifik keuangan. |

---

## 2. Workflow: Sales Pipeline (Peluang / Opportunity)

Alur kerja setiap Opportunity (Peluang) diatur oleh `PipelineService`.

### Aturan Dasar:
1. **Titik Awal (Start):** Semua opportunity baru selalu dimulai dari stage **`call_meeting`** (Call/Meeting).
2. **Kewenangan Pindah Stage:** Hanya pemilik (Sales yang bersangkutan) yang dapat menggeser stage deal-nya.
3. **Approval Required (Persetujuan Dibutuhkan):** Jika terdapat **Diskon** atau **Perubahan Angka (Harga/Nilai)** pada saat deal berada di stage yang sama, maka sistem akan mengunci deal ke mode `approval_pending` dan membutuhkan *Approval* dari Manager. *Catatan: Hanya sekedar pindah/geser stage tidak membutuhkan approval.*

### Tahapan Pipeline (Stages):
- **Call/Meeting** (Mulai)
- **Prospecting**
- **Proposal**
- **Negotiation**
- **Won** (Berhasil) / **Lost** (Gagal)

---

## 3. Workflow: Fleet, Pool, & Long-Term Fulfillment

Ketika sebuah Opportunity (dengan tipe produk tertentu seperti **Mobil Long Term**) mencapai stage **WON**, maka operasional mengambil alih untuk pemenuhan alokasi (fulfillment).

### Alur Fulfillment (Alokasi Mobil & Supir):
1. **Trigger `WON`:** Saat Opportunity berstatus *WON*, data akan muncul di tabel **Pending Assignments** pada *Dashboard Operational* dan *Dashboard Pool*.
2. **Kebutuhan Alokasi:** Untuk mobil long term, dibutuhkan spesifikasi pengalokasian armada (Vehicles) dan supir (Drivers).
3. **Aturan Assignment per Role:**
   - **`operational`**: Dapat mengalokasikan armada/supir manapun yang tersedia (Global).
   - **`pool`**: Hanya dapat memproses *Assign / Fulfill* menggunakan aset (kendaraan & supir) yang terdaftar di database **Pool miliknya** (didasarkan pada `pool_id` dari user login).
4. **Driver Linking:** Supir dikaitkan dengan kendaraan tertentu, mempermudah manajemen bahwa supir A sedang bertugas membawa kendaraan X.
5. **Perubahan Status Armada:** 
   - Ketika dialokasikan, status armada berubah menjadi `reserved` / `in_use` dan kolom `opportunity_id` pada tabel vehicle terisi.
   - Saat alokasi dicabut/dilepas, armada kembali berstatus `available` dan `opportunity_id` kembali menjadi NULL.
6. **Keamanan Transaksi (DB Transaction):** Proses assignment berjalan di dalam skema perlindungan ACID (transaksi *database*). Jika terdapat error/konflik *race condition* (contoh: 2 user berebut mobil yang sama pada detik yang sama), transaksi digagalkan untuk mencegah inkonsistensi data.

---

## 4. Target & Revenue Logic

- Target pendapatan ditetapkan setiap awal bulan.
- Opportunity yang berstatus **WON** akan memberikan kontribusi langsung ke persentase pencapaian (Achievement Target).
- Filter data memungkinkan role pimpinan (GM / Manager) untuk menganalisis Breakdown Revenue (Pemasukan) berdasarkan produk, cabang, rentang waktu tertentu, serta performa per masing-masing sales di bawahnya.
