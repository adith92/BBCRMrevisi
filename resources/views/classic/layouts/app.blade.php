<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-skin="classic">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $title ?? 'Bluebird CRM' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/css/classic.css', 'resources/js/app.js'])
    <script>(function(){var t=localStorage.getItem('crm-theme')||'light';document.documentElement.classList.remove('dark','light');document.documentElement.classList.add(t);})();</script>
    @stack('styles')
</head>
@php
    $role = Auth::user()->role ?? '';
    $roleLabels = ['director'=>'Director HQ','gm'=>'General Manager','manager'=>'Sales Manager','sales'=>'Sales Rep','operational'=>'Operations','pool'=>'Pool Admin','finance'=>'Finance'];
    $navActive = fn($pattern) => Request::routeIs($pattern) ? 'active' : '';
@endphp
<body class="bb-app">
<div class="bb-shell" x-data="{ notif: false }">

    {{-- ── SIDEBAR ── --}}
    <aside class="bb-sidebar">
        <div class="bb-brand">
            <div style="width:38px;height:38px;border-radius:9px;background:var(--bb-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span class="material-symbols-outlined" style="color:#fff;font-size:22px;">directions_bus</span>
            </div>
            <div>
                <div class="bb-brand-title">Bluebird CRM</div>
                <div class="bb-brand-sub">Fleet Command</div>
            </div>
        </div>

        <nav style="flex:1;padding-bottom:10px;">
            <div class="bb-navsec">
                <div class="bb-navsec-label">Utama</div>
                <a href="{{ route('dashboard') }}" class="bb-navitem {{ $navActive('dashboard') }}">
                    <span class="material-symbols-outlined">space_dashboard</span><span>Dashboard</span>
                </a>
            </div>

            @if(in_array($role, ['gm','manager','sales']))
            <div class="bb-navsec">
                <div class="bb-navsec-label">Penjualan</div>
                <a href="{{ route('pipeline.index') }}" class="bb-navitem {{ $navActive('pipeline*') }}"><span class="material-symbols-outlined">view_kanban</span><span>Deals Board</span></a>
                <a href="{{ route('opportunities.index') }}" class="bb-navitem {{ $navActive('opportunities*') }}"><span class="material-symbols-outlined">handshake</span><span>All Opportunities</span></a>
                <a href="{{ route('clients.index') }}" class="bb-navitem {{ $navActive('clients*') }}"><span class="material-symbols-outlined">corporate_fare</span><span>Clients</span></a>
            </div>
            @endif

            @if(in_array($role, ['gm','manager','operational','pool']))
            <div class="bb-navsec">
                <div class="bb-navsec-label">Operasional</div>
                <a href="{{ route('fleet.index') }}" class="bb-navitem {{ $navActive('fleet*') }}"><span class="material-symbols-outlined">local_shipping</span><span>Fleet / Armada</span></a>
                <a href="{{ route('drivers.index') }}" class="bb-navitem {{ $navActive('drivers*') }}"><span class="material-symbols-outlined">badge</span><span>Driver / Supir</span></a>
                <a href="{{ route('bookings.index') }}" class="bb-navitem {{ $navActive('bookings*') }}"><span class="material-symbols-outlined">route</span><span>Dispatch</span></a>
            </div>
            @endif

            @if(in_array($role, ['gm','manager','finance']))
            <div class="bb-navsec">
                <div class="bb-navsec-label">Keuangan</div>
                <a href="{{ route('subscriptions.index') }}" class="bb-navitem {{ $navActive('subscriptions*') }}"><span class="material-symbols-outlined">autorenew</span><span>Subscription</span></a>
                <a href="{{ route('finance.index') }}" class="bb-navitem {{ $navActive('finance*') }}"><span class="material-symbols-outlined">payments</span><span>Finance &amp; Billing</span></a>
            </div>
            @endif

            @if(in_array($role, ['gm','manager','sales']))
            <div class="bb-navsec">
                <div class="bb-navsec-label">Intelligence</div>
                <a href="{{ route('kpi.index') }}" class="bb-navitem {{ $navActive('kpi.index') }}"><span class="material-symbols-outlined">leaderboard</span><span>KPI Target</span></a>
                @if(in_array($role, ['gm','manager']))
                <a href="{{ route('analytics.index') }}" class="bb-navitem {{ $navActive('analytics*') }}"><span class="material-symbols-outlined">query_stats</span><span>Reports &amp; Analytics</span></a>
                @endif
            </div>
            @endif
        </nav>

        <div style="padding:14px 12px;border-top:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,0.10);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-family:var(--font-brand);">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
            <div style="min-width:0;flex:1;">
                <div style="font-size:12px;font-weight:700;color:#fff;font-family:var(--font-brand);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name ?? 'User' }}</div>
                <div style="font-size:10px;color:var(--bb-text-on-dark-muted);text-transform:uppercase;letter-spacing:0.08em;">{{ $roleLabels[$role] ?? strtoupper($role) }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="bb-iconbtn" style="background:transparent;border-color:rgba(255,255,255,0.12);color:#fff;" title="Logout">
                    <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN ── --}}
    <div class="bb-main">
        <header class="bb-topbar">
            <div class="bb-search">
                <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                <span>Pencarian cepat…</span>
            </div>
            <div style="flex:1"></div>

            {{-- Skin switch --}}
            <div class="bb-seg">
                <a href="{{ route('skin.switch', 'classic') }}" class="active">Classic</a>
                <a href="{{ route('skin.switch', 'modern') }}">Modern</a>
            </div>

            {{-- Dark / light --}}
            <button class="bb-iconbtn" title="Mode gelap/terang" @click="$store.theme.toggle()">
                <span class="material-symbols-outlined" style="font-size:19px;" x-show="$store.theme.mode !== 'dark'">dark_mode</span>
                <span class="material-symbols-outlined" style="font-size:19px;" x-show="$store.theme.mode === 'dark'">light_mode</span>
            </button>

            {{-- Notifications --}}
            <button class="bb-iconbtn" title="Notifikasi"><span class="material-symbols-outlined" style="font-size:19px;">notifications</span></button>

            {{-- Language --}}
            <div class="bb-seg">
                <a href="{{ route('language.switch', 'id') }}" class="{{ app()->getLocale() === 'id' ? 'active' : '' }}">ID</a>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            </div>

            {{-- Avatar --}}
            <div style="display:flex;align-items:center;gap:9px;padding-left:6px;">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--bb-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;font-family:var(--font-brand);">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </div>
            </div>
        </header>

        @hasSection('flash') @yield('flash') @endif
        <div style="flex:1;overflow-y:auto;">
            <div class="bb-content">
                @yield('content')
            </div>
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
