<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar poliza</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $journal->number }} - {{ $cliente->razon_social }}
                </p>
            </div>

            <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                Regresar a polizas
            </a>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
            Revisa la informacion capturada. {{ $errors->first() }}
        </div>
    @endif

    @php
        $rows = old('entries', $journal->entries->map(fn ($entry) => [
            'accounting_account_id' => $entry->accounting_account_id,
            'sat_cfdi_id' => $entry->sat_cfdi_id,
            'description' => $entry->description,
            'reference' => $entry->reference,
            'debit' => (float) $entry->debit,
            'credit' => (float) $entry->credit,
        ])->all());

        $minimumRows = max(10, count($rows) + 4);
        for ($i = count($rows); $i < $minimumRows; $i++) {
            $rows[$i] = [
                'accounting_account_id' => null,
                'sat_cfdi_id' => null,
                'description' => null,
                'reference' => null,
                'debit' => null,
                'credit' => null,
            ];
        }
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Datos generales</h2>
            <p class="text-sm text-gray-500">Solo las polizas en borrador pueden modificarse.</p>
        </div>

        <form method="POST" action="{{ route('client.clientes.accounting-journals.update', [$cliente, $journal]) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Numero</label>
                    <input value="{{ $journal->number }}" disabled
                           class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm text-gray-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Fecha</label>
                    <input type="date" name="date" value="{{ old('date', optional($journal->date)->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                    <select name="type" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="diary" @selected(old('type', $journal->type) === 'diary')>Diario</option>
                        <option value="income" @selected(old('type', $journal->type) === 'income')>Ingreso</option>
                        <option value="expense" @selected(old('type', $journal->type) === 'expense')>Egreso</option>
                    </select>
                </div>

                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Concepto</label>
                    <input name="concept" value="{{ old('concept', $journal->concept) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            @if($learningContext)
                <div class="rounded-xl border border-violet-200 bg-violet-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-xs font-bold text-violet-700 uppercase">Aprendizaje asistido</p>
                            <p class="mt-2 text-sm text-violet-900">
                                Esta poliza viene de XML de
                                <span class="font-bold">{{ $learningContext['type_label'] }}</span>:
                                <span class="font-mono font-bold">{{ $learningContext['rfc'] }}</span>
                                - {{ $learningContext['name'] }}.
                            </p>
                            <p class="mt-1 text-xs text-violet-700">
                                Cuenta actual del tercero: {{ $learningContext['current_account'] }}.
                            </p>
                        </div>

                        <label class="inline-flex items-start gap-2 rounded-lg bg-white/70 border border-violet-100 px-4 py-3 text-sm text-violet-900">
                            <input type="checkbox" name="save_third_party_default" value="1" class="mt-1 rounded border-violet-300">
                            <span>
                                Guardar la cuenta principal de esta poliza como default para futuros XML de este {{ $learningContext['type_label'] }}.
                            </span>
                        </label>
                    </div>

                    <input type="hidden" name="third_party_rfc" value="{{ $learningContext['rfc'] }}">
                    <input type="hidden" name="third_party_type" value="{{ $learningContext['type'] }}">
                </div>
            @endif

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
                        @foreach($rows as $i => $row)
                            <tr>
                                <td class="px-4 py-2 min-w-64">
                                    <input type="hidden" name="entries[{{ $i }}][sat_cfdi_id]" value="{{ $row['sat_cfdi_id'] ?? '' }}">
                                    <select name="entries[{{ $i }}][accounting_account_id]"
                                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleccionar cuenta</option>
                                        @foreach($activeAccountingAccounts as $account)
                                            <option value="{{ $account->id }}" @selected((string) ($row['accounting_account_id'] ?? '') === (string) $account->id)>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2 min-w-56">
                                    <input name="entries[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-2 min-w-48">
                                    <input name="entries[{{ $i }}][reference]" value="{{ $row['reference'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 text-sm font-mono focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-2 min-w-32">
                                    <input type="number" step="0.01" min="0" name="entries[{{ $i }}][debit]" value="{{ $row['debit'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </td>
                                <td class="px-4 py-2 min-w-32">
                                    <input type="number" step="0.01" min="0" name="entries[{{ $i }}][credit]" value="{{ $row['credit'] ?? '' }}"
                                           class="w-full rounded-lg border-gray-300 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 text-sm text-blue-800">
                Para eliminar un movimiento, deja la cuenta vacia y el debe/haber en cero. Para agregar uno nuevo, usa una fila vacia.
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
                   class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 text-center">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-layouts.client>
