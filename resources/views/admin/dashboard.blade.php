{{-- resources/views/admin/dashboard.blade.php --}}
<x-layouts.admin>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard master</h1>
                <p class="text-sm text-gray-500 mt-1">Clientes SaaS, vencimientos y metodos de pago.</p>
            </div>
            <a href="{{ route('admin.tenants.index') }}"
               class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                Ver clientes
            </a>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $kpis = $dashboard['kpis'];
        $latestTenant = $dashboard['latest_tenant'];
        $maxPlanTenants = max(1, (int) $dashboard['plan_distribution']->max('tenants_count'));
    @endphp

    <style>
        .master-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .master-kpi-card {
            min-height: 132px;
            border-radius: 16px;
            padding: 18px;
            color: #fff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .master-kpi-label {
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .84;
        }

        .master-kpi-value {
            margin-top: 10px;
            font-size: 31px;
            line-height: 1.1;
            font-weight: 900;
        }

        .master-kpi-sub {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.45;
            opacity: .88;
        }

        .master-blue { background: linear-gradient(135deg, #2563eb, #0f172a); }
        .master-emerald { background: linear-gradient(135deg, #059669, #064e3b); }
        .master-rose { background: linear-gradient(135deg, #e11d48, #7f1d1d); }
        .master-amber { background: linear-gradient(135deg, #d97706, #7c2d12); }
        .master-cyan { background: linear-gradient(135deg, #0891b2, #164e63); }
        .master-violet { background: linear-gradient(135deg, #7c3aed, #312e81); }
        .master-slate { background: linear-gradient(135deg, #475569, #020617); }

        .master-panel {
            background: #fff;
            border: 1px solid #d9dee8;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .master-panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .master-panel-title {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #0f172a;
        }

        .master-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .master-list-row {
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .master-list-row:last-child {
            border-bottom: 0;
        }

        .master-bar {
            height: 9px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 8px;
        }

        .master-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: #0ea5e9;
        }

        @media (max-width: 1100px) {
            .master-kpi-grid,
            .master-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .master-kpi-grid,
            .master-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="master-kpi-grid">
        <div class="master-kpi-card master-blue">
            <div>
                <p class="master-kpi-label">Clientes SaaS</p>
                <p class="master-kpi-value">{{ number_format($kpis['total_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">
                Activos: {{ number_format($kpis['active_tenants']) }} · Vencidos: {{ number_format($kpis['expired_tenants']) }}
            </p>
        </div>

        <div class="master-kpi-card master-emerald">
            <div>
                <p class="master-kpi-label">Clientes activos</p>
                <p class="master-kpi-value">{{ number_format($kpis['active_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">Con acceso vigente a la plataforma</p>
        </div>

        <div class="master-kpi-card master-rose">
            <div>
                <p class="master-kpi-label">Clientes vencidos</p>
                <p class="master-kpi-value">{{ number_format($kpis['expired_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">Past due, cancelados o con periodo vencido</p>
        </div>

        <div class="master-kpi-card master-amber">
            <div>
                <p class="master-kpi-label">Por vencer</p>
                <p class="master-kpi-value">{{ number_format($kpis['expiring_soon_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">Renuevan en los proximos 7 dias</p>
        </div>

        <div class="master-kpi-card master-cyan">
            <div>
                <p class="master-kpi-label">Pago automatico</p>
                <p class="master-kpi-value">{{ number_format($kpis['automatic_payment_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">Clientes con suscripcion Stripe</p>
        </div>

        <div class="master-kpi-card master-violet">
            <div>
                <p class="master-kpi-label">Pago manual</p>
                <p class="master-kpi-value">{{ number_format($kpis['manual_payment_tenants']) }}</p>
            </div>
            <p class="master-kpi-sub">Sin suscripcion automatica activa</p>
        </div>

        <div class="master-kpi-card master-slate">
            <div>
                <p class="master-kpi-label">Ingreso estimado</p>
                <p class="master-kpi-value">{{ $money($kpis['monthly_revenue']) }}</p>
            </div>
            <p class="master-kpi-sub">Suma de planes de clientes activos</p>
        </div>

        <div class="master-kpi-card master-emerald">
            <div>
                <p class="master-kpi-label">Ultimo cliente</p>
                <p class="master-kpi-value text-2xl truncate">{{ $latestTenant?->name ?? 'Sin clientes' }}</p>
            </div>
            <p class="master-kpi-sub">
                {{ $latestTenant?->plan?->name ?? 'Sin plan' }}
                @if($latestTenant?->created_at)
                    · {{ $latestTenant->created_at->format('d/m/Y') }}
                @endif
            </p>
        </div>
    </div>

    <div class="master-grid-2">
        <div class="master-panel">
            <div class="master-panel-head">
                <h2 class="master-panel-title">Clientes por vencer</h2>
                <a href="{{ route('admin.tenants.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ver clientes</a>
            </div>
            <div>
                @forelse($dashboard['expiring_tenants'] as $tenant)
                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="master-list-row hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-gray-500">{{ $tenant->plan?->name ?? 'Sin plan' }}</p>
                        </div>
                        <span class="text-xs font-bold text-amber-700 whitespace-nowrap">
                            {{ $tenant->current_period_ends_at?->format('d/m/Y') }}
                        </span>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-emerald-600 font-semibold">No hay clientes por vencer esta semana.</div>
                @endforelse
            </div>
        </div>

        <div class="master-panel">
            <div class="master-panel-head">
                <h2 class="master-panel-title">Distribucion por plan</h2>
                <a href="{{ route('admin.planes.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ver planes</a>
            </div>
            <div>
                @forelse($dashboard['plan_distribution'] as $row)
                    <div class="master-list-row">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $row->plan_name }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($row->tenants_count) }} clientes</p>
                            <div class="master-bar">
                                <div class="master-bar-fill" style="width: {{ min(100, ((int) $row->tenants_count / $maxPlanTenants) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-400">Aun no hay clientes con plan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="master-panel">
        <div class="master-panel-head">
            <h2 class="master-panel-title">Clientes recientes</h2>
        </div>
        <div>
            @forelse($dashboard['recent_tenants'] as $tenant)
                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="master-list-row hover:bg-gray-50">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $tenant->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $tenant->billing_email ?? 'Sin email' }} · {{ $tenant->plan?->name ?? 'Sin plan' }}
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $tenant->created_at?->format('d/m/Y') }}</span>
                </a>
            @empty
                <div class="p-8 text-center text-sm text-gray-400">Aun no hay clientes registrados.</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
