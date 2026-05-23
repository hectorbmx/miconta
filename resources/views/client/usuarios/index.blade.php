<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $currentUsers }} / {{ $maxUsers ?? 'Sin limite' }} usuarios del plan
                </p>
            </div>

            <button type="button"
                    onclick="document.getElementById('createUserModal').classList.remove('hidden')"
                    @disabled(! is_null($maxUsers) && $currentUsers >= $maxUsers)
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                + Nuevo usuario
            </button>
        </div>
    </x-slot>

    @if(! is_null($maxUsers) && $currentUsers >= $maxUsers)
        <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-5 py-4 text-sm text-yellow-900">
            Tu plan permite maximo {{ $maxUsers }} usuario(s). Para agregar mas usuarios, cambia a un plan con mayor limite.
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-4">
        @foreach($roles as $roleValue => $roleLabel)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="text-sm font-semibold text-gray-900">{{ $roleLabel }}</div>
                <p class="mt-1 text-xs text-gray-500">{{ $roleDescriptions[$roleValue] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Nombre</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Email</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Rol</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600">Alta</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    @php
                        $role = $user->roles->first()?->name;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $roles[$role] ?? 'Sin rol' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button"
                                        onclick="openEditUser('{{ route('client.users.update', $user) }}', '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $role }}')"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                    Editar
                                </button>

                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('client.users.destroy', $user) }}"
                                          onsubmit="return confirm('Seguro que deseas eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Aun no hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

    <div id="createUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Nuevo usuario</h3>
                <button type="button"
                        onclick="document.getElementById('createUserModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('client.users.store') }}" class="p-6 space-y-4">
                @csrf
                @include('client.usuarios.partials.form', ['roles' => $roles, 'mode' => 'create'])
            </form>
        </div>
    </div>

    <div id="editUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Editar usuario</h3>
                <button type="button"
                        onclick="document.getElementById('editUserModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <form id="editUserForm" method="POST" action="#" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                @include('client.usuarios.partials.form', ['roles' => $roles, 'mode' => 'edit'])
            </form>
        </div>
    </div>

    <script>
        function openEditUser(action, name, email, role) {
            const form = document.getElementById('editUserForm');
            form.action = action;
            form.querySelector('[name="name"]').value = name;
            form.querySelector('[name="email"]').value = email;
            form.querySelector('[name="role"]').value = role;
            form.querySelector('[name="password"]').value = '';
            form.querySelector('[name="password_confirmation"]').value = '';
            document.getElementById('editUserModal').classList.remove('hidden');
        }
    </script>
</x-layouts.client>
