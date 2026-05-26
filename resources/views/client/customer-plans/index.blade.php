<x-layouts.client>
    <div x-data="{ openCreatePlan: {{ $errors->any() ? 'true' : 'false' }}, billingMode: '{{ old('billing_mode', 'manual') }}' }"
         class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Honorarios para clientes</h1>
                <p class="text-sm text-slate-500">
                    Define los paquetes, mensualidades o servicios que tu despacho cobrara a sus clientes contables.
                </p>
            </div>

            <button type="button"
                    @click="openCreatePlan = true"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Nuevo honorario
            </button>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-700 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @if(!auth()->user()->tenant?->stripe_charges_enabled)
            <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-sm">
                Puedes registrar honorarios manuales ahora. Conecta Stripe para cobrar a tus clientes con tarjeta.
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Honorario</th>
                        <th class="px-4 py-3 text-left">Importe</th>
                        <th class="px-4 py-3 text-left">Frecuencia</th>
                        <th class="px-4 py-3 text-left">Cobro</th>
                        <th class="px-4 py-3 text-left">Alcance</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">
                                    {{ $plan->name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $plan->description ?? 'Sin descripcion' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                ${{ number_format($plan->price, 2) }}
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                @if($plan->billing_period === 'monthly')
                                    Mensual
                                @elseif($plan->billing_period === 'yearly')
                                    Anual
                                @else
                                    Pago unico
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if(($plan->billing_mode ?? 'manual') === 'stripe')
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200">
                                        Stripe
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 border border-slate-200">
                                        Manual
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                <div>Descargas SAT: {{ $plan->max_downloads ?? 'Ilimitadas' }}</div>
                                <div>Empresas: {{ $plan->max_companies ?? 'Ilimitadas' }}</div>
                                <div>Vigencia: {{ $plan->duration_days ? $plan->duration_days.' dias' : 'Sin vencimiento' }}</div>
                            </td>

                            <td class="px-4 py-3">
                                @if($plan->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 border border-slate-200">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('client.customer-plans.edit', $plan) }}"
                                   class="text-sm font-semibold text-slate-700 hover:text-slate-900">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                Aun no tienes honorarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $plans->links() }}
        </div>

        <div x-show="openCreatePlan"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

            <div @click.away="openCreatePlan = false"
                 class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">

                <div class="mb-5">
                    <h2 class="text-xl font-bold text-slate-800">Nuevo honorario</h2>
                    <p class="text-sm text-slate-500">
                        Configura lo que tu despacho cobrara a un cliente: mensualidad, servicio anual o pago unico.
                    </p>
                </div>

                <form method="POST" action="{{ route('client.customer-plans.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Nombre del servicio
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Ej. Mensualidad contable, Declaracion anual, Paquete nomina"
                                   class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                                   required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Importe de honorarios
                                </label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="price"
                                       value="{{ old('price') }}"
                                       placeholder="Ej. 3500.00"
                                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                                       required>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Frecuencia de cobro
                                </label>
                                <select name="billing_period"
                                        class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                                        required>
                                    <option value="monthly" @selected(old('billing_period') === 'monthly')>Mensual</option>
                                    <option value="yearly" @selected(old('billing_period') === 'yearly')>Anual</option>
                                    <option value="one_time" @selected(old('billing_period') === 'one_time')>Pago unico</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Forma de cobro al cliente
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="rounded-xl border border-slate-200 p-4 cursor-pointer"
                                       :class="billingMode === 'manual' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700'">
                                    <input type="radio"
                                           name="billing_mode"
                                           value="manual"
                                           class="sr-only"
                                           x-model="billingMode">
                                    <span class="block text-sm font-bold">Cobro manual</span>
                                    <span class="block text-xs mt-1 opacity-80">
                                        Registra el honorario y cobra por transferencia, efectivo u otro medio.
                                    </span>
                                </label>

                                <label class="rounded-xl border border-slate-200 p-4 cursor-pointer {{ auth()->user()->tenant?->stripe_charges_enabled ? '' : 'opacity-50' }}"
                                       :class="billingMode === 'stripe' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700'">
                                    <input type="radio"
                                           name="billing_mode"
                                           value="stripe"
                                           class="sr-only"
                                           x-model="billingMode"
                                           @disabled(!auth()->user()->tenant?->stripe_charges_enabled)>
                                    <span class="block text-sm font-bold">Cobro con Stripe</span>
                                    <span class="block text-xs mt-1 opacity-80">
                                        {{ auth()->user()->tenant?->stripe_charges_enabled ? 'Cobra este honorario al cliente con tarjeta.' : 'Conecta Stripe para cobrar honorarios con tarjeta.' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Vigencia
                                </label>
                                <input type="number"
                                       min="1"
                                       name="duration_days"
                                       value="{{ old('duration_days') }}"
                                       placeholder="Dias"
                                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Descargas SAT
                                </label>
                                <input type="number"
                                       min="0"
                                       name="max_downloads"
                                       value="{{ old('max_downloads') }}"
                                       placeholder="Opcional"
                                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Empresas incluidas
                                </label>
                                <input type="number"
                                       min="0"
                                       name="max_companies"
                                       value="{{ old('max_companies') }}"
                                       placeholder="Opcional"
                                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Descripcion del servicio
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      placeholder="Ej. Incluye contabilidad mensual, declaraciones, conciliacion bancaria y soporte fiscal."
                                      class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                @click="openCreatePlan = false"
                                class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            Cancelar
                        </button>

                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Guardar honorario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.client>
