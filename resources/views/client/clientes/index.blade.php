<x-layouts.client>
    <div x-data="{
        openAssignPlan: false,
        selectedCustomerId: null,
        selectedCustomerName: '',
        assignAction: '',
        openPaymentModal: false,
        paymentAction: '',
        paymentCustomerName: '',
        paymentPlanName: '',
        paymentAmount: '',

        openModal(customerId, customerName) {
            this.selectedCustomerId = customerId;
            this.selectedCustomerName = customerName;
            this.assignAction = `/client/clientes/${customerId}/assign-plan`;
            this.openAssignPlan = true;
        },

        openPayment(customerId, subscriptionId, customerName, planName, amount) {
            this.paymentCustomerName = customerName;
            this.paymentPlanName = planName;
            this.paymentAmount = amount;
            this.paymentAction = `/client/clientes/${customerId}/subscriptions/${subscriptionId}/manual-payment`;
            this.openPaymentModal = true;
        }
    }">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">
                Clientes
            </h1>

            <button type="button"
                    onclick="document.getElementById('modalCliente').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                + Nuevo cliente
            </button>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                Listado de clientes
            </h2>
        </div>

        <table class="w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">RFC</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">Razón social</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">Plan</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">Pago</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">Certificado</th>
            <th class="px-6 py-3 text-left font-semibold text-gray-600">Llave privada</th>
            <th class="px-6 py-3 text-right font-semibold text-gray-600">Acciones</th>
        </tr>
    </thead>

    <tbody class="divide-y divide-gray-200">
        @forelse ($clientes as $cliente)
            @php
                $sub = $cliente->activeSubscription;
                $plan = $sub?->plan;
            @endphp

            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">
                    {{ $cliente->rfc }}
                </td>

                <td class="px-6 py-4 text-gray-700">
                    <div class="font-medium text-gray-900">{{ $cliente->razon_social }}</div>
                    @if($cliente->email)
                        <div class="text-xs text-gray-500">{{ $cliente->email }}</div>
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if($sub && $plan)
                        <div class="font-semibold text-slate-900">
                            {{ $plan->name }}
                        </div>

                        <div class="text-xs text-slate-500">
                            ${{ number_format($sub->price_snapshot, 2) }}
                            · {{ ucfirst($plan->billing_period) }}
                        </div>

                        @if($sub->ends_at)
                            <div class="text-xs text-slate-500">
                                Vence: {{ $sub->ends_at->format('d/m/Y') }}
                            </div>
                        @endif
                    @else
                        <span class="text-xs text-slate-500">Sin plan</span>
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if($sub)
                        @if($sub->ends_at && now()->gt($sub->ends_at))
                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                Vencido
                            </span>
                        @elseif(($sub->payment_status ?? null) === 'paid' || $sub->stripe_payment_status === 'paid')
                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                Pagado
                            </span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                                Pendiente pago
                            </span>
                        @endif

                        @if(($sub->billing_mode ?? $plan?->billing_mode) === 'manual')
                            <div class="mt-2 text-xs text-slate-500">
                                Cobro manual
                            </div>

                            @if(($sub->payment_status ?? 'pending') !== 'paid')
                                <button type="button"
                                        @click="openPayment({{ $cliente->id }}, {{ $sub->id }}, '{{ addslashes($cliente->razon_social) }}', '{{ addslashes($plan?->name ?? 'Plan') }}', '{{ $sub->price_snapshot }}')"
                                        class="mt-2 text-xs px-3 py-1 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                    Registrar pago
                                </button>
                            @elseif($sub->paid_at)
                                <div class="mt-1 text-xs text-emerald-600">
                                    {{ $sub->paid_at->format('d/m/Y') }}
                                </div>
                            @endif
                        @elseif($plan?->stripe_price_id)
                            <div class="mt-1 text-xs text-emerald-600">
                                Stripe sincronizado
                            </div>
                        @else
                            <div class="mt-1 text-xs text-red-600">
                                Sin Stripe
                            </div>
                        @endif
                    @else
                        <button type="button"
                                @click="openModal({{ $cliente->id }}, '{{ addslashes($cliente->razon_social) }}')"
                                class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Asignar plan
                        </button>
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if ($cliente->certificate_path)
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                            Cargado
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                            Pendiente
                        </span>
                    @endif
                </td>

                <td class="px-6 py-4">
                    @if ($cliente->private_key_path)
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                            Cargado
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                            Pendiente
                        </span>
                    @endif
                </td>

                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                                @click="openModal({{ $cliente->id }}, '{{ addslashes($cliente->razon_social) }}')"
                                class="text-xs px-3 py-1 rounded-lg bg-slate-800 text-white hover:bg-slate-700">
                            {{ $sub ? 'Cambiar plan' : 'Asignar plan' }}
                        </button>

                        <a href="{{ route('client.clientes.show', $cliente->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            Ver
                        </a>

                        <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
                           class="text-emerald-600 hover:text-emerald-800 font-medium">
                            Polizas
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                    Aún no tienes clientes registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
    </div>

    {{-- MODAL NUEVO CLIENTE --}}
    <div id="modalCliente"
         class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center px-4">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    Nuevo cliente
                </h3>

                <button type="button"
                        onclick="document.getElementById('modalCliente').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('client.clientes.store') }}" class="p-6 space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            RFC
        </label>
        <input type="text"
               name="rfc"
               maxlength="13"
               required
               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="Ej. XAXX010101000">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Razón social
        </label>
        <input type="text"
               name="razon_social"
               required
               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
               placeholder="Nombre o razón social">
    </div>
    <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Correo electrónico
    </label>
    <input type="email"
           name="email"
           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
           placeholder="cliente@correo.com">
</div>

    <div class="flex items-center justify-end gap-3 pt-4">
        <button type="button"
                onclick="document.getElementById('modalCliente').classList.add('hidden')"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
            Cancelar
        </button>

        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">
            Guardar cliente
        </button>
    </div>
</form>
        </div>
        
    </div>
    {{-- MODAL ASIGNAR PLAN --}}
<div x-show="openAssignPlan"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

    <div @click.away="openAssignPlan = false"
         class="w-full max-w-lg rounded-2xl bg-white shadow-xl">

        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Asignar plan
                </h3>
                <p class="text-sm text-gray-500">
                    Cliente: <span class="font-semibold" x-text="selectedCustomerName"></span>
                </p>
            </div>

            <button type="button"
                    @click="openAssignPlan = false"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                &times;
            </button>
        </div>

        <form method="POST" :action="assignAction" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Plan
                </label>

                <select name="customer_plan_id"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Selecciona un plan</option>

                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} - ${{ number_format($plan->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de inicio
                </label>

                <input type="date"
                       name="starts_at"
                       value="{{ now()->toDateString() }}"
                       required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <button type="button"
                        @click="openAssignPlan = false"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">
                    Asignar plan
                </button>
            </div>
        </form>
    </div>
</div>
    {{-- MODAL REGISTRAR PAGO MANUAL --}}
<div x-show="openPaymentModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

    <div @click.away="openPaymentModal = false"
         class="w-full max-w-lg rounded-2xl bg-white shadow-xl">

        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Registrar pago manual
                </h3>
                <p class="text-sm text-gray-500">
                    <span x-text="paymentCustomerName"></span> · <span x-text="paymentPlanName"></span>
                </p>
            </div>

            <button type="button"
                    @click="openPaymentModal = false"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                &times;
            </button>
        </div>

        <form method="POST" :action="paymentAction" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Monto pagado
                </label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="paid_amount"
                       x-model="paymentAmount"
                       required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de pago
                </label>
                <input type="date"
                       name="paid_at"
                       value="{{ now()->toDateString() }}"
                       required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Metodo de pago
                </label>
                <select name="payment_method"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="transfer">Transferencia</option>
                    <option value="cash">Efectivo</option>
                    <option value="deposit">Deposito</option>
                    <option value="card_external">Tarjeta externa</option>
                    <option value="other">Otro</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Referencia
                </label>
                <input type="text"
                       name="payment_reference"
                       placeholder="Folio, SPEI, nota o referencia"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Notas
                </label>
                <textarea name="payment_notes"
                          rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <button type="button"
                        @click="openPaymentModal = false"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                    Guardar pago
                </button>
            </div>
        </form>
    </div>
</div>
 </div>
</x-layouts.client>
