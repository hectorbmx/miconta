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
            <a href="{{ route('client.clientes.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                ← Regresar
            </a>
        </div>
    </x-slot>

    @php $cliente = $cliente ?? $customer; @endphp

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
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

            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $cliente->satCfdis->count() }}</p>
                <p class="text-xs text-gray-500">XMLs</p>
            </div>
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
    

    {{-- ROW 2: INFORMACIÓN DEL CLIENTE (ANCHO COMPLETO) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900">Configuración del Cliente</h2>
            <p class="text-sm text-gray-500">Edita los datos fiscales y actualiza los archivos de la e.firma.</p>
        </div>

        <form method="POST" action="{{ route('client.clientes.update', $cliente->id) }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Columna Datos Fiscales --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-blue-600 uppercase">Datos Fiscales</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RFC</label>
                        <input type="text" name="rfc" value="{{ old('rfc', $cliente->rfc) }}" maxlength="13"
                               class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social</label>
                        <input type="text" name="razon_social" value="{{ old('razon_social', $cliente->razon_social) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo para Notificaciones</label>
                        <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Columna Archivos FIEL --}}
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold text-blue-600 uppercase">Actualizar FIEL / e.firma</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 border rounded-lg bg-gray-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certificado (.cer)</label>
                            <input type="file" name="certificate" accept=".cer" class="text-sm w-full">
                        </div>
                        <div class="p-4 border rounded-lg bg-gray-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Llave privada (.key)</label>
                            <input type="file" name="private_key" accept=".key" class="text-sm w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña FIEL</label>
                        <input type="password" name="fiel_password" autocomplete="new-password"
                               class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                               placeholder="{{ $cliente->fiel_password ? '••••••••••••' : 'Captura la contraseña' }}">
                        <p class="text-[10px] text-gray-400 mt-1 italic">Solo escribe si deseas cambiar la contraseña actual.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="password_has_spaces" value="1" id="password_has_spaces" class="rounded text-blue-600">
                        <label for="password_has_spaces" class="text-sm text-gray-600">La contraseña incluye espacios al final</label>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t flex justify-end gap-3">
                <button type="submit" class="px-6 py-2 bg-gray-900 text-white font-bold rounded-lg hover:bg-black transition">
                    Actualizar Información
                </button>
            </div>
        </form>
    </div>

    {{-- SECCIÓN DESCARGAS SAT --}}
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