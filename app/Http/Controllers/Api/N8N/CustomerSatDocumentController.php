<?php

namespace App\Http\Controllers\Api\N8N;

use App\Http\Controllers\Api\N8N\Concerns\AuthorizesN8NCustomer;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SatComplianceOpinionRequest;
use App\Models\SatCsfRequest;
use App\Services\Sat\SatComplianceOpinionService;
use App\Services\Sat\SatCsfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerSatDocumentController extends Controller
{
    use AuthorizesN8NCustomer;

    public function latestCsf(Customer $customer): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $request = SatCsfRequest::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->first();

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'document' => $request ? $this->csfPayload($customer, $request) : null,
        ]);
    }

    public function requestCsf(Customer $customer, SatCsfService $service): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $request = $service->download($customer);

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'document' => $this->csfPayload($customer, $request),
        ], $request->isCompleted() ? 201 : 422);
    }

    public function downloadCsf(Customer $customer, SatCsfRequest $satCsfRequest): StreamedResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            abort($response->status(), $response->getData(true)['message'] ?? 'Unauthorized');
        }

        abort_unless($satCsfRequest->customer_id === $customer->id, 404);
        abort_unless($satCsfRequest->pdf_path && Storage::disk('local')->exists($satCsfRequest->pdf_path), 404);

        return Storage::disk('local')->download(
            $satCsfRequest->pdf_path,
            'Constancia-Situacion-Fiscal-' . $satCsfRequest->rfc . '.pdf'
        );
    }

    public function latestComplianceOpinion(Customer $customer): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $request = SatComplianceOpinionRequest::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->first();

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'document' => $request ? $this->compliancePayload($customer, $request) : null,
        ]);
    }

    public function requestComplianceOpinion(Customer $customer, SatComplianceOpinionService $service): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $request = $service->download($customer);

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'document' => $this->compliancePayload($customer, $request),
        ], $request->isCompleted() ? 201 : 422);
    }

    public function downloadComplianceOpinion(Customer $customer, SatComplianceOpinionRequest $satComplianceOpinionRequest): StreamedResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            abort($response->status(), $response->getData(true)['message'] ?? 'Unauthorized');
        }

        abort_unless($satComplianceOpinionRequest->customer_id === $customer->id, 404);
        abort_unless($satComplianceOpinionRequest->pdf_path && Storage::disk('local')->exists($satComplianceOpinionRequest->pdf_path), 404);

        return Storage::disk('local')->download(
            $satComplianceOpinionRequest->pdf_path,
            'Opinion-Cumplimiento-32D-' . $satComplianceOpinionRequest->rfc . '.pdf'
        );
    }

    private function customerPayload(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'rfc' => $customer->rfc,
            'razon_social' => $customer->razon_social,
        ];
    }

    private function csfPayload(Customer $customer, SatCsfRequest $request): array
    {
        return [
            'id' => $request->id,
            'type' => 'csf',
            'estado' => $request->estado,
            'rfc' => $request->rfc,
            'downloaded_at' => $request->downloaded_at?->toDateTimeString(),
            'has_pdf' => (bool) ($request->pdf_path && Storage::disk('local')->exists($request->pdf_path)),
            'download_url' => route('api.n8n.customers.documents.csf.download', [$customer, $request]),
            'error_message' => $request->error_message,
        ];
    }

    private function compliancePayload(Customer $customer, SatComplianceOpinionRequest $request): array
    {
        return [
            'id' => $request->id,
            'type' => 'opinion_32d',
            'estado' => $request->estado,
            'rfc' => $request->rfc,
            'downloaded_at' => $request->downloaded_at?->toDateTimeString(),
            'has_pdf' => (bool) ($request->pdf_path && Storage::disk('local')->exists($request->pdf_path)),
            'download_url' => route('api.n8n.customers.documents.compliance.download', [$customer, $request]),
            'error_message' => $request->error_message,
        ];
    }
}
