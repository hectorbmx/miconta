<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equinox Ledger - Prueba gratis</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy: '#0d1b2a',
                            deep: '#0a1929',
                            accent: '#2a9fd6',
                            'accent-hover': '#1e8abf',
                            text: '#c8dce8',
                            dim: '#7a9bb5',
                        },
                    },
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        display: ['"DM Serif Display"', 'serif'],
                    },
                },
            },
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            background: #0d1b2a;
            font-family: 'DM Sans', sans-serif;
        }

        .panel-bg {
            position: relative;
            overflow: hidden;
        }

        .panel-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(13,27,42,.58) 0%, rgba(10,25,41,.9) 62%),
                url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1200&q=80')
                center/cover no-repeat;
            z-index: 0;
        }

        .form-input,
        .form-select {
            width: 100%;
            border: 1px solid #d1dde8;
            border-radius: 6px;
            background: #f8fafc;
            color: #1a2a3a;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input {
            padding: 11px 14px 11px 40px;
        }

        .form-select {
            appearance: none;
            padding: 11px 40px 11px 14px;
        }

        .form-input::placeholder { color: #9ab0c4; }

        .form-input:focus,
        .form-select:focus {
            border-color: #2a9fd6;
            box-shadow: 0 0 0 3px rgba(42,159,214,.15);
        }

        .btn-primary {
            width: 100%;
            border: none;
            border-radius: 6px;
            background: #0d1b2a;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .01em;
            transition: background .2s, transform .1s;
        }

        .btn-primary:hover { background: #162a3d; }
        .btn-primary:active { transform: scale(.99); }
        .btn-primary:disabled {
            cursor: wait;
            opacity: .78;
        }

        .trial-card {
            border: 1px solid #c8dce8;
            border-radius: 8px;
            background: linear-gradient(180deg, #f8fafc 0%, #eef7fb 100%);
            padding: 14px;
        }

        .fade-up { animation: fadeUp .55s ease both; }
        .delay-1 { animation-delay: .08s; }
        .delay-2 { animation-delay: .16s; }
        .delay-3 { animation-delay: .24s; }
        .delay-4 { animation-delay: .32s; }

        .loading-spinner {
            width: 34px;
            height: 34px;
            border: 3px solid #d7e7f0;
            border-top-color: #2a9fd6;
            border-radius: 999px;
            animation: spin .8s linear infinite;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="min-h-screen flex">
    <div class="w-full lg:w-[52%] xl:w-[50%] bg-white flex flex-col min-h-screen">
        <header class="px-8 sm:px-10 pt-8">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-brand-navy rounded-md flex items-center justify-center flex-shrink-0">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <rect x="2" y="2" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                            <rect x="12" y="2" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                            <rect x="2" y="12" width="6" height="6" rx="1" fill="white" opacity=".6"/>
                            <rect x="12" y="12" width="6" height="6" rx="1" fill="white" opacity=".9"/>
                        </svg>
                    </div>
                    <span class="text-[15px] font-semibold text-gray-800 tracking-tight">Equinox Ledger</span>
                </a>

                <a href="{{ route('login') }}" class="text-[13px] text-gray-500 hover:text-brand-accent font-medium transition-colors">
                    Ya tengo cuenta
                </a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-8 sm:px-10 xl:px-16 py-10">
            <div class="w-full max-w-[460px]">
                <div class="mb-7 fade-up">
                    <p class="text-[12px] font-semibold tracking-widest text-brand-accent uppercase mb-3">Prueba gratis</p>
                    <h1 class="text-[24px] font-semibold text-gray-900 mb-2">Crea tu cuenta</h1>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Activa tu despacho, confirma tu correo y empieza con el plan gratuito.
                    </p>
                </div>

                <div id="register-error" class="hidden mb-5 p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-600 fade-up"></div>

                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-600 fade-up">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 fade-up delay-1">
                        <div class="sm:col-span-2">
                            <label for="tenant_name" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                                Despacho o empresa
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="15" height="15" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M3 18V5.5A1.5 1.5 0 014.5 4h8A1.5 1.5 0 0114 5.5V18M2 18h16M6 8h1.5M6 11h1.5M6 14h1.5M10 8h1.5M10 11h1.5M10 14h1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input id="tenant_name" class="form-input" type="text" name="tenant_name" value="{{ old('tenant_name') }}" placeholder="Despacho Contable Rivera" required autofocus autocomplete="organization">
                            </div>
                        </div>

                        <div>
                            <label for="name" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                                Tu nombre
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="15" height="15" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M10 10a4 4 0 100-8 4 4 0 000 8zM3 18a7 7 0 0114 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" placeholder="Nombre completo" required autocomplete="name">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                                Email
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="15" height="15" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M2 6l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@empresa.com" required autocomplete="username">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 fade-up delay-2">
                        <label for="plan_id" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                            Plan inicial
                        </label>
                        <div class="trial-card">
                            @php($defaultPlan = $plans->first())
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $defaultPlan?->name ?? 'Prueba gratis' }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Sin pago inicial. Acceso inmediato despues de verificar el correo.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-brand-accent/10 px-3 py-1 text-[11px] font-semibold text-brand-accent">
                                    Gratis
                                </span>
                            </div>

                            @if ($plans->count() === 1 && $defaultPlan)
                                <input type="hidden" name="plan_id" value="{{ old('plan_id', $defaultPlan->id) }}">
                                <div class="mt-3 rounded-md border border-white bg-white/70 px-3 py-3 text-sm text-gray-700">
                                    {{ $defaultPlan->name }} - ${{ number_format($defaultPlan->price, 2) }} {{ $defaultPlan->currency }}
                                    @if ($defaultPlan->max_users)
                                        / {{ $defaultPlan->max_users }} usuario(s)
                                    @endif
                                    @if ($defaultPlan->max_customers)
                                        / {{ $defaultPlan->max_customers }} cliente(s)
                                    @endif
                                </div>
                            @else
                                <div class="relative mt-3">
                                    <select id="plan_id" name="plan_id" required class="form-select">
                                        <option value="">Selecciona un plan</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                                {{ $plan->name }} - ${{ number_format($plan->price, 2) }} {{ $plan->currency }}
                                                @if ($plan->max_users)
                                                    / {{ $plan->max_users }} usuario(s)
                                                @endif
                                                @if ($plan->max_customers)
                                                    / {{ $plan->max_customers }} cliente(s)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">
                                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M5 7.5l5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5 fade-up delay-3">
                        <div>
                            <label for="password" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <rect x="3" y="9" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M7 9V6a3 3 0 016 0v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input id="password" class="form-input" type="password" name="password" placeholder="Minimo 8 caracteres" required autocomplete="new-password">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-semibold tracking-widest text-gray-500 uppercase mb-2">
                                Confirmar
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M8.5 13.5l-2-2 1.1-1.1.9.9 3.9-3.9 1.1 1.1-5 5z" fill="currentColor"/>
                                        <rect x="3" y="3" width="14" height="14" rx="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </span>
                                <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" placeholder="Repite password" required autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 fade-up delay-4">
                        <button id="register-submit" type="submit" class="btn-primary">
                            <span>Crear cuenta gratis</span>
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 10h12M12 6l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div class="hidden lg:flex lg:w-[48%] xl:w-[50%] panel-bg flex-col justify-end min-h-screen">
        <div class="relative z-10 px-12 pb-14">
            <div class="inline-flex items-center gap-2 border border-brand-accent/40 bg-brand-accent/10 text-brand-accent text-[12px] font-medium px-4 py-1.5 rounded-full mb-6 backdrop-blur-sm">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M10 2l2.4 5 5.6.8-4 3.9.9 5.5L10 14.5l-4.9 2.7.9-5.5-4-3.9 5.6-.8z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
                Alta en menos de 2 minutos
            </div>

            <h2 class="text-white text-[28px] xl:text-[32px] font-semibold leading-tight mb-4" style="font-family:'DM Serif Display',serif;">
                Tu despacho listo para operar.
            </h2>

            <p class="text-brand-text text-[14px] leading-relaxed max-w-[500px] mb-8">
                Administra clientes, usuarios y procesos SAT desde una cuenta nueva. Al confirmar tu correo activamos el acceso de prueba.
            </p>

            <div class="grid grid-cols-2 gap-5 border-t border-white/15 pt-8">
                <div>
                    <p class="text-white text-[28px] font-semibold leading-none mb-1">14</p>
                    <p class="text-brand-dim text-[11px] tracking-widest uppercase font-medium">Dias de prueba</p>
                </div>
                <div>
                    <p class="text-white text-[28px] font-semibold leading-none mb-1">$0</p>
                    <p class="text-brand-dim text-[11px] tracking-widest uppercase font-medium">Pago inicial</p>
                </div>
            </div>
        </div>
    </div>

    <div id="registration-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-5 backdrop-blur-sm">
        <div class="w-full max-w-[420px] rounded-lg bg-white p-7 shadow-2xl">
            <div id="registration-loading" class="flex items-center gap-4">
                <div class="loading-spinner flex-shrink-0"></div>
                <div>
                    <h2 class="text-[18px] font-semibold text-gray-900">Creando tu cuenta</h2>
                    <p class="mt-1 text-sm text-gray-500">Estamos activando tu prueba gratis.</p>
                </div>
            </div>

            <div id="registration-success" class="hidden">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-md bg-brand-accent/10 text-brand-accent">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4 10.5l3.5 3.5L16 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="text-[18px] font-semibold text-gray-900">Cuenta creada</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-500">
                    Enviamos el enlace de activacion al correo registrado. Por ahora puedes continuar desde la pantalla de verificacion.
                </p>
                <a id="verify-email-link"
                   href="{{ route('verification.notice') }}"
                   class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-brand-navy px-4 py-3 text-sm font-semibold text-white hover:bg-[#162a3d]">
                    Ir a verificar correo
                </a>
            </div>
        </div>
    </div>

    <script>
        const registerForm = document.getElementById('register-form');
        const registerSubmit = document.getElementById('register-submit');
        const registerError = document.getElementById('register-error');
        const registrationModal = document.getElementById('registration-modal');
        const registrationLoading = document.getElementById('registration-loading');
        const registrationSuccess = document.getElementById('registration-success');
        const verifyEmailLink = document.getElementById('verify-email-link');

        function showModal(state) {
            registrationModal.classList.remove('hidden');
            registrationModal.classList.add('flex');
            registrationLoading.classList.toggle('hidden', state !== 'loading');
            registrationSuccess.classList.toggle('hidden', state !== 'success');
        }

        function hideModal() {
            registrationModal.classList.add('hidden');
            registrationModal.classList.remove('flex');
        }

        function showError(message) {
            registerError.textContent = message;
            registerError.classList.remove('hidden');
        }

        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            registerError.classList.add('hidden');
            registerSubmit.disabled = true;
            showModal('loading');

            try {
                const response = await fetch(registerForm.action, {
                    method: 'POST',
                    body: new FormData(registerForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json();

                if (!response.ok) {
                    const firstError = payload?.errors
                        ? Object.values(payload.errors).flat()[0]
                        : payload?.message;

                    throw new Error(firstError || 'No pudimos crear la cuenta. Revisa los datos e intenta de nuevo.');
                }

                if (payload.verify_email_url) {
                    verifyEmailLink.href = payload.verify_email_url;
                }

                showModal('success');
            } catch (error) {
                hideModal();
                showError(error.message);
            } finally {
                registerSubmit.disabled = false;
            }
        });
    </script>
</body>
</html>
