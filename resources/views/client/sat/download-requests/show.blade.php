<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Solicitud de descarga</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $downloadRequest->customer->razon_social }} — {{ $downloadRequest->customer->rfc }}
                </p>
            </div>
            <a href="{{ route('client.clientes.show', $downloadRequest->customer) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                ← Regresar
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- ESTADO SOLICITUD --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ processing: false }">

    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">
            Estado de la solicitud
        </h2>

        <div class="flex items-center gap-3">
            {{-- BOTÓN PROCESAR --}}
            @if(in_array($downloadRequest->estado, ['verifying', 'downloading']))
                <form method="POST" 
                      action="{{ route('client.sat.download-requests.process', $downloadRequest) }}"
                      x-on:submit="processing = true">
                    @csrf
                    <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-yellow-500 text-white text-xs font-bold hover:bg-yellow-600 transition flex items-center gap-2 shadow-sm"
                            :class="{ 'opacity-50 cursor-not-allowed': processing }"
                            :disabled="processing">
                        
                        <template x-if="processing">
                            <svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>

                        <span x-text="processing ? 'Procesando...' : '▶ Procesar ahora'"></span>
                    </button>
                </form>
            @endif

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
                    'pending'     => '⏳ Pendiente',
                    'querying'    => '🔍 Consultando SAT',
                    'verifying'   => '⏱ Verificando',
                    'downloading' => '⬇️ Descargando',
                    'completed'   => '✅ Completada',
                    'failed'      => '❌ Error',
                ];
            @endphp

            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $estadoClasses[$downloadRequest->estado] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $estadoLabels[$downloadRequest->estado] ?? $downloadRequest->estado }}
            </span>
        </div>
    </div>

    <div class="p-6 grid grid-cols-2 gap-6">
        <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Período</p>
            <p class="text-sm font-semibold text-gray-900 mt-1">
                {{ $downloadRequest->fecha_inicio->format('d/m/Y') }}
                →
                {{ $downloadRequest->fecha_fin->format('d/m/Y') }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Tipo descarga</p>
            <p class="text-sm font-semibold text-gray-900 mt-1">
                {{ ucfirst($downloadRequest->tipo_descarga) }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Tipo solicitud</p>
            <p class="text-sm font-semibold text-gray-900 mt-1">
                {{ strtoupper($downloadRequest->tipo_solicitud) }}
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Total XMLs</p>
            <p class="text-sm font-semibold text-gray-900 mt-1">
                {{ $downloadRequest->total_xml ?: '—' }}
            </p>
        </div>

        <div class="col-span-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Request ID SAT</p>
            <p class="text-xs font-mono text-gray-700 mt-1 break-all">
                {{ $downloadRequest->request_id_sat ?? '—' }}
            </p>
        </div>
    </div>

    {{-- MODAL DE CARGA (OVERLAY) --}}
    <div x-show="processing" 
         class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
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
            <h3 class="mt-6 text-lg font-bold text-gray-900 text-center">Verificando en el SAT</h3>
            <p class="mt-2 text-sm text-gray-500 text-center leading-relaxed">
                Estamos consultando si tus paquetes ya están listos para descarga.
            </p>
        </div>
    </div>
</div>

    {{-- CLIENTE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Cliente</h2>
        </div>

        <div class="p-6 space-y-4">

            <div>
                <p class="text-xs text-gray-500">Razón social</p>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $downloadRequest->customer->razon_social }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">RFC</p>
                <p class="text-sm font-mono text-gray-900">
                    {{ $downloadRequest->customer->rfc }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Solicitado por</p>
                <p class="text-sm text-gray-900">
                    {{ $downloadRequest->user->name ?? 'Sistema' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Fecha solicitud</p>
                <p class="text-sm text-gray-900">
                    {{ $downloadRequest->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

        </div>
    </div>

    {{-- FLUJO SAT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Flujo SAT</h2>
        </div>

        <div class="p-6 space-y-3">

            @php
                $pasos = [
                    'querying'    => ['label' => 'Solicitud enviada', 'estados' => ['querying', 'verifying', 'downloading', 'completed']],
                    'verifying'   => ['label' => 'Verificación', 'estados' => ['verifying', 'downloading', 'completed']],
                    'downloading' => ['label' => 'Descarga paquetes', 'estados' => ['downloading', 'completed']],
                    'completed'   => ['label' => 'Completado', 'estados' => ['completed']],
                ];
            @endphp

            @foreach($pasos as $paso)

                @php
                    $activo = in_array($downloadRequest->estado, $paso['estados']);
                @endphp

                <div class="flex items-center gap-3">

                    <span class="text-lg">
                        {{ $activo ? '✅' : '⬜' }}
                    </span>

                    <span class="text-sm {{ $activo ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                        {{ $paso['label'] }}
                    </span>
                </div>

            @endforeach

            @if($downloadRequest->packages_ids)

                <div class="pt-4 border-t border-gray-200">

                    <p class="text-xs text-gray-500 uppercase font-medium mb-3">
                        Paquetes SAT
                    </p>

                    <div class="space-y-2">

                        @foreach($downloadRequest->packages_ids as $packageId)

                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200">

                                <span>📦</span>

                                <span class="text-[11px] font-mono text-gray-700 break-all">
                                    {{ $packageId }}
                                </span>

                            </div>

                        @endforeach

                    </div>
                </div>

            @endif

        </div>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-end gap-4">
        
        {{-- RFC --}}
        <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">RFC</label>
            <input type="text" name="rfc" value="{{ request('rfc') }}" placeholder="Emisor/Receptor"
                class="w-full h-9 rounded-lg border-gray-200 text-sm focus:ring-blue-500">
        </div>

        {{-- Fechas --}}
        <div class="flex-[2] min-w-[260px] grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                    class="w-full h-9 rounded-lg border-gray-200 text-sm focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                    class="w-full h-9 rounded-lg border-gray-200 text-sm focus:ring-blue-500">
            </div>
        </div>

        {{-- Tipo Comprobante --}}
        <div class="flex-1 min-w-[130px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipo</label>
            <select name="tipo" class="w-full h-9 rounded-lg border-gray-200 text-sm focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="I" {{ request('tipo') == 'I' ? 'selected' : '' }}>Ingreso (I)</option>
                <option value="E" {{ request('tipo') == 'E' ? 'selected' : '' }}>Egreso (E)</option>
                <option value="N" {{ request('tipo') == 'N' ? 'selected' : '' }}>Nómina (N)</option>
                <option value="P" {{ request('tipo') == 'P' ? 'selected' : '' }}>Pago (P)</option>
            </select>
        </div>

        {{-- Acciones --}}
        <div class="flex gap-2">
            <button type="submit" class="h-9 px-4 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-gray-800 transition">
                🔍 Filtrar
            </button>
            @if(request()->anyFilled(['rfc', 'fecha_inicio', 'fecha_fin', 'tipo']))
                <a href="{{ url()->current() }}" class="h-9 px-4 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold flex items-center hover:bg-gray-200 transition">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>
            {{-- CFDIS DESCARGADOS --}}
            @if($downloadRequest->cfdis->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            CFDIs descargados
                            <span class="ml-2 text-sm font-normal text-gray-500">({{ $downloadRequest->cfdis->count() }})</span>
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
<div x-data="cfdiModal()">





                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UUID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RFC</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>


                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($downloadRequest->cfdis as $cfdi)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-gray-700">
                                            {{ $cfdi->fecha_emision?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 font-mono text-xs text-gray-500">
                                            {{ substr($cfdi->uuid, 0, 16) }}...
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">
                                            {{ $downloadRequest->tipo_descarga === 'emitidas' ? $cfdi->rfc_receptor : $cfdi->rfc_emisor }}
                                        </td>
                                        <td class="px-6 py-3 font-semibold text-gray-900">
                                            ${{ number_format($cfdi->total, 2) }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                {{ $cfdi->estado_sat === 'vigente' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ ucfirst($cfdi->estado_sat) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                                <button
                                                    type="button"
                                                    @click="show({{ $cfdi->id }})"
                                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 text-xs font-semibold hover:bg-blue-100"
                                                >
                                                    👁 Ver
                                                </button>

                                            </td>
                                            

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
<!-- MODAL CFDI -->
<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
>

    <div
        @click.away="open = false"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden"
    >

        <!-- HEADER -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">

            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Detalle CFDI
                </h2>

                <p class="text-sm text-gray-500 mt-1" x-text="cfdi?.uuid"></p>
            </div>

            <button
                @click="open = false"
                class="text-gray-400 hover:text-gray-600 text-xl"
            >
                ✕
            </button>
        </div>

        <!-- BODY -->
        <div class="p-6">

            <template x-if="loading">

                <div class="py-20 text-center text-gray-500">
                    Cargando CFDI...
                </div>

            </template>

            <template x-if="!loading && cfdi">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="space-y-4">

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Emisor
                            </p>

                            <p class="font-semibold text-gray-900" x-text="cfdi.razon_social_emisor"></p>

                            <p class="text-sm text-gray-600" x-text="cfdi.rfc_emisor"></p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Receptor
                            </p>

                            <p class="font-semibold text-gray-900" x-text="cfdi.razon_social_receptor"></p>

                            <p class="text-sm text-gray-600" x-text="cfdi.rfc_receptor"></p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                UUID
                            </p>

                            <p class="text-sm font-mono text-gray-900 break-all" x-text="cfdi.uuid"></p>
                        </div>

                    </div>

                    <div class="space-y-4">

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Fecha emisión
                            </p>

                            <p class="text-sm font-semibold text-gray-900" x-text="cfdi.fecha_emision"></p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Tipo comprobante
                            </p>

                            <p class="text-sm font-semibold text-gray-900" x-text="cfdi.tipo_comprobante"></p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Total
                            </p>

                            <p class="text-2xl font-bold text-gray-900">
                                $<span x-text="cfdi.total"></span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Estado SAT
                            </p>

                            <span
                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"
                                :class="cfdi.estado_sat === 'vigente'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                                x-text="cfdi.estado_sat"
                            ></span>
                        </div>
                        

                    </div>
                    

                </div>

            </template>

        </div>
<div class="mt-8">
    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b pb-2">
        Conceptos / Partidas
    </h3>
    
    <div class="overflow-x-auto border rounded-xl">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 border-b">
                <tr>
                    <th class="px-4 py-3 font-medium">Clave/Cant.</th>
                    <th class="px-4 py-3 font-medium">Descripción</th>
                    <th class="px-4 py-3 font-medium text-right">Valor Unit.</th>
                    <th class="px-4 py-3 font-medium text-right">Importe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="item in cfdi.conceptos" :key="item.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs text-blue-600" x-text="item.clave_prod_serv"></div>
                            <div class="text-gray-500" x-text="item.cantidad + ' ' + item.clave_unidad"></div>
                        </td>
                        <td class="px-4 py-3 text-gray-700" x-text="item.descripcion"></td>
                        <td class="px-4 py-3 text-right text-gray-600" x-text="'$' + parseFloat(item.valor_unitario).toLocaleString()"></td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900" x-text="'$' + parseFloat(item.importe).toLocaleString()"></td>
                    </tr>
                </template>

                <template x-if="cfdi.conceptos.length === 0">
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                            No hay conceptos registrados para este CFDI.
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
    </div>

</div>


                        </div>
                    </div>
                </div>
            @endif
        </div>

     

         
    </div>

</x-layouts.client>
<script>

function cfdiModal() {
    return {
        open: false,
        loading: false,
        cfdi: null,

        async show(id) {
            this.open = true;
            this.loading = true;
            this.cfdi = null;

            try {
                // Asegúrate de que esta ruta coincida con tu archivo de rutas web.php
                // Usamos la ruta que devuelve JSON
                const response = await fetch(`/client/sat/cfdis/${id}/json`);
                
                if (!response.ok) throw new Error('Error al cargar el CFDI');

                const data = await response.json();
                this.cfdi = data;
            } catch (e) {
                console.error("Error fetching CFDI:", e);
                alert("No se pudo cargar el detalle del CFDI.");
                this.open = false; // Cerramos el modal si hay error
            } finally {
                this.loading = false;
            }
        }
    }
}

</script>