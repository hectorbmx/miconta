<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $cliente->razon_social ?? $customer->razon_social }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    RFC: {{ $cliente->rfc ?? $customer->rfc }}
                </p>
            </div>
            
            {{-- En la cabecera del show.blade.php --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('client.clientes.edit', $cliente->id) }}" 
                    class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configuración
                    </a>
                    <a href="{{ route('client.clientes.index') }}" ...>← Regresar</a>
                </div>
        </div>
    </x-slot>

    @php $cliente = $cliente ?? $customer; @endphp

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

    {{-- ROW 1: ESTADO + RESUMEN + NUEVA SOLICITUD --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">

    {{-- CARD A: ESTADO FIEL --}}
    <div class="xl:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">
            Estado FIEL
        </h3>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Certificado</p>
                @if($cliente->certificate_path)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Cargado</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Llave privada</p>
                @if($cliente->private_key_path)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Cargada</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Contraseña</p>
                @if($cliente->fiel_password)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Guardada</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendiente</span>
                @endif
            </div>
        </div>
    </div>

    {{-- CARD B: RESUMEN --}}
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">
            Resumen
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $cliente->satDownloadRequests->count() }}</p>
                <p class="text-xs text-gray-500">Solicitudes</p>
            </div>

            <a href="{{ route('client.sat.cfdis.index', ['customer_id' => $cliente->id]) }}"
               class="group block rounded-lg -m-2 p-2 hover:bg-blue-50 transition">
                <p class="text-2xl font-bold text-gray-900 group-hover:text-blue-700">
                    {{ $cliente->satCfdis->count() }}
                </p>
                <p class="text-xs text-gray-500 group-hover:text-blue-600">XMLs</p>
            </a>
        </div>
    </div>

    {{-- CARD C: NUEVA SOLICITUD --}}
    <div class="xl:col-span-7 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                    Nueva solicitud SAT
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    Solicita XMLs directamente al SAT.
                </p>
            </div>
        </div>

        @if($cliente->certificate_path && $cliente->private_key_path && $cliente->fiel_password)
    <div x-data="{ loading: false }">
        <form method="POST"
              action="{{ route('client.sat.download-requests.store') }}"
              class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end"
              x-on:submit="loading = true">
            @csrf

            <input type="hidden" name="customer_id" value="{{ $cliente->id }}">

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider font-bold">Desde</label>
                <input type="date"
                       name="fecha_inicio"
                       required
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider font-bold">Hasta</label>
                <input type="date"
                       name="fecha_fin"
                       required
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider font-bold">Descarga</label>
                <select name="tipo_descarga"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="emitidas">Emitidas (Ingresos)</option>
                    <option value="recibidas">Recibidas (Egresos)</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider font-bold">Solicitud</label>
                <select name="tipo_solicitud"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="cfdi">CFDI / XML Completo</option>
                    <option value="metadata">Solo Metadata</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                        class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-2"
                        :class="{ 'opacity-70 cursor-not-allowed': loading }"
                        :disabled="loading">
                    
                    {{-- Pequeño spinner en el botón --}}
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>

                    <span x-text="loading ? 'Enviando...' : 'Enviar'"></span>
                </button>
            </div>
        </form>

        {{-- MODAL DE CARGA (OVERLAY) --}}
        <div x-show="loading" 
             class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-cloak>
            
            <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center max-w-xs w-full mx-4">
                <div class="relative flex items-center justify-center">
                    {{-- Spinner de fondo --}}
                    <div class="w-16 h-16 border-4 border-blue-50 border-t-blue-600 rounded-full animate-spin"></div>
                    {{-- Icono de Nube --}}
                    <div class="absolute">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="mt-6 text-lg font-bold text-gray-900 text-center">Solicitando al SAT</h3>
                <p class="mt-2 text-sm text-gray-500 text-center leading-relaxed">
                    Estamos validando tus credenciales y enviando la solicitud. Por favor, no cierres esta ventana.
                </p>
            </div>
        </div>
    </div>
@else
    <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="text-sm font-medium text-amber-800">
            Carga certificado, llave privada y contraseña FIEL para poder solicitar descargas.
        </span>
    </div>
@endif
    </div>

</div>
    

    {{-- SECCIÓN DESCARGAS SAT --}}
    {{-- RESUMEN FISCAL MENSUAL --}}
    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $estimatedTotal = data_get($taxSummary, 'estimated.total_estimado_a_pagar', 0);
        $estimatedClass = $estimatedTotal >= 0 ? 'text-rose-700 bg-rose-50 border-rose-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200';
    @endphp

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Resumen fiscal mensual</h2>
                <p class="text-sm text-gray-500">Estimacion basada en XML vigentes descargados para este cliente.</p>
            </div>

            <form method="GET" action="{{ route('client.clientes.show', $cliente) }}" class="flex items-end gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mes</label>
                    <input type="month"
                           name="month"
                           value="{{ $selectedMonth }}"
                           class="h-9 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit"
                        class="h-9 px-4 rounded-lg bg-gray-900 text-white text-xs font-bold hover:bg-gray-800">
                    Ver mes
                </button>
            </form>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Ingresos emitidos</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $money(data_get($taxSummary, 'issued.income.total')) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        IVA: {{ $money(data_get($taxSummary, 'issued.net_iva_trasladado')) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Gastos recibidos</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $money(data_get($taxSummary, 'received.expenses.total')) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        IVA acreditable: {{ $money(data_get($taxSummary, 'received.net_iva_acreditable')) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Retenciones</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $money(data_get($taxSummary, 'estimated.retenciones_a_pagar')) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        IVA {{ $money(data_get($taxSummary, 'retentions.iva_retenido_recibidas')) }}
                        / ISR {{ $money(data_get($taxSummary, 'retentions.isr_retenido_recibidas')) }}
                    </p>
                </div>

                <div class="rounded-lg border p-4 {{ $estimatedClass }}">
                    <p class="text-xs font-bold uppercase">Total estimado</p>
                    <p class="mt-2 text-2xl font-bold">{{ $money($estimatedTotal) }}</p>
                    <p class="mt-1 text-xs">
                        IVA periodo: {{ $money(data_get($taxSummary, 'estimated.iva_periodo')) }}
                    </p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">XML considerados</p>
                    <p class="mt-2 text-xl font-bold text-gray-900">{{ data_get($taxSummary, 'cfdis_count', 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        Periodo {{ data_get($taxSummary, 'period.start') }} a {{ data_get($taxSummary, 'period.end') }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Notas de credito</p>
                    <p class="mt-2 text-sm text-gray-700">
                        Emitidas: {{ $money(data_get($taxSummary, 'issued.credit_notes.total')) }}
                    </p>
                    <p class="mt-1 text-sm text-gray-700">
                        Recibidas: {{ $money(data_get($taxSummary, 'received.credit_notes.total')) }}
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase">Alertas</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            Sin impuestos: {{ data_get($taxSummary, 'alerts.missing_taxes_cfdis', 0) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            Moneda ext.: {{ data_get($taxSummary, 'alerts.foreign_currency_cfdis', 0) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            PPD sin pago: {{ data_get($taxSummary, 'alerts.ppd_without_payment_cfdis', 0) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            Pagos: {{ data_get($taxSummary, 'alerts.payment_complements_cfdis', 0) }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('client.clientes.accounting-journals.index', $cliente) }}"
                   class="rounded-lg border border-blue-200 bg-blue-50 p-4 hover:bg-blue-100 transition">
                    <p class="text-xs font-bold text-blue-700 uppercase">Polizas contables</p>
                    <p class="mt-2 text-xl font-bold text-blue-950">{{ number_format($accountingJournalStats['total'] ?? 0) }}</p>
                    <p class="mt-1 text-xs text-blue-700">
                        {{ number_format($accountingJournalStats['draft'] ?? 0) }} borradores / {{ number_format($accountingJournalStats['posted'] ?? 0) }} contabilizadas
                    </p>
                    <p class="mt-3 text-xs font-bold text-blue-800">Ver polizas</p>
                </a>

                <a href="{{ route('client.clientes.accounting-journals.reports', $cliente) }}"
                   class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 hover:bg-emerald-100 transition">
                    <p class="text-xs font-bold text-emerald-700 uppercase">Reportes contables</p>
                    <p class="mt-2 text-xl font-bold text-emerald-950">Diario / Auxiliar</p>
                    <p class="mt-1 text-xs text-emerald-700">Balanza de comprobacion</p>
                    <p class="mt-3 text-xs font-bold text-emerald-800">Ver reportes</p>
                </a>

                <a href="{{ route('client.clientes.third-parties.index', $cliente) }}"
                   class="rounded-lg border border-violet-200 bg-violet-50 p-4 hover:bg-violet-100 transition">
                    <p class="text-xs font-bold text-violet-700 uppercase">Terceros contables</p>
                    <p class="mt-2 text-xl font-bold text-violet-950">Clientes / Proveedores</p>
                    <p class="mt-1 text-xs text-violet-700">Reglas por RFC y cuenta</p>
                    <p class="mt-3 text-xs font-bold text-violet-800">Ver terceros</p>
                </a>
            </div>
        </div>
    </div>

    {{-- CATALOGO CONTABLE --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ openAccountForm: false }">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Cuentas contables</h2>
                <p class="text-sm text-gray-500">Catalogo base para automatizar polizas de este cliente.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($cliente->accountingAccounts->isEmpty())
                    <form method="POST" action="{{ route('client.clientes.accounting-accounts.seed', $cliente) }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                            Generar catalogo base
                        </button>
                    </form>
                @endif

                <button type="button"
                        @click="openAccountForm = !openAccountForm"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    + Nueva cuenta
                </button>
            </div>
        </div>

        <div x-show="openAccountForm" x-cloak class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <form method="POST" action="{{ route('client.clientes.accounting-accounts.store', $cliente) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Codigo</label>
                    <input name="code" value="{{ old('code') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="601.01">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nombre</label>
                    <input name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Gastos generales">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                    <select name="type" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="asset">Activo</option>
                        <option value="liability">Pasivo</option>
                        <option value="equity">Capital</option>
                        <option value="income">Ingreso</option>
                        <option value="expense">Gasto</option>
                        <option value="cost">Costo</option>
                        <option value="order">Orden</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Naturaleza</label>
                    <select name="nature" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="debit">Deudora</option>
                        <option value="credit">Acreedora</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Codigo SAT</label>
                    <input name="sat_group_code" value="{{ old('sat_group_code') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="601.01">
                </div>

                <div class="md:col-span-1">
                    <button type="submit" class="w-full px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-bold hover:bg-gray-800">
                        Guardar
                    </button>
                </div>
            </form>
        </div>

        @if($cliente->accountingAccounts->isEmpty())
            <div class="p-10 text-center text-gray-400">
                <p class="text-sm">Este cliente aun no tiene catalogo contable.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Naturaleza</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo SAT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($cliente->accountingAccounts as $account)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-gray-900">{{ $account->code }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-900">{{ $account->name }}</div>
                                    @if($account->is_default)
                                        <div class="text-xs text-blue-600">Cuenta base</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ ucfirst($account->type) }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $account->nature === 'debit' ? 'Deudora' : 'Acreedora' }}</td>
                                <td class="px-6 py-3 font-mono text-gray-500">{{ $account->sat_group_code ?? '-' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $account->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form method="POST" action="{{ route('client.clientes.accounting-accounts.toggle', [$cliente, $account]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-xs font-semibold text-gray-600 hover:text-gray-900">
                                            {{ $account->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if(false)
    {{-- POLIZAS CONTABLES --}}
    @php
        $activeAccountingAccounts = $cliente->accountingAccounts->where('is_active', true);
        $accountingJournals = $cliente->accountingJournals->sortByDesc('date');
    @endphp

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-data="{
            openJournalForm: false,
            reviewJournal: null,
            money(value) {
                return Number(value || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
            }
         }">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Polizas contables</h2>
                <p class="text-sm text-gray-500">Captura manual de polizas para despues automatizarlas con XML.</p>
            </div>

            <button type="button"
                    @click="openJournalForm = !openJournalForm"
                    class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 disabled:opacity-50"
                    @disabled($activeAccountingAccounts->count() < 2)>
                + Nueva poliza
            </button>
        </div>

        @if($activeAccountingAccounts->count() < 2)
            <div class="px-6 py-4 bg-amber-50 border-b border-amber-100 text-sm text-amber-700">
                Necesitas al menos dos cuentas contables activas para crear una poliza.
            </div>
        @endif

        <div x-show="openJournalForm" x-cloak class="px-6 py-5 border-b border-gray-200 bg-gray-50">
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

        @if($accountingJournals->isEmpty())
            <div class="p-10 text-center text-gray-400">
                <p class="text-sm">Aun no hay polizas contables para este cliente.</p>
            </div>
        @else
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
                        @foreach($accountingJournals as $journal)
                            @php
                                $journalReview = [
                                    'id' => $journal->id,
                                    'number' => $journal->number,
                                    'date' => optional($journal->date)->format('d/m/Y'),
                                    'type' => ['income' => 'Ingreso', 'expense' => 'Egreso', 'diary' => 'Diario'][$journal->type] ?? ucfirst($journal->type),
                                    'concept' => $journal->concept,
                                    'status' => $journal->status,
                                    'status_label' => $journal->status === 'posted' ? 'Contabilizada' : 'Borrador',
                                    'source' => $journal->source,
                                    'total_debit' => (float) $journal->total_debit,
                                    'total_credit' => (float) $journal->total_credit,
                                    'difference' => round((float) $journal->total_debit - (float) $journal->total_credit, 2),
                                    'post_url' => route('client.clientes.accounting-journals.post', [$cliente, $journal]),
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
                                <td class="px-6 py-3 text-gray-700">{{ optional($journal->date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 font-mono text-gray-900">{{ $journal->number }}</td>
                                <td class="px-6 py-3 text-gray-700">
                                    {{ ['income' => 'Ingreso', 'expense' => 'Egreso', 'diary' => 'Diario'][$journal->type] ?? ucfirst($journal->type) }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-900">{{ $journal->concept }}</div>
                                    <div class="text-xs text-gray-500">{{ $journal->entries->count() }} movimientos</div>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">${{ number_format($journal->total_debit, 2) }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">${{ number_format($journal->total_credit, 2) }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $journal->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $journal->status === 'posted' ? 'Contabilizada' : 'Borrador' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button type="button"
                                            @click="reviewJournal = JSON.parse(document.getElementById('journal-review-{{ $journal->id }}').textContent)"
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                                        {{ $journal->status === 'draft' ? 'Revisar' : 'Ver' }}
                                    </button>
                                    <script type="application/json" id="journal-review-{{ $journal->id }}">@json($journalReview)</script>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

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

    @endif

    @if($cliente->certificate_path && $cliente->private_key_path && $cliente->fiel_password)
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Solicitudes de descarga SAT</h2>
                    <p class="text-sm text-gray-500">Historial de solicitudes enviadas al SAT para este cliente.</p>
                </div>
                <button @click="$dispatch('open-modal', 'nueva-solicitud')"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    + Nueva solicitud
                </button>
            </div>

            @if($cliente->satDownloadRequests->isEmpty())
                <div class="p-12 text-center text-gray-400">
                    <p class="text-4xl mb-3">📭</p>
                    <p class="text-sm">Aún no hay solicitudes para este cliente.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitud</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">XMLs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cliente->satDownloadRequests->sortByDesc('created_at') as $solicitud)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $solicitud->fecha_inicio->format('d/m/Y') }}
                                        <span class="text-gray-400">→</span>
                                        {{ $solicitud->fecha_fin->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            {{ $solicitud->tipo_descarga === 'emitidas' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                            {{ ucfirst($solicitud->tipo_descarga) }}
                                        </span>
                                        <span class="ml-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ strtoupper($solicitud->tipo_solicitud) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                        {{ $solicitud->request_id_sat ? substr($solicitud->request_id_sat, 0, 16) . '...' : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 font-semibold">
                                        {{ $solicitud->total_xml ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $estadoClasses = [
                                                'pending'     => 'bg-gray-100 text-gray-600',
                                                'querying'    => 'bg-yellow-100 text-yellow-700',
                                                'verifying'   => 'bg-yellow-100 text-yellow-700',
                                                'downloading' => 'bg-blue-100 text-blue-700',
                                                'completed'   => 'bg-green-100 text-green-700',
                                                'failed'      => 'bg-red-100 text-red-700',
                                            ];
                                            $estadoLabels = [
                                                'pending'     => 'Pendiente',
                                                'querying'    => 'Consultando',
                                                'verifying'   => 'Verificando',
                                                'downloading' => 'Descargando',
                                                'completed'   => 'Completada',
                                                'failed'      => 'Error',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $estadoClasses[$solicitud->estado] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $estadoLabels[$solicitud->estado] ?? $solicitud->estado }}
                                        </span>
                                        @if($solicitud->error_message)
                                            <p class="text-xs text-red-500 mt-1 max-w-xs truncate">{{ $solicitud->error_message }}</p>
                                        @endif
                                    </td>
                                  <td class="px-6 py-4 flex items-center gap-2">
                                        @if(in_array($solicitud->estado, ['verifying', 'downloading']))
                                            <div x-data="{ processing: false }">
                                                <form method="POST" 
                                                    action="{{ route('client.sat.download-requests.process', $solicitud) }}"
                                                    x-on:submit="processing = true">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-3 py-1 rounded-lg bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 transition flex items-center gap-1.5"
                                                            :class="{ 'opacity-50 cursor-not-allowed': processing }"
                                                            :disabled="processing">
                                                        
                                                        {{-- Spinner pequeño --}}
                                                        <svg x-show="processing" class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>

                                                        <span x-text="processing ? 'Procesando...' : '▶ Procesar'"></span>
                                                    </button>

                                                    {{-- Modal de Carga para Procesamiento --}}
                                                    <div x-show="processing" 
                                                        class="fixed inset-0 z-[1000] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
                                                        x-cloak>
                                                        <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center max-w-xs w-full mx-4">
                                                            <div class="relative flex items-center justify-center">
                                                                <div class="w-16 h-16 border-4 border-yellow-50 border-t-yellow-500 rounded-full animate-spin"></div>
                                                                <div class="absolute">
                                                                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <h3 class="mt-6 text-lg font-bold text-gray-900 text-center">Verificando con el SAT</h3>
                                                            <p class="mt-2 text-sm text-gray-500 text-center">
                                                                Estamos consultando el estado de los paquetes. Por favor, no recargues la página.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif

                                        <a href="{{ route('client.sat.download-requests.show', $solicitud) }}"
                                        class="px-3 py-1 rounded-lg border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
    <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Constancias de Situación Fiscal
            </h2>
            <p class="text-sm text-slate-500">
                Historial de constancias descargadas desde el SAT para este cliente.
            </p>
        </div>

       <div x-data="{ loading: false }">
    <form method="POST" 
          action="{{ route('client.sat.csf.store', $customer) }}"
          @submit="loading = true">
        @csrf
        <button type="submit"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-colors">
            Descargar CSF
        </button>
    </form>

    <div x-show="loading" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60"
         style="display: none;">
        <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-indigo-600 mb-4"></div>
            <h2 class="text-lg font-bold text-gray-800">Conectando al SAT</h2>
            <p class="text-sm text-gray-500">Estamos procesando tu solicitud, esto puede tardar unos segundos.</p>
        </div>
    </div>
</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-bold text-slate-500 uppercase">
                    <th class="px-6 py-3">RFC</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3">Error</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($csfRequests ?? [] as $csf)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $csf->rfc }}
                        </td>

                        <td class="px-6 py-4 text-slate-600">
                            {{ $csf->downloaded_at?->format('d/m/Y H:i') ?? $csf->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $csfClasses = [
                                    'pending'     => 'bg-gray-100 text-gray-700',
                                    'downloading' => 'bg-blue-100 text-blue-700',
                                    'completed'   => 'bg-green-100 text-green-700',
                                    'failed'      => 'bg-red-100 text-red-700',
                                ];

                                $csfLabels = [
                                    'pending'     => 'Pendiente',
                                    'downloading' => 'Descargando',
                                    'completed'   => 'Completada',
                                    'failed'      => 'Error',
                                ];
                            @endphp

                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $csfClasses[$csf->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $csfLabels[$csf->estado] ?? $csf->estado }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-red-500 text-xs">
                            {{ $csf->error_message ? Str::limit($csf->error_message, 80) : '—' }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">
                            {{-- <a href="{{ route('client.sat.csf.show', [$customer, $csf]) }}"
                               class="inline-flex px-3 py-1.5 rounded-lg border text-xs font-semibold hover:bg-slate-50">
                                Ver
                            </a> --}}

                            @if($csf->pdf_path)
                                <a href="{{ route('client.sat.csf.download-pdf', [$customer, $csf]) }}"
                                   class="inline-flex px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                                    PDF
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            Aún no hay constancias descargadas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
    <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Opiniones de cumplimiento 32-D
            </h2>
            <p class="text-sm text-slate-500">
                Historial de opiniones de cumplimiento descargadas desde el SAT para este cliente.
            </p>
        </div>

        <div x-data="{ loading: false }">
            <form method="POST"
                  action="{{ route('client.sat.compliance-opinions.store', $customer) }}"
                  @submit="loading = true">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors">
                    Descargar 32-D
                </button>
            </form>

            <div x-show="loading"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60"
                 style="display: none;">
                <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-emerald-600 mb-4"></div>
                    <h2 class="text-lg font-bold text-gray-800">Conectando al SAT</h2>
                    <p class="text-sm text-gray-500">Estamos descargando la opinión 32-D, esto puede tardar unos segundos.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-bold text-slate-500 uppercase">
                    <th class="px-6 py-3">RFC</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3">Error</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($complianceOpinionRequests ?? [] as $opinion)
                    <tr>
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $opinion->rfc }}
                        </td>

                        <td class="px-6 py-4 text-slate-600">
                            {{ $opinion->downloaded_at?->format('d/m/Y H:i') ?? $opinion->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $opinionClasses = [
                                    'pending'     => 'bg-gray-100 text-gray-700',
                                    'downloading' => 'bg-blue-100 text-blue-700',
                                    'completed'   => 'bg-green-100 text-green-700',
                                    'failed'      => 'bg-red-100 text-red-700',
                                ];

                                $opinionLabels = [
                                    'pending'     => 'Pendiente',
                                    'downloading' => 'Descargando',
                                    'completed'   => 'Completada',
                                    'failed'      => 'Error',
                                ];
                            @endphp

                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $opinionClasses[$opinion->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $opinionLabels[$opinion->estado] ?? $opinion->estado }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-red-500 text-xs">
                            {{ $opinion->error_message ? Str::limit($opinion->error_message, 80) : '-' }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">
                            @if($opinion->pdf_path)
                                <a href="{{ route('client.sat.compliance-opinions.download-pdf', [$customer, $opinion]) }}"
                                   class="inline-flex px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                                    PDF
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            Aun no hay opiniones 32-D descargadas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
    {{-- MODAL NUEVA SOLICITUD --}}
    <x-modal name="nueva-solicitud" :show="false">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Nueva solicitud de descarga</h2>
            <p class="text-sm text-gray-500 mb-6">El SAT procesará la solicitud y pondrá disponibles los paquetes para descarga.</p>

            <form method="POST" action="{{ route('client.sat.download-requests.store') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="customer_id" value="{{ $cliente->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" required
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                        <input type="date" name="fecha_fin" required
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de descarga</label>
                        <select name="tipo_descarga" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="emitidas">Emitidas (ingresos)</option>
                            <option value="recibidas">Recibidas (gastos)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de solicitud</label>
                        <select name="tipo_solicitud" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="cfdi">CFDI (XML completo)</option>
                            <option value="metadata">Metadata</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" x-on:click="$dispatch('close')"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">
                        Enviar solicitud al SAT
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

</x-layouts.client>
