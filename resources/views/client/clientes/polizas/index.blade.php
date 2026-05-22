<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Polizas contables</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $cliente->razon_social }} - {{ $cliente->rfc }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('client.clientes.accounting-journals.reports', $cliente) }}"
                   class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Reportes
                </a>
                <a href="{{ route('client.clientes.show', $cliente) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    Regresar al cliente
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $typeLabels = ['income' => 'Ingreso', 'expense' => 'Egreso', 'diary' => 'Diario'];
    @endphp

    <div x-data="{
        openJournalForm: false,
        reviewJournal: null,
        money(value) {
            return Number(value || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        }
    }">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase">Polizas totales</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm">
                <p class="text-xs font-bold text-yellow-700 uppercase">Borradores</p>
                <p class="mt-2 text-3xl font-black text-yellow-800">{{ number_format($stats['draft']) }}</p>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm">
                <p class="text-xs font-bold text-green-700 uppercase">Contabilizadas</p>
                <p class="mt-2 text-3xl font-black text-green-800">{{ number_format($stats['posted']) }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-xs font-bold text-blue-700 uppercase">Debe / Haber contabilizado</p>
                <p class="mt-2 text-xl font-black text-blue-900">{{ $money($stats['debit']) }}</p>
                <p class="mt-1 text-sm font-semibold text-blue-700">{{ $money($stats['credit']) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Busqueda</h2>
                    <p class="text-sm text-gray-500">Filtra por fecha, estado, tipo, numero, concepto o UUID.</p>
                </div>

                <button type="button"
                        @click="openJournalForm = !openJournalForm"
                        class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 disabled:opacity-50"
                        @disabled($activeAccountingAccounts->count() < 2)>
                    + Nueva poliza
                </button>
            </div>

            <form method="GET" action="{{ route('client.clientes.accounting-journals.index', $cliente) }}" class="p-6 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Buscar</label>
                    <input name="q" value="{{ request('q') }}" placeholder="Numero, concepto, UUID"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Estado</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="draft" @selected(request('status') === 'draft')>Borrador</option>
                        <option value="posted" @selected(request('status') === 'posted')>Contabilizada</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                    <select name="type" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Mostrar</label>
                    <select name="per_page" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-12 flex justify-end">
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                        Filtrar
                    </button>
                </div>
            </form>

            @if(request()->hasAny(['q', 'date_from', 'date_to', 'status', 'type']))
                <div class="px-6 pb-5">
                    <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
                       class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                        Limpiar filtros
                    </a>
                </div>
            @endif

            @if($activeAccountingAccounts->count() < 2)
                <div class="px-6 py-4 bg-amber-50 border-t border-amber-100 text-sm text-amber-700">
                    Necesitas al menos dos cuentas contables activas para crear una poliza.
                </div>
            @endif

            <div x-show="openJournalForm" x-cloak class="px-6 py-5 border-t border-gray-200 bg-gray-50">
                <form method="POST" action="{{ route('client.clientes.accounting-journals.store', $cliente) }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Fecha</label>
                            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                            <select name="type" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="diary" @selected(old('type') === 'diary')>Diario</option>
                                <option value="income" @selected(old('type') === 'income')>Ingreso</option>
                                <option value="expense" @selected(old('type') === 'expense')>Egreso</option>
                            </select>
                        </div>
                        <div class="md:col-span-8">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Concepto</label>
                            <input name="concept" value="{{ old('concept') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej. Registro de venta del periodo">
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg bg-white">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @for($i = 0; $i < 6; $i++)
                                    <tr>
                                        <td class="px-4 py-2 min-w-64">
                                            <select name="entries[{{ $i }}][accounting_account_id]"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Seleccionar cuenta</option>
                                                @foreach($activeAccountingAccounts as $account)
                                                    <option value="{{ $account->id }}" @selected(old("entries.$i.accounting_account_id") == $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2 min-w-56">
                                            <input name="entries[{{ $i }}][description]" value="{{ old("entries.$i.description") }}"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Detalle del movimiento">
                                        </td>
                                        <td class="px-4 py-2 min-w-40">
                                            <input name="entries[{{ $i }}][reference]" value="{{ old("entries.$i.reference") }}"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Folio/UUID">
                                        </td>
                                        <td class="px-4 py-2 min-w-32">
                                            <input type="number" step="0.01" min="0" name="entries[{{ $i }}][debit]" value="{{ old("entries.$i.debit") }}"
                                                   class="w-full rounded-lg border-gray-300 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00">
                                        </td>
                                        <td class="px-4 py-2 min-w-32">
                                            <input type="number" step="0.01" min="0" name="entries[{{ $i }}][credit]" value="{{ old("entries.$i.credit") }}"
                                                   class="w-full rounded-lg border-gray-300 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    @if($errors->has('entries'))
                        <p class="text-sm text-red-600">{{ $errors->first('entries') }}</p>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                            Guardar borrador
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($journals as $journal)
                            @php
                                $journalReview = [
                                    'id' => $journal->id,
                                    'number' => $journal->number,
                                    'date' => optional($journal->date)->format('d/m/Y'),
                                    'type' => $typeLabels[$journal->type] ?? ucfirst($journal->type),
                                    'concept' => $journal->concept,
                                    'status' => $journal->status,
                                    'status_label' => $journal->status === 'posted' ? 'Contabilizada' : 'Borrador',
                                    'source' => $journal->source,
                                    'total_debit' => (float) $journal->total_debit,
                                    'total_credit' => (float) $journal->total_credit,
                                    'difference' => round((float) $journal->total_debit - (float) $journal->total_credit, 2),
                                    'post_url' => route('client.clientes.accounting-journals.post', [$cliente, $journal]),
                                    'edit_url' => route('client.clientes.accounting-journals.edit', [$cliente, $journal]),
                                    'entries' => $journal->entries->map(fn ($entry) => [
                                        'account' => trim(($entry->account?->code ? $entry->account->code . ' - ' : '') . ($entry->account?->name ?? 'Cuenta no disponible')),
                                        'description' => $entry->description ?: '-',
                                        'reference' => $entry->reference ?: '-',
                                        'debit' => (float) $entry->debit,
                                        'credit' => (float) $entry->credit,
                                        'uuid' => $entry->cfdi?->uuid,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-700 whitespace-nowrap">{{ optional($journal->date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 font-mono text-gray-900">{{ $journal->number }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $typeLabels[$journal->type] ?? ucfirst($journal->type) }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-900">{{ $journal->concept }}</div>
                                    <div class="text-xs text-gray-500">{{ $journal->entries->count() }} movimientos</div>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ $money($journal->total_debit) }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ $money($journal->total_credit) }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $journal->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $journal->status === 'posted' ? 'Contabilizada' : 'Borrador' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        @if($journal->status === 'draft')
                                            <a href="{{ route('client.clientes.accounting-journals.edit', [$cliente, $journal]) }}"
                                               class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                                Editar
                                            </a>
                                        @endif

                                        <button type="button"
                                                @click="reviewJournal = JSON.parse(document.getElementById('journal-review-{{ $journal->id }}').textContent)"
                                                class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                            {{ $journal->status === 'draft' ? 'Revisar' : 'Ver' }}
                                        </button>
                                    </div>
                                    <script type="application/json" id="journal-review-{{ $journal->id }}">@json($journalReview)</script>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-400">No hay polizas con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $journals->links() }}
            </div>
        </div>

        <div x-show="reviewJournal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="reviewJournal = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Revision de poliza</p>
                        <h3 class="mt-1 text-xl font-bold text-gray-900" x-text="reviewJournal?.number"></h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="reviewJournal?.concept"></p>
                    </div>
                    <button type="button" @click="reviewJournal = null" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">Fecha</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900" x-text="reviewJournal?.date"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">Tipo</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900" x-text="reviewJournal?.type"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">Origen</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900" x-text="reviewJournal?.source === 'cfdi' ? 'XML CFDI' : 'Manual'"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">Estado</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900" x-text="reviewJournal?.status_label"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase">Diferencia</p>
                            <p class="mt-1 text-sm font-semibold"
                               :class="Math.abs(reviewJournal?.difference || 0) < 0.01 ? 'text-green-700' : 'text-red-700'"
                               x-text="money(reviewJournal?.difference || 0)"></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="entry in reviewJournal?.entries || []" :key="entry.account + entry.description + entry.reference">
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900" x-text="entry.account"></td>
                                        <td class="px-4 py-3 text-gray-700" x-text="entry.description"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-mono text-xs text-gray-500 break-all" x-text="entry.reference"></div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900" x-text="money(entry.debit)"></td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900" x-text="money(entry.credit)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Totales</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900" x-text="money(reviewJournal?.total_debit || 0)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900" x-text="money(reviewJournal?.total_credit || 0)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button"
                                @click="reviewJournal = null"
                                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                            Cerrar
                        </button>

                        <template x-if="reviewJournal?.status === 'draft'">
                            <a :href="reviewJournal?.edit_url || '#'"
                               class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 text-center">
                                Editar borrador
                            </a>
                        </template>

                        <form method="POST" :action="reviewJournal?.post_url || '#'" x-show="reviewJournal?.status === 'draft'">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                                Contabilizar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.client>
