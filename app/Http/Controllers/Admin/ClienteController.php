<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::query()
            ->latest()
            ->paginate(10);

        return view('admin.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('admin.clientes.create');
    }

    public function store(Request $request)
    {
        // Pendiente: validación y guardado
    }

    public function edit(User $cliente)
    {
        return view('admin.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, User $cliente)
    {
        // Pendiente: validación y actualización
    }

    public function destroy(User $cliente)
    {
        // Por ahora inactivaremos después
    }
}