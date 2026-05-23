<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const TENANT_ROLES = [
        'tenant_admin' => 'Administrador',
        'tenant_contador' => 'Contador',
        'tenant_auxiliar' => 'Auxiliar',
        'tenant_lectura' => 'Solo lectura',
    ];

    private const ROLE_DESCRIPTIONS = [
        'tenant_admin' => 'Administra usuarios, clientes, planes, SAT, contabilidad y facturacion.',
        'tenant_contador' => 'Gestiona clientes, SAT y contabilidad.',
        'tenant_auxiliar' => 'Gestiona clientes y operaciones SAT.',
        'tenant_lectura' => 'Consulta informacion general del panel.',
    ];

    public function index()
    {
        $tenant = auth()->user()->tenant;
        $users = User::with('roles')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        return view('client.usuarios.index', [
            'users' => $users,
            'roles' => self::TENANT_ROLES,
            'roleDescriptions' => self::ROLE_DESCRIPTIONS,
            'tenant' => $tenant,
            'maxUsers' => $tenant->plan?->max_users,
            'currentUsers' => User::where('tenant_id', $tenant->id)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $maxUsers = $tenant->plan?->max_users;
        $currentUsers = User::where('tenant_id', $tenant->id)->count();

        if (! is_null($maxUsers) && $currentUsers >= $maxUsers) {
            return back()
                ->withInput()
                ->with('error', "Tu plan permite maximo {$maxUsers} usuario(s).");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(self::TENANT_ROLES))],
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('client.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTenantUser($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(self::TENANT_ROLES))],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($user->hasRole('tenant_admin') && $validated['role'] !== 'tenant_admin' && $this->isLastTenantAdmin($user)) {
            return back()->with('error', 'El tenant debe conservar al menos un administrador.');
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('client.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $this->authorizeTenantUser($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($user->hasRole('tenant_admin') && $this->isLastTenantAdmin($user)) {
            return back()->with('error', 'El tenant debe conservar al menos un administrador.');
        }

        $user->delete();

        return redirect()
            ->route('client.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    private function authorizeTenantUser(User $user): void
    {
        abort_if($user->tenant_id !== auth()->user()->tenant_id, 403);
    }

    private function isLastTenantAdmin(User $user): bool
    {
        return User::where('tenant_id', $user->tenant_id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'tenant_admin'))
            ->where('id', '<>', $user->id)
            ->doesntExist();
    }
}
