<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('page-title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { transition: transform 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: 0; top: 0; bottom: 0; z-index: 50; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            .sidebar-overlay.open { display: block; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">

        {{-- Mobile overlay --}}
        <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/40 z-40 md:hidden" onclick="toggleSidebar()"></div>

        {{-- Sidebar --}}
        <aside id="sidebar" class="sidebar w-64 bg-blue-900 text-white flex-shrink-0 flex flex-col overflow-y-auto">
            <div class="p-6 border-b border-blue-800">
                <h1 class="text-xl font-bold">🐦 Golden Bird</h1>
                <p class="text-blue-300 text-xs mt-1">CRM System v7.2</p>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-800 font-semibold' : '' }}">
                    📊 <span>Dashboard</span>
                </a>

                <a href="{{ route('bookings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('bookings.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    📅 <span>Bookings</span>
                </a>

                @if(auth()->user()->isGM() || auth()->user()->isSales() || auth()->user()->isFinance())
                <a href="{{ route('clients.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('clients.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    👥 <span>Clients</span>
                </a>
                @endif

                @if(auth()->user()->isGM() || auth()->user()->isOperational())
                <a href="{{ route('fleet.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('fleet.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    🚌 <span>Fleet</span>
                </a>
                @endif

                @if(auth()->user()->isGM() || auth()->user()->isFinance())
                <a href="{{ route('finance.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('finance.*') || request()->routeIs('invoices.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    💰 <span>Finance</span>
                </a>
                @endif

                @if(auth()->user()->isGM() || auth()->user()->isOperational())
                <a href="{{ route('maintenance.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('maintenance.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    🔧 <span>Maintenance</span>
                </a>
                @endif

                @if(auth()->user()->isSales())
                <a href="{{ route('sales.performance', auth()->id()) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('sales.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    📈 <span>My Performance</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isSales())
                <a href="{{ route('pipeline.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('pipeline.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Pipeline</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isSales())
                <a href="{{ route('opportunities.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('opportunities.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>Opportunities</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isFinance())
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Produk</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isSales())
                <a href="{{ route('approvals.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('approvals.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Persetujuan</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isSales())
                <a href="{{ route('activities.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('activities.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Aktivitas</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isSales())
                <a href="{{ route('kpi.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('kpi.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>KPI</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isFinance())
                <a href="{{ route('subscriptions.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('subscriptions.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Subscriptions</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isFinance())
                <a href="{{ route('vouchers.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('vouchers.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    <span>Vouchers</span>
                </a>
                @endif

                @if(auth()->user()->isDirector() || auth()->user()->isGM() || auth()->user()->isManager())
                <a href="{{ route('analytics.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('analytics.*') ? 'bg-blue-800 font-semibold' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    <span>Analytics</span>
                </a>
                @endif
            </nav>

            <div class="p-4 border-t border-blue-800">
                <p class="text-blue-200 text-sm font-medium">{{ auth()->user()->name }}</p>
                <span class="inline-block bg-blue-700 px-2 py-0.5 rounded text-xs font-semibold mt-1">{{ strtoupper(auth()->user()->role) }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-blue-300 text-xs hover:text-white transition-colors">🚪 Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto min-w-0">

            {{-- Top Navbar --}}
            <nav class="bg-white border-b border-gray-200 px-4 md:px-6 py-3 flex items-center justify-between sticky top-0 z-30">
                {{-- Hamburger (mobile) --}}
                <button onclick="toggleSidebar()" class="md:hidden text-gray-600 hover:text-gray-900 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h2 class="text-lg font-semibold text-gray-800 flex-1 ml-3 md:ml-0">@yield('page-title', 'Dashboard')</h2>

                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 hidden md:block">{{ now()->format('d M Y') }}</span>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded font-semibold uppercase">{{ auth()->user()->role }}</span>
                </div>
            </nav>

            {{-- Content --}}
            <div class="p-4 md:p-6">
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    </script>

    @stack('scripts')
</body>
</html>
