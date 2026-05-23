<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Opiniones de cumplimiento 32-D</h1>
                <p class="text-sm text-gray-500">{{ $customer->razon_social }} · {{ $customer->rfc }}</p>
            </div>

            <a href="{{ route('client.clientes.show', $customer) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Volver al cliente
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Historial 32-D</h2>
                <p class="text-sm text-slate-500">Descargas de opinión de cumplimiento del SAT.</p>
            </div>

            <form method="POST" action="{{ route('client.sat.compliance-opinions.store', $customer) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors">
                    Descargar 32-D
                </button>
            </form>
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
                    @forelse($requests as $opinion)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $opinion->rfc }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $opinion->downloaded_at?->format('d/m/Y H:i') ?? $opinion->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $opinion->estado === 'completed' ? 'bg-green-100 text-green-700' : ($opinion->estado === 'failed' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $opinion->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-red-500 text-xs">
                                {{ $opinion->error_message ? Str::limit($opinion->error_message, 80) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
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

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $requests->links() }}
        </div>
    </div>
</x-layouts.client>
