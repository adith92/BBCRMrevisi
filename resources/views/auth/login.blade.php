<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — BlueERP</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        navy: '#042C53',
                        blueerp: '#185FA5',
                        accent: '#378ADD',
                        success: '#22C55E',
                        warning: '#F59E0B',
                        danger: '#EF4444'
                    },
                    borderRadius: { DEFAULT: '8px' }
                }
            }
        }

        function formatIDR(amount) {
            const n = parseInt((amount ?? 0).toString().replace(/\D/g, ''), 10) || 0;
            return 'Rp ' + n.toLocaleString('id-ID');
        }

        function initIDRMasking(root = document) {
            root.querySelectorAll('.idr-input').forEach(input => {
                if (input.dataset.idrBound === '1') return;
                input.dataset.idrBound = '1';

                input.addEventListener('input', function () {
                    const raw = this.value.replace(/\D/g, '');
                    this.dataset.raw = raw;
                    this.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
                });

                const form = input.closest('form');
                if (form && form.dataset.idrSubmitBound !== '1') {
                    form.dataset.idrSubmitBound = '1';
                    form.addEventListener('submit', function () {
                        this.querySelectorAll('.idr-input').forEach(el => {
                            el.value = el.value.replace(/\D/g, '');
                        });
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => initIDRMasking());
        window.formatIDR = formatIDR;
        window.initIDRMasking = initIDRMasking;
    </script>
</head>
<body class="h-full font-sans text-slate-800">
<div class="min-h-full flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-slate-200">
        <div class="px-6 py-6 border-b border-slate-100">
            <h1 class="text-2xl font-bold text-navy">BlueERP</h1>
            <p class="text-sm text-slate-500 mt-1">B2B Fleet Management & CRM</p>
        </div>

        <div class="px-6 py-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-danger/30 bg-danger/5 px-4 py-3 text-sm text-danger">
                    <p class="font-semibold mb-1">Login gagal:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="email">Email</label>
                    <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent"
                           placeholder="you@bluebird.co.id">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent"
                           placeholder="••••••••">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-blueerp hover:bg-navy text-white font-semibold py-2.5 transition">
                    Login
                </button>
            </form>

            <div class="mt-5 rounded-lg border border-blueerp/20 bg-blueerp/5 px-4 py-3 text-xs text-slate-700">
                <p class="font-semibold text-blueerp mb-1">Demo Account</p>
                <p>gm@bluebird.co.id / password123</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
