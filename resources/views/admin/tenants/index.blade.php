<x-layouts.admin>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Clientes 
            </h2>

            <button type="button"
                    @click="$dispatch('open-tenant-modal')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                + Nuevo cliente
            </button>
        </div>
    </x-slot>

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

    <div x-data="{
            openModal: {{ $errors->any() ? 'true' : 'false' }},
            saving: false,
            form: {
                name: @js(old('name', '')),
                rfc: @js(old('rfc', '')),
                billing_email: @js(old('billing_email', '')),
                phone: @js(old('phone', '')),
                state: @js(old('state', '')),
                city: @js(old('city', '')),
                postal_code: @js(old('postal_code', '')),
                plan_id: @js(old('plan_id', '')),
            },
            normalizeRfc() {
                this.form.rfc = this.form.rfc.toUpperCase().replace(/[^A-Z0-9&Ñ]/g, '').slice(0, 13);
            },
            normalizePostalCode() {
                this.form.postal_code = this.form.postal_code.replace(/\D/g, '').slice(0, 5);
            },
            hasValue(field) {
                return String(this.form[field] ?? '').trim().length > 0;
            },
            validName() {
                return this.hasValue('name');
            },
            validEmail() {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.billing_email);
            },
            validRfc() {
                if (!this.hasValue('rfc')) return true;
                return /^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/.test(this.form.rfc);
            },
            validPostalCode() {
                if (!this.hasValue('postal_code')) return true;
                return /^[0-9]{5}$/.test(this.form.postal_code);
            },
            validState() {
                return this.hasValue('state');
            },
            canSubmit() {
                return this.validName() && this.validEmail() && this.validRfc() && this.validPostalCode() && this.validState();
            }
        }"
         @open-tenant-modal.window="openModal = true">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    Listado de clientes
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">RFC</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($tenants as $tenant)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $tenant->name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $tenant->rfc ?? 'Sin RFC' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $tenant->billing_email ?? 'Sin email' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    
                                    {{ $tenant->plan?->name }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    @if($tenant->status === 'active')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                               <td class="px-6 py-4 text-sm text-right">
    <div class="flex justify-end gap-3">
        @if($tenant->ownerUser && is_null($tenant->ownerUser->email_verified_at))
    <form action="{{ route('admin.tenants.resendInvitation', $tenant) }}" method="POST">
        @csrf
        <button type="submit"
                class="text-yellow-600 hover:text-yellow-800 font-medium">
            Reenviar invitación
        </button>
    </form>
@endif

        @if($tenant->plan?->isManual())
            <span class="text-slate-600 text-sm font-medium">
                Pago manual
            </span>
        @elseif(!$tenant->stripe_subscription_id)
            <a href="{{ route('admin.tenants.subscribe', $tenant) }}"
               class="text-indigo-600 hover:text-indigo-800 font-medium">
                Suscribir
            </a>
        @elseif($tenant->stripe_status === 'active')
            <span class="text-green-600 text-sm font-medium">
                Activo
            </span>
        @else
            <span class="text-yellow-600 text-sm font-medium">
                {{ $tenant->stripe_status ?? 'Pendiente' }}
            </span>
        @endif

        <a href="{{ route('admin.tenants.edit', $tenant) }}"
           class="text-blue-600 hover:text-blue-800 font-medium">
            Editar
        </a>

        <form action="{{ route('admin.tenants.destroy', $tenant) }}"
              method="POST"
              onsubmit="return confirm('¿Seguro que deseas cambiar el estado de este cliente?');">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="text-red-600 hover:text-red-800 font-medium">
                {{ $tenant->status === 'active' ? 'Inactivar' : 'Activar' }}
            </button>
        </form>

    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tenants->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL --}}
        <div x-show="openModal"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
             style="display: none;">

            <div @click.away="openModal = false"
                 class="relative overflow-hidden bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Nuevo Cliente SaaS
                </h2>

                <form method="POST" action="{{ route('admin.tenants.store') }}" @submit="saving = true">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <div class="relative">
                            <input type="text" name="name" placeholder="Nombre / Razon social"
                                   x-model="form.name"
                                   class="w-full rounded-lg border-gray-300 pr-10 text-sm" required>
                            <span x-show="validName()" class="absolute right-3 top-2.5 text-sm font-bold text-green-600">&#10003;</span>
                        </div>

                        <div>
                            <div class="relative">
                                <input type="text" name="rfc" placeholder="RFC"
                                       x-model="form.rfc"
                                       @input="normalizeRfc()"
                                       maxlength="13"
                                       class="w-full rounded-lg pr-16 text-sm"
                                       :class="validRfc() ? 'border-gray-300' : 'border-red-400 focus:border-red-500 focus:ring-red-500'">
                                <span x-show="validRfc() && hasValue('rfc')" class="absolute right-3 top-2.5 text-sm font-bold text-green-600">&#10003;</span>
                                <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400" x-show="!hasValue('rfc') || !validRfc()" x-text="form.rfc.length + '/13'"></span>
                            </div>
                            <p class="mt-1 text-xs" :class="validRfc() ? 'text-gray-500' : 'text-red-600'">
                                RFC opcional. Si lo capturas debe tener 12 o 13 caracteres con formato valido.
                            </p>
                        </div>

                        <div class="relative">
                            <input type="email" name="billing_email" placeholder="Email facturacion"
                                   x-model="form.billing_email"
                                   class="w-full rounded-lg border-gray-300 pr-10 text-sm" required>
                            <span x-show="validEmail()" class="absolute right-3 top-2.5 text-sm font-bold text-green-600">&#10003;</span>
                        </div>

                        <input type="text" name="phone" placeholder="Celular"
                               x-model="form.phone"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <div class="relative">
                            <select name="state"
                                    x-model="form.state"
                                    class="w-full rounded-lg border-gray-300 pr-10 text-sm" required>
                                <option value="">Seleccionar estado</option>
                                @foreach($states as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                            <span x-show="validState()" class="absolute right-9 top-2.5 text-sm font-bold text-green-600">&#10003;</span>
                        </div>

                        <input type="text" name="city" placeholder="Ciudad"
                               x-model="form.city"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <div class="relative">
                            <input type="text" name="postal_code" placeholder="Codigo postal"
                                   x-model="form.postal_code"
                                   @input="normalizePostalCode()"
                                   maxlength="5"
                                   class="w-full rounded-lg pr-16 text-sm"
                                   :class="validPostalCode() ? 'border-gray-300' : 'border-red-400 focus:border-red-500 focus:ring-red-500'">
                            <span x-show="validPostalCode() && hasValue('postal_code')" class="absolute right-3 top-2.5 text-sm font-bold text-green-600">&#10003;</span>
                            <span class="absolute right-3 top-2.5 text-xs font-semibold text-gray-400" x-show="!hasValue('postal_code') || !validPostalCode()" x-text="form.postal_code.length + '/5'"></span>
                        </div>

                        <select name="plan_id"
                                x-model="form.plan_id"
                                class="w-full rounded-lg border-gray-300 text-sm">

                            <option value="">Seleccionar plan</option>

                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} - ${{ number_format($plan->price, 2) }} {{ $plan->currency }} - {{ ($plan->billing_mode ?? 'manual') === 'stripe' ? 'Stripe' : 'Manual' }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button"
                                @click="openModal = false"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                            Cancelar
                        </button>

                        <button type="submit"
                                :disabled="!canSubmit() || saving"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                            Guardar
                        </button>
                    </div>
                </form>

                <div x-show="saving"
                     x-transition.opacity
                     class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/75 backdrop-blur-sm"
                     style="display: none;">
                    <div class="text-center">
                        <div class="mx-auto mb-3 h-10 w-10 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"></div>
                        <p class="text-sm font-bold text-slate-900">Guardando</p>
                        <p class="mt-1 text-xs text-slate-500">Estamos trabajando...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
