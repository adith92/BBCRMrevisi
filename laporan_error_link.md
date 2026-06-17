# Rangkuman Temuan Link Error & Belum Terkoneksi (Broken Links)

Berdasarkan hasil audit dan pencarian di dalam kode (codebase), berikut adalah temuan beberapa link yang berpotensi menimbulkan error (404 Not Found) atau belum terkoneksi (placeholder), beserta penjelasan mengenai asal angka/ID yang digunakan:

## 1. Link Belum Terkoneksi (Placeholder `#`)

**Lokasi File:** `resources/views/drivers/show.blade.php` baris 42
**Kode Asli:**
```html
<a href="#" class="flex items-center gap-1 text-xs bg-gray-100/10 ...">
    <span class="material-symbols-outlined text-[14px]">edit</span> Edit Profil
</a>
```
**Penyebab & Sumber:**
- Link ini adalah tombol "Edit Profil" untuk Supir (Driver).
- Saat ini `href` hanya diisi `#` (placeholder), sehingga jika diklik tidak akan mengarah ke mana-mana (hanya kembali ke atas halaman).
- **Harusnya:** Mengarah ke halaman edit supir, misalnya `href="{{ route('drivers.edit', $driver->id) }}"`. Angka/ID ini berasal dari database tabel `drivers` (ID supir yang sedang dilihat profilnya).

## 2. Link Error (404 Not Found)

**Lokasi File:** `resources/views/kpi/index.blade.php` baris 391
**Kode Asli:**
```html
<a :href="'/pipeline/' + deal.id" class="font-bold text-cc-cyan hover:underline truncate" x-text="deal.title"></a>
```
**Penyebab & Sumber:**
- Saat daftar oportunitas/deal diklik di halaman KPI, link akan mengarah ke `/pipeline/123` (jika ID deal adalah 123).
- Namun di sistem route Laravel (`routes/web.php`), route `/pipeline` hanya diperuntukkan untuk halaman index kanban board (`Route::get('/pipeline', ...)`), bukan untuk detail per ID. Detail opportunity seharusnya menggunakan route `/opportunities/{id}`.
- **Hasil:** Jika diklik akan memunculkan error **404 Not Found**.
- **Sumber Angka (`deal.id`):** Angka ini didapat dari request API KPI Breakdown yang dipanggil via JavaScript (Alpine.js). API ini mengembalikan daftar Oportunitas (Deals) yang berkontribusi pada pencapaian KPI. ID ini merujuk pada `id` dari tabel `opportunities`.
- **Solusi:** Harus diubah menjadi `:href="'/opportunities/' + deal.id"`.

## 3. Route (Link API) yang Didefinisikan tapi Method-nya Hilang (Error 500)

Sesuai dengan catatan pada `SYSTEM_AUDIT.md`, ada dua route backend (link yang dipanggil di belakang layar) yang akan menyebabkan **Error 500 Internal Server Error** jika tak sengaja terpicu, karena fungsi di Controllernya sudah terhapus:

- **Link 1:** `POST /opportunities/{opportunity}/discount`
  - Didefinisikan di route, tapi method `OpportunityController@storeDiscount` tidak ada di controllernya.
  - **Sumber Angka:** `{opportunity}` adalah ID dari tabel `opportunities` saat user mencoba memberi diskon pada suatu deal.

- **Link 2:** `GET /api/opportunities/by-client/{client}`
  - Didefinisikan di route, tapi method `OpportunityController@byClient` tidak ada.
  - **Sumber Angka:** `{client}` adalah ID dari tabel `clients`. Link ini mungkin dulunya dipanggil oleh Javascript ketika memilih klien untuk mengambil daftar opportunity mereka.

---

### Kesimpulan untuk Perbaikan (Dapat di-copy ke AI / Tim Dev)
1. Perbaiki tombol "Edit Profil" di `drivers/show.blade.php` agar tidak menggunakan `#`.
2. Ganti link dinamis di `kpi/index.blade.php` dari `:href="'/pipeline/' + deal.id"` menjadi `:href="'/opportunities/' + deal.id"`.
3. Cek apakah fitur "Diskon Opportunity" dan pencarian "Opportunity by Client" masih digunakan di frontend. Jika masih, method-nya perlu dibuat ulang di `OpportunityController`. Jika tidak, route-nya sebaiknya dihapus agar tidak menjadi celah error.
