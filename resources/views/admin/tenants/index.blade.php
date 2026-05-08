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

    <div x-data="{ openModal: false }"
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

        @if(!$tenant->stripe_subscription_id)
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
                 class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Nuevo Cliente SaaS
                </h2>

                <form method="POST" action="{{ route('admin.tenants.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <input type="text" name="name" placeholder="Nombre / Razón social"
                               class="w-full rounded-lg border-gray-300 text-sm" required>

                        <input type="text" name="rfc" placeholder="RFC"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <input type="email" name="billing_email" placeholder="Email facturación"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <input type="text" name="phone" placeholder="Celular"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <input type="text" name="state" placeholder="Estado"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <input type="text" name="city" placeholder="Ciudad"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <select name="plan_id"
                                class="w-full rounded-lg border-gray-300 text-sm">

                            <option value="">Seleccionar plan</option>

                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} - ${{ number_format($plan->price, 2) }} {{ $plan->currency }}
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
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.admin>