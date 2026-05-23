<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripcion pendiente</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <section class="w-full max-w-2xl rounded-xl bg-white border border-slate-200 shadow-sm p-8">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="text-sm font-semibold text-blue-600">Facturacion</p>
                    <h1 class="mt-2 text-2xl font-bold text-slate-950">Tu suscripcion esta pendiente</h1>
                    <p class="mt-3 text-sm text-slate-600">
                        @if($tenant?->plan?->isManual())
                            Para entrar al panel necesitas que el administrador active o renueve tu plan manual.
                        @else
                            Para entrar al panel necesitas una suscripcion activa. Completa el pago del plan asignado y el acceso se activara automaticamente cuando Stripe confirme el cobro.
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-900">
                        Cerrar sesion
                    </button>
                </form>
            </div>

            <div class="mt-8 rounded-lg border border-slate-200 bg-slate-50 p-5">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Cliente</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $tenant?->name ?? 'Sin cliente' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Plan</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $tenant?->plan?->name ?? 'Sin plan asignado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Metodo de pago</dt>
                        <dd class="mt-1 font-semibold text-slate-900">
                            {{ $tenant?->plan?->isManual() ? 'Manual' : ($tenant?->stripe_status ?? 'Sin suscripcion') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Email</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $tenant?->billing_email ?? 'Sin email' }}</dd>
                    </div>
                </dl>
            </div>

            @if (session('error'))
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (request('success'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Pago recibido. Si Stripe aun esta procesando el webhook, espera unos segundos y actualiza la pagina.
                </div>
            @endif

            @if (request('cancel'))
                <div class="mt-5 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700">
                    El checkout fue cancelado. Puedes volver a intentarlo cuando estes listo.
                </div>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @if($tenant?->plan?->isStripe() && $tenant?->plan?->stripe_price_id)
                    <a href="{{ route('client.billing.checkout') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Completar pago
                    </a>
                @endif

                <span class="text-sm text-slate-500">
                    {{ $tenant?->plan?->isManual() ? 'Si ya realizaste el pago, espera a que el administrador active tu acceso.' : 'Si el pago ya fue realizado, actualiza la pagina cuando Stripe confirme la suscripcion.' }}
                </span>
            </div>
        </section>
    </main>
</body>
</html>
