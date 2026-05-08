<x-layouts.admin>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Planes
            </h2>

            <button @click="$dispatch('open-plan-modal')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                + Nuevo plan
            </button>
        </div>
    </x-slot>

    <div x-data="{ openModal: false }"
         @open-plan-modal.window="openModal = true">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Periodo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Límites</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($plans as $plan)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $plan->name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    ${{ number_format($plan->price, 2) }} {{ $plan->currency }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600 capitalize">
                                    {{ $plan->billing_period }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    Users: {{ $plan->max_users ?? '∞' }}<br>
                                    Customers: {{ $plan->max_customers ?? '∞' }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    @if($plan->is_active)
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
                                        <a href="{{ route('admin.planes.edit', $plan) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            Editar
                                        </a>

                                        <form action="{{ route('admin.planes.destroy', $plan) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 font-medium">
                                                {{ $plan->is_active ? 'Inactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No hay planes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($plans->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $plans->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL CREAR PLAN --}}
        <div x-show="openModal"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
             style="display: none;">

            <div @click.away="openModal = false"
                 class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Nuevo Plan
                </h2>

                <form method="POST" action="{{ route('admin.planes.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">

                        <input type="text" name="name" placeholder="Nombre del plan"
                               class="w-full rounded-lg border-gray-300 text-sm" required>

                        <input type="number" step="0.01" name="price" placeholder="Precio"
                               class="w-full rounded-lg border-gray-300 text-sm" required>

                        <select name="currency" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="MXN">MXN</option>
                            <option value="USD">USD</option>
                        </select>

                        <select name="billing_period" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="monthly">Mensual</option>
                            <option value="yearly">Anual</option>
                        </select>

                        <input type="number" name="max_users" placeholder="Máx usuarios"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <input type="number" name="max_customers" placeholder="Máx clientes"
                               class="w-full rounded-lg border-gray-300 text-sm">

                        <textarea name="description" placeholder="Descripción"
                                  class="w-full rounded-lg border-gray-300 text-sm"></textarea>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button"
                                @click="openModal = false"
                                class="px-4 py-2 text-sm text-gray-600">
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