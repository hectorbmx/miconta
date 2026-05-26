<x-layouts.client>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard del tenant</h1>
                <p class="text-sm text-gray-500 mt-1">Clientes, planes, cobros y actividad SAT.</p>
            </div>
            <a href="{{ route('client.clientes.index') }}"
               class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800">
                Ver clientes
            </a>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => '$' . number_format((float) $value, 2);
        $kpis = $dashboard['kpis'];
        $maxPlanRevenue = max(1, (float) $dashboard['plan_distribution']->max('revenue'));
        $maxXml = max(1, (int) $dashboard['cfdi_by_customer']->max('xml_count'));
    @endphp

    <style>
        .tenant-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .tenant-kpi-card {
            min-height: 124px;
            border-radius: 16px;
            padding: 18px;
            color: #fff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .tenant-kpi-label {
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .82;
        }

        .tenant-kpi-value {
            margin-top: 10px;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 900;
        }

        .tenant-kpi-sub {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.45;
            opacity: .86;
        }

        .tenant-blue { background: linear-gradient(135deg, #2563eb, #0f172a); }
        .tenant-emerald { background: linear-gradient(135deg, #059669, #064e3b); }
        .tenant-violet { background: linear-gradient(135deg, #7c3aed, #312e81); }
        .tenant-amber { background: linear-gradient(135deg, #d97706, #7c2d12); }
        .tenant-cyan { background: linear-gradient(135deg, #0891b2, #164e63); }
        .tenant-slate { background: linear-gradient(135deg, #475569, #020617); }

        .tenant-panel {
            background: #fff;
            border: 1px solid #d9dee8;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .tenant-panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .tenant-panel-title {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #0f172a;
        }

        .tenant-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .tenant-list-row {
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .tenant-list-row:last-child {
            border-bottom: 0;
        }

        .tenant-bar {
            height: 9px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 8px;
        }

        .tenant-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: #0ea5e9;
        }

        @media (max-width: 1100px) {
            .tenant-kpi-grid,
            .tenant-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .tenant-kpi-grid,
            .tenant-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if(! $dashboard['stripe_ready'])
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <strong>Configura Stripe</strong>
                    <p class="text-sm">Conecta tu cuenta para cobrar honorarios a tus clientes desde la plataforma.</p>
                </div>
                <a href="{{ route('client.configuracion.index') }}"
                   class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-semibold hover:bg-yellow-700">
                    Configurar Stripe
                </a>
            </div>
        </div>
    @endif

    <div class="tenant-kpi-grid">
        <div class="tenant-kpi-card tenant-blue">
            <div>
                <p class="tenant-kpi-label">Clientes totales</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['customers_total']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Activos: {{ number_format($kpis['customers_active']) }} · No activos: {{ number_format($kpis['customers_inactive']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-emerald">
            <div>
                <p class="tenant-kpi-label">Listos para SAT</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['fiel_ready']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Pendientes FIEL: {{ number_format($kpis['fiel_pending']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-violet">
            <div>
                <p class="tenant-kpi-label">Planes creados</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['plans_total']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Activos: {{ number_format($kpis['plans_active']) }} · Inactivos: {{ number_format($kpis['plans_inactive']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-amber">
            <div>
                <p class="tenant-kpi-label">Planes Stripe</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['stripe_plans']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Cobro auto: {{ number_format($kpis['stripe_plans']) }} · Manuales: {{ number_format($kpis['manual_plans']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-cyan">
            <div>
                <p class="tenant-kpi-label">Tarjeta de credito</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['subscriptions_with_card']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Manual/sin tarjeta: {{ number_format($kpis['subscriptions_manual']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-slate">
            <div>
                <p class="tenant-kpi-label">Ingreso estimado</p>
                <p class="tenant-kpi-value">{{ $money($kpis['monthly_revenue']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                Suscripciones activas: {{ number_format($kpis['subscriptions_active']) }}
            </p>
        </div>

        <div class="tenant-kpi-card tenant-blue">
            <div>
                <p class="tenant-kpi-label">XML descargados</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['total_xml']) }}</p>
            </div>
            <p class="tenant-kpi-sub">Documentos SAT del tenant</p>
        </div>

        <div class="tenant-kpi-card tenant-emerald">
            <div>
                <p class="tenant-kpi-label">Suscripciones</p>
                <p class="tenant-kpi-value">{{ number_format($kpis['subscriptions_active']) }}</p>
            </div>
            <p class="tenant-kpi-sub">
                No activas: {{ number_format($kpis['subscriptions_inactive']) }}
            </p>
        </div>
    </div>

    <div class="tenant-grid-2">
        <div class="tenant-panel">
            <div class="tenant-panel-head">
                <h2 class="tenant-panel-title">Suscripciones por plan</h2>
                <a href="{{ route('client.customer-plans.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ver planes</a>
            </div>
            <div>
                @forelse($dashboard['plan_distribution'] as $row)
                    <div class="tenant-list-row">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $row->plan->name ?? 'Plan eliminado' }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($row->subscriptions) }} suscripciones · {{ $money($row->revenue) }}</p>
                            <div class="tenant-bar">
                                <div class="tenant-bar-fill" style="width: {{ min(100, ((float) $row->revenue / $maxPlanRevenue) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-400">Aun no hay suscripciones activas.</div>
                @endforelse
            </div>
        </div>

        <div class="tenant-panel">
            <div class="tenant-panel-head">
                <h2 class="tenant-panel-title">Clientes con mas XML</h2>
                <a href="{{ route('client.sat.cfdis.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Ver XML</a>
            </div>
            <div>
                @forelse($dashboard['cfdi_by_customer'] as $row)
                    <div class="tenant-list-row">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $row->razon_social }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $row->rfc }} · {{ number_format($row->xml_count) }} XML</p>
                            <div class="tenant-bar">
                                <div class="tenant-bar-fill" style="width: {{ min(100, ((int) $row->xml_count / $maxXml) * 100) }}%"></div>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ $money($row->total) }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-400">Aun no hay XML descargados.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="tenant-grid-2">
        <div class="tenant-panel">
            <div class="tenant-panel-head">
                <h2 class="tenant-panel-title">Clientes recientes</h2>
            </div>
            <div>
                @forelse($dashboard['recent_customers'] as $customer)
                    <a href="{{ route('client.clientes.show', $customer) }}" class="tenant-list-row hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $customer->razon_social }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $customer->rfc }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $customer->created_at?->format('d/m/Y') }}</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-gray-400">Aun no hay clientes.</div>
                @endforelse
            </div>
        </div>

        <div class="tenant-panel">
            <div class="tenant-panel-head">
                <h2 class="tenant-panel-title">Pendientes FIEL</h2>
            </div>
            <div>
                @forelse($dashboard['customers_pending_fiel'] as $customer)
                    <a href="{{ route('client.clientes.edit', $customer) }}" class="tenant-list-row hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $customer->razon_social }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $customer->rfc }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Configurar</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-emerald-600 font-semibold">Todos los clientes tienen FIEL cargada.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.client>
