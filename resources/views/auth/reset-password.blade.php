<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipo ContaFacil – Create New Password</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy:   '#0d1b2a',
                            deep:   '#0a1929',
                            panel:  '#1a2e42',
                            card:   '#1e3347',
                            accent: '#2a9fd6',
                            'accent-hover': '#1e8abf',
                            muted:  '#4a7a9b',
                            border: '#1e3a52',
                            text:   '#c8dce8',
                            dim:    '#7a9bb5',
                        },
                    },
                    fontFamily: {
                        sans:    ['"DM Sans"', 'sans-serif'],
                        display: ['"DM Serif Display"', 'serif'],
                    },
                },
            },
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #0d1b2a; margin: 0; }

        /* Right panel background */
        .panel-bg {
            position: relative;
            overflow: hidden;
        }
        .panel-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(13,27,42,.6) 0%, rgba(10,25,41,.88) 65%),
                url('https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=1200&q=80')
                center/cover no-repeat;
            z-index: 0;
        }

        /* Input */
        .form-input {
            background: #f8fafc;
            border: 1px solid #d1dde8;
            border-radius: 6px;
            width: 100%;
            padding: 11px 42px 11px 14px;
            font-size: 14px;
            color: #1a2a3a;
            outline: none;
            letter-spacing: .15em;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input::placeholder { color: #9ab0c4; letter-spacing: .15em; }
        .form-input:focus {
            border-color: #2a9fd6;
            box-shadow: 0 0 0 3px rgba(42,159,214,.15);
        }

        /* Toggle eye button */
        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px;
            color: #9ab0c4;
            display: flex;
            align-items: center;
            transition: color .15s;
        }
        .eye-btn:hover { color: #4a7a9b; }

        /* Submit button */
        .btn-primary {
            background: #0d1b2a;
            color: #fff;
            border: none;
            border-radius: 6px;
            width: 100%;
            padding: 13px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .2s, transform .1s;
        }
        .btn-primary:hover  { background: #162a3d; }
        .btn-primary:active { transform: scale(.99); }

        /* Validation hint */
        .hint {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .hint-ok   { color: #2a9fd6; }
        .hint-idle { color: #9ab0c4; }

        /* Glass card on right panel */
        .glass-card {
            background: rgba(255,255,255,.06);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px;
            padding: 28px 28px 24px;
        }

        /* Avatar stack */
        .avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            border: 2px solid #0d1b2a;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 600; color: #fff;
        }
        .avatar-jd { background: #2a6d9f; }
        .avatar-an { background: #1a4d72; }
        .avatar-rc { background: #3a8fc4; }

        /* Progress bar */
        .progress-track {
            height: 5px;
            background: rgba(255,255,255,.1);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 7px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #2a9fd6, #4ec4ee);
        }

        /* Fade-up */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up  { animation: fadeUp .5s ease both; }
        .delay-1  { animation-delay: .07s; }
        .delay-2  { animation-delay: .14s; }
        .delay-3  { animation-delay: .21s; }
        .delay-4  { animation-delay: .28s; }
        .delay-5  { animation-delay: .35s; }
    </style>
</head>

<body class="min-h-screen flex">

    {{-- ════════════ LEFT PANEL ════════════ --}}
    <div class="w-full lg:w-[40%] xl:w-[38%] bg-white flex flex-col min-h-screen">

        {{-- Logo --}}
        <header class="px-10 pt-8 fade-up">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-brand-navy rounded-md flex items-center justify-center flex-shrink-0">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                        <rect x="2" y="2" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                        <rect x="12" y="2" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                        <rect x="2" y="12" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                        <rect x="12" y="12" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                    </svg>
                </div>
                <span class="text-[15px] font-semibold text-gray-800 tracking-tight">ContaFacil</span>
            </div>
        </header>

        {{-- Form --}}
        <main class="flex-1 flex items-center justify-center px-10 xl:px-14">
            <div class="w-full max-w-[380px]">

                {{-- Heading --}}
                <div class="mb-8 fade-up delay-1">
                    <h1 class="text-[21px] font-semibold text-gray-900 mb-1">Activa tu cuenta</h1>
                    <p class="text-sm text-gray-500">Crea tu password para acceder al sistema.</p>
                </div>

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-600 fade-up">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                    {{-- New Password --}}
                    <div class="mb-5 fade-up delay-2">
                        <label class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                            Nuevo Password
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                class="form-input"
                                oninput="checkRules()"
                            >
                            <button type="button" class="eye-btn" onclick="toggleVisibility('password', 'eye1')">
                                <svg id="eye1" width="17" height="17" viewBox="0 0 20 20" fill="none">
                                    <path d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="1.4"/>
                                    <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-5 fade-up delay-3">
                        <label class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                            Confirma Password
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                class="form-input"
                            >
                            <button type="button" class="eye-btn" onclick="toggleVisibility('password_confirmation', 'eye2')">
                                <svg id="eye2" width="17" height="17" viewBox="0 0 20 20" fill="none">
                                    <path d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="1.4"/>
                                    <circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Validation hints --}}
                    <div class="flex items-center gap-6 mb-7 fade-up delay-4">
                        <span id="hint-length" class="hint hint-idle">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.4"/>
                                <path id="hint-length-check" d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="display:none"/>
                            </svg>
                            8+ characters
                        </span>
                        <span id="hint-symbol" class="hint hint-idle">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.4"/>
                                <path id="hint-symbol-check" d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="display:none"/>
                            </svg>
                            One symbol
                        </span>
                    </div>

                    {{-- Submit --}}
                    <div class="fade-up delay-5">
                        <button type="submit" class="btn-primary">
                            Create Password
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.6"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Back to login --}}
                <div class="mt-6 text-center fade-up delay-5">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-700 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
                            <path d="M16 10H4M8 6l-4 4 4 4" stroke="currentColor" stroke-width="1.6"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Login
                    </a>
                </div>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="px-10 py-6 text-[11px] text-gray-400">
            © 2024 Equinox Ledger Systems. ISO 27001 Certified.
        </footer>

    </div>

    {{-- ════════════ RIGHT PANEL ════════════ --}}
    <div class="hidden lg:flex lg:w-[60%] xl:w-[62%] panel-bg flex-col justify-center items-center min-h-screen">

        <div class="relative z-10 w-full max-w-[500px] px-10">

            {{-- Glass card – Fiscal Integrity Index --}}
            <div class="glass-card mb-12">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-semibold tracking-[.18em] text-brand-dim uppercase mb-1">
                            Fiscal Integrity Index
                        </p>
                        <p class="text-white text-[28px] font-semibold leading-none">99.98%</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-brand-accent/20 border border-brand-accent/30
                                flex items-center justify-center flex-shrink-0">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2l1.5 4.5H16l-3.7 2.7 1.4 4.3L10 11l-3.7 2.5 1.4-4.3L4 6.5h4.5z"
                                  stroke="#2a9fd6" stroke-width="1.3" stroke-linejoin="round" fill="rgba(42,159,214,.2)"/>
                        </svg>
                    </div>
                </div>

                {{-- Progress bars --}}
                <div class="mb-6 space-y-0">
                    <div class="progress-track">
                        <div class="progress-fill" style="width:96%"></div>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:82%; opacity:.75;"></div>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:91%; opacity:.6;"></div>
                    </div>
                </div>

                {{-- Trust row --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <div class="avatar avatar-jd">JD</div>
                        <div class="avatar avatar-an">AN</div>
                        <div class="avatar avatar-rc">RC</div>
                    </div>
                    <p class="text-brand-text text-[13px] leading-snug">
                        Trusted by over 4,000 elite accounting firms worldwide.
                    </p>
                </div>
            </div>

            {{-- Bottom badge row --}}
            <div class="flex items-center justify-center gap-0">

                <div class="flex flex-col items-center px-10">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="mb-2 text-brand-dim">
                        <path d="M12 3l7 4v5c0 4.5-3 8.7-7 10C5 20.7 2 16.5 2 12V7l10-4z"
                              stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <p class="text-[10px] tracking-[.14em] text-brand-dim uppercase font-semibold">SOC2 Type II</p>
                </div>

                <div class="w-px h-10 bg-brand-border"></div>

                <div class="flex flex-col items-center px-10">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="mb-2 text-brand-dim">
                        <path d="M12 3l7 4v5c0 4.5-3 8.7-7 10C5 20.7 2 16.5 2 12V7l10-4z"
                              stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <rect x="9" y="10" width="6" height="5" rx="1" stroke="currentColor" stroke-width="1.3"/>
                        <path d="M10 10V8.5a2 2 0 014 0V10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                    <p class="text-[10px] tracking-[.14em] text-brand-dim uppercase font-semibold">256-bit AES</p>
                </div>

                <div class="w-px h-10 bg-brand-border"></div>

                <div class="flex flex-col items-center px-10">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="mb-2 text-brand-dim">
                        <polyline points="2,14 6,10 10,14 14,8 18,12 22,8"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="text-[10px] tracking-[.14em] text-brand-dim uppercase font-semibold">Real-Time Sync</p>
                </div>

            </div>

        </div>
    </div>

    {{-- ════════════ JS: eye toggle + validation hints ════════════ --}}
    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.innerHTML = isHidden
                ? `<path d="M3 3l14 14M10.5 10.7A3 3 0 0013.3 13M6.8 6.9C4.5 8.2 2.7 10 2.7 10s2.7 5 7.3 5c1.5 0 2.8-.5 3.9-1.2M9 4.2C9.6 4.1 10.3 4 11 4c4.6 0 7.3 5 7.3 5s-.7 1.2-2 2.4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>`
                : `<path d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.4"/>`;
        }

        function checkRules() {
            const val = document.getElementById('password').value;

            const hasLength = val.length >= 8;
            const hasSymbol = /[^a-zA-Z0-9]/.test(val);

            applyHint('hint-length', 'hint-length-check', hasLength);
            applyHint('hint-symbol', 'hint-symbol-check', hasSymbol);
        }

        function applyHint(hintId, checkId, active) {
            const hint  = document.getElementById(hintId);
            const check = document.getElementById(checkId);
            if (active) {
                hint.classList.remove('hint-idle');
                hint.classList.add('hint-ok');
                check.style.display = 'block';
            } else {
                hint.classList.remove('hint-ok');
                hint.classList.add('hint-idle');
                check.style.display = 'none';
            }
        }
    </script>

</body>
</html>