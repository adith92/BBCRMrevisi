# 🚀 Setup Kanban Pipeline v7.5 — Step by Step

## 1. Install npm dependencies

```bash
cd golden-bird-crm
npm install
```

## 2. Install Livewire (tidak wajib, tapi recommended untuk fitur lain)

```bash
composer require livewire/livewire
```

## 3. Build assets (development)

```bash
npm run dev
```

## 4. Build assets (production)

```bash
npm run build
```

## 5. Jalankan migration (tidak ada migration baru, tapi pastikan fresh)

```bash
php artisan migrate
```

## 6. Jalankan semua tests

```bash
./vendor/bin/pest
# atau
php artisan test
```

Expected output:
```
Tests:  120+ passed
Failures: 0
```

## 7. Smoke test manual

Buka browser, login, dan cek:
- [ ] /pipeline — Kanban board tampil dengan 5 kolom
- [ ] Drag card dari satu kolom ke kolom lain → toast sukses muncul
- [ ] Klik ikon edit (pensil) → modal edit terbuka
- [ ] Klik ikon 360° → modal 360 terbuka dengan 4 tab
- [ ] Drag ke kolom "Kalah" → dialog alasan kalah muncul
- [ ] Search box filter card secara real-time
- [ ] Filter dropdown sembunyikan kolom lain
- [ ] Mobile: board scroll horizontal, tidak layout-break

## 8. Deploy ke Railway/Render

### Render
```bash
# render.yaml sudah ada — tidak perlu ubah
git add -A
git commit -m "feat: Kanban Pipeline v7.5 with drag-drop, 360° view, Vite build"
git push origin main
```

### Railway
```bash
# Pastikan nixpacks.toml sudah include npm build
git add -A
git commit -m "feat: Kanban Pipeline v7.5"
git push railway main
```

### Environment variables yang diperlukan (production)
```
APP_ENV=production
APP_KEY=base64:... (generate dengan php artisan key:generate)
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://...
```

## 9. Verifikasi production

- [ ] Assets ter-compile (tidak ada CDN Tailwind error)
- [ ] /pipeline load < 2 detik
- [ ] Drag-drop berfungsi di production
- [ ] CSRF token tidak error (check meta tag di head)

---

## Fitur Kanban yang sudah live

| Fitur | Status |
|-------|--------|
| 5 kolom stage (Prospekting, Proposal, Negosiasi, Menang, Kalah) | ✅ |
| Drag & drop real-time + autosave ke DB | ✅ |
| Validasi transisi stage (pakai PipelineService existing) | ✅ |
| Dialog alasan kalah saat drag ke Lost | ✅ |
| Inline edit modal (title, nilai, close date, notes, pax) | ✅ |
| 360° view modal (4 tab: Info, Aktivitas, Approval, Data Terhubung) | ✅ |
| Activity log otomatis setiap perpindahan stage | ✅ |
| Toast notification sukses/error | ✅ |
| Search real-time (filter card by title/client) | ✅ |
| Filter per stage | ✅ |
| Summary bar (count + total value per stage) | ✅ |
| Role-scoped (sales hanya lihat deal sendiri) | ✅ |
| Responsive mobile (horizontal scroll) | ✅ |
| Vite compiled assets (tidak ada CDN) | ✅ |
| 20 test cases (unit + feature) | ✅ |
