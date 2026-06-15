# Changelog 15 Jun 2026

Berikut adalah rangkuman dari seluruh perbaikan yang telah dilakukan pada modul Sales Pipeline dan UI Golden Bird CRM hari ini:

## 1. Perbaikan *Eager Loading* di Pipeline Board (Issue Checklist & Detail Hilang)
- **Problem:** Fitur *Carry Over* tidak berfungsi dengan baik. Kendaraan dan supir yang dipilih pada tahap *Proposal* hilang saat berpindah ke *Negotiation*, dan detail kendaraan serta supir tidak muncul di riwayat.
- **Penyebab Utama:** 
  1. API `/api/vehicles/available` menyembunyikan supir/kendaraan yang statusnya tidak *available*, padahal entitas tersebut sedang di-*assign* di kesempatan (opportunity) yang bersangkutan.
  2. Saat *Opportunity* disimpan/diperbarui, backend (`OpportunityController@update`) melakukan `fresh()` yang membuang data relasi *eager loaded* (*assigned_vehicles*, *assigned_drivers*) dari respons JSON. Sehingga saat modal baru dibuka, UI menerima array kosong.
- **Solusi:** 
  - Memperbarui `PipelineController@index` untuk memuat *assignedDrivers* dan *assignedVehicles* beserta kolom relasi penting secara *eager loading*.
  - Mengubah logika ketersediaan di `FleetController` dengan membolehkan pengambilan data apabila `assigned_opportunity_id` sama dengan ID oportunitas yang sedang diedit (`orWhere`).
  - Mengganti kembalian pada `OpportunityController@update` dan `advanceStage` untuk menggunakan `refresh()->load(...)` alih-alih `fresh()`, memastikan relasi diikutsertakan di *payload* balasan untuk *frontend*.

## 2. Penyesuaian Status Fleet & Driver
- **Problem:** Terdapat perbedaan antara ekspektasi status kendaraan dan supir dengan implementasi sistem saat tahap negosiasi.
- **Penyebab:** Sebelumnya kendaraan diset menjadi `booked` di tahap *Proposal/Negotiation*, sedangkan instruksi mengharuskan menjadi `reserved`.
- **Solusi:** Menyeragamkan logika di `OpportunityController.php` menjadi status `reserved` untuk *Fleet* (kendaraan) dan *Driver* (supir) pada saat *Proposal / Negotiation*. Saat berubah menjadi *Won*, status otomatis diset ke `assigned`.

## 3. UI/UX: Kontras Kotak Teks (Text Box)
- **Problem:** Teks di dalam *text box* "Details / Note" tidak terbaca karena warna latar teks berubah menjadi putih.
- **Penyebab:** Pada `resources/views/pipeline/index.blade.php`, atribut `bg-[var(--cc-modal-bg)]/50` digunakan. Karena variabel `--cc-modal-bg` adalah Hex Code, menambahkan *opacity* `/50` membuatnya menjadi CSS tidak valid (*invalid*). Alhasil, *browser* mengatur kotak menjadi warna *default* bawaan (putih).
- **Solusi:** Menghapus efek transparan `/50` dan menggantinya dengan atribut yang lebih stabil yakni `bg-[var(--cc-modal-bg)]` berpadu dengan *border* `var(--cc-border)`.

## 4. Perbaikan Menu Role Operations
- **Problem:** Menu dropdown/aksi di halaman Role Operations tidak bisa di-*klik*.
- **Penyebab Utama:** *Z-index* tumpang tindih (*overlapping*), *event bubbling* dari *Alpine.js* yang di-*block*, atau kontainer transparan (*backdrop*) yang menutupi aksi klik. (Bug ini telah diverifikasi dan diselesaikan di awal sesi sehingga semua menu kini dapat berfungsi kembali secara reaktif).

---
*Catatan:* Semua perubahan di atas telah berhasil diaplikasikan dengan stabil dan *push* ke repositori lokal. Anda dapat menggabungkan *file* Markdown ini langsung ke *MasterPrompt* atau *MasterPlan MD*.
