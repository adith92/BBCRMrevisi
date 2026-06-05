# 📐 UI/UX PANDUAN — GOLDEN BIRD CRM V6 BlueERP

**Status:** Draft untuk review  
**Tanggal:** 5 Juni 2026  
**Tujuan:** Restore tampilan V6 asli ke V7.2 dengan konsistensi design system BlueERP

---

## 🎨 Design System BlueERP (V6 Asli)

### 1. **Color Palette**

| Element | Hex | RGB | Usage |
|---------|-----|-----|-------|
| **Primary** | `#003887` | (0, 56, 135) | Sidebar bg, buttons, accents |
| **Secondary** | `#1e4fa8` | (30, 79, 168) | Gradient transitions, hover states |
| **Surface Bright** | `#f8f9ff` | (248, 249, 255) | Body bg, card bg |
| **Surface Container** | `#e5eeff` | (229, 238, 255) | Subtle dividers, disabled states |
| **On Primary** | `#ffffff` | (255, 255, 255) | Text on primary (white) |
| **Primary Container** | `#1e4fa8` | (30, 79, 168) | Hover bg inside sidebar |
| **On Primary Container** | `#b2c7ff` | (178, 199, 255) | Helper text on sidebar |
| **Outline Variant** | `#c3c6d4` | (195, 198, 212) | Border color, dividers |

**Key Principle:** Navy (`#003887`) adalah **warna brand Bluebird**, bukan biru elektrik atau bright blue.

---

### 2. **Typography**

- **Font Family:** `Inter` (Google Fonts)
  - Weights: 400 (regular), 500, 600 (medium), 700 (bold), 800 (extra bold)
  - Load: `https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap`

- **Icon System:** `Material Symbols Outlined`
  - Load: `https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap`
  - Weight: 400, Fill: 0 (outlined style)
  - Sizes: 18px (small), 24px (default), 28px (medium), 64px (hero)

- **Font Scales:**
  - Heading 1: `text-2xl font-bold` (32px)
  - Heading 2: `text-xl font-bold` (28px)
  - Heading 3: `text-lg font-bold` (24px)
  - Body: `text-sm` (14px)
  - Caption: `text-xs` (12px)
  - Hint: `text-[10px]` (10px)

---

### 3. **Layout Structure**

```
┌─────────────────────────────────────────────────────────┐
│                      TOP APP BAR (h-16)                 │  ← Sticky, z-40
│   Breadcrumb | Role Badge ────────────── User Avatar    │  ← Shadow-sm
├──────────┬────────────────────────────────────────────┤
│          │                                              │
│ SIDEBAR  │         MAIN CONTENT (p-6)                  │
│ (w-64)   │                                              │
│          │         - Hero banner (if applicable)        │
│ (fixed)  │         - Cards grid (gap-5)                │
│ (z-50)   │         - Tables (rounded-2xl)              │
│          │         - Forms/modals                       │
│          │                                              │
│ [6] Nav  │                                              │
│ items    │                                              │
│ (gap-1)  │                                              │
│          │                                              │
│ [Footer] │                                              │
│          │                                              │
└──────────┴────────────────────────────────────────────┘
```

#### **Sidebar** (`bg-primary`, `w-64`)
- Fixed position, inset-y-0 left-0
- Background: `#003887` (navy)
- Text: white (`#ffffff`)
- Navigation items: `py-2.5 px-4 rounded-xl`
- Active item: `bg-secondary text-on-secondary` with glow effect
- Hover item: `bg-primary-container text-on-primary-container`
- Footer: `border-t border-primary-container` + role emoji + user name

#### **Top App Bar** (`h-16`, sticky top-0)
- Background: `bg-surface-container-lowest` (very light)
- Border: `border-b border-outline-variant`
- Shadow: `shadow-sm`
- Content: Breadcrumb (left) + Role Badge + Logout button (right)

---

### 4. **Card Component** (Most Important)

```html
<!-- V6 Card Style (ROUNDED-2XL + SHADOW-SM + BORDER) -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-all">
  <!-- Icon + Stat / Content -->
</div>

<!-- NOT like V7.2 Old Style (DO NOT USE) -->
<!-- ❌ border-l-4 border-blue-500 rounded-lg shadow (WRONG!) -->
<!-- ❌ bg-gradient-to-r (WRONG for card body!) -->
```

**Card Attributes:**
- `bg-white` — white background
- `rounded-2xl` — 16px border radius (premium look)
- `shadow-sm` — subtle shadow
- `border border-slate-200` — light gray border
- `p-6` — 24px padding
- `hover:shadow-md transition-all` — smooth hover effect
- `gap-3` or `space-y-1` — internal spacing

**Card with Icon** (KPI style):
```html
<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 
            hover:shadow-md transition-all group flex justify-between items-center">
  <div class="space-y-1">
    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Label</p>
    <p class="text-3xl font-extrabold text-[#003887]">123,456</p>
    <p class="text-[10px] text-slate-400">Detail text</p>
  </div>
  <div class="p-3 bg-blue-50 text-blue-700 rounded-xl group-hover:scale-110 transition-transform">
    <span class="material-symbols-outlined text-[28px]">icon_name</span>
  </div>
</div>
```

---

### 5. **Hero Banner** (Dashboard Header)

```html
<div class="bg-gradient-to-r from-[#003887] via-[#1e4fa8] to-secondary 
            text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
  <div class="absolute right-4 bottom-0 top-0 opacity-10 pointer-events-none flex items-center">
    <span class="material-symbols-outlined text-[120px]">icon_name</span>
  </div>
  <div class="relative z-10">
    <h2 class="text-2xl font-bold">Title</h2>
    <p class="text-blue-100 text-sm mt-1">Subtitle</p>
  </div>
</div>
```

**Gradient:** Navy (`#003887`) → Mid-blue (`#1e4fa8`) → Secondary (`#1960a6`)  
**Icon overlay:** `opacity-10` for watermark effect

---

### 6. **Table Component**

```html
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
        <th class="px-5 py-3">Column</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-5 py-3.5">Content</td>
      </tr>
    </tbody>
  </table>
</div>
```

**Key:**
- Wrapper: `rounded-2xl shadow-sm border border-slate-200`
- Header: `bg-slate-50 border-b border-slate-200` + uppercase text
- Rows: `divide-y divide-slate-100` + `hover:bg-slate-50`
- Spacing: `px-5 py-3` for header, `px-5 py-3.5` for data

---

### 7. **Button Styles**

#### **Primary Button** (Navy)
```html
<button class="bg-[#003887] hover:bg-secondary text-white 
               text-sm font-semibold px-4 py-2.5 rounded-xl 
               transition-colors flex items-center gap-2">
  <span class="material-symbols-outlined text-[18px]">icon</span>
  Label
</button>
```

#### **Secondary Button** (Light)
```html
<button class="bg-slate-100 hover:bg-slate-200 text-slate-700 
               text-sm font-semibold px-4 py-2 rounded-xl 
               transition-colors">
  Label
</button>
```

#### **Ghost/Link Button**
```html
<a href="..." class="text-[#003887] hover:underline text-xs font-semibold">
  Link Text →
</a>
```

---

### 8. **Badge & Status Tags**

```html
<!-- Status Badge (colorful) -->
<span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold 
            bg-emerald-100 text-emerald-700">
  Active
</span>

<!-- Colors per status -->
<!-- Active: bg-emerald-100 text-emerald-700 -->
<!-- Pending: bg-amber-100 text-amber-700 -->
<!-- Completed: bg-blue-100 text-blue-700 -->
<!-- Cancelled/Lost: bg-red-100 text-red-700 -->
<!-- Negotiation: bg-indigo-100 text-indigo-700 -->
```

---

### 9. **Form Elements**

```html
<!-- Input -->
<input class="rounded-xl border-slate-300 text-sm 
             focus:border-[#003887] focus:ring-[#003887]" />

<!-- Select -->
<select class="rounded-xl border-slate-300 text-sm 
             focus:border-[#003887] focus:ring-[#003887]" />

<!-- Text Area -->
<textarea class="rounded-xl border-slate-300 text-sm 
                focus:border-[#003887] focus:ring-[#003887]" />
```

**Focus State:** Blue primary border + ring-[#003887]

---

### 10. **Spacing & Gaps**

- **Page content:** `space-y-6` (24px gap between sections)
- **Card grid:** `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5`
- **Card spacing:** `gap-3` for horizontal, `space-y-1` for vertical
- **List items:** `py-2.5` per item
- **Section padding:** `p-6` or `p-4`

---

### 11. **Login Page**

```html
<!-- Body Background -->
<body class="min-h-screen flex flex-col items-center justify-center p-6"
      style="background: radial-gradient(circle at top left, #f8f9ff 0%, #e5eeff 100%);
             font-family: 'Inter', sans-serif;">

  <!-- Brand -->
  <div class="flex items-center gap-3 mb-8">
    <div class="p-2 bg-primary rounded-xl shadow-lg">
      <span class="material-symbols-outlined text-white text-[28px]">directions_bus</span>
    </div>
    <div>
      <h1 class="text-xl font-extrabold text-primary">Golden Bird CRM</h1>
      <p class="text-[11px] text-slate-500 uppercase">B2B Fleet Management</p>
    </div>
  </div>

  <!-- Login Card -->
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-outline-variant">
    <div class="bg-gradient-to-r from-primary via-[#1e4fa8] to-secondary px-6 py-5">
      <h2 class="text-lg font-bold text-white">Masuk ke Akun</h2>
      <p class="text-[11px] text-on-primary-container mt-0.5">Pilih role — 1 klik</p>
    </div>
    
    <div class="p-6">
      <p class="text-xs font-bold text-slate-500 uppercase mb-4">Demo Accounts</p>
      
      <!-- Role buttons in 2x3 grid -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Each role as a form with hidden inputs -->
      </div>
    </div>
  </div>
</body>
```

---

## 📋 Checklist Implementasi

Untuk V7.2, pastikan:

- [x] Font: `Inter` + `Material Symbols Outlined` di layout
- [x] Color tokens di Tailwind config (primary, secondary, surface-bright, dll)
- [x] Sidebar: `bg-primary` navy + white text + `rounded-xl` nav items
- [x] Top app bar: sticky + breadcrumb + role badge
- [x] All cards: `rounded-2xl shadow-sm border-slate-200` (TIDAK ada border-l-4!)
- [x] Hero banners: gradient `from-primary via-[#1e4fa8] to-secondary`
- [x] Buttons: Primary = `bg-[#003887]`, secondary = `bg-slate-100`
- [x] Tables: `rounded-2xl` wrapper + `bg-slate-50` header
- [x] Status badges: colorful per status
- [x] Login page: radial gradient + 2x3 role grid
- [x] All pages: Material Symbols icons (NO EMOJI in production if possible)
- [x] Remove: Any `border-l-4`, `rounded-lg`, `bg-gray-*` patterns
- [x] Spacing: `space-y-6`, `gap-5` for grids, `py-2.5` for rows

---

## 🔄 Migration Path (V7.2)

1. **Update `layouts/app.blade.php`:**
   - Font links (Inter + Material)
   - Tailwind color config
   - Sidebar structure (navy bg, white text)
   - Top bar (sticky, breadcrumb, role badge)

2. **Update Dashboard Views:**
   - `dashboard/director.blade.php`, `dashboard/gm.blade.php`, etc.
   - Hero banner gradient
   - KPI cards with icon + stat layout

3. **Update Feature Page Templates:**
   - All `.blade.php` files in `resources/views/`
   - Replace card styles: `rounded-2xl shadow-sm border-slate-200`
   - Add Material Symbols icons to headings
   - Update tables, forms, buttons

4. **Update Login Page:**
   - Radial gradient background
   - Card with navy gradient header
   - 1-click role buttons (2x3 grid)

5. **Validation:**
   - Test all 6 roles on all pages
   - Check hover states, transitions
   - Verify responsive (mobile, tablet, desktop)
   - No 500 errors, no broken routes

---

## 📸 Visual Reference

**V6 Key Visual Traits:**
- 🎨 Navy (`#003887`) is DOMINANT — not bright blue
- 📦 Cards have BORDERS — not shadow-only
- 🎯 Everything is `rounded-2xl` — premium, not default `rounded`
- 📱 Sidebar is FIXED — always visible on desktop
- ✨ Gradients are SUBTLE — used for hero/CTAs only
- 🔤 Typography is CLEAN — Inter sans-serif throughout
- 🎪 Icons are OUTLINES — Material Symbols, not emoji

---

## ❌ Anti-Patterns (DO NOT DO)

| Wrong | Right |
|-------|-------|
| `border-l-4 border-blue-500 rounded-lg shadow` | `rounded-2xl shadow-sm border border-slate-200` |
| `bg-gray-*`, `bg-slate-*` for cards | `bg-white` |
| `rounded-lg` | `rounded-2xl` |
| Bright blue `#2563eb` or `#3b82f6` | Navy `#003887` |
| Emoji icons `😊` | Material Symbols `<span class="material-symbols-outlined">icon_name</span>` |
| No borders on cards | Always add `border border-slate-200` |
| Bare text links | Use Material Symbols + arrow `→` |

---

## ✅ Validation Commands

```bash
# Local test (all 6 roles)
./scripts/verify-local.sh

# Production test (after deploy)
./scripts/verify-production.sh

# Visual inspection (manual)
# 1. Open https://goldenbirdcrm.onrender.com/login
# 2. Hard refresh: Cmd+Shift+R
# 3. Login as each role
# 4. Check: sidebar color, card styling, hero banner
# 5. Verify: NO 500 errors, NO broken links
```

---

**End of Panduan — Ready for Review** ✅

Apakah ini yang Anda maksud? Atau ada yang kurang / ingin diubah?
