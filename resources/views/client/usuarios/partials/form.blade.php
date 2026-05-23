<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
    <input type="text"
           name="name"
           required
           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
    <input type="email"
           name="email"
           required
           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
    <select name="role"
            required
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        @foreach($roles as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Password {{ $mode === 'edit' ? '(opcional)' : '' }}
        </label>
        <input type="password"
               name="password"
               @if($mode === 'create') required @endif
               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar password</label>
        <input type="password"
               name="password_confirmation"
               @if($mode === 'create') required @endif
               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>
</div>

<div class="flex items-center justify-end gap-3 pt-4">
    <button type="button"
            onclick="document.getElementById('{{ $mode === 'edit' ? 'editUserModal' : 'createUserModal' }}').classList.add('hidden')"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
        Cancelar
    </button>

    <button type="submit"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">
        Guardar
    </button>
</div>
