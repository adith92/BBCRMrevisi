@php
    $role = Auth::user()->role ?? '';
    $roleIcons  = ['director'=>'👔','gm'=>'🏢','manager'=>'📊','sales'=>'💼','operational'=>'🚗','pool'=>'🅿️','finance'=>'💰'];
    $roleLabels = ['director'=>'Director HQ','gm'=>'General Manager','manager'=>'Manager','sales'=>'Sales Officer','operational'=>'Operations','pool'=>'Pool Admin','finance'=>'Finance'];
    $demoSwitchGm = \App\Models\User::where('role', 'gm')
        ->orderByRaw("CASE WHEN email = 'gm@goldenbird.co.id' THEN 0 ELSE 1 END")
        ->orderBy('id')
        ->first();
    $demoSwitchManagers = \App\Models\User::where('role', 'manager')
        ->with(['subordinates' => fn ($query) => $query->where('role', 'sales')->orderBy('name')])
        ->orderBy('name')
        ->get();
    $demoSwitchOps = \App\Models\User::whereIn('role', ['operational', 'pool', 'finance'])
        ->orderByRaw("CASE role WHEN 'operational' THEN 1 WHEN 'pool' THEN 2 WHEN 'finance' THEN 3 ELSE 4 END")
        ->orderBy('name')
        ->get()
        ->groupBy('role');
    $demoSwitchCount = ($demoSwitchGm ? 1 : 0)
        + $demoSwitchManagers->count()
        + $demoSwitchManagers->sum(fn ($manager) => $manager->subordinates->count())
        + $demoSwitchOps->flatten()->count();
@endphp

<aside id="sidebar" 
       :class="sidebarOpen ? 'w-56 md:w-[224px] md:min-w-[224px]' : 'w-16 md:w-[64px] md:min-w-[64px]'" 
       class="sidebar fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300 ease-in-out z-50 flex flex-col min-h-screen">

    {{-- Brand --}}
    <div class="hidden md:flex items-center gap-3 px-3 py-5" style="border-bottom:1px solid var(--cc-sidebar-divider);">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,rgba(0,229,255,0.15),rgba(59,130,246,0.15)); border:1px solid rgba(0,229,255,0.2);">
            <span class="material-symbols-outlined text-[20px]" style="color:var(--color-primary);">directions_bus</span>
        </div>
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="overflow-hidden whitespace-nowrap">
            <div class="text-sm font-bold text-[var(--cc-sidebar-brand)] leading-tight">{{ __('ui.bluebird_crm') }}</div>
            <div class="text-[9px] uppercase tracking-widest font-semibold text-[var(--cc-sidebar-user-role)]">{{ __('ui.command_center') }}</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-grow overflow-y-auto px-3 py-3 space-y-0.5">

        @if($demoSwitchCount > 1)
        <div x-show="sidebarOpen" class="nav-section-label">Demo Switch</div>
        <div x-data="{ open: false }" class="relative mb-2">
            <button type="button"
                    @click="open = !open"
                    class="nav-item w-full flex items-center justify-between"
                    :class="open ? 'active' : ''">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">switch_account</span>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Quick Switch Role</span>
                </div>
                <span x-show="sidebarOpen" class="material-symbols-outlined text-[16px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>

            <div x-show="open && sidebarOpen" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-1 rounded-xl border border-[var(--cc-sidebar-divider)] bg-black/5 dark:bg-slate-950/40 overflow-hidden">
                @if($demoSwitchGm)
                <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest font-bold text-[var(--cc-sidebar-user-role)]">General Management</div>
                <div class="space-y-0.5 px-1 pb-2">
                    <form method="POST" action="{{ route('system.switch-demo-user') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $demoSwitchGm->id }}">
                        <button type="submit"
                                class="w-full flex items-center gap-2 rounded-lg px-2 py-2 text-left text-xs transition-all {{ auth()->id() === $demoSwitchGm->id ? 'bg-blue-500/15 text-blue-400' : 'text-[var(--cc-sidebar-link)] hover:bg-white/10' }}">
                            <span class="material-symbols-outlined text-[15px]">apartment</span>
                            <span class="min-w-0">
                                <span class="block truncate font-semibold">{{ $demoSwitchGm->name }}</span>
                                <span class="block truncate text-[10px] opacity-70">General Manager</span>
                            </span>
                        </button>
                    </form>
                </div>
                @endif

                @if($demoSwitchManagers->isNotEmpty())
                <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest font-bold text-[var(--cc-sidebar-user-role)]">Sales Managers</div>
                <div class="space-y-1 px-1 pb-2">
                    @foreach($demoSwitchManagers as $switchManager)
                    <div x-data="{ expanded: {{ $switchManager->id === auth()->user()->manager_id || auth()->id() === $switchManager->id ? 'true' : 'false' }} }" class="rounded-lg overflow-hidden">
                        <div class="flex items-stretch {{ auth()->id() === $switchManager->id ? 'bg-blue-500/15 text-blue-400' : 'text-[var(--cc-sidebar-link)] hover:bg-white/10' }} rounded-lg transition-all">
                            <form method="POST" action="{{ route('system.switch-demo-user') }}" class="min-w-0 flex-1">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $switchManager->id }}">
                                <button type="submit" class="w-full flex items-center gap-2 px-2 py-2 text-left text-xs">
                                    <span class="material-symbols-outlined text-[15px]">leaderboard</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold">{{ $switchManager->name }}</span>
                                        <span class="block truncate text-[10px] opacity-70">{{ $switchManager->subordinates->count() }} Sales Rep</span>
                                    </span>
                                </button>
                            </form>
                            @if($switchManager->subordinates->isNotEmpty())
                            <button type="button"
                                    @click.stop="expanded = !expanded"
                                    class="flex w-8 items-center justify-center text-[var(--cc-sidebar-user-role)] hover:text-blue-400"
                                    title="Lihat sales rep">
                                <span class="material-symbols-outlined text-[16px] transition-transform" :class="expanded ? 'rotate-90' : ''">chevron_right</span>
                            </button>
                            @endif
                        </div>

                        @if($switchManager->subordinates->isNotEmpty())
                        <div x-show="expanded" x-cloak x-transition class="ml-4 mt-1 space-y-0.5 border-l border-[var(--cc-sidebar-divider)] pl-2">
                            @foreach($switchManager->subordinates as $switchSales)
                            <form method="POST" action="{{ route('system.switch-demo-user') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $switchSales->id }}">
                                <button type="submit"
                                        class="w-full flex items-center gap-2 rounded-lg px-2 py-1.5 text-left text-[11px] transition-all {{ auth()->id() === $switchSales->id ? 'bg-blue-500/15 text-blue-400' : 'text-[var(--cc-sidebar-link)] hover:bg-white/10' }}">
                                    <span class="material-symbols-outlined text-[13px]">business_center</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold">{{ $switchSales->name }}</span>
                                        <span class="block truncate text-[9px] opacity-70">Sales Rep</span>
                                    </span>
                                </button>
                            </form>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @php
                    $switchGroups = [
                        'operational' => 'Operations',
                        'pool' => 'Pool',
                        'finance' => 'Finance',
                    ];
                @endphp
                @foreach($switchGroups as $switchRole => $switchLabel)
                    @if(($demoSwitchOps[$switchRole] ?? collect())->isNotEmpty())
                    <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest font-bold text-[var(--cc-sidebar-user-role)]">{{ $switchLabel }}</div>
                    <div class="space-y-0.5 px-1 pb-2">
                        @foreach($demoSwitchOps[$switchRole] as $switchUser)
                        <form method="POST" action="{{ route('system.switch-demo-user') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $switchUser->id }}">
                            <button type="submit"
                                    class="w-full flex items-center gap-2 rounded-lg px-2 py-2 text-left text-xs transition-all {{ auth()->id() === $switchUser->id ? 'bg-blue-500/15 text-blue-400' : 'text-[var(--cc-sidebar-link)] hover:bg-white/10' }}">
                                <span class="material-symbols-outlined text-[15px]">
                                    {{ $switchRole === 'gm' ? 'apartment' : ($switchRole === 'manager' ? 'leaderboard' : ($switchRole === 'sales' ? 'business_center' : ($switchRole === 'finance' ? 'payments' : 'badge'))) }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $switchUser->name }}</span>
                                    <span class="block truncate text-[10px] opacity-70">{{ $roleLabels[$switchRole] ?? strtoupper($switchRole) }}</span>
                                </span>
                            </button>
                        </form>
                        @endforeach
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <div x-show="sidebarOpen" class="nav-section-label">{{ __('ui.main') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">space_dashboard</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.dashboard') }}</span>
        </a>

        @if(in_array($role, ['gm','manager','sales']))
        <div x-show="sidebarOpen" class="nav-section-label">{{ __('ui.sales') }}</div>

        <a href="{{ route('pipeline.index') }}" class="nav-item {{ Request::routeIs('pipeline*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">view_kanban</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Deals Board (Kanban)</span>
        </a>

        <a href="{{ route('opportunities.index') }}" class="nav-item {{ Request::routeIs('opportunities*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">handshake</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">All Opportunities</span>
        </a>

        <a href="{{ route('clients.index') }}" class="nav-item {{ Request::routeIs('clients*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">corporate_fare</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.clients') }}</span>
        </a>
        @endif

        @if(in_array($role, ['gm','manager','operational','sales','pool']))
        <div x-show="sidebarOpen" class="nav-section-label">{{ __('ui.operations') }}</div>

        <a href="{{ route('fleet.index') }}" class="nav-item {{ Request::routeIs('fleet*','vehicles*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">local_shipping</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Fleet / Armada</span>
        </a>
        <a href="{{ route('drivers.index') }}" class="nav-item {{ Request::routeIs('drivers*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">badge</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Driver / Supir</span>
        </a>
        @endif

        <a href="{{ route('bookings.index') }}" class="nav-item {{ Request::routeIs('bookings*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">route</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.dispatch') }}</span>
        </a>

        @if(in_array($role, ['gm','manager','finance']))
        <div x-show="sidebarOpen" class="nav-section-label">{{ __('ui.finance') }}</div>

        <a href="{{ route('subscriptions.index') }}" class="nav-item {{ Request::routeIs('subscriptions*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">autorenew</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.subscriptions') }}</span>
        </a>

        <a href="{{ route('finance.index') }}" class="nav-item {{ Request::routeIs('finance*','invoices*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">payments</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.finance_billing') }}</span>
        </a>
        @endif

        @if(in_array($role, ['gm','manager','sales']))
        <a href="{{ route('products.index') }}" class="nav-item {{ Request::routeIs('products*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">menu_book</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.price_book') }}</span>
        </a>

        <div x-show="sidebarOpen" class="nav-section-label">{{ __('ui.intelligence') }}</div>

        <a href="{{ route('kpi.index') }}" class="nav-item {{ Request::routeIs('kpi.index') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">leaderboard</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.kpi_target') }}</span>
        </a>
        @if(in_array($role, ['gm','manager']))
        <a href="{{ route('kpi.targets') }}" class="nav-item {{ Request::routeIs('kpi.targets') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">track_changes</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Set Sales Targets</span>
        </a>
        @endif
        @endif

        @if(in_array($role, ['gm','manager']))
        <a href="{{ route('analytics.index') }}" class="nav-item {{ Request::routeIs('analytics*') ? 'active' : '' }} flex items-center gap-3">
            <span class="material-symbols-outlined">query_stats</span>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">{{ __('ui.reports_analytics') }}</span>
        </a>
        @endif

        {{-- Quick Add Dropdown --}}
        @if(in_array($role, ['gm','manager','sales','operational']))
        <div x-show="sidebarOpen" class="nav-section-label mt-3">{{ __('ui.quick_add') ?? 'Tambah Baru' }}</div>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="nav-item w-full flex items-center justify-between"
                    :class="open ? 'active' : ''">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="whitespace-nowrap">Tambah Baru</span>
                </div>
                <span x-show="sidebarOpen" class="material-symbols-outlined text-[16px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="open && sidebarOpen" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-1 ml-2 space-y-0.5 border-l-2 pl-3"
                 style="border-color: var(--cc-sidebar-divider)">
                @if(in_array($role, ['gm','manager','sales']))
                <a href="{{ route('opportunities.create') }}" class="nav-item text-[12px] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[14px]">star</span>
                    <span>Deal / Opportunity</span>
                </a>
                @endif
                @if($role === 'sales')
                <a href="{{ route('clients.create') }}" class="nav-item text-[12px] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[14px]">corporate_fare</span>
                    <span>Klien Baru</span>
                </a>
                @endif
                @if(in_array($role, ['gm','manager','sales','operational']))
                <a href="{{ route('bookings.create') }}" class="nav-item text-[12px] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[14px]">route</span>
                    <span>Booking</span>
                </a>
                @endif

            </div>
        </div>
        @endif

    </nav>

    {{-- Sidebar Footer --}}
    <div class="px-3 py-4 space-y-3" style="border-top:1px solid var(--cc-sidebar-divider);">
        <div class="flex items-center gap-2.5 px-1">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base flex-shrink-0" style="background: var(--cc-sidebar-user-bg); border: 1px solid var(--cc-sidebar-user-bd);">
                {{ $roleIcons[$role] ?? '👤' }}
            </div>
            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" class="overflow-hidden whitespace-nowrap">
                <div class="text-xs font-bold text-[var(--cc-sidebar-user)] truncate">{{ Auth::user()->name ?? 'User' }}</div>
                <div class="text-[9px] uppercase tracking-wider font-semibold truncate text-[var(--cc-sidebar-user-role)]">{{ $roleLabels[$role] ?? strtoupper($role) }}</div>
            </div>
        </div>
        
        @if($role === 'gm')
            <div x-data="{ open: false, amount: 100, customAmount: '', loading: false, message: '', isError: false }" class="mb-2">
                <!-- Seeder Trigger Button -->
                <button @click="open = true" type="button" class="w-full flex items-center justify-center gap-2 py-2 mb-2 rounded-lg text-xs font-semibold transition-all text-amber-400 border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/15">
                    <span class="material-symbols-outlined text-[15px]">database</span>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Seed Demo Data</span>
                </button>

                <!-- Alpine.js Modal overlay -->
                <div x-show="open" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @keydown.escape.window="open = false">
                    <!-- Modal card -->
                    <div @click.away="if (!loading) open = false" class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-500">database</span>
                                Seed Demo Data
                            </h3>
                            <button @click="open = false" :disabled="loading" class="text-slate-400 hover:text-slate-600 disabled:opacity-50">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>

                        <div x-show="!loading" class="space-y-4 text-xs">
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-left">
                                Pilih jumlah data demo (Opportunity, Client, dll.) yang ingin Anda tambahkan ke sistem secara otomatis dengan kalkulasi finansial riil.
                            </p>
                            
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="amount = 100" class="py-2 rounded-lg font-bold border transition-all"
                                        :class="amount === 100 ? 'bg-amber-500/10 dark:bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'">
                                    100 Data
                                </button>
                                <button type="button" @click="amount = 1000" class="py-2 rounded-lg font-bold border transition-all"
                                        :class="amount === 1000 ? 'bg-amber-500/10 dark:bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'">
                                    1.000 Data
                                </button>
                                <button type="button" @click="amount = 10000" class="py-2 rounded-lg font-bold border transition-all"
                                        :class="amount === 10000 ? 'bg-amber-500/10 dark:bg-amber-500/20 border-amber-500 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'">
                                    10.000 Data
                                </button>
                            </div>

                            <div class="space-y-1 text-left">
                                <label class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Atau Custom Jumlah Data</label>
                                <input type="number" x-model="customAmount" placeholder="Contoh: 500" min="1" max="100000" 
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500/50" />
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="open = false" class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 rounded-lg font-semibold transition-all">
                                    Batal
                                </button>
                                <button type="button" 
                                        @click="
                                            loading = true;
                                            message = '';
                                            const finalAmount = customAmount ? parseInt(customAmount) : amount;
                                            fetch('/system/seed-demo', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ amount: finalAmount })
                                            })
                                            .then(res => res.json().then(data => ({ status: res.status, data })))
                                            .then(resObj => {
                                                loading = false;
                                                if (resObj.status === 200) {
                                                    message = 'Berhasil menambahkan ' + finalAmount + ' data demo!';
                                                    isError = false;
                                                    setTimeout(() => { open = false; window.location.reload(); }, 1500);
                                                } else {
                                                    message = resObj.data.message || 'Gagal menambahkan data demo.';
                                                    isError = true;
                                                }
                                            })
                                            .catch(err => {
                                                loading = false;
                                                message = 'Terjadi kesalahan sistem.';
                                                isError = true;
                                            });
                                        "
                                        class="flex-1 py-2 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-lg font-bold transition-all">
                                    Mulai Seeding
                                </button>
                            </div>
                        </div>

                        <!-- Loading Spinner -->
                        <div x-show="loading" class="flex flex-col items-center justify-center py-6 space-y-3">
                            <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-medium animate-pulse">Menghasilkan data demo riil, mohon tunggu...</span>
                        </div>

                        <!-- Success / Error Message -->
                        <div x-show="message" class="text-center py-2 px-3 rounded-lg text-xs font-semibold"
                             :class="isError ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' : 'bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20'">
                            <span x-text="message"></span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-semibold transition-all"
                style="color:#ef4444; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15);"
                onmouseover="this.style.background='rgba(239,68,68,0.18)'"
                onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                <span class="material-symbols-outlined text-[15px]">logout</span>
                <span x-show="sidebarOpen" class="whitespace-nowrap">{{ __('ui.logout') }}</span>
            </button>
        </form>
    </div>

    <!-- Toggle Close Sidebar Button -->
    <div class="border-t border-[var(--cc-sidebar-divider)] mt-auto hidden md:block">
        <button @click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar-open', sidebarOpen)"
                class="w-full flex items-center p-3 text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] hover:bg-[var(--cc-sidebar-user-bg)] transition-colors">
            <div class="grid size-10 place-content-center shrink-0">
                <span class="material-symbols-outlined transition-transform duration-300" :class="sidebarOpen ? 'rotate-180' : ''">keyboard_double_arrow_right</span>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity class="text-sm font-semibold whitespace-nowrap ml-1">Hide</span>
        </button>
    </div>

</aside>
