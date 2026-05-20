<x-layouts.client>
  <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Configuración de {{ $customer->razon_social }}</h1>
            
            {{-- Botón Regresar --}}
            <a href="{{ route('client.clientes.show', $customer->id) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 bg-white transition-colors shadow-sm">
               ← Regresar
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-6">
        {{-- Estado de la e.firma (Visualización rápida) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">Estado de la e.firma</h2>
            <div class="flex gap-4">
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $customer->certificate_path ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    Certificado: {{ $customer->certificate_path ? 'Cargado' : 'Pendiente' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $customer->private_key_path ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    Llave privada: {{ $customer->private_key_path ? 'Cargado' : 'Pendiente' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $customer->fiel_password ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    Contraseña: {{ $customer->fiel_password ? 'Guardada' : 'Pendiente' }}
                </span>
            </div>
        </div>

        {{-- Formulario de Edición --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('client.clientes.update', $customer->id) }}" enctype="multipart/form-data">
                @csrf 
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Datos Fiscales --}}
                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider">Datos Fiscales</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RFC</label>
                            <input type="text" name="rfc" value="{{ old('rfc', $customer->rfc) }}" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social', $customer->razon_social) }}" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Correo para Notificaciones</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                        </div>
                    </div>

                    {{-- Actualizar FIEL / E.FIRMA --}}
                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider">Actualizar FIEL / E.FIRMA</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Certificado (.cer)</label>
                            <input type="file" name="certificate" class="w-full mt-1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Llave privada (.key)</label>
                            <input type="file" name="private_key" class="w-full mt-1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contraseña FIEL</label>
                            <input type="password" name="fiel_password" placeholder="••••••••" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-800">
                        Actualizar Información
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.client>