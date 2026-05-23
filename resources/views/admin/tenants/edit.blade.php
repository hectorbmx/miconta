<x-layouts.admin>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Perfil del cliente
            </h2>

            <a href="{{ route('admin.tenants.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                ← Regresar
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @php
            $states = [
                'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas',
                'Chihuahua', 'Ciudad de Mexico', 'Coahuila', 'Colima', 'Durango', 'Estado de Mexico',
                'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'Michoacan', 'Morelos', 'Nayarit',
                'Nuevo Leon', 'Oaxaca', 'Puebla', 'Queretaro', 'Quintana Roo', 'San Luis Potosi',
                'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatan',
                'Zacatecas',
            ];
        @endphp

        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre / Razón social
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $tenant->name) }}"
                           class="w-full rounded-lg border-gray-300 text-sm" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        RFC
                    </label>
                    <input type="text" name="rfc"
                           value="{{ old('rfc', $tenant->rfc) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email facturación
                    </label>
                    <input type="email" name="billing_email"
                           value="{{ old('billing_email', $tenant->billing_email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Celular
                    </label>
                    <input type="text" name="phone"
                           value="{{ old('phone', $tenant->phone) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Estado
                    </label>
                    <select name="state"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Seleccionar estado</option>
                        @foreach($states as $state)
                            <option value="{{ $state }}" @selected(old('state', $tenant->state) === $state)>
                                {{ $state }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ciudad
                    </label>
                    <input type="text" name="city"
                           value="{{ old('city', $tenant->city) }}"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Codigo postal
                    </label>
                    <input type="text" name="postal_code"
                           value="{{ old('postal_code', $tenant->postal_code) }}"
                           maxlength="5"
                           class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Plan
                    </label>
              <select name="plan_id" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="">Sin plan</option>

                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}"
                        @selected(old('plan_id', $tenant->plan_id) == $plan->id)>
                        {{ $plan->name }} - ${{ number_format($plan->price, 2) }} {{ $plan->currency }}
                    </option>
                @endforeach
            </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Dominio
                    </label>
                    <input type="text" name="domain"
                           value="{{ old('domain', $tenant->domain) }}"
                           class="w-full rounded-lg border-gray-300 text-sm"
                           placeholder="cliente.tusistema.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Estado del cliente
                    </label>
                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="active" @selected(old('status', $tenant->status) === 'active')>
                            Activo
                        </option>
                        <option value="inactive" @selected(old('status', $tenant->status) === 'inactive')>
                            Inactivo
                        </option>
                        <option value="suspended" @selected(old('status', $tenant->status) === 'suspended')>
                            Suspendido
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Dias de gracia
                    </label>
                    <input type="number"
                           name="grace_days"
                           min="0"
                           max="365"
                           value="{{ old('grace_days', $tenant->grace_days ?? 0) }}"
                           class="w-full rounded-lg border-gray-300 text-sm"
                           placeholder="Ej. 7">
                    <p class="mt-1 text-xs text-gray-500">
                        Permite acceso despues del vencimiento antes de bloquear.
                    </p>
                </div>

            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            Historial / Suscripción
        </h3>

        @if($tenant->stripe_status === 'active')
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                Suscripción activa
            </span>
        @else
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                {{ $tenant->stripe_status ?? 'Sin suscripción' }}
            </span>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Inicio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Próximo pago</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stripe Subscription</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-4 text-sm text-gray-900">
                        {{ $tenant->plan?->name ?? 'Sin plan' }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600">
                        {{ $tenant->created_at?->format('d/m/Y H:i') }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600">
                        {{ $tenant->current_period_ends_at ? $tenant->current_period_ends_at->format('d/m/Y H:i') : 'Pendiente' }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600">
                        {{ $tenant->stripe_status ?? 'Sin estado' }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600">
                        {{ $tenant->stripe_subscription_id ?? 'Sin suscripción' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        Pagos Stripe
    </h3>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Subscription</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tenant->payments as $payment)
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900">
                            ${{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600">
                            {{ $payment->status }}
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-600">
                            {{ $payment->stripe_invoice_id ?? 'Sin invoice' }}
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-600">
                            {{ $payment->stripe_subscription_id ?? 'Sin suscripcion' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-sm text-gray-500 text-center">
                            Aun no hay pagos registrados en la base local.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layouts.admin>
