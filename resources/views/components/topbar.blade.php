@php
    $role = Auth::user()->role ?? '';
    $roleBadge = ['director'=>'Director HQ','gm'=>'GM','manager'=>'Manager','sales'=>'Sales','operational'=>'Ops','finance'=>'Finance'];
@endphp

<header id="topbar" class="topbar sticky top-0 h-14 flex items-center justify-between px-6 z-40">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs">
        <span class="font-semibold text-slate-600">Bluebird CRM</span>
        <span class="material-symbols-outlined text-[14px] text-slate-800">chevron_right</span>
        <span class="font-bold uppercase tracking-widest text-[#00e5ff] text-[11px]">
            @yield('header_title', 'Dashboard')
        </span>
    </div>

    {{-- Right side --}}
    <div class="flex items-center gap-2">
        <span class="badge-role">{{ $roleBadge[$role] ?? strtoupper($role) }}</span>
        <span class="badge-live">
            <span class="pulse-dot" style="width:6px;height:6px;"></span>
            Live
        </span>
        <span class="badge-demo hidden md:inline">Jun 2026</span>
    </div>
</header>
