<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Reportes contables</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $cliente->razon_social }} - {{ $cliente->rfc }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    Polizas
                </a>
                <a href="{{ route('client.clientes.show', $cliente) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    Cliente
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $journalTotalDebit = $journals->sum('total_debit');
        $journalTotalCredit = $journals->sum('total_credit');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Polizas contabilizadas</p>
            <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($journals->count()) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-green-700 uppercase">Debe del periodo</p>
            <p class="mt-2 text-2xl font-black text-green-900">{{ $money($journalTotalDebit) }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-700 uppercase">Haber del periodo</p>
            <p class="mt-2 text-2xl font-black text-blue-900">{{ $money($journalTotalCredit) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
            <p class="text-sm text-gray-500">Los reportes consideran solo polizas contabilizadas.</p>
        </div>

        <form method="GET" action="{{ route('client.clientes.accounting-journals.reports', $cliente) }}" class="p-6 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cuenta para auxiliar</label>
                <select name="account_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas las cuentas</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected($selectedAccount?->id === $account->id)>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                    Actualizar
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Libro diario</h2>
                <p class="text-sm text-gray-500">Polizas contabilizadas en orden cronologico.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poliza</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto / cuenta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($journals as $journal)
                            <tr class="bg-gray-50">
                                <td class="px-6 py-3 font-semibold text-gray-900">{{ optional($journal->date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 font-mono font-semibold text-gray-900">{{ $journal->number }}</td>
                                <td class="px-6 py-3 font-semibold text-gray-900">{{ $journal->concept }}</td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $money($journal->total_debit) }}</td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $money($journal->total_credit) }}</td>
                            </tr>
                            @foreach($journal->entries as $entry)
                                <tr>
                                    <td class="px-6 py-2"></td>
                                    <td class="px-6 py-2"></td>
                                    <td class="px-6 py-2">
                                        <div class="font-semibold text-gray-800">{{ $entry->account?->code }} - {{ $entry->account?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $entry->description }} {{ $entry->reference ? '· ' . $entry->reference : '' }}</div>
                                    </td>
                                    <td class="px-6 py-2 text-right text-gray-900">{{ $entry->debit > 0 ? $money($entry->debit) : '-' }}</td>
                                    <td class="px-6 py-2 text-right text-gray-900">{{ $entry->credit > 0 ? $money($entry->credit) : '-' }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No hay polizas contabilizadas en este periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Auxiliar por cuenta</h2>
                <p class="text-sm text-gray-500">
                    {{ $selectedAccount ? $selectedAccount->code . ' - ' . $selectedAccount->name : 'Mostrando movimientos de todas las cuentas.' }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poliza</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($auxiliaryEntries as $entry)
                            <tr>
                                <td class="px-6 py-3 text-gray-700">{{ optional($entry->journal?->date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 font-mono text-gray-900">{{ $entry->journal?->number }}</td>
                                <td class="px-6 py-3 font-semibold text-gray-900">{{ $entry->account?->code }} - {{ $entry->account?->name }}</td>
                                <td class="px-6 py-3">
                                    <div class="text-gray-900">{{ $entry->description }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $entry->reference }}</div>
                                </td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $entry->debit > 0 ? $money($entry->debit) : '-' }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $entry->credit > 0 ? $money($entry->credit) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay movimientos auxiliares para el filtro.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $auxiliaryEntries->links() }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Balanza de comprobacion</h2>
                <p class="text-sm text-gray-500">Saldo inicial, movimientos y saldo final por cuenta.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Naturaleza</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo inicial</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Haber</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($trialBalance as $row)
                            <tr>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-900">{{ $row['account']->code }} - {{ $row['account']->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($row['account']->type) }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $row['account']->nature === 'debit' ? 'Deudora' : 'Acreedora' }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $money($row['initial_balance']) }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $money($row['debit']) }}</td>
                                <td class="px-6 py-3 text-right text-gray-900">{{ $money($row['credit']) }}</td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $money($row['final_balance']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">No hay movimientos para balanza en este periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Totales movimientos</td>
                            <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $money($trialBalance->sum('debit')) }}</td>
                            <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $money($trialBalance->sum('credit')) }}</td>
                            <td class="px-6 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.client>
