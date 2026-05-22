<?php

namespace App\Http\Controllers\Client\Sat;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingThirdParty;
use App\Models\Customer;
use App\Models\SatDownloadRequest;
use App\Services\Sat\SatDescargaMasivaService;
use Illuminate\Http\Request;


class SatDownloadRequestController extends Controller
{
    public function __construct(
        private SatDescargaMasivaService $satService
    ) {}

    /**
     * Lista todas las solicitudes del tenant actual
     */
    public function index()
    {
        $downloadRequests = SatDownloadRequest::with('customer')
            ->whereHas('customer', fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))
            ->latest()
            ->paginate(20);

        return view('client.sat.download-requests.index', compact('downloadRequests'));
    }

    /**
     * Formulario para nueva solicitud
     */
    public function create()
    {
        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->whereNotNull('certificate_path')
            ->whereNotNull('private_key_path')
            ->get();

        return view('client.sat.download-requests.create', compact('customers'));
    }

    /**
     * Guarda la solicitud y lanza el query al SAT
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'tipo_descarga'  => 'required|in:emitidas,recibidas',
            'tipo_solicitud' => 'required|in:cfdi,metadata',
        ]);

        // Verificar que el customer pertenece al tenant
        $customer = Customer::where('id', $validated['customer_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $downloadRequest = SatDownloadRequest::create([
            'customer_id'    => $customer->id,
            'user_id'        => auth()->id(),
            'rfc_solicitante'=> $customer->rfc,
            'fecha_inicio'   => $validated['fecha_inicio'],
            'fecha_fin'      => $validated['fecha_fin'],
            'tipo_descarga'  => $validated['tipo_descarga'],
            'tipo_solicitud' => $validated['tipo_solicitud'],
            'estado'         => 'pending',
        ]);

        // Paso 1 — Query al SAT
        $this->satService->query($downloadRequest);

        return redirect()
            ->route('client.sat.download-requests.show', $downloadRequest)
            ->with('success', 'Solicitud enviada al SAT correctamente.');
    }

    /**
     * Detalle de una solicitud
     */
   public function show(SatDownloadRequest $downloadRequest, Request $request)
{
    $this->authorizeTenant($downloadRequest);

    // Cargamos la solicitud y filtramos los CFDIs relacionados
    $downloadRequest->load(['customer', 'cfdis' => function ($query) use ($request) {
        $query->with('journalEntries.journal');

        
        // Filtro por RFC (Emisor o Receptor)
        if ($request->filled('rfc')) {
            $query->where(function ($q) use ($request) {
                $q->where('rfc_emisor', 'like', '%' . $request->rfc . '%')
                  ->orWhere('rfc_receptor', 'like', '%' . $request->rfc . '%');
            });
        }

        // Filtro por Rango de Fechas
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_fin);
        }

        // Filtro por Tipo de Comprobante (Ingreso, Egreso, Nomina)
        if ($request->filled('tipo')) {
            $query->where('tipo_comprobante', $request->tipo);
        }

        return $query->latest('fecha_emision');
    }]);

    [$accountingAccountsByCustomer, $thirdPartiesByKey] = $this->accountingPromptData($downloadRequest->cfdis);

    return view('client.sat.download-requests.show', compact('downloadRequest', 'accountingAccountsByCustomer', 'thirdPartiesByKey'));
}

    /**
     * Ejecuta verify + download sobre una solicitud existente
     */
    public function process(SatDownloadRequest $downloadRequest)
    {
        
        $this->authorizeTenant($downloadRequest);

        if ($downloadRequest->estado === 'verifying') {
            $ready = $this->satService->verify($downloadRequest);

            if ($ready) {
                $this->satService->download($downloadRequest);
            }
        } elseif ($downloadRequest->estado === 'downloading') {
            $this->satService->download($downloadRequest);
        }

        return redirect()
            ->route('client.sat.download-requests.show', $downloadRequest)
            ->with('success', 'Proceso ejecutado correctamente.');
    }

    /**
     * Verifica que la solicitud pertenece al tenant del usuario autenticado
     */
    private function authorizeTenant(SatDownloadRequest $downloadRequest): void
    {
        abort_unless(
            $downloadRequest->customer->tenant_id === auth()->user()->tenant_id,
            403
        );
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
}
