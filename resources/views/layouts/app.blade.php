<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $title ?? 'Bluebird CRM | Command Center' }}</title>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    {{-- Compiled Assets (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="cc-body-glow min-h-screen flex flex-col md:flex-row relative">

    {{-- Mobile Header --}}
    <div class="md:hidden w-full flex justify-between items-center px-5 py-4 z-50 relative sidebar">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:linear-gradient(135deg,#00e5ff22,#3b82f622); border:1px solid rgba(0,229,255,0.2);">
                <span class="material-symbols-outlined text-[18px]" style="color:#00e5ff;">directions_bus</span>
            </div>
            <span class="text-sm font-bold text-white tracking-wide">Bluebird CRM</span>
        </div>
        <button id="hamburger-btn" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 focus:outline-none">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>
    </div>

    {{-- Sidebar Component --}}
    <x-sidebar/>

    {{-- Main Content --}}
    <main class="flex-grow min-h-screen flex flex-col relative z-10 bg-cc-bg">
        <x-topbar/>
        <x-flash/>
        <div class="p-6 flex-grow">
            @yield('content')
        </div>
    </main>

    {{-- Mobile Sidebar Toggle --}}
    <script>
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        if (hamburgerBtn && sidebar) {
            let open = false;
            hamburgerBtn.addEventListener('click', () => {
                open = !open;
                sidebar.classList.toggle('-translate-x-full', !open);
            });
            document.addEventListener('click', (e) => {
                if (open && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                    open = false;
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
