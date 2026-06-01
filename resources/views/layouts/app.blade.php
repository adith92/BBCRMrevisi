<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BlueERP — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            navy: '#042C53', blue: '#185FA5', accent: '#378ADD',
        }, fontFamily: { sans: ['Inter','sans-serif'] }}}
    }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .popup-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:50; display:flex; align-items:center; justify-content:center; }
        .popup-content { background:white; border-radius:8px; padding:24px; max-width:600px; width:90%; max-height:80vh; overflow-y:auto; }
    </style>
</head>
<body class="min-h-screen">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-navy text-white flex-shrink-0 flex flex-col">
        <div class="p-4 border-b border-white/10">
            <h1 class="text-xl font-bold">🐦 BlueERP</h1>
            <p class="text-xs text-white/60 mt-1">Fleet Management System</p>
        </div>
        <nav class="flex-1 p-3 space-y-1">
            @php $role = auth()->user()->role; $current = request()->route() ? request()->route()->getName() : ''; @endphp
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'dashboard') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                📊 Dashboard
            </a>
            @if(in_array($role,['sales','gm']))
            <a href="{{ route('bookings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'bookings') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                📅 Bookings
            </a>
            <a href="{{ route('clients.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'clients') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                👥 Clients
            </a>
            @endif
            @if(in_array($role,['operational','gm']))
            <a href="{{ route('fleet.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'fleet') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                🚗 Fleet
            </a>
            <a href="{{ route('pool.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'pool') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                🅿️ Pool
            </a>
            <a href="{{ route('maintenance.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'maintenance') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                🔧 Maintenance
            </a>
            @endif
            @if(in_array($role,['finance','gm']))
            <a href="{{ route('finance.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ str_contains($current,'finance') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10' }}">
                💰 Finance
            </a>
            @endif
        </nav>
        <div class="p-4 border-t border-white/10 text-xs text-white/50">v3.0 — {{ now()->year }}</div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col">
        <!-- Navbar -->
        <header class="bg-white shadow-sm px-6 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <span class="px-2 py-1 rounded text-xs font-medium
                    @if($role==='gm') bg-purple-100 text-purple-700
                    @elseif($role==='sales') bg-blue-100 text-blue-700
                    @elseif($role==='operational') bg-green-100 text-green-700
                    @else bg-yellow-100 text-yellow-700 @endif">{{ ucfirst($role) }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
                </form>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<!-- Popup System -->
<div id="popup-stack" class="fixed inset-0 pointer-events-none z-50"></div>

<script>
// IDR Formatting
function formatIDR(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}
function initIDRMasking(root) {
    (root||document).querySelectorAll('.idr-input').forEach(el => {
        el.addEventListener('input', function() {
            let v = this.value.replace(/\D/g,'');
            this.value = v ? Number(v).toLocaleString('id-ID') : '';
        });
        el.closest('form')?.addEventListener('submit', function() {
            el.value = el.value.replace(/\D/g,'');
        });
    });
}

// Popup System
let popupCount = 0;
const MAX_POPUPS = 3;
window.BlueERP = {
    popup(title, content) {
        if (popupCount >= MAX_POPUPS) {
            const stack = document.getElementById('popup-stack');
            if (stack.firstElementChild) stack.firstElementChild.remove();
            popupCount--;
        }
        const id = 'popup-' + Date.now();
        const html = `<div id="${id}" class="popup-overlay pointer-events-auto" onclick="if(event.target===this)BlueERP.closePopup('${id}')">
            <div class="popup-content shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-navy">${title}</h3>
                    <button onclick="BlueERP.closePopup('${id}')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <div>${content}</div>
            </div></div>`;
        document.getElementById('popup-stack').insertAdjacentHTML('beforeend', html);
        popupCount++;
    },
    closePopup(id) {
        const el = document.getElementById(id);
        if (el) { el.remove(); popupCount--; }
    },
    closeTopPopup() {
        const stack = document.getElementById('popup-stack');
        if (stack.lastElementChild) { stack.lastElementChild.remove(); popupCount--; }
    },
    toast(title, message, type='info', ttl=4000) {
        const colors = {success:'bg-green-500',error:'bg-red-500',warning:'bg-yellow-500',info:'bg-blue-500'};
        const div = document.createElement('div');
        div.className = `fixed top-4 right-4 ${colors[type]||colors.info} text-white px-4 py-3 rounded-lg shadow-lg z-[100] max-w-sm`;
        div.innerHTML = `<strong>${title}</strong><br><span class="text-sm">${message}</span>`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), ttl);
    }
};
document.addEventListener('keydown', e => { if (e.key === 'Escape') BlueERP.closeTopPopup(); });

// Init IDR masking on load
document.addEventListener('DOMContentLoaded', () => initIDRMasking());
</script>
@stack('scripts')
</body>
</html>
