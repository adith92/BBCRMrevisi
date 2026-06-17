# LOGIC MAP — Golden Bird CRM (Peta Hak Akses & Workflow)

> **Versi:** Update 16 Juni 2026 malam
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

## 1A. UI/UX Boundary Yang Wajib Dijaga

- Dashboard depan/Command Center mengikuti `UI_UX_LOCK.md`; jangan ubah warna, layout, font, struktur visual, atau pola interaksi tanpa instruksi eksplisit dari user.
- Perubahan yang diperbolehkan untuk area dashboard terkunci: data dibuat dinamis, link diperbaiki, query/backend diperbaiki, dan bug state diperbaiki.
- Perubahan visual untuk halaman operasional/fleet boleh dilakukan hanya jika memang diminta; untuk bug fix Assign/Fulfill terakhir, perbaikan harus tetap fokus pada struktur/state modal, bukan desain.

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
2. **Kebutuhan Alokasi:** Untuk mobil long term, dibutuhkan spesifikasi pengalokasian armada (Vehicles) dan supir (Drivers). Jumlah kendaraan dihitung dari helper kebutuhan kendaraan, sementara jumlah supir dihitung dari helper kebutuhan supir; dua angka ini tidak boleh saling tertukar.
3. **Aturan Assignment per Role:**
   - **`operational`**: Dapat mengalokasikan armada/supir manapun yang tersedia (Global).
   - **`pool`**: Hanya dapat memproses *Assign / Fulfill* menggunakan aset (kendaraan & supir) yang terdaftar di database **Pool miliknya** (didasarkan pada `pool_id` dari user login).
4. **Driver Linking:** Supir dikaitkan dengan kendaraan tertentu, mempermudah manajemen bahwa supir A sedang bertugas membawa kendaraan X.
5. **Perubahan Status Armada:** 
   - Ketika dialokasikan, status armada berubah menjadi `reserved` / `in_use` dan kolom `opportunity_id` pada tabel vehicle terisi.
   - Saat alokasi dicabut/dilepas, armada kembali berstatus `available` dan `opportunity_id` kembali menjadi NULL.
6. **Keamanan Transaksi (DB Transaction):** Proses assignment berjalan di dalam skema perlindungan ACID (transaksi *database*). Jika terdapat error/konflik *race condition* (contoh: 2 user berebut mobil yang sama pada detik yang sama), transaksi digagalkan untuk mencegah inkonsistensi data.

### Catatan Teknis Modal Assign/Fulfill:
- Tombol **Assign / Fulfill** harus membuka modal alokasi langsung dari kartu Pending Assignment.
- Root cause bug terakhir: modal alokasi pernah terletak di dalam wrapper modal **Register Vehicle** (`showCreateModal`), sehingga state `showAssignModal=true` tidak terlihat sampai Register Vehicle dibuka.
- Aturan struktur: elemen modal dengan `x-show="showAssignModal"` tidak boleh menjadi turunan elemen modal Register Vehicle dengan `x-show="showCreateModal"`.
- Test regresi terkait ada di `tests/Feature/RoleBugFixTest.php` dengan skenario `assign_modal_is_not_nested_inside_register_vehicle_modal`.

---

## 4. Target & Revenue Logic

- Target pendapatan ditetapkan setiap awal bulan.
- Opportunity yang berstatus **WON** akan memberikan kontribusi langsung ke persentase pencapaian (Achievement Target).
- Filter data memungkinkan role pimpinan (GM / Manager) untuk menganalisis Breakdown Revenue (Pemasukan) berdasarkan produk, cabang, rentang waktu tertentu, serta performa per masing-masing sales di bawahnya.

---

## 5. Finance & Subscription Billing

- Manual billing subscription memakai route POST bernama `subscriptions.billing.run`.
- Aksi billing harus memakai CSRF dan role gate yang valid; jangan memakai directive authorization yang tidak tersedia di Blade.
- Role utama yang boleh menjalankan billing manual: `finance`, `gm`, atau role lain yang memang sudah diberi izin eksplisit di controller.

---

## 6. Deployment & Cache Notes

- Jika bug sudah fix di git tetapi production masih menampilkan perilaku lama, cek dulu commit yang sedang berjalan di server.
- Untuk Laravel/Blade, hard refresh browser tidak cukup jika server masih memakai compiled view lama. Jalankan clear cache/view di server setelah deploy bila gejalanya tetap sama.
- Artefak build duplikat seperti `public/build/* 2.*` tidak termasuk logic bisnis. Cleanup harus dilakukan terpisah dan tidak boleh dicampur dengan fix backend/frontend logic tanpa persetujuan user.
