<?php

namespace App\Http\Controllers\Client\Sat;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingThirdParty;
use App\Models\Customer;
use App\Models\SatCfdi;
use App\Services\Accounting\CfdiAccountingJournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SatCfdiController extends Controller
{
    /**
     * Lista CFDIs del tenant con filtros para el dashboard
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $selectedCustomer = null;

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::where('tenant_id', auth()->user()->tenant_id)
                ->find($request->customer_id);
        }

        $query = SatCfdi::with(['customer', 'journalEntries.journal'])
            ->whereHas('customer', fn($q) => $q->where('tenant_id', auth()->user()->tenant_id));

        // Filtros
        if ($request->filled('tipo_descarga')) {
            $query->where('tipo_descarga', $request->tipo_descarga);
        }

        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->tipo_comprobante);
        }

        if ($request->filled('estado_sat')) {
            $query->where('estado_sat', $request->estado_sat);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_emision', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha_emision', '<=', $request->fecha_fin . ' 23:59:59');
        }

        if ($request->filled('rfc')) {
            $query->where(function ($q) use ($request) {
                $q->where('rfc_emisor', 'like', '%' . $request->rfc . '%')
                  ->orWhere('rfc_receptor', 'like', '%' . $request->rfc . '%');
            });
        }

        if ($request->filled('uuid')) {
            $query->where('uuid', 'like', '%' . $request->uuid . '%');
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('uuid', 'like', '%' . $request->q . '%')
                    ->orWhere('rfc_emisor', 'like', '%' . $request->q . '%')
                    ->orWhere('rfc_receptor', 'like', '%' . $request->q . '%')
                    ->orWhere('razon_social_emisor', 'like', '%' . $request->q . '%')
                    ->orWhere('razon_social_receptor', 'like', '%' . $request->q . '%')
                    ->orWhere('serie', 'like', '%' . $request->q . '%')
                    ->orWhere('folio', 'like', '%' . $request->q . '%');
            });
        }

        // Totales para el dashboard
        $totales = (clone $query)
            ->selectRaw("
                SUM(CASE WHEN tipo_descarga = 'emitidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN tipo_descarga = 'recibidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as total_gastos,
                COUNT(CASE WHEN estado_sat = 'vigente' THEN 1 END) as total_vigentes,
                COUNT(CASE WHEN estado_sat = 'cancelado' THEN 1 END) as total_cancelados
            ")
            ->first();

        $stats = $this->buildStats($query, $selectedCustomer);

        $cfdis = $query->latest('fecha_emision')->paginate($perPage)->withQueryString();
        [$accountingAccountsByCustomer, $thirdPartiesByKey] = $this->accountingPromptData($cfdis->getCollection());

        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('razon_social')
            ->get();

        return view('client.sat.cfdis.index', compact(
            'cfdis',
            'totales',
            'customers',
            'perPage',
            'selectedCustomer',
            'stats',
            'accountingAccountsByCustomer',
            'thirdPartiesByKey'
        ));
    }

    /**
     * Detalle de un CFDI con conceptos y pagos
     */
    public function show(SatCfdi $cfdi)
    {
        $this->authorizeTenant($cfdi);

        $cfdi->load(['conceptos', 'pagos', 'customer', 'downloadRequest', 'journalEntries.journal']);

        return view('client.sat.cfdis.show', compact('cfdi'));
    }

    /**
     * Verifica que el CFDI pertenece al tenant del usuario autenticado
     */
    private function authorizeTenant(SatCfdi $cfdi): void
    {
        abort_unless(
            $cfdi->customer->tenant_id === auth()->user()->tenant_id,
            403
        );
    }
public function json(SatCfdi $cfdi)
{
    $this->authorizeTenant($cfdi);

    $cfdi->load(['conceptos', 'pagos', 'customer', 'downloadRequest', 'journalEntries.journal']);

    return response()->json($cfdi);
}

public function generateAccountingJournal(Request $request, SatCfdi $cfdi, CfdiAccountingJournalService $service)
{
    $this->authorizeTenant($cfdi);

    try {
        if ($request->boolean('save_third_party') && $request->filled('default_account_id')) {
            $this->assignThirdPartyAccount($cfdi, (int) $request->input('default_account_id'));
        }

        $journal = $service->createDraftFromCfdi($cfdi);
    } catch (ValidationException $exception) {
        return back()
            ->withInput()
            ->with('error', collect($exception->errors())->flatten()->first() ?: 'No se pudo generar la poliza.');
    }

    return redirect()
        ->route('client.clientes.show', $cfdi->customer)
        ->with('success', "Poliza {$journal->number} generada en borrador desde el XML.");
}

private function accountingPromptData($cfdis): array
{
    $customerIds = $cfdis->pluck('customer_id')->unique()->values();
    $rfcs = $cfdis
        ->flatMap(fn ($cfdi) => [strtoupper((string) $cfdi->rfc_emisor), strtoupper((string) $cfdi->rfc_receptor)])
        ->filter()
        ->unique()
        ->values();

    $accounts = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
        ->whereIn('customer_id', $customerIds)
        ->where('is_active', true)
        ->orderBy('code')
        ->get()
        ->groupBy('customer_id');

    $thirdParties = AccountingThirdParty::where('tenant_id', auth()->user()->tenant_id)
        ->whereIn('customer_id', $customerIds)
        ->whereIn('rfc', $rfcs)
        ->get()
        ->keyBy(fn ($thirdParty) => $thirdParty->customer_id . '|' . strtoupper($thirdParty->rfc));

    return [$accounts, $thirdParties];
}

private function assignThirdPartyAccount(SatCfdi $cfdi, int $accountId): void
{
    $cfdi->loadMissing('customer');

    $account = AccountingAccount::where('tenant_id', $cfdi->customer->tenant_id)
        ->where('customer_id', $cfdi->customer_id)
        ->where('is_active', true)
        ->findOrFail($accountId);

    $isIssued = strtoupper((string) $cfdi->rfc_emisor) === strtoupper((string) $cfdi->customer->rfc)
        || $cfdi->tipo_descarga === 'emitidas';

    $rfc = strtoupper((string) ($isIssued ? $cfdi->rfc_receptor : $cfdi->rfc_emisor));
    $name = $isIssued ? $cfdi->razon_social_receptor : $cfdi->razon_social_emisor;
    $type = $isIssued ? 'client' : 'supplier';

    AccountingThirdParty::updateOrCreate(
        [
            'tenant_id' => $cfdi->customer->tenant_id,
            'customer_id' => $cfdi->customer_id,
            'rfc' => $rfc,
        ],
        [
            'name' => $name,
            'type' => $type,
            'default_account_id' => $account->id,
            'is_active' => true,
        ]
    );
}

private function buildStats($filteredQuery, ?Customer $selectedCustomer): array
{
    $trendRows = (clone $filteredQuery)
        ->selectRaw("
            DATE_FORMAT(fecha_emision, '%Y-%m') as month,
            SUM(CASE WHEN tipo_descarga = 'emitidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as ingresos,
            SUM(CASE WHEN tipo_descarga = 'recibidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as gastos
        ")
        ->whereNotNull('fecha_emision')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get()
        ->sortBy('month')
        ->values();

    $maxTrend = max(1, (float) $trendRows->max(fn($row) => max((float) $row->ingresos, (float) $row->gastos)));

    $status = (clone $filteredQuery)
        ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado_sat = 'vigente' THEN 1 ELSE 0 END) as vigentes,
            SUM(CASE WHEN estado_sat = 'cancelado' THEN 1 ELSE 0 END) as cancelados
        ")
        ->first();

    return [
        'trend' => $trendRows->map(fn($row) => [
            'month' => $row->month,
            'label' => substr($row->month, 5, 2) . '/' . substr($row->month, 2, 2),
            'ingresos' => round((float) $row->ingresos, 2),
            'gastos' => round((float) $row->gastos, 2),
            'ingresos_percent' => round(((float) $row->ingresos / $maxTrend) * 100, 1),
            'gastos_percent' => round(((float) $row->gastos / $maxTrend) * 100, 1),
        ])->all(),
        'status' => [
            'total' => (int) ($status->total ?? 0),
            'vigentes' => (int) ($status->vigentes ?? 0),
            'cancelados' => (int) ($status->cancelados ?? 0),
            'vigentes_percent' => ($status->total ?? 0) > 0
                ? round(((int) $status->vigentes / (int) $status->total) * 100, 1)
                : 0,
        ],
        'top_clients' => $selectedCustomer ? $this->topCounterparties($filteredQuery, $selectedCustomer, 'clients') : collect(),
        'top_suppliers' => $selectedCustomer ? $this->topCounterparties($filteredQuery, $selectedCustomer, 'suppliers') : collect(),
    ];
}

private function topCounterparties($filteredQuery, Customer $customer, string $type)
{
    $isClients = $type === 'clients';

    return (clone $filteredQuery)
        ->where($isClients ? 'rfc_emisor' : 'rfc_receptor', $customer->rfc)
        ->selectRaw(($isClients ? 'rfc_receptor' : 'rfc_emisor') . ' as rfc')
        ->selectRaw(($isClients ? 'razon_social_receptor' : 'razon_social_emisor') . ' as nombre')
        ->selectRaw('COUNT(*) as cfdis')
        ->selectRaw('COALESCE(SUM(total), 0) as total')
        ->groupBy('rfc', 'nombre')
        ->orderByDesc(DB::raw('COALESCE(SUM(total), 0)'))
        ->limit(10)
        ->get();
}
}
