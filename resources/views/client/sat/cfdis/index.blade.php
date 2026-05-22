<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">XML descargados</h1>
                <p class="text-sm text-gray-500 mt-1">Consulta CFDI por cliente, RFC, UUID, fechas y tipo.</p>
            </div>
            @if(request('customer_id'))
                <a href="{{ route('client.clientes.show', request('customer_id')) }}"
                   class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    Regresar al cliente
                </a>
            @endif
        </div>
    </x-slot>

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $typeLabels = ['I' => 'Ingreso', 'E' => 'Egreso', 'P' => 'Pago', 'N' => 'Nomina', 'T' => 'Traslado'];
    @endphp

    <style>
        .xml-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .xml-kpi-card {
            min-height: 112px;
            border-radius: 14px;
            padding: 18px;
            color: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.10);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .xml-kpi-card p {
            margin: 0;
        }

        .xml-kpi-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .82;
        }

        .xml-kpi-value {
            margin-top: 10px;
            font-size: 28px;
            line-height: 1.1;
            font-weight: 900;
        }

        .xml-kpi-sub {
            margin-top: 8px;
            font-size: 12px;
            opacity: .82;
        }

        .xml-kpi-blue { background: linear-gradient(135deg, #2563eb, #0f172a); }
        .xml-kpi-emerald { background: linear-gradient(135deg, #059669, #064e3b); }
        .xml-kpi-amber { background: linear-gradient(135deg, #d97706, #7c2d12); }
        .xml-kpi-cyan { background: linear-gradient(135deg, #0891b2, #164e63); }

        .xml-filter-panel {
            background: #ffffff;
            border: 1px solid #d9dee8;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .xml-filter-grid {
            display: grid;
            grid-template-columns: 2fr 1.4fr 1.4fr 1.8fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .xml-filter-grid-secondary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
            margin-top: 12px;
        }

        .xml-field-label {
            display: block;
            margin-bottom: 6px;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .xml-input {
            width: 100%;
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            color: #0f172a;
        }

        .xml-table-card,
        .xml-stats-panel {
            background: #ffffff;
            border: 1px solid #d9dee8;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .xml-stats-panel {
            margin-top: 24px;
            padding: 18px;
        }

        .xml-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        @media (max-width: 1100px) {
            .xml-kpi-grid,
            .xml-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .xml-filter-grid,
            .xml-filter-grid-secondary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .xml-kpi-grid,
            .xml-stats-grid,
            .xml-filter-grid,
            .xml-filter-grid-secondary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="xml-kpi-grid">
        <div class="xml-kpi-card xml-kpi-blue">
            <p class="xml-kpi-label">XML filtrados</p>
            <p class="xml-kpi-value">{{ number_format($cfdis->total()) }}</p>
            <p class="xml-kpi-sub">Documentos encontrados</p>
        </div>
        <div class="xml-kpi-card xml-kpi-emerald">
            <p class="xml-kpi-label">Ingresos</p>
            <p class="xml-kpi-value">{{ $money($totales->total_ingresos) }}</p>
            <p class="xml-kpi-sub">Emitidas vigentes</p>
        </div>
        <div class="xml-kpi-card xml-kpi-amber">
            <p class="xml-kpi-label">Gastos</p>
            <p class="xml-kpi-value">{{ $money($totales->total_gastos) }}</p>
            <p class="xml-kpi-sub">Recibidas vigentes</p>
        </div>
        <div class="xml-kpi-card xml-kpi-cyan">
            <p class="xml-kpi-label">Vigentes / Cancelados</p>
            <p class="xml-kpi-value">
                {{ number_format((int) $totales->total_vigentes) }}
                <span style="font-size: 16px; opacity: .75;">/ {{ number_format((int) $totales->total_cancelados) }}</span>
            </p>
            <p class="xml-kpi-sub">Estatus SAT</p>
        </div>
    </div>

    <div class="xml-filter-panel">
        <form method="GET" action="{{ route('client.sat.cfdis.index') }}">
            <div class="xml-filter-grid">
            <div>
                <label class="xml-field-label">Busqueda</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="RFC, razon social, serie o folio"
                       class="xml-input">
            </div>

            <div>
                <label class="xml-field-label">UUID</label>
                <input type="text" name="uuid" value="{{ request('uuid') }}" placeholder="UUID"
                       class="xml-input">
            </div>

            <div>
                <label class="xml-field-label">RFC</label>
                <input type="text" name="rfc" value="{{ request('rfc') }}" placeholder="Emisor/Receptor"
                       class="xml-input">
            </div>

            <div>
                <label class="xml-field-label">Cliente</label>
                <select name="customer_id" class="xml-input">
                    <option value="">Todos</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>
                            {{ $customer->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="xml-field-label">Tipo</label>
                <select name="tipo_comprobante" class="xml-input">
                    <option value="">Todos</option>
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('tipo_comprobante') === $value)>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="xml-field-label">Mostrar</label>
                <select name="per_page" class="xml-input">
                    @foreach([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="h-9 px-4 rounded-lg bg-gray-900 text-white text-xs font-bold hover:bg-gray-800">
                    Filtrar
                </button>
            </div>
            </div>

            <div class="xml-filter-grid-secondary">
                <div>
                    <label class="xml-field-label">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                           class="xml-input">
                </div>
                <div>
                    <label class="xml-field-label">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                           class="xml-input">
                </div>
                <div>
                    <label class="xml-field-label">Descarga</label>
                    <select name="tipo_descarga" class="xml-input">
                        <option value="">Todas</option>
                        <option value="emitidas" @selected(request('tipo_descarga') === 'emitidas')>Emitidas</option>
                        <option value="recibidas" @selected(request('tipo_descarga') === 'recibidas')>Recibidas</option>
                    </select>
                </div>
                <div>
                    <label class="xml-field-label">Estado</label>
                    <select name="estado_sat" class="xml-input">
                        <option value="">Todos</option>
                        <option value="vigente" @selected(request('estado_sat') === 'vigente')>Vigente</option>
                        <option value="cancelado" @selected(request('estado_sat') === 'cancelado')>Cancelado</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('client.sat.cfdis.index', request('customer_id') ? ['customer_id' => request('customer_id')] : []) }}"
                       class="h-9 px-4 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold hover:bg-gray-200 inline-flex items-center">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div x-data="cfdiModal()">
        <div class="xml-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">UUID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receptor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cfdis as $cfdi)
                        @php
                            $journalEntry = $cfdi->journalEntries->first();
                            $journal = $journalEntry?->journal;
                            $canGenerateJournal = $cfdi->estado_sat === 'vigente'
                                && $cfdi->tipo_comprobante === 'I'
                                && ! $journal;
                            $isIssuedByCustomer = strtoupper((string) $cfdi->rfc_emisor) === strtoupper((string) $cfdi->customer->rfc)
                                || $cfdi->tipo_descarga === 'emitidas';
                            $counterpartyRfc = strtoupper((string) ($isIssuedByCustomer ? $cfdi->rfc_receptor : $cfdi->rfc_emisor));
                            $counterpartyName = $isIssuedByCustomer ? $cfdi->razon_social_receptor : $cfdi->razon_social_emisor;
                            $thirdParty = $thirdPartiesByKey->get($cfdi->customer_id . '|' . $counterpartyRfc);
                            $hasThirdPartyAccount = $thirdParty?->default_account_id;
                            $accountsForPrompt = ($accountingAccountsByCustomer->get($cfdi->customer_id) ?? collect())
                                ->map(fn ($account) => ['id' => $account->id, 'label' => $account->code . ' - ' . $account->name])
                                ->values();
                            $journalPrompt = [
                                'action' => route('client.sat.cfdis.accounting-journal', $cfdi),
                                'rfc' => $counterpartyRfc,
                                'name' => $counterpartyName ?: 'Sin razon social',
                                'type' => $isIssuedByCustomer ? 'cliente' : 'proveedor',
                                'accounts' => $accountsForPrompt,
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $cfdi->fecha_emision?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                <span title="{{ $cfdi->uuid }}">{{ Str::limit($cfdi->uuid, 18) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ Str::limit($cfdi->customer->razon_social, 28) }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                <div class="font-mono text-xs">{{ $cfdi->rfc_emisor }}</div>
                                <div class="text-xs text-gray-400">{{ Str::limit($cfdi->razon_social_emisor, 24) }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                <div class="font-mono text-xs">{{ $cfdi->rfc_receptor }}</div>
                                <div class="text-xs text-gray-400">{{ Str::limit($cfdi->razon_social_receptor, 24) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ $cfdi->tipo_comprobante }} {{ $typeLabels[$cfdi->tipo_comprobante] ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $money($cfdi->total) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $cfdi->estado_sat === 'vigente' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($cfdi->estado_sat) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($journal)
                                        <span class="px-2 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold"
                                              title="{{ $journal->number }}">
                                            Poliza {{ $journal->status === 'posted' ? 'contabilizada' : 'borrador' }}
                                        </span>
                                    @elseif($canGenerateJournal)
                                        @if($hasThirdPartyAccount)
                                            <form method="POST" action="{{ route('client.sat.cfdis.accounting-journal', $cfdi) }}">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100">
                                                    Generar poliza
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                    @click="openJournalAccountPrompt(JSON.parse($el.nextElementSibling.textContent))"
                                                    class="px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-xs font-semibold hover:bg-amber-100">
                                                Generar poliza
                                            </button>
                                            <script type="application/json">@json($journalPrompt)</script>
                                        @endif
                                    @endif

                                    <button type="button"
                                            @click="show({{ $cfdi->id }})"
                                            class="px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100">
                                        Ver
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-400">No hay XML con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $cfdis->links() }}
            </div>
        </div>

        <div class="xml-stats-panel">
            <div class="xml-stats-grid">
                <div class="bg-white border border-gray-200 rounded-lg p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Ingresos vs gastos</h3>
                        <span class="text-xs text-gray-400">Ultimos meses filtrados</span>
                    </div>

                    <div class="h-48 flex items-end gap-4">
                        @forelse(data_get($stats, 'trend', []) as $month)
                            <div class="flex-1 h-full flex flex-col justify-end items-center gap-2 min-w-0">
                                <div class="w-full h-36 flex items-end justify-center gap-2">
                                    <div class="w-5 rounded-t bg-sky-400"
                                         style="height: {{ max(4, $month['ingresos_percent']) }}%"
                                         title="Ingresos {{ $money($month['ingresos']) }}"></div>
                                    <div class="w-5 rounded-t bg-slate-900"
                                         style="height: {{ max(4, $month['gastos_percent']) }}%"
                                         title="Gastos {{ $money($month['gastos']) }}"></div>
                                </div>
                                <span class="text-[11px] text-gray-500 truncate">{{ $month['label'] }}</span>
                            </div>
                        @empty
                            <div class="w-full h-full flex items-center justify-center text-sm text-gray-400">
                                No hay datos para graficar.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 flex justify-center gap-5 text-xs text-gray-600">
                        <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>Ingresos</span>
                        <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>Gastos</span>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Estado de documentos</h3>
                        <span class="text-xs text-gray-400">{{ number_format(data_get($stats, 'status.total', 0)) }} XML</span>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-center gap-8">
                        <div class="relative w-40 h-40 rounded-full"
                             style="background: conic-gradient(#2698b8 0 {{ data_get($stats, 'status.vigentes_percent', 0) }}%, #e5e7eb {{ data_get($stats, 'status.vigentes_percent', 0) }}% 100%);">
                            <div class="absolute inset-4 rounded-full bg-white flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-gray-900">{{ data_get($stats, 'status.vigentes_percent', 0) }}%</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Vigentes</span>
                            </div>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-8">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2698b8]"></span>Vigentes
                                </span>
                                <span class="font-bold text-gray-900">{{ number_format(data_get($stats, 'status.vigentes', 0)) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-8">
                                <span class="inline-flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>Cancelados
                                </span>
                                <span class="font-bold text-gray-900">{{ number_format(data_get($stats, 'status.cancelados', 0)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Top 10 clientes</h3>
                        <span class="text-xs text-gray-400">Cuando {{ $selectedCustomer?->rfc ?? 'el RFC' }} emite</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse(data_get($stats, 'top_clients', []) as $item)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $item->nombre ?: 'Sin razon social' }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $item->rfc }} · {{ number_format($item->cfdis) }} XML</p>
                                </div>
                                <p class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ $money($item->total) }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-sm text-gray-400 text-center">
                                Selecciona un cliente para calcular sus principales clientes.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Top 10 proveedores</h3>
                        <span class="text-xs text-gray-400">Cuando {{ $selectedCustomer?->rfc ?? 'el RFC' }} recibe</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse(data_get($stats, 'top_suppliers', []) as $item)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $item->nombre ?: 'Sin razon social' }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $item->rfc }} · {{ number_format($item->cfdis) }} XML</p>
                                </div>
                                <p class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ $money($item->total) }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-sm text-gray-400 text-center">
                                Selecciona un cliente para calcular sus principales proveedores.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Detalle CFDI</h2>
                        <p class="text-sm text-gray-500 mt-1 font-mono break-all" x-text="cfdi?.uuid"></p>
                    </div>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <div class="p-6">
                    <template x-if="loading">
                        <div class="py-20 text-center text-gray-500">Cargando CFDI...</div>
                    </template>

                    <template x-if="!loading && cfdi">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Emisor</p>
                                        <p class="font-semibold text-gray-900" x-text="cfdi.razon_social_emisor"></p>
                                        <p class="text-sm text-gray-600 font-mono" x-text="cfdi.rfc_emisor"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Receptor</p>
                                        <p class="font-semibold text-gray-900" x-text="cfdi.razon_social_receptor"></p>
                                        <p class="text-sm text-gray-600 font-mono" x-text="cfdi.rfc_receptor"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">UUID</p>
                                        <p class="text-sm font-mono text-gray-900 break-all" x-text="cfdi.uuid"></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Fecha</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="formatDate(cfdi.fecha_emision)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Tipo</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="cfdi.tipo_comprobante"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Subtotal</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="money(cfdi.subtotal)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Total</p>
                                        <p class="text-lg font-bold text-gray-900" x-text="money(cfdi.total)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">IVA trasladado</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="money(cfdi.total_impuestos_trasladados)"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase">Retenciones</p>
                                        <p class="text-sm font-semibold text-gray-900" x-text="money(cfdi.total_impuestos_retenidos)"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">Conceptos</h3>
                                <div class="overflow-x-auto border rounded-xl">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-50 text-gray-600 border-b">
                                            <tr>
                                                <th class="px-4 py-3 font-medium">Clave/Cant.</th>
                                                <th class="px-4 py-3 font-medium">Descripcion</th>
                                                <th class="px-4 py-3 font-medium text-right">Importe</th>
                                                <th class="px-4 py-3 font-medium text-right">IVA</th>
                                                <th class="px-4 py-3 font-medium text-right">Retenciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="item in cfdi.conceptos" :key="item.id">
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <div class="font-mono text-xs text-blue-600" x-text="item.clave_prod_serv"></div>
                                                        <div class="text-gray-500" x-text="item.cantidad + ' ' + item.clave_unidad"></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-700" x-text="item.descripcion"></td>
                                                    <td class="px-4 py-3 text-right font-semibold" x-text="money(item.importe)"></td>
                                                    <td class="px-4 py-3 text-right" x-text="money(item.importe_iva_trasladado)"></td>
                                                    <td class="px-4 py-3 text-right" x-text="money((parseFloat(item.importe_iva_retenido || 0) + parseFloat(item.importe_isr_retenido || 0)))"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="journalPrompt.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="journalPrompt.open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
                <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Asignar cuenta al tercero</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Este <span x-text="journalPrompt.type"></span> no tiene cuenta default.
                        </p>
                    </div>
                    <button @click="journalPrompt.open = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-bold text-gray-400 uppercase">RFC</p>
                        <p class="mt-1 font-mono font-semibold text-gray-900" x-text="journalPrompt.rfc"></p>
                        <p class="mt-1 text-sm text-gray-600" x-text="journalPrompt.name"></p>
                    </div>

                    <form method="POST" :action="journalPrompt.action" class="space-y-4">
                        @csrf
                        <input type="hidden" name="save_third_party" value="1">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cuenta default</label>
                            <select name="default_account_id" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccionar cuenta</option>
                                <template x-for="account in journalPrompt.accounts" :key="account.id">
                                    <option :value="account.id" x-text="account.label"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button"
                                    @click="journalPrompt.open = false"
                                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
                                Asignar y generar
                            </button>
                        </div>
                    </form>

                    <form method="POST" :action="journalPrompt.action" class="border-t border-gray-100 pt-4">
                        @csrf
                        <button class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                            Generar solo esta vez con cuenta default
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.client>

<script>
function cfdiModal() {
    return {
        open: false,
        loading: false,
        cfdi: null,
        journalPrompt: {
            open: false,
            action: '',
            rfc: '',
            name: '',
            type: '',
            accounts: []
        },
        openJournalAccountPrompt(data) {
            this.journalPrompt = {
                open: true,
                action: data.action,
                rfc: data.rfc,
                name: data.name,
                type: data.type,
                accounts: data.accounts || []
            };
        },
        async show(id) {
            this.open = true;
            this.loading = true;
            this.cfdi = null;

            try {
                const response = await fetch(`/client/sat/cfdis/${id}/json`);
                if (!response.ok) throw new Error('Error al cargar el CFDI');
                this.cfdi = await response.json();
            } catch (error) {
                console.error(error);
                alert('No se pudo cargar el detalle del CFDI.');
                this.open = false;
            } finally {
                this.loading = false;
            }
        },
        money(value) {
            return Number(value || 0).toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            });
        },
        formatDate(value) {
            return value ? new Date(value).toLocaleDateString('es-MX') : '-';
        }
    }
}
</script>
