<x-layouts.client>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Configuracion</h1>
            <p class="text-sm text-gray-500 mt-1">Conecta tu despacho con Stripe para cobrar honorarios a tus clientes.</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Cobros con Stripe Connect</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Tu cuenta SaaS usa Stripe Connect para que cada despacho cobre con su propia cuenta.
                        </p>
                    </div>

                    @if($tenant->stripe_charges_enabled)
                        <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                            Lista para cobrar
                        </span>
                    @elseif($tenant->stripe_account_id)
                        <span class="inline-flex w-fit items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                            Pendiente de activar
                        </span>
                    @else
                        <span class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">
                            Sin conectar
                        </span>
                    @endif
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Cuenta Stripe</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $tenant->stripe_account_id ?? 'No conectada' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Cargos</p>
                        <p class="mt-2 text-sm font-semibold {{ $tenant->stripe_charges_enabled ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $tenant->stripe_charges_enabled ? 'Habilitados' : 'Pendientes' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Pagos al despacho</p>
                        <p class="mt-2 text-sm font-semibold {{ $tenant->stripe_payouts_enabled ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $tenant->stripe_payouts_enabled ? 'Habilitados' : 'Pendientes' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-5">
                    <h3 class="text-sm font-bold text-slate-900">Como funciona</h3>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-600">
                        <div>
                            <p class="font-bold text-slate-800">1. Conecta Stripe</p>
                            <p class="mt-1">El contador completa el alta en Stripe desde un enlace seguro.</p>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">2. Crea honorarios</p>
                            <p class="mt-1">Los paquetes con cobro Stripe crean productos en la cuenta conectada.</p>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">3. Cobra al cliente</p>
                            <p class="mt-1">El checkout se genera desde la cuenta Stripe del despacho.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('client.stripe.connect') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700">
                        {{ $tenant->stripe_account_id ? 'Continuar configuracion en Stripe' : 'Conectar Stripe' }}
                    </a>

                    @if($tenant->stripe_account_id)
                        <a href="https://dashboard.stripe.com/"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                            Abrir dashboard de Stripe
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <aside class="rounded-2xl border border-blue-200 bg-blue-50 p-6 text-blue-950">
            <h2 class="text-lg font-bold">Por que no pedimos llaves secretas</h2>
            <p class="mt-3 text-sm leading-relaxed">
                Guardar llaves Stripe de cada despacho aumenta el riesgo y mezcla responsabilidades.
                Con Connect, Stripe gestiona el alta del despacho y la plataforma solo guarda el ID de la cuenta conectada.
            </p>

            <div class="mt-5 space-y-3 text-sm">
                <div class="rounded-xl bg-white/70 p-4">
                    <p class="font-bold">Tu cobro SaaS</p>
                    <p class="mt-1 text-blue-800">Se maneja con los planes master del sistema.</p>
                </div>
                <div class="rounded-xl bg-white/70 p-4">
                    <p class="font-bold">Honorarios del contador</p>
                    <p class="mt-1 text-blue-800">Se cobran desde la cuenta conectada del tenant.</p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.client>
