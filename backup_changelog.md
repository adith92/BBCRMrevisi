# Backup Theme Changelog - Golden Bird CRM
Tanggal: 2026-06-09

Dokumen ini mencantumkan konfigurasi warna dan layout sebelum perubahan tema terinspirasi BLUECRM diaplikasikan.

## 1. CSS Variables Lama (resources/css/app.css)

### Light Mode (Lama)
```css
html, html.light {
    --cc-bg:          #f4f7fb;
    --cc-surface:     #ffffff;
    --cc-card:        #ffffff;
    --cc-card-hover:  #f8fafc;
    --cc-sidebar:     #082b5f;
    --cc-topbar:      #ffffff;
    --cc-border:      rgba(16,40,72,0.10);
    --cc-border-h:    rgba(20,104,168,0.24);
    --cc-text:        #101828;
    --cc-text-muted:  #667085;
    --cc-text-faint:  #98a2b3;
    --cc-input-bg:    #ffffff;
    --cc-input-bd:    rgba(16,40,72,0.14);
    --cc-scrollbar:   rgba(16,40,72,0.18);
    --cc-overlay:     rgba(8,43,95,0.28);
    --cc-modal-bg:    #ffffff;
    --cc-row-hover:   rgba(20,104,168,0.06);
    --cc-th-bg:       rgba(20,104,168,0.06);
    --cc-progress-bg: rgba(16,40,72,0.10);
    --cc-kbd:         rgba(20,104,168,0.08);
    --cc-kbd-text:    #315a84;
}
```

### Dark Mode (Lama)
```css
html.dark {
    --cc-bg:          #0b1120;
    --cc-surface:     #111827;
    --cc-card:        #1a2332;
    --cc-card-hover:  #1e2a3d;
    --cc-sidebar:     #06172e;
    --cc-topbar:      #111827;
    --cc-border:      rgba(255,255,255,0.07);
    --cc-border-h:    rgba(99,179,237,0.28);
    --cc-text:        #e2e8f0;
    --cc-text-muted:  #94a3b8;
    --cc-text-faint:  #64748b;
    --cc-input-bg:    rgba(255,255,255,0.06);
    --cc-input-bd:    rgba(255,255,255,0.12);
    --cc-scrollbar:   rgba(255,255,255,0.10);
    --cc-overlay:     rgba(0,0,0,0.65);
    --cc-modal-bg:    #1a2332;
    --cc-row-hover:   rgba(255,255,255,0.04);
    --cc-th-bg:       rgba(255,255,255,0.04);
    --cc-progress-bg: rgba(255,255,255,0.08);
    --cc-kbd:         rgba(255,255,255,0.08);
    --cc-kbd-text:    #94a3b8;
}
```

## 2. Layout Shell Lama (resources/views/layouts/app.blade.php)

Potongan kode `app-shell` lama:
```html
<div class="app-shell">

    {{-- ── SIDEBAR ── --}}
    <x-sidebar/>
...
```
Tanpa `.theme-orbs` background element.
