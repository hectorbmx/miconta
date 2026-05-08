<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equinox Ledger – Sign In</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (replace with your compiled asset in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy:    '#0d1b2a',
                            deep:    '#0a1929',
                            panel:   '#0f2336',
                            accent:  '#2a9fd6',
                            'accent-hover': '#1e8abf',
                            muted:   '#4a7a9b',
                            border:  '#1e3a52',
                            text:    '#c8dce8',
                            dim:     '#7a9bb5',
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
        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0d1b2a;
            margin: 0;
        }

        /* ── Right-panel background image overlay ── */
        .panel-bg {
            position: relative;
            overflow: hidden;
        }
        .panel-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(13,27,42,0.55) 0%, rgba(10,25,41,0.85) 60%),
                url('https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=1200&q=80')
                center/cover no-repeat;
            z-index: 0;
        }

        /* ── Chart icon decoration ── */
        .chart-icon {
            position: absolute;
            top: 8%;
            right: 6%;
            width: min(220px, 30vw);
            z-index: 1;
            opacity: .9;
        }

        /* ── Glassmorphic input ── */
        .form-input {
            background: #f8fafc;
            border: 1px solid #d1dde8;
            border-radius: 6px;
            width: 100%;
            padding: 11px 14px 11px 40px;
            font-size: 14px;
            color: #1a2a3a;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input::placeholder { color: #9ab0c4; }
        .form-input:focus {
            border-color: #2a9fd6;
            box-shadow: 0 0 0 3px rgba(42,159,214,.15);
        }

        /* ── Password dots ── */
        .input-password { letter-spacing: .18em; }

        /* ── Custom checkbox ── */
        .custom-checkbox {
            appearance: none;
            width: 16px; height: 16px;
            border: 1.5px solid #b0c4d4;
            border-radius: 3px;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s, border-color .15s;
        }
        .custom-checkbox:checked {
            background: #0d1b2a;
            border-color: #0d1b2a;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 10 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3 3 5-6' stroke='white' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 10px;
        }

        /* ── Sign-in button ── */
        .btn-signin {
            background: #0d1b2a;
            color: #fff;
            border: none;
            border-radius: 6px;
            width: 100%;
            padding: 13px;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .2s, transform .1s;
        }
        .btn-signin:hover  { background: #162a3d; }
        .btn-signin:active { transform: scale(.99); }

        /* ── Divider line ── */
        .stat-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,.12);
            margin: 0;
        }

        /* ── Fade-in animation ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .55s ease both; }
        .delay-1 { animation-delay: .08s; }
        .delay-2 { animation-delay: .16s; }
        .delay-3 { animation-delay: .24s; }
        .delay-4 { animation-delay: .32s; }
    </style>
</head>

<body class="min-h-screen flex">

    {{-- ════════════════════════════════════════
         LEFT PANEL – Login Form
    ════════════════════════════════════════ --}}
    <div class="w-full lg:w-[50%] xl:w-[48%] bg-white flex flex-col min-h-screen">

        {{-- Logo --}}
        <header class="px-10 pt-8">
            <div class="flex items-center gap-3">
                {{-- Icon --}}
                <div class="w-9 h-9 bg-brand-navy rounded-md flex items-center justify-center flex-shrink-0">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="2" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                        <rect x="12" y="2" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                        <rect x="2" y="12" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                        <rect x="12" y="12" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                    </svg>
                </div>
                <span class="text-[15px] font-semibold text-gray-800 tracking-tight">Equinox Ledger</span>
            </div>
        </header>

        {{-- Form area – centered vertically --}}
        <main class="flex-1 flex items-center justify-center px-10 xl:px-16">
            <div class="w-full max-w-[420px]">

                {{-- Heading --}}
                <div class="mb-8 fade-up">
                    <h1 class="text-[22px] font-semibold text-gray-900 mb-1">Welcome Back</h1>
                    <p class="text-sm text-gray-500">Enter your credentials to access your financial dashboard.</p>
                </div>

                {{-- Session errors --}}
                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-600 fade-up">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Login form --}}
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5 fade-up delay-1">
                        <label class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="15" height="15" viewBox="0 0 20 20" fill="none">
                                    <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M2 6l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="name@company.com"
                                autocomplete="email"
                                required
                                class="form-input"
                            >
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-5 fade-up delay-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-[13px] text-brand-accent hover:text-brand-accent-hover font-medium transition-colors">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
                                    <rect x="3" y="9" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M7 9V6a3 3 0 016 0v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                                class="form-input input-password"
                            >
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center gap-2.5 mb-7 fade-up delay-3">
                        <input type="checkbox" name="remember" id="remember" class="custom-checkbox">
                        <label for="remember" class="text-[13.5px] text-gray-600 cursor-pointer select-none">
                            Remember me for 30 days
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div class="fade-up delay-4">
                        <button type="submit" class="btn-signin">
                            Sign In
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Register link --}}
                <p class="mt-7 text-center text-[13px] text-gray-500 fade-up delay-4">
                    Don't have an account?
                    <a href="mailto:admin@equinoxledger.com"
                       class="text-brand-accent hover:text-brand-accent-hover font-medium transition-colors">
                        Contact your administrator
                    </a>
                </p>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="px-10 py-6 flex items-center justify-between text-[11px] text-gray-400">
            <span>© 2024 EQUINOX LEDGER SYSTEMS</span>
            <div class="flex gap-5">
                <a href="#" class="hover:text-gray-600 transition-colors tracking-wide uppercase">Privacy Policy</a>
                <a href="#" class="hover:text-gray-600 transition-colors tracking-wide uppercase">Terms of Service</a>
            </div>
        </footer>

    </div>

    {{-- ════════════════════════════════════════
         RIGHT PANEL – Brand / Marketing
    ════════════════════════════════════════ --}}
    <div class="hidden lg:flex lg:w-[50%] xl:w-[52%] panel-bg flex-col justify-end min-h-screen">

        {{-- Chart / logo graphic decoration --}}
        <div class="chart-icon">
            <svg viewBox="0 0 220 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="4" width="212" height="212" rx="18"
                      stroke="rgba(255,255,255,.18)" stroke-width="1.5"
                      fill="rgba(255,255,255,.05)"/>
                <rect x="26" y="26" width="74" height="74" rx="8"
                      fill="rgba(42,159,214,.25)" stroke="rgba(42,159,214,.4)" stroke-width="1"/>
                <rect x="120" y="26" width="74" height="74" rx="8"
                      fill="rgba(42,159,214,.15)" stroke="rgba(42,159,214,.3)" stroke-width="1"/>
                <rect x="26" y="120" width="74" height="74" rx="8"
                      fill="rgba(42,159,214,.15)" stroke="rgba(42,159,214,.3)" stroke-width="1"/>
                <rect x="120" y="120" width="74" height="74" rx="8"
                      fill="rgba(42,159,214,.25)" stroke="rgba(42,159,214,.4)" stroke-width="1"/>
                {{-- Inner bars --}}
                <rect x="40"  y="70" width="10" height="20" rx="2" fill="rgba(255,255,255,.5)"/>
                <rect x="56"  y="55" width="10" height="35" rx="2" fill="rgba(255,255,255,.7)"/>
                <rect x="72"  y="62" width="10" height="28" rx="2" fill="rgba(255,255,255,.5)"/>
                <rect x="134" y="68" width="10" height="22" rx="2" fill="rgba(255,255,255,.4)"/>
                <rect x="150" y="52" width="10" height="38" rx="2" fill="rgba(255,255,255,.65)"/>
                <rect x="166" y="60" width="10" height="30" rx="2" fill="rgba(255,255,255,.4)"/>
            </svg>
        </div>

        {{-- Text & stats block --}}
        <div class="relative z-10 px-12 pb-14">

            {{-- Trust badge --}}
            <div class="inline-flex items-center gap-2 border border-brand-accent/40 bg-brand-accent/10
                        text-brand-accent text-[12px] font-medium px-4 py-1.5 rounded-full mb-6 backdrop-blur-sm">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none">
                    <path d="M10 2l2.4 5 5.6.8-4 3.9.9 5.5L10 14.5l-4.9 2.7.9-5.5-4-3.9 5.6-.8z"
                          stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none"/>
                </svg>
                Trusted by 500+ Global Firms
            </div>

            {{-- Headline --}}
            <h2 class="text-white text-[26px] xl:text-[30px] font-semibold leading-tight mb-4"
                style="font-family:'DM Serif Display',serif;">
                Precision in every transaction.
            </h2>

            {{-- Body copy --}}
            <p class="text-brand-text text-[14px] leading-relaxed max-w-[480px] mb-8">
                Equinox Ledger provides the mathematical harmony required for complex global
                accounting. Manage high-density data with an interface built for experts.
            </p>

            {{-- Divider --}}
            <hr class="stat-divider mb-8">

            {{-- Stats row --}}
            <div class="flex gap-14">
                <div>
                    <p class="text-white text-[28px] font-semibold leading-none mb-1">99.99%</p>
                    <p class="text-brand-dim text-[11px] tracking-widest uppercase font-medium">Uptime Reliability</p>
                </div>
                <div>
                    <p class="text-white text-[28px] font-semibold leading-none mb-1">256-bit</p>
                    <p class="text-brand-dim text-[11px] tracking-widest uppercase font-medium">Bank-Grade Encryption</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>