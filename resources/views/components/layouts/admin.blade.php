<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900">

<div x-data="{ sidebarOpen: true }" class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside
        class="bg-slate-900 border-r border-slate-800 text-white min-h-screen fixed left-0 top-0 z-40 transition-all duration-300 flex flex-col"
        :class="sidebarOpen ? 'w-64' : 'w-20'"
    >
        {{-- LOGO --}}
        <div class="h-16 flex items-center justify-center border-b border-slate-800">
            <span class="text-xl font-bold text-white" x-show="sidebarOpen">
                Panel Admin
            </span>

            <span class="text-xl font-bold text-white" x-show="!sidebarOpen">
                PA
            </span>
        </div>

        {{-- NAV --}}
        <nav class="p-4 space-y-2 flex-1">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs('admin.dashboard')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🏠</span>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>

            <a href="{{ route('admin.tenants.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs('admin.tenants.*')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>👥</span>
                <span x-show="sidebarOpen">Clientes</span>
            </a>

            <a href="{{ route('admin.planes.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs('admin.planes.*')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>💳</span>
                <span x-show="sidebarOpen">Planes</span>
            </a>

            <a href="{{ route('admin.configuracion.index') }}"
               class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs('admin.configuracion.*')
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>⚙️</span>
                <span x-show="sidebarOpen">Configuración</span>
            </a>

        </nav>

        {{-- LOGOUT --}}
        <div class="p-4 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:bg-red-600 hover:text-white">
                    <span>🚪</span>
                    <span x-show="sidebarOpen">Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO --}}
    <div class="flex-1 transition-all duration-300"
         :class="sidebarOpen ? 'ml-64' : 'ml-20'">

        {{-- TOPBAR --}}
        <nav class="bg-slate-900 border-b border-slate-800 shadow-sm h-16 flex items-center justify-between px-8">

            <button type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">
                ☰
            </button>

            <div class="flex items-center">
                <span class="text-sm text-slate-300">
                    {{ Auth::user()->name }}
                </span>
            </div>
        </nav>

        {{-- Header opcional --}}
        @isset($header)
            <header class="bg-white shadow">
                <div class="py-6 px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- Contenido --}}
        <main class="p-8">
            {{ $slot }}
        </main>

    </div>

</div>

@if (session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-5 right-5 z-50 bg-green-100 border border-green-300 text-green-800 px-5 py-3 rounded-xl shadow">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-5 right-5 z-50 bg-red-100 border border-red-300 text-red-800 px-5 py-3 rounded-xl shadow">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div x-data="{ show: true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-5 right-5 z-50 bg-red-100 border border-red-300 text-red-800 px-5 py-3 rounded-xl shadow max-w-md">
        <div class="font-semibold mb-1">Revisa los datos</div>
        <ul class="text-sm list-disc pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

</body>
</html>
