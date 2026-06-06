<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | Golden Bird CRM Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        :root {
            /* Bluebird Blue palette */
            --bb-blue-900: #001a3a;
            --bb-blue-800: #002d6b;
            --bb-blue-700: #003f99;
            --bb-blue-600: #0052cc;
            --bb-blue-500: #0066ff;
            --bb-blue-400: #3385ff;
            --bb-blue-300: #66a3ff;
            --bb-blue-200: #99c2ff;
            --bb-blue-glow: rgba(0, 102, 255, 0.18);
            --bb-blue-glow-sm: rgba(0, 102, 255, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #000d1f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Bluebird background atmosphere */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 70% at 20% 10%, rgba(0,82,204,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 80% 90%, rgba(0,41,128,0.22) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(0,66,204,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        /* Subtle grid */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,102,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,102,255,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Animated floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }
        .orb-1 { width: 400px; height: 400px; background: #0052cc; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: #003f99; bottom: -80px; right: -80px; animation-delay: -4s; }
        @keyframes float {
            0%,100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(20px, -20px) scale(1.05); }
        }

        .login-card {
            background: rgba(0, 8, 24, 0.88);
            border: 1px solid rgba(0, 102, 255, 0.2);
            border-radius: 22px;
            padding: 42px 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            backdrop-filter: blur(24px);
            box-shadow:
                0 30px 60px rgba(0,0,0,0.6),
                0 0 0 1px rgba(0,102,255,0.08),
                0 0 60px rgba(0,52,204,0.12) inset;
        }
        /* Top blue glow line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; transform: translateX(-50%);
            width: 70%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,102,255,0.6), rgba(51,133,255,0.8), rgba(0,102,255,0.6), transparent);
        }

        /* Logo icon */
        .logo-ring {
            width: 60px; height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(0,82,204,0.25), rgba(0,41,128,0.35));
            border: 1px solid rgba(0,102,255,0.35);
            box-shadow: 0 0 20px rgba(0,102,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }

        /* Input fields */
        .input-field {
            width: 100%;
            background: rgba(0, 30, 80, 0.3);
            border: 1px solid rgba(0, 82, 204, 0.2);
            border-radius: 10px;
            padding: 12px 14px;
            color: #e2e8f0;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(0, 102, 255, 0.55);
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.12);
            background: rgba(0, 40, 100, 0.25);
        }
        .input-field::placeholder { color: #2d4a7a; }
        .input-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #4a6fa5;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        /* Login button — Bluebird Blue */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #0052cc, #0066ff, #1a75ff);
            color: white;
            font-weight: 800;
            font-size: 14px;
            padding: 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 20px rgba(0,82,204,0.4);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0066ff, #3385ff, #4d94ff);
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(0,102,255,0.45);
        }
        .btn-login:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(0,82,204,0.3); }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .pulse-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.4;} }

        .demo-account {
            background: rgba(0, 30, 80, 0.25);
            border: 1px solid rgba(0, 82, 204, 0.15);
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .demo-account:hover {
            background: rgba(0, 82, 204, 0.12);
            border-color: rgba(0, 102, 255, 0.3);
            box-shadow: 0 0 12px rgba(0,82,204,0.1);
        }

        /* Version badge */
        .badge-bb {
            background: rgba(0,82,204,0.15);
            color: var(--bb-blue-300);
            border: 1px solid rgba(0,102,255,0.25);
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-live {
            background: rgba(16,185,129,0.1);
            color: #10b981;
            border: 1px solid rgba(16,185,129,0.2);
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .divider { border: none; border-top: 1px solid rgba(0,82,204,0.12); margin: 20px 0; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="relative z-10 w-full max-w-md px-4">
        <div class="login-card">

            {{-- Brand Header --}}
            <div class="text-center mb-8">
                <div class="logo-ring">
                    <span class="material-symbols-outlined text-[30px]" style="color:#3385ff;">directions_bus</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight" style="color:#ffffff;">
                    Bluebird <span style="color:#3385ff;">CRM</span>
                </h1>
                <p class="text-[11px] font-semibold uppercase tracking-widest mt-1" style="color:#2d4a7a;">
                    Command Center · B2B Fleet Management
                </p>
                <div class="flex items-center justify-center gap-2 mt-3">
                    <span class="badge-live flex items-center gap-1.5">
                        <span class="pulse-dot"></span>
                        Live
                    </span>
                    <span class="badge-bb">v7.7</span>
                    <span class="badge-bb">Render</span>
                </div>
            </div>

            {{-- Error Alert --}}
            @if($errors->any())
            <div class="mb-4 flex items-center gap-2 px-3 py-3 rounded-lg text-xs font-semibold"
                 style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171;">
                <span class="material-symbols-outlined text-[16px]">error</span>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="input-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           autocomplete="email" autofocus
                           class="input-field" placeholder="you@bluebird.co.id"/>
                </div>
                <div>
                    <label class="input-label">Password</label>
                    <input type="password" name="password" required
                           autocomplete="current-password"
                           class="input-field" placeholder="••••••••"/>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember"
                               style="accent-color:#0066ff;width:14px;height:14px;">
                        <span class="text-xs font-medium" style="color:#4a6fa5;">Remember me</span>
                    </label>
                </div>
                <button type="submit" class="btn-login mt-1">
                    <span class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        Masuk Command Center
                    </span>
                </button>
            </form>

            <hr class="divider">

            {{-- Demo Accounts --}}
            <div>
                <div class="text-[10px] font-bold uppercase tracking-widest mb-3 text-center"
                     style="color:#2d4a7a;">Demo Access — Klik untuk isi otomatis</div>
                <div class="grid grid-cols-2 gap-2">
                    @php
                    $demos = [
                        ['label' => 'Director HQ',   'email' => 'director@goldenbird.co.id', 'icon' => '👔', 'color' => '#99c2ff'],
                        ['label' => 'General Manager','email' => 'gm@goldenbird.co.id',       'icon' => '🏢', 'color' => '#66a3ff'],
                        ['label' => 'Manager',        'email' => 'manager@goldenbird.co.id',  'icon' => '📊', 'color' => '#34d399'],
                        ['label' => 'Sales Officer',  'email' => 'sales@goldenbird.co.id',    'icon' => '💼', 'color' => '#fbbf24'],
                    ];
                    @endphp
                    @foreach($demos as $d)
                    <div class="demo-account"
                         onclick="document.querySelector('[name=email]').value='{{ $d['email'] }}';document.querySelector('[name=password]').value='password123';">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">{{ $d['icon'] }}</span>
                            <div>
                                <div class="text-[10px] font-bold" style="color:{{ $d['color'] }};">{{ $d['label'] }}</div>
                                <div class="text-[9px] font-mono" style="color:#2d4a7a;">password123</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-5 text-center">
                <p class="text-[10px]" style="color:#1a3060;">
                    PT Blue Bird Group · B2B Fleet CRM · Jakarta 2026
                </p>
            </div>
        </div>
    </div>
</body>
</html>
