<?php

namespace App\Http\Controllers\Client\Sat;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SatCsfRequest;
use App\Services\Sat\SatCsfService;
use Illuminate\Support\Facades\Storage;

class SatCsfRequestController extends Controller
{
    public function __construct(
        protected SatCsfService $satCsfService
    ) {
    }

    /**
     * Listado de constancias del cliente
     */
    public function index(Customer $customer)
    {
        $this->authorizeTenant($customer);

        $requests = SatCsfRequest::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('client.sat.csf.index', compact(
            'customer',
            'requests'
        ));
    }

    /**
     * Descargar nueva constancia
     */
 /**
 * Descargar nueva constancia
 */
public function store(Customer $customer)
{
    $this->authorizeTenant($customer);

    $request = $this->satCsfService->download($customer);

    if ($request->estado === 'completed') {

        return back()->with(
            'success',
            'Constancia descargada correctamente.'
        );
    }

    return back()->with(
        'error',
        $request->error_message ?? 'No fue posible descargar la constancia.'
    );
}

    /**
     * Ver detalle de descarga
     */
    public function show(Customer $customer, SatCsfRequest $satCsfRequest)
    {
        $this->authorizeTenant($customer);

        abort_unless(
            $satCsfRequest->customer_id === $customer->id,
            404
        );

        return view('client.sat.csf.show', compact(
            'customer',
            'satCsfRequest'
        ));
    }

    /**
     * Descargar PDF
     */
    public function downloadPdf(Customer $customer, SatCsfRequest $satCsfRequest)
    {
        $this->authorizeTenant($customer);

        abort_unless(
            $satCsfRequest->customer_id === $customer->id,
            404
        );

        abort_unless(
            $satCsfRequest->pdf_path
            && Storage::disk('local')->exists($satCsfRequest->pdf_path),
            404
        );

        return Storage::disk('local')->download(
            $satCsfRequest->pdf_path,
            'Constancia-Situacion-Fiscal-' . $satCsfRequest->rfc . '.pdf'
        );
    }

    /**
     * Seguridad tenant
     */
    private function authorizeTenant(Customer $customer): void
    {
        abort_unless(
            $customer->tenant_id === auth()->user()->tenant_id,
            403
        );
    }
}