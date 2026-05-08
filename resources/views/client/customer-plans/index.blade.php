<x-layouts.client>
    {{-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"> --}}
        <div x-data="{ openCreatePlan: false }"
     class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Planes para clientes</h1>
                <p class="text-sm text-slate-500">
                    Administra los planes que venderás o asignarás a tus clientes.
                </p>
            </div>

          <button type="button"
                @click="openCreatePlan = true"
                class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            Nuevo plan
        </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Plan</th>
                        <th class="px-4 py-3 text-left">Precio</th>
                        <th class="px-4 py-3 text-left">Periodo</th>
                        <th class="px-4 py-3 text-left">Límites</th>
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
                                    {{ $plan->description ?? 'Sin descripción' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                ${{ number_format($plan->price, 2) }}
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ ucfirst($plan->billing_period) }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                <div>Descargas: {{ $plan->max_downloads ?? 'Ilimitadas' }}</div>
                                <div>Empresas: {{ $plan->max_companies ?? 'Ilimitadas' }}</div>
                                <div>Días: {{ $plan->duration_days ?? 'N/A' }}</div>
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
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                Aún no tienes planes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $plans->links() }}
        </div>
        {{-- MODAL CREAR PLAN --}}
<div x-show="openCreatePlan"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

    <div @click.away="openCreatePlan = false"
         class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">

        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-800">Nuevo plan</h2>
            <p class="text-sm text-slate-500">
                Crea un plan para asignarlo a tus clientes.
            </p>
        </div>

        <form method="POST" action="{{ route('client.customer-plans.store') }}">
            @csrf

            <div class="space-y-4">
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Nombre del plan"
                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       required>

                <input type="number"
                       step="0.01"
                       min="0"
                       name="price"
                       value="{{ old('price') }}"
                       placeholder="Precio"
                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                       required>

                <select name="billing_period"
                        class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                        required>
                    <option value="monthly">Mensual</option>
                    <option value="yearly">Anual</option>
                    <option value="one_time">Pago único</option>
                </select>

                <input type="number"
                       min="1"
                       name="duration_days"
                       value="{{ old('duration_days') }}"
                       placeholder="Duración en días (opcional)"
                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                <input type="number"
                       min="0"
                       name="max_downloads"
                       value="{{ old('max_downloads') }}"
                       placeholder="Máx. descargas (opcional)"
                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                <input type="number"
                       min="0"
                       name="max_companies"
                       value="{{ old('max_companies') }}"
                       placeholder="Máx. empresas (opcional)"
                       class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                <textarea name="description"
                          rows="3"
                          placeholder="Descripción"
                          class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">{{ old('description') }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        @click="openCreatePlan = false"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Cancelar
                </button>

                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
    </div>
    
</x-layouts.client>