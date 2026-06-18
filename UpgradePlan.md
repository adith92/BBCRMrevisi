# UpgradePlan: 4 Upgrade Fitur CRM (Final)

> Dibuat: 17 Juni 2026
> Update: 18 Juni 2026 — Tambahan UPGRADE 3 (GM Dashboard Cleanup) & UPGRADE 4 (Opportunities: Filter Manager + Sorting)
> Status: SIAP EKSEKUSI - tinggal ACC dan jalankan
> Berdasarkan sesi diskusi lengkap antara pemilik sistem dan AI

---

# DAFTAR UPGRADE

| #   | Upgrade                                | File Diubah | File Baru | Route Baru |
| --- | -------------------------------------- | ----------- | --------- | ---------- |
| 1   | Pending Assignment di Fleet & Driver   | 4           | 0         | 0          |
| 2   | Sales Performance Revamp               | 2           | 0         | 0          |
| 3   | GM Dashboard Cleanup (Header + Widget) | 4           | 0         | 0          |
| 4   | Opportunities: Filter Manager + Sorting| 2           | 0         | 0          |
| **TOTAL** |                                  | **12**      | **0**     | **0**      |

Semua upgrade TIDAK memerlukan:
- Tabel/migration baru
- Route baru
- Controller baru
- Library baru (semua pakai Chart.js + Alpine.js + Tailwind yang sudah ada)

---

# UPGRADE 1: Section "Pending Assignment" di Fleet & Driver

---

## Latar Belakang

Di dashboard GM ada widget: "Action Required: Fulfill Mobil Long Term Units & Supir"
Pemilik ingin: tiap halaman (Fleet dan Driver) punya section sendiri di bagian atas
untuk menampilkan opportunities yang belum di-assign unit/supir.
Bukan dijadikan 1 menu baru, tapi masing-masing di halaman yang relevan.

---

## Keputusan dari Diskusi

Q: Di mana section ini ditaruh?
A: Di bagian ATAS halaman, sebelum tabel utama Fleet/Driver

Q: Bisa assign langsung dari sana?
A: YA, ada tombol [Assign] yang bisa langsung assign tanpa pindah halaman

Q: Setelah assign, row-nya hilang?
A: TIDAK - row tetap muncul, status badge berubah jadi Fulfilled (hijau)

Q: Modal Fleet tampilkan apa?
A: Hanya pilihan VEHICLE saja (bukan vehicle + driver)

Q: Modal Driver tampilkan apa?
A: Hanya pilihan DRIVER saja (bukan vehicle + driver)

Q: Siapa yang bisa lihat section ini?
A: Hanya role OPERATIONAL dan POOL
   (GM dan Manager tidak perlu karena sudah ada di dashboard Command Center)

---

## Tampilan Section di Halaman Fleet

  +----------------------------------------------------------+
  | Opportunities Butuh Unit (12 pending)                    |
  +----------+---------------+-------+---------+------------+
  | Klien    | Kontrak       | Stage | Butuh   | Status     |
  +----------+---------------+-------+---------+------------+
  | PT X     | Shuttle 4 unit| Won   | 4 unit  | Pending [Assign]
  | PT Z     | Long Term 3   | Nego  | 3 unit  | Fulfilled
  | PT Y     | Charter 2     | Won   | 2 unit  | Pending [Assign]
  +----------+---------------+-------+---------+------------+

Klik [Assign] -> modal muncul -> checkbox pilih vehicle (status=available) -> Simpan
Hasil: badge berubah Fulfilled, tanpa refresh halaman

---

## Tampilan Section di Halaman Driver

  +----------------------------------------------------------+
  | Opportunities Butuh Supir (8 pending)                    |
  +----------+---------------+-------+---------+------------+
  | Klien    | Kontrak       | Stage | Butuh   | Status     |
  +----------+---------------+-------+---------+------------+
  | PT X     | Shuttle 4 org | Won   | 4 supir | Pending [Assign]
  | PT Z     | Long Term 3   | Nego  | 3 supir | Fulfilled
  +----------+---------------+-------+---------+------------+

Klik [Assign] -> modal muncul -> checkbox pilih driver (status=available) -> Simpan
Hasil: badge berubah Fulfilled, tanpa refresh halaman

---

## Catatan Teknis Penting

Masalah data: field "products" banyak yang kosong di database demo.
Solusi (sudah disepakati): gunakan field "pax" sebagai fallback.
Jika products kosong dan pax > 0 -> anggap butuh pax unit mobil/supir.

Endpoint assign yang dipakai (SUDAH ADA, tidak perlu buat baru):
  Fleet: POST /api/vehicles/assign-to-opportunity/{opportunity}
         kirim vehicle_ids[]
  Driver: POST /api/vehicles/assign-to-opportunity/{opportunity}
          kirim driver_ids[]
  (Satu endpoint yang sama sudah handle keduanya)

Relasi yang dipakai (SUDAH ADA di model):
  $opp->assignedVehicles -> hasMany(Vehicle, 'assigned_opportunity_id')
  $opp->assignedDrivers  -> hasMany(Driver,  'assigned_opportunity_id')

---

## File yang Diubah (Upgrade 1)

| No | File                                         | Aksi  | Keterangan                        |
|----|----------------------------------------------|-------|-----------------------------------|
| 1  | app/Http/Controllers/FleetController.php     | Edit  | Tambah query pending di index()   |
| 2  | resources/views/fleet/index.blade.php        | Edit  | Tambah section atas tabel         |
| 3  | app/Http/Controllers/DriverController.php    | Edit  | Tambah query pending di index()   |
| 4  | resources/views/drivers/index.blade.php      | Edit  | Tambah section atas tabel         |

Total: 4 file diedit, 0 file baru, 0 route baru.

---

## Checklist Eksekusi Upgrade 1

- [ ] 1. Edit FleetController@index - tambah $pendingFleet query
- [ ] 2. Edit fleet/index.blade.php - tambah section pending di atas tabel
- [ ] 3. Edit DriverController@index - tambah $pendingDriver query
- [ ] 4. Edit drivers/index.blade.php - tambah section pending di atas tabel
- [ ] 5. php artisan view:clear && php artisan view:cache
- [ ] 6. Test login sebagai ops@goldenbird.co.id
- [ ] 7. Verifikasi section muncul di halaman Fleet
- [ ] 8. Verifikasi section muncul di halaman Driver
- [ ] 9. Test assign vehicle dari modal
- [ ] 10. Test assign driver dari modal
- [ ] 11. Verifikasi badge berubah Fulfilled tanpa refresh
- [ ] 12. Push ke main

---

---

# UPGRADE 2: Analytics - Sales Performance (Revamp Total)

---

## Latar Belakang

Halaman Analytics > Sales Performance saat ini sudah ada tapi sederhana.
Pemilik ingin revamp total dengan:
- Tampilan daftar SEMUA Sales Rep (flat list, bukan hierarki Manager)
- Tambah kolom "Sales Manager" di tabel
- Dropdown filter per Manager
- Sorting semua kolom
- 3 jenis grafik
- Semua metrik penting untuk GM

---

## Struktur Data yang Ada di DB

User yang sudah ada (dari seeder):
  GM: 1 user
  Manager: 6 user (Ratna Dewi, Bambang, Citra, Dimas, Eka, Sales Manager)
  Sales: 25 user (sebagian punya manager_id, sebagian belum)

Data target & aktual tersedia di tabel sales_targets:
  target_meetings, target_calls, target_visits
  target_opportunities, target_won, target_revenue
  actual_meetings, actual_calls, actual_visits
  actual_opportunities, actual_won, actual_revenue

Field users.manager_id:
  Sales rep -> pointing ke manager
  Pool officer -> pointing ke pool (BUKAN manager)

---

## Keputusan dari Diskusi

Q: Tampilan awal halaman menampilkan apa?
A: Daftar SEMUA Sales Rep (flat list) - langsung 25 sales, BUKAN manager dulu

Q: Tabel ada kolom apa saja?
A: Nama | Sales Manager | Revenue | Target | % Target | Win Rate |
   Pipeline | Avg Deal Size | Deals Won | Deals Lost | Conversion Rate

Q: Dropdown "Filter Manager" di atas tabel untuk apa?
A: Filter tabel - pilih manager tertentu -> hanya tampilkan sales rep
   bawahannya. Tabel & grafik berubah sesuai filter.

Q: Grafik yang diminta?
A: KETIGANYA:
   1. Bar chart horizontal - ranking revenue semua sales rep
      (hijau = above target, merah = below target)
   2. Line chart - trend revenue 6 bulan per rep
      (default: semua rep dalam filter manager, bisa toggle show/hide)
   3. Donut grid - win rate per rep
      (kartu-kartu kecil berisi donut per rep)

Q: Sorting kolom?
A: SEMUA kolom bisa di-sort (klik header kolom)

---

## Desain Halaman Baru Sales Performance

### BAGIAN 1 - Header + Filter

```blade
<div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
    <div>
        <h2 class="text-xl font-bold">{{ __('ui.sales_performance') }}</h2>
        <p class="text-xs text-muted">{{ __('ui.period') }}:
            <select name="month" class="bg-transparent border-none text-sm font-semibold">
                @foreach(range(1,12) as $m)
                    <option value="{{$m}}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            {{ $now->year }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs text-muted">{{ __('ui.filter_manager') }}:</span>
        <select name="manager_id" class="dark-input text-sm px-3 py-2 min-w-40">
            <option value="">{{ __('ui.all_managers') }}</option>
            @foreach($managers as $m)
                <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
```

### BAGIAN 2 - Tabel Utama (Alpine.js sorting)

```blade
<table class="w-full" x-data="salesTable()">
    <thead>
        <tr class="text-xs uppercase tracking-widest text-muted">
            <th class="cursor-pointer" @click="sort('name')">
                {{ __('ui.sales_rep') }} <span x-text="icon('name')"></span>
            </th>
            <th class="cursor-pointer" @click="sort('manager_name')">
                {{ __('ui.sales_manager') }} <span x-text="icon('manager_name')"></span>
            </th>
            <th class="text-right cursor-pointer" @click="sort('revenue')">
                {{ __('ui.revenue') }} <span x-text="icon('revenue')"></span>
            </th>
            <th class="text-right cursor-pointer" @click="sort('target')">
                {{ __('ui.target') }} <span x-text="icon('target')"></span>
            </th>
            <th class="text-right cursor-pointer" @click="sort('target_pct')">
                % {{ __('ui.target') }} <span x-text="icon('target_pct')"></span>
            </th>
            <th class="text-center cursor-pointer" @click="sort('win_rate')">
                {{ __('ui.win_rate') }} <span x-text="icon('win_rate')"></span>
            </th>
            <th class="text-right cursor-pointer" @click="sort('pipeline')">
                {{ __('ui.pipeline') }} <span x-text="icon('pipeline')"></span>
            </th>
            <th class="text-right cursor-pointer" @click="sort('avg_deal')">
                {{ __('ui.avg_deal') }} <span x-text="icon('avg_deal')"></span>
            </th>
            <th class="text-center cursor-pointer" @click="sort('deals_won')">
                {{ __('ui.deals_won') }} <span x-text="icon('deals_won')"></span>
            </th>
            <th class="text-center cursor-pointer" @click="sort('deals_lost')">
                {{ __('ui.deals_lost') }} <span x-text="icon('deals_lost')"></span>
            </th>
            <th class="text-center cursor-pointer" @click="sort('conversion')">
                {{ __('ui.conversion_rate') }} <span x-text="icon('conversion')"></span>
            </th>
        </tr>
    </thead>
    <tbody>
        <template x-for="row in sortedRows()" :key="row.user_id">
            <tr class="border-t border-[var(--cc-border)]">
                <td class="px-3 py-2 font-semibold" x-text="row.name"></td>
                <td class="px-3 py-2 text-muted text-sm" x-text="row.manager_name"></td>
                <td class="px-3 py-2 text-right" x-text="row.revenue_fmt"></td>
                <td class="px-3 py-2 text-right text-muted" x-text="row.target_fmt"></td>
                <td class="px-3 py-2 text-right">
                    <span :class="row.target_pct >= 100 ? 'text-emerald-400' : (row.target_pct >= 70 ? 'text-amber-400' : 'text-red-400')"
                          class="font-bold" x-text="row.target_pct + '%'"></span>
                </td>
                <td class="px-3 py-2 text-center" x-text="row.win_rate + '%'"></td>
                <td class="px-3 py-2 text-right" x-text="row.pipeline_fmt"></td>
                <td class="px-3 py-2 text-right text-muted" x-text="row.avg_deal_fmt"></td>
                <td class="px-3 py-2 text-center text-emerald-400 font-semibold" x-text="row.deals_won"></td>
                <td class="px-3 py-2 text-center text-red-400" x-text="row.deals_lost"></td>
                <td class="px-3 py-2 text-center" x-text="row.conversion + '%'"></td>
            </tr>
        </template>
    </tbody>
</table>

<script>
function salesTable() {
    return {
        sortBy: 'revenue',
        sortDir: 'desc',
        rows: @json($salesRows),
        sort(field) {
            if (this.sortBy === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortDir = 'desc';
            }
        },
        icon(field) {
            if (this.sortBy !== field) return '';
            return this.sortDir === 'asc' ? '↑' : '↓';
        },
        sortedRows() {
            return [...this.rows].sort((a, b) => {
                let av = a[this.sortBy], bv = b[this.sortBy];
                if (typeof av === 'string') {
                    return this.sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                }
                return this.sortDir === 'asc' ? av - bv : bv - av;
            });
        }
    };
}
</script>
```

### BAGIAN 3 - Grafik

**Grafik 1 - Bar Chart Horizontal (Revenue vs Target):**

```javascript
// Chart.js horizontal bar chart
{
    type: 'bar',
    data: {
        labels: reps.map(r => r.name),
        datasets: [
            {
                label: 'Actual Revenue',
                data: reps.map(r => r.revenue),
                backgroundColor: reps.map(r =>
                    r.target_pct >= 100 ? '#10b981' :
                    (r.target_pct >= 70 ? '#f59e0b' : '#ef4444')
                ),
                borderRadius: 4
            },
            {
                label: 'Target',
                data: reps.map(r => r.target),
                type: 'line',
                borderColor: '#6366f1',
                borderDash: [5, 5],
                borderWidth: 2,
                pointRadius: 0,
                fill: false
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
}
```

**Grafik 2 - Line Chart Trend 6 Bulan:**

```javascript
// Multi-line chart per rep
{
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: reps.map((r, i) => ({
            label: r.name,
            data: r.trend_6m,  // array 6 nilai revenue
            borderColor: palette[i % palette.length],
            backgroundColor: palette[i % palette.length] + '20',
            tension: 0.3,
            pointRadius: 3,
            borderWidth: 2
        }))
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' }
        }
    }
}
```

**Grafik 3 - Donut Win Rate Grid:**

```blade
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
    @foreach($salesRows as $r)
    <div class="cc-card p-3 text-center">
        <div class="relative mx-auto" style="width:80px;height:80px;">
            <canvas id="donut-{{ $r['user_id'] }}"></canvas>
            <div class="absolute inset-0 flex items-center justify-center font-bold text-sm">
                {{ $r['win_rate'] }}%
            </div>
        </div>
        <p class="text-xs font-semibold mt-2 truncate">{{ $r['name'] }}</p>
        <p class="text-[10px] text-muted">{{ $r['deals_won'] }}W / {{ $r['deals_lost'] }}L</p>
    </div>
    @endforeach
</div>

<script>
// Inisialisasi donut per rep
@foreach($salesRows as $r)
new Chart(document.getElementById('donut-{{ $r['user_id'] }}'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [{{ $r['win_rate'] }}, {{ 100 - $r['win_rate'] }}],
            backgroundColor: [
                '{{ $r['win_rate'] >= 60 ? '#10b981' : ($r['win_rate'] >= 40 ? '#f59e0b' : '#ef4444') }}',
                '#1e293b'
            ],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '70%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } }
    }
});
@endforeach
</script>
```

---

## File yang Diubah (Upgrade 2)

| No | File                                         | Aksi  | Keterangan                              |
|----|----------------------------------------------|-------|-----------------------------------------|
| 1  | app/Http/Controllers/AnalyticsController.php | Edit  | Revamp method salesPerformance()        |
| 2  | resources/views/analytics/sales.blade.php    | Edit  | Revamp total tampilan                   |

Total: 2 file diedit, 0 file baru.

---

## Logic Controller salesPerformance() (Baru)

```php
public function salesPerformance(Request $request)
{
    $month = $request->get('month', now()->month);
    $year  = $request->get('year', now()->year);
    $managerId = $request->get('manager_id');

    // 1. Ambil semua user dengan role=sales
    $salesQuery = User::where('role', 'sales')
        ->with(['manager']);

    // 2. Filter jika manager dipilih
    if ($managerId) {
        $salesQuery->where('manager_id', $managerId);
    }

    $salesUsers = $salesQuery->orderBy('name')->get();

    // 3. Ambil semua manager untuk dropdown
    $managers = User::where('role', 'manager')->orderBy('name')->get();

    // 4. Per sales, hitung metrik
    $salesRows = [];
    foreach ($salesUsers as $u) {
        // Revenue bulan ini (won opportunities)
        $revenue = Opportunity::where('sales_id', $u->id)
            ->where('stage', 'won')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->sum('estimated_value');

        // Target bulan ini
        $targetRow = SalesTarget::where('user_id', $u->id)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();
        $target = $targetRow?->target_revenue ?? 0;
        $targetPct = $target > 0 ? round(($revenue / $target) * 100) : 0;

        // Won / Lost
        $dealsWon = Opportunity::where('sales_id', $u->id)
            ->where('stage', 'won')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->count();
        $dealsLost = Opportunity::where('sales_id', $u->id)
            ->where('stage', 'lost')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->count();
        $winRate = ($dealsWon + $dealsLost) > 0
            ? round(($dealsWon / ($dealsWon + $dealsLost)) * 100)
            : 0;

        // Pipeline (stage aktif)
        $pipeline = Opportunity::where('sales_id', $u->id)
            ->whereIn('stage', ['call_meeting', 'prospecting', 'proposal', 'negotiation'])
            ->sum('estimated_value');

        // Avg deal size
        $avgDeal = $dealsWon > 0 ? round($revenue / $dealsWon) : 0;

        // Conversion rate
        $totalOpps = Opportunity::where('sales_id', $u->id)
            ->whereIn('stage', ['won', 'lost'])
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->count();
        $conversion = $totalOpps > 0 ? round(($dealsWon / $totalOpps) * 100) : 0;

        // Trend 6 bulan
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $trend[] = Opportunity::where('sales_id', $u->id)
                ->where('stage', 'won')
                ->whereMonth('updated_at', $d->month)
                ->whereYear('updated_at', $d->year)
                ->sum('estimated_value');
        }

        $salesRows[] = [
            'user_id'       => $u->id,
            'name'          => $u->name,
            'manager_name'  => $u->manager?->name ?? '-',
            'revenue'       => $revenue,
            'revenue_fmt'   => FormatHelper::formatIDR($revenue),
            'target'        => $target,
            'target_fmt'    => FormatHelper::formatIDR($target),
            'target_pct'    => $targetPct,
            'win_rate'      => $winRate,
            'pipeline'      => $pipeline,
            'pipeline_fmt'  => FormatHelper::formatIDR($pipeline),
            'avg_deal'      => $avgDeal,
            'avg_deal_fmt'  => FormatHelper::formatIDR($avgDeal),
            'deals_won'     => $dealsWon,
            'deals_lost'    => $dealsLost,
            'conversion'    => $conversion,
            'trend_6m'      => $trend,
        ];
    }

    return view('analytics.sales', compact(
        'salesRows', 'managers', 'managerId', 'month', 'year'
    ));
}
```

---

## Checklist Eksekusi Upgrade 2

- [ ] 1. Edit AnalyticsController@salesPerformance - rebuild data query
- [ ] 2. Edit resources/views/analytics/sales.blade.php - revamp total
       - Header + filter dropdown manager + periode
       - Tabel dengan semua kolom + sorting Alpine.js
       - Bar chart horizontal (Chart.js)
       - Line chart trend 6 bulan (Chart.js)
       - Grid donut win rate (Chart.js)
- [ ] 3. php artisan view:clear && php artisan view:cache
- [ ] 4. Test login sebagai gm@goldenbird.co.id
- [ ] 5. Verifikasi semua 25 sales rep muncul di tabel default
- [ ] 6. Test dropdown filter per manager
- [ ] 7. Verifikasi sales rep bawahan muncul saat filter
- [ ] 8. Test sorting semua kolom
- [ ] 9. Verifikasi bar chart muncul dengan warna yang benar
- [ ] 10. Test toggle line chart per rep
- [ ] 11. Verifikasi donut win rate muncul
- [ ] 12. Push ke main

---

---

# UPGRADE 3: GM Dashboard - Header Cleanup + Widget Fleet League → Top Opportunities

---

## Latar Belakang

Header dashboard GM (Command Center) saat ini penuh dengan badge dan teks dekoratif
yang tidak relevan untuk penggunaan produksi. Pemilik minta:
1. Hapus SEMUA badge & teks dekoratif di header
2. Ganti widget "Fleet League" dengan widget "Top Opportunities"

CATATAN: UI dashboard GM secara umum TERKUNCI per UI_UX_LOCK.md, namun pemilik
memberi izin eksplisit untuk:
- Menghapus badge/teks dekoratif yang tidak perlu
- Mengganti widget Fleet League dengan Top Opportunities
- Mengubah konten (bukan struktur/layout/warna)

Yang TIDAK boleh diubah: layout, warna, font, struktur, design system.

---

## A. Header Cleanup (Hapus Semua Badge & Teks)

Yang dihapus dari `resources/views/dashboard/gm.blade.php`:

| No | Yang Dihapus                          | Lokasi Baris | Translation Key (jika ada) |
|----|---------------------------------------|--------------|----------------------------|
| 1  | "Corporate Fleet - Sales Pipeline - Dispatch - Revenue Intelligence" | ~107 | `ui.corporate_intel` |
| 2  | Badge "Render Deploy"                 | ~116         | `ui.render_deploy`         |
| 3  | Badge "Demo Live"                     | ~108-115     | `ui.demo_live`             |
| 4  | Badge "Juni 2026"                     | ~108-115     | `ui.june_2026`             |
| 5  | Badge "Director HQ" (sidebar footer)  | ~157-162     | `ui.director_hq`           |
| 6  | Badge "API Siap"                      | ~108-115     | `ui.api_ready`             |

Setelah header bersih, yang tersisa hanya:
- Avatar user + nama
- Tombol logout
- (Tidak ada lagi badge dekoratif)

### Translation Key Cleanup

File: `lang/en/ui.php` & `lang/id/ui.php`

Translation keys yang dihapus (atau dibiarkan tapi tidak dipakai - rekomendasi: hapus):
- `corporate_intel` → "Corporate Fleet - Sales Pipeline - Dispatch - Revenue Intelligence"
- `render_deploy` → "Render Deploy" (salah label, seharusnya "Dispatch")
- `demo_live` → "Demo Live"
- `june_2026` → "Juni 2026"
- `director_hq` → "Director HQ"
- `api_ready` → "API Siap"

Translation keys BARU yang ditambahkan:
- `hot_opportunities` → "Hot Opportunities" / "Opportunity Terpanas"
- `view_all` → "Lihat Semua" (mungkin sudah ada)
- `no_active_opportunity` → "Belum ada opportunity aktif" / "No active opportunity"

---

## B. Widget Fleet League → Top Opportunities

**File: `resources/views/dashboard/gm.blade.php`**

Widget lama (baris 347-387): `widget-fleet-league` - hapus
Widget baru: `widget-top-opportunities` (1/3 width) - taruh di posisi yang sama

### Tampilan Widget Top Opportunities

```
+--------------------------------------+
|  [🔥] HOT OPPORTUNITIES    View All |
+--------------------------------------+
| 1  PT Pertamina                   1.2M|
|    Shuttle Bulanan - Negotiation      |
|    Andi Pratama                       |
+--------------------------------------+
| 2  PT Telkom Indonesia            980jt|
|    Long Term 3 tahun - Proposal       |
|    Sari Dewi                          |
+--------------------------------------+
| 3  PT Bank Mandiri               750jt|
|    Charter VIP - Won                  |
|    Reza Kurniawan                     |
+--------------------------------------+
```

### Query Logic

```php
@php
$hotOpps = \App\Models\Opportunity::with(['client', 'sales'])
    ->whereIn('stage', ['call_meeting', 'prospecting', 'proposal', 'negotiation'])
    ->orderByDesc('estimated_value')
    ->take(5)
    ->get();
@endphp
```

### Kode Blade Widget

```blade
{{-- ===== Top Opportunities (1/3 width, replaces Fleet League) ===== --}}
<div class="grid-stack-item" gs-id="widget-top-opportunities" gs-x="8" gs-y="6" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
        <div class="cc-card p-5 h-full overflow-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#ef4444;">whatshot</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">
                        {{ __('ui.hot_opportunities') }}
                    </span>
                </div>
                <a href="{{ route('opportunities.index', ['stage' => 'call_meeting,prospecting,proposal,negotiation']) }}"
                   class="text-[10px] font-semibold" style="color:#3b82f6;">
                    {{ __('ui.view_all') }} →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($hotOpps as $i => $o)
                <a href="{{ route('opportunities.show', $o->id) }}"
                   class="block hover:bg-[var(--cc-card-hover)] rounded-xl p-3 transition-colors border border-[var(--cc-border)]">
                    <div class="flex items-center justify-between mb-1">
                        <div class="rank-num font-bold"
                             style="color:{{ $i==0 ? '#ef4444' : ($i==1 ? '#f59e0b' : ($i==2 ? '#3b82f6' : '#6366f1')) }};">
                            #{{ $i+1 }}
                        </div>
                        <span class="text-[11px] font-bold text-emerald-400">
                            {{ \App\Helpers\FormatHelper::formatIDR($o->estimated_value) }}
                        </span>
                    </div>
                    <div class="font-semibold text-[var(--cc-text)] text-sm truncate">
                        {{ $o->client->company_name ?? $o->title }}
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-[var(--cc-text-muted)] mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold
                                     @switch($o->stage)
                                         @case('call_meeting') bg-cyan-500/20 text-cyan-400 @break
                                         @case('prospecting') bg-blue-500/20 text-blue-400 @break
                                         @case('proposal') bg-purple-500/20 text-purple-400 @break
                                         @case('negotiation') bg-amber-500/20 text-amber-400 @break
                                     @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $o->stage)) }}
                        </span>
                        <span>{{ $o->sales->name ?? '-' }}</span>
                    </div>
                </a>
                @empty
                <p class="text-[var(--cc-text-muted)] text-center py-4 text-sm">
                    {{ __('ui.no_active_opportunity') }}
                </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
```

---

## File yang Diubah (Upgrade 3)

| No | File                                       | Aksi  | Keterangan                              |
|----|--------------------------------------------|-------|-----------------------------------------|
| 1  | resources/views/dashboard/gm.blade.php     | Edit  | Hapus 5 badge + teks + ganti Fleet League widget |
| 2  | resources/views/components/sidebar.blade.php | Edit | Hapus badge "Director HQ" (jika ada)   |
| 3  | lang/en/ui.php                             | Edit  | Hapus 6 translation keys lama + tambah 3 baru |
| 4  | lang/id/ui.php                             | Edit  | Sama seperti en/ui.php                  |

Total: 4 file diedit, 0 file baru.

---

## Checklist Eksekusi Upgrade 3

- [ ] 1. Buka resources/views/dashboard/gm.blade.php
- [ ] 2. Cari dan hapus baris 107 (corporate_intel)
- [ ] 3. Cari dan hapus baris 116 (render_deploy badge)
- [ ] 4. Cari dan hapus badge Demo Live, Juni 2026, API Siap
- [ ] 5. Buka resources/views/components/sidebar.blade.php
- [ ] 6. Hapus badge "Director HQ" (footer sidebar)
- [ ] 7. Cari widget-fleet-league (baris 347-387) di gm.blade.php
- [ ] 8. Ganti dengan widget-top-opportunities (kode di atas)
- [ ] 9. Edit lang/en/ui.php - hapus keys lama + tambah baru
- [ ] 10. Edit lang/id/ui.php - sama
- [ ] 11. php artisan view:clear && php artisan view:cache
- [ ] 12. Test login sebagai gm@goldenbird.co.id
- [ ] 13. Verifikasi header bersih dari badge dekoratif
- [ ] 14. Verifikasi Top Opportunities widget muncul dengan 5 deals
- [ ] 15. Test klik link "View All" -> redirect ke opportunities dengan filter stage
- [ ] 16. Test klik salah satu opportunity -> redirect ke detail
- [ ] 17. Push ke main

---

---

# UPGRADE 4: Opportunities - Filter Manager + Sorting Semua Kolom

---

## Latar Belakang

Halaman Opportunities (`/opportunities`) saat ini menampilkan semua opportunities
tanpa filter manager. Pemilik ingin:
1. Tambah dropdown filter "Sales Manager" di atas tabel
2. Sorting SEMUA kolom di tabel (klik header)
3. Tambah kolom "Sales Manager" di tabel

---

## Keputusan dari Diskusi

Q: Filter Manager untuk apa?
A: Memfilter tabel - pilih manager tertentu -> hanya tampilkan opportunities
   yang sales_id-nya pointing ke sales rep bawahan manager itu.

Q: Kolom baru?
A: "Sales Manager" - nama manager dari sales rep yang handle opportunity itu.

Q: Sorting?
A: SEMUA kolom bisa di-sort. Gunakan Alpine.js (sudah ada di project).

---

## Perubahan

### A. Controller

File: `app/Http/Controllers/OpportunityController.php`

Tambah filter logic di `index()`:

```php
$query = Opportunity::with(['client', 'sales', 'sales.manager']);

if ($request->filled('manager_id') && !$user->isSales()) {
    $query->whereHas('sales', function ($q) use ($request) {
        $q->where('manager_id', $request->manager_id);
    });
}

$opportunities = $query->latest()->paginate(20);

$managers = User::where('role', 'manager')->orderBy('name')->get();
```

### B. View

File: `resources/views/opportunities/index.blade.php`

Tambah dropdown filter di header tabel:

```blade
<div class="flex items-center justify-between gap-3 mb-4">
    <h2 class="text-xl font-bold">{{ __('ui.opportunities') }}</h2>
    <form method="GET" class="flex items-center gap-2">
        <span class="text-xs text-muted">{{ __('ui.filter_manager') }}:</span>
        <select name="manager_id" class="dark-input text-sm px-3 py-2 min-w-40"
                onchange="this.form.submit()">
            <option value="">{{ __('ui.all_managers') }}</option>
            @foreach($managers as $m)
                <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>
```

Tambah kolom "Sales Manager" di header tabel:

```blade
<th @click="sort('manager_name')" class="cursor-pointer">
    {{ __('ui.sales_manager') }} <span x-text="icon('manager_name')"></span>
</th>
```

Sorting Alpine.js (sudah ada pattern-nya, tinggal copy dari analytics/sales.blade.php):

```blade
<table class="w-full dark-table" x-data="opportunityTable()">
    <thead>
        <tr class="text-xs uppercase tracking-widest text-muted">
            <th @click="sort('opp_number')" class="cursor-pointer">
                No <span x-text="icon('opp_number')"></span>
            </th>
            <th @click="sort('company_name')" class="cursor-pointer">
                {{ __('ui.client') }} <span x-text="icon('company_name')"></span>
            </th>
            <th @click="sort('sales_name')" class="cursor-pointer">
                {{ __('ui.sales') }} <span x-text="icon('sales_name')"></span>
            </th>
            <th @click="sort('manager_name')" class="cursor-pointer">
                {{ __('ui.sales_manager') }} <span x-text="icon('manager_name')"></span>
            </th>
            <th @click="sort('stage')" class="cursor-pointer">
                {{ __('ui.stage') }} <span x-text="icon('stage')"></span>
            </th>
            <th @click="sort('estimated_value')" class="cursor-pointer text-right">
                {{ __('ui.value') }} <span x-text="icon('estimated_value')"></span>
            </th>
            <th @click="sort('created_at')" class="cursor-pointer">
                {{ __('ui.created_at') }} <span x-text="icon('created_at')"></span>
            </th>
        </tr>
    </thead>
    <tbody>
        <template x-for="row in sortedRows()" :key="row.id">
            <tr class="border-t border-[var(--cc-border)]">
                <td class="px-3 py-2 text-muted text-xs" x-text="row.opp_number"></td>
                <td class="px-3 py-2 font-semibold" x-text="row.company_name"></td>
                <td class="px-3 py-2" x-text="row.sales_name"></td>
                <td class="px-3 py-2 text-muted text-sm" x-text="row.manager_name"></td>
                <td class="px-3 py-2">
                    <span class="status-badge" :class="'status-' + row.stage_color" x-text="row.stage_label"></span>
                </td>
                <td class="px-3 py-2 text-right font-semibold" x-text="row.estimated_value_fmt"></td>
                <td class="px-3 py-2 text-muted text-xs" x-text="row.created_at_fmt"></td>
            </tr>
        </template>
    </tbody>
</table>

<script>
function opportunityTable() {
    return {
        sortBy: 'created_at',
        sortDir: 'desc',
        rows: @json($opportunityRows),
        sort(field) {
            if (this.sortBy === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortDir = 'desc';
            }
        },
        icon(field) {
            if (this.sortBy !== field) return '';
            return this.sortDir === 'asc' ? '↑' : '↓';
        },
        sortedRows() {
            return [...this.rows].sort((a, b) => {
                let av = a[this.sortBy], bv = b[this.sortBy];
                if (typeof av === 'string') {
                    return this.sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                }
                return this.sortDir === 'asc' ? av - bv : bv - av;
            });
        }
    };
}
</script>
```

### C. Controller - Data Preparation untuk Alpine.js

```php
$opportunityRows = $opportunities->getCollection()->map(function ($o) {
    return [
        'id'                 => $o->id,
        'opp_number'         => $o->opp_number ?? 'OPP-' . str_pad($o->id, 4, '0', STR_PAD_LEFT),
        'company_name'       => $o->client->company_name ?? '-',
        'sales_name'         => $o->sales->name ?? '-',
        'manager_name'       => $o->sales->manager->name ?? '-',
        'stage'              => $o->stage,
        'stage_color'        => $o->stage,
        'stage_label'        => ucfirst(str_replace('_', ' ', $o->stage)),
        'estimated_value'    => $o->estimated_value ?? 0,
        'estimated_value_fmt'=> FormatHelper::formatIDR($o->estimated_value ?? 0),
        'created_at'         => $o->created_at->timestamp,
        'created_at_fmt'     => $o->created_at->format('d M Y'),
    ];
})->values();

return view('opportunities.index', compact(
    'opportunities', 'opportunityRows', 'managers'
));
```

---

## File yang Diubah (Upgrade 4)

| No | File                                          | Aksi  | Keterangan                              |
|----|-----------------------------------------------|-------|-----------------------------------------|
| 1  | app/Http/Controllers/OpportunityController.php | Edit | Tambah filter manager_id + rows untuk Alpine |
| 2  | resources/views/opportunities/index.blade.php | Edit  | Tambah dropdown manager + kolom + Alpine.js sort |

Total: 2 file diedit, 0 file baru.

---

## Checklist Eksekusi Upgrade 4

- [ ] 1. Edit OpportunityController@index - tambah manager_id filter + rows mapping
- [ ] 2. Edit opportunities/index.blade.php - tambah dropdown + Alpine.js sorting
- [ ] 3. php artisan view:clear && php artisan view:cache
- [ ] 4. Test login sebagai gm@goldenbird.co.id
- [ ] 5. Verifikasi kolom "Sales Manager" muncul di tabel
- [ ] 6. Test dropdown filter "Semua Manager" -> tampil semua
- [ ] 7. Test dropdown pilih satu manager -> filter sesuai bawahan
- [ ] 8. Test sorting klik header kolom manapun
- [ ] 9. Push ke main

---

---

# TRANSLATION KEYS BARU YANG DITAMBAHKAN

Tambah di `lang/en/ui.php` & `lang/id/ui.php`:

```php
// Baru
'hot_opportunities'    => 'Hot Opportunities' / 'Opportunity Terpanas',
'view_all'             => 'View All' / 'Lihat Semua',  // mungkin sudah ada
'no_active_opportunity'=> 'No active opportunity' / 'Belum ada opportunity aktif',
'filter_manager'       => 'Filter Manager' / 'Filter Manager',
'all_managers'         => 'All Managers' / 'Semua Manager',
'sales_manager'        => 'Sales Manager' / 'Sales Manager',
'sales_rep'            => 'Sales Rep' / 'Sales Rep',
'period'               => 'Period' / 'Periode',
'avg_deal'             => 'Avg Deal Size' / 'Rata-rata Deal',
'conversion_rate'      => 'Conversion Rate' / 'Tingkat Konversi',
'opportunities'        => 'Opportunities' / 'Opportunities',
'client'               => 'Client' / 'Klien',
'value'                => 'Value' / 'Nilai',
'created_at'           => 'Created At' / 'Dibuat',
```

## TRANSLATION KEYS LAMA YANG DIHAPUS

```php
// Hapus (tidak dipakai lagi)
'corporate_intel'      => 'Corporate Fleet - Sales Pipeline...',
'render_deploy'        => 'Render Deploy',  // salah label
'demo_live'            => 'Demo Live',
'june_2026'            => 'Juni 2026',
'director_hq'          => 'Director HQ',
'api_ready'            => 'API Siap',
```

---

# RINGKASAN SEMUA UPGRADE

| Upgrade | Fitur                                | File Diubah | File Baru | Route Baru |
|---------|--------------------------------------|-------------|-----------|------------|
| 1       | Pending Assignment di Fleet & Driver | 4           | 0         | 0          |
| 2       | Sales Performance Revamp             | 2           | 0         | 0          |
| 3       | GM Dashboard Cleanup + Top Opps      | 4           | 0         | 0          |
| 4       | Opportunities: Filter + Sorting      | 2           | 0         | 0          |
| **TOTAL** |                                  | **12**      | **0**     | **0**      |

---

# LARANGAN SAAT EKSEKUSI

Sesuai UI_UX_LOCK.md dan CLAUDE.md:
  DILARANG: ubah dashboard/gm.blade.php (Command Center terkunci) - KECUALI untuk upgrade 3 (header cleanup + widget Fleet League) yang sudah diizinkan eksplisit
  DILARANG: ubah/hapus existing routes
  DILARANG: ubah Middleware
  DILARANG: ubah existing migrations
  DILARANG: tambah library UI/CSS baru
  DILARANG: ubah relasi model yang sudah ada

BOLEH:
  Edit controller yang ada (FleetController, DriverController, AnalyticsController, OpportunityController)
  Edit view yang ada (fleet/index, drivers/index, analytics/sales, opportunities/index, dashboard/gm, components/sidebar)
  Edit translation files (lang/en, lang/id)
  Push langsung ke main setelah test passed

---

# URUTAN EKSEKUSI YANG DIREKOMENDASIKAN

1. **Upgrade 1** dulu (Pending Assignment) - sederhana, tidak ada risiko UI
2. **Upgrade 4** (Opportunities Filter) - sederhana, terpisah dari dashboard
3. **Upgrade 2** (Sales Performance) - menengah, banyak Alpine.js
4. **Upgrade 3** (GM Dashboard) terakhir - karena menyentuh Command Center (terkunci)

Setelah setiap upgrade:
- `php artisan view:clear && php artisan view:cache`
- Test login sebagai role terkait
- Verifikasi tampilan sesuai desain
- Commit dengan pesan jelas dalam bahasa Indonesia
- Push ke main

---

# CARA PAKAI DOKUMEN INI

Saat mau eksekusi, buka sesi baru lalu ketik:
"Baca UpgradePlan.md, lalu eksekusi Upgrade [nomor]"

Semua konteks sudah tersimpan di dokumen ini. Tidak perlu cerita ulang.
