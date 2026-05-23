<?php

namespace App\Http\Controllers\Client\Sat;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SatComplianceOpinionRequest;
use App\Services\Sat\SatComplianceOpinionService;
use Illuminate\Support\Facades\Storage;

class SatComplianceOpinionRequestController extends Controller
{
    public function __construct(
        protected SatComplianceOpinionService $satComplianceOpinionService
    ) {
    }

    public function index(Customer $customer)
    {
        $this->authorizeTenant($customer);

        $requests = SatComplianceOpinionRequest::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('client.sat.compliance-opinions.index', compact('customer', 'requests'));
    }

    public function store(Customer $customer)
    {
        $this->authorizeTenant($customer);

        $request = $this->satComplianceOpinionService->download($customer);

        if ($request->estado === 'completed') {
            return back()->with('success', 'Opinión 32-D descargada correctamente.');
        }

        return back()->with(
            'error',
            $request->error_message ?? 'No fue posible descargar la opinión 32-D.'
        );
    }

    public function downloadPdf(Customer $customer, SatComplianceOpinionRequest $satComplianceOpinionRequest)
    {
        $this->authorizeTenant($customer);

        abort_unless($satComplianceOpinionRequest->customer_id === $customer->id, 404);

        abort_unless(
            $satComplianceOpinionRequest->pdf_path
                && Storage::disk('local')->exists($satComplianceOpinionRequest->pdf_path),
            404
        );

        return Storage::disk('local')->download(
            $satComplianceOpinionRequest->pdf_path,
            'Opinion-Cumplimiento-32D-' . $satComplianceOpinionRequest->rfc . '.pdf'
        );
    }

    private function authorizeTenant(Customer $customer): void
    {
        abort_unless($customer->tenant_id === auth()->user()->tenant_id, 403);
    }
}
