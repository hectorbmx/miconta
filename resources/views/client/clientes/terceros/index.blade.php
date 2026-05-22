<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Terceros contables</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $cliente->razon_social }} - {{ $cliente->rfc }}</p>
            </div>

            <a href="{{ route('client.clientes.show', $cliente) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                Regresar al cliente
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $typeLabels = ['client' => 'Cliente', 'supplier' => 'Proveedor', 'both' => 'Cliente/Proveedor'];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Terceros</p>
            <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-700 uppercase">Clientes</p>
            <p class="mt-2 text-3xl font-black text-blue-900">{{ number_format($stats['clients']) }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-amber-700 uppercase">Proveedores</p>
            <p class="mt-2 text-3xl font-black text-amber-900">{{ number_format($stats['suppliers']) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-green-700 uppercase">Con cuenta asignada</p>
            <p class="mt-2 text-3xl font-black text-green-900">{{ number_format($stats['configured']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Busqueda</h2>
                <p class="text-sm text-gray-500">Asigna la cuenta contable default para futuros XML por RFC.</p>
            </div>

            <form method="POST" action="{{ route('client.clientes.third-parties.sync', $cliente) }}">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800">
                    Sincronizar desde XML
                </button>
            </form>
        </div>

        <form method="GET" action="{{ route('client.clientes.third-parties.index', $cliente) }}" class="p-6 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-5">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Buscar</label>
                <input name="q" value="{{ request('q') }}" placeholder="RFC o razon social"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                <select name="type" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Configuracion</label>
                <select name="configured" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="yes" @selected(request('configured') === 'yes')>Con cuenta</option>
                    <option value="no" @selected(request('configured') === 'no')>Sin cuenta</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RFC / razon social</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">XML</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta default</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($thirdParties as $thirdParty)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-mono font-semibold text-gray-900">{{ $thirdParty->rfc }}</div>
                                <div class="text-xs text-gray-500">{{ $thirdParty->name ?: 'Sin razon social' }}</div>
                                @if($thirdParty->last_cfdi_at)
                                    <div class="text-xs text-gray-400">Ultimo XML: {{ $thirdParty->last_cfdi_at->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ $typeLabels[$thirdParty->type] ?? ucfirst($thirdParty->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ number_format($thirdParty->cfdis_count) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ $money($thirdParty->total_amount) }}</td>
                            <td class="px-6 py-4 min-w-80">
                                <form id="third-party-{{ $thirdParty->id }}" method="POST" action="{{ route('client.clientes.third-parties.update', [$cliente, $thirdParty]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 gap-2">
                                        <select name="default_account_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Sin cuenta asignada</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" @selected($thirdParty->default_account_id === $account->id)>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="flex gap-2">
                                            <select name="type" class="w-full rounded-lg border-gray-300 text-xs focus:ring-blue-500 focus:border-blue-500">
                                                @foreach($typeLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($thirdParty->type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <label class="inline-flex items-center gap-1 text-xs text-gray-600 whitespace-nowrap">
                                                <input type="checkbox" name="is_active" value="1" @checked($thirdParty->is_active) class="rounded border-gray-300">
                                                Activo
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button form="third-party-{{ $thirdParty->id }}"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                    Guardar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                No hay terceros detectados. Sincroniza desde XML para crearlos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $thirdParties->links() }}
        </div>
    </div>
</x-layouts.client>
