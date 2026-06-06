<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | Bluebird CRM Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #09090f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 60% at 30% 20%, rgba(0,229,255,0.04) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 70% 80%, rgba(139,92,246,0.04) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(59,130,246,0.03) 0%, transparent 60%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        .login-card {
            background: rgba(13,13,24,0.9);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            position: relative;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.04);
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; transform: translateX(-50%);
            width: 60%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,229,255,0.4), transparent);
        }
        .input-field {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 10px;
            padding: 11px 14px;
            color: #e2e8f0;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(0,229,255,0.35);
            box-shadow: 0 0 0 3px rgba(0,229,255,0.07);
            background: rgba(0,229,255,0.03);
        }
        .input-field::placeholder { color: #334155; }
        .input-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #0284c7, #3b82f6);
            color: white;
            font-weight: 800;
            font-size: 14px;
            padding: 13px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.02em;
        }
        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(59,130,246,0.3);
        }
        .btn-login:active { transform: translateY(0); }
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
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .demo-account:hover {
            background: rgba(0,229,255,0.05);
            border-color: rgba(0,229,255,0.15);
        }
    </style>
</head>
<body>
    <div class="relative z-10 w-full max-w-md px-4">
        <div class="login-card">

            <!-- Brand -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:linear-gradient(135deg,rgba(0,229,255,0.12),rgba(59,130,246,0.12)); border:1px solid rgba(0,229,255,0.2);">
                    <span class="material-symbols-outlined text-[28px]" style="color:#00e5ff;">directions_bus</span>
                </div>
                <h1 class="text-xl font-black text-white tracking-tight">Bluebird CRM</h1>
                <p class="text-xs font-semibold uppercase tracking-widest mt-1" style="color:#334155;">Command Center — B2B Fleet Management</p>
                <div class="flex items-center justify-center gap-3 mt-3">
                    <span style="background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.2);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.05em;" class="flex items-center gap-1.5">
                        <span class="pulse-dot"></span>
                        Live Demo
                    </span>
                    <span style="background:rgba(0,229,255,0.08);color:#00e5ff;border:1px solid rgba(0,229,255,0.15);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.05em;">Railway Deploy</span>
                </div>
            </div>

            <!-- Error -->
            @if($errors->any())
            <div class="mb-4 flex items-center gap-2 px-3 py-3 rounded-lg text-xs font-semibold" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;">
                <span class="material-symbols-outlined text-[16px]">error</span>
                {{ $errors->first() }}
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="input-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="input-field" placeholder="you@goldenbird.co.id"/>
                </div>
                <div>
                    <label class="input-label">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        class="input-field" placeholder="••••••••••"/>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" style="accent-color:#00e5ff;width:14px;height:14px;">
                        <span class="text-xs font-medium" style="color:#64748b;">Remember me</span>
                    </label>
                </div>
                <button type="submit" class="btn-login mt-2">
                    Access Command Center
                </button>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-6 pt-5" style="border-top:1px solid rgba(255,255,255,0.06);">
                <div class="text-[10px] font-bold uppercase tracking-widest mb-3 text-center" style="color:#334155;">Demo Access — Click to fill</div>
                <div class="grid grid-cols-2 gap-2">
                    @php
                    $demos = [
                        ['label'=>'Director HQ','email'=>'director@goldenbird.co.id','icon'=>'👔','color'=>'#a78bfa'],
                        ['label'=>'GM','email'=>'gm@goldenbird.co.id','icon'=>'🏢','color'=>'#60a5fa'],
                        ['label'=>'Manager','email'=>'manager@goldenbird.co.id','icon'=>'📊','color'=>'#34d399'],
                        ['label'=>'Sales Officer','email'=>'sales@goldenbird.co.id','icon'=>'💼','color'=>'#fbbf24'],
                    ];
                    @endphp
                    @foreach($demos as $d)
                    <div class="demo-account" onclick="document.querySelector('[name=email]').value='{{ $d['email'] }}';document.querySelector('[name=password]').value='password123';">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">{{ $d['icon'] }}</span>
                            <div>
                                <div class="text-[10px] font-bold" style="color:{{ $d['color'] }};">{{ $d['label'] }}</div>
                                <div class="text-[9px] font-mono" style="color:#334155;">password123</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-5 text-center">
                <p class="text-[10px]" style="color:#1e293b;">Golden Bird Group · B2B Fleet Management · 2026</p>
            </div>
        </div>
    </div>
</body>
</html>
