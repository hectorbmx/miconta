{{-- resources/views/client/dashboard.blade.php --}}
<x-layouts.client>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel Cliente
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                Bienvenido al panel del cliente / tenant.
            </div>
        </div>
    </div>
    {{-- @if(!auth()->user()->tenant->stripe_account_id) --}}
    @if(!auth()->user()->tenant->stripe_charges_enabled)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-4">
        <div class="flex items-center justify-between">
            <div>
                <strong>Configura Stripe</strong>
                <p class="text-sm">
                    Conecta tu cuenta para empezar a recibir pagos de tus clientes.
                </p>
            </div>

            <a href="{{ route('client.stripe.connect') }}"
               class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-semibold hover:bg-yellow-700">
                Conectar Stripe
            </a>
        </div>
    </div>
@endif
</x-layouts.client>