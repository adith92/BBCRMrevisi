<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Masuk | Golden Bird B2B Fleet Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#003887",
                        "secondary": "#1960a6",
                        "surface-bright": "#f8f9ff",
                        "surface-container": "#e5eeff",
                        "on-primary": "#ffffff",
                        "on-primary-container": "#b2c7ff",
                        "primary-container": "#1e4fa8",
                        "outline-variant": "#c3c6d4"
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top left, #f8f9ff 0%, #e5eeff 100%);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <!-- Brand -->
    <div class="flex items-center gap-3 mb-8">
        <div class="p-2 bg-primary rounded-xl shadow-lg">
            <span class="material-symbols-outlined text-white text-[28px]">directions_bus</span>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-primary tracking-tight">Golden Bird CRM</h1>
            <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-widest">B2B Fleet Management System</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="w-full max-w-md mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">
            <span class="material-symbols-outlined text-red-600 text-[18px]">error</span>
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Card -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-outline-variant overflow-hidden">

        <!-- Card Header -->
        <div class="bg-gradient-to-r from-primary via-[#1e4fa8] to-secondary px-6 py-5">
            <h2 class="text-lg font-bold text-white">Masuk ke Akun</h2>
            <p class="text-[11px] text-on-primary-container mt-0.5">Pilih role Anda untuk langsung masuk — 1 klik</p>
        </div>

        <!-- 1-Click Role Buttons -->
        <div class="p-6">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Demo Accounts</p>

            <div class="grid grid-cols-2 gap-3">

                <!-- Director -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="director@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-purple-200 hover:border-purple-500 hover:bg-purple-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 group-hover:bg-purple-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">👔</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Director</div>
                            <div class="text-[10px] text-slate-400 font-medium">Full access</div>
                        </div>
                    </button>
                </form>

                <!-- GM -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="gm@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-blue-200 hover:border-blue-500 hover:bg-blue-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">🏢</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">GM</div>
                            <div class="text-[10px] text-slate-400 font-medium">General Manager</div>
                        </div>
                    </button>
                </form>

                <!-- Manager -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="manager@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-green-200 hover:border-green-500 hover:bg-green-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-green-100 group-hover:bg-green-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">📊</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Manager</div>
                            <div class="text-[10px] text-slate-400 font-medium">Sales Manager</div>
                        </div>
                    </button>
                </form>

                <!-- Sales -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="sales1@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-yellow-200 hover:border-yellow-500 hover:bg-yellow-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 group-hover:bg-yellow-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">💼</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Sales</div>
                            <div class="text-[10px] text-slate-400 font-medium">Account Executive</div>
                        </div>
                    </button>
                </form>

                <!-- Operational -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="ops@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-orange-200 hover:border-orange-500 hover:bg-orange-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">🚗</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Operational</div>
                            <div class="text-[10px] text-slate-400 font-medium">Fleet Ops</div>
                        </div>
                    </button>
                </form>

                <!-- Finance -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="email" value="finance@goldenbird.co.id">
                    <input type="hidden" name="password" value="password123">
                    <button type="submit" class="w-full flex items-center gap-3 p-3.5 rounded-xl border-2 border-emerald-200 hover:border-emerald-500 hover:bg-emerald-50 active:scale-95 transition-all group text-left cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">💰</div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">Finance</div>
                            <div class="text-[10px] text-slate-400 font-medium">Finance Team</div>
                        </div>
                    </button>
                </form>

            </div>

            <p class="text-center text-[11px] text-slate-400 mt-5">
                Password semua akun: <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-slate-600">password123</code>
            </p>
        </div>
    </div>

    <p class="mt-6 text-[11px] text-slate-400">
        © 2026 Golden Bird CRM · V7.2 · <span class="text-slate-500 font-semibold">Bluebird Group</span>
    </p>

</body>
</html>
