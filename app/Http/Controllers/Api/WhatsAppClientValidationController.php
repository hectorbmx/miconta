<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WhatsAppClientValidationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'rfc' => ['required', 'string', 'min:12', 'max:13'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $rfc = strtoupper(trim($validated['rfc']));

        if ($phone === '') {
            throw ValidationException::withMessages([
                'phone' => 'El celular no tiene un formato valido.',
            ]);
        }

        $customer = Customer::query()
            ->with(['tenant.plan', 'activeSubscription'])
            ->whereRaw('UPPER(rfc) = ?', [$rfc])
            ->get()
            ->first(function (Customer $customer) use ($phone) {
                return $this->normalizePhone((string) $customer->phone) === $phone;
            });

        if (! $customer || ! $customer->tenant || ! $customer->tenant->hasActiveSaasAccess()) {
            return $this->notAuthorized();
        }

        $subscription = $customer->activeSubscription;

        if (! $subscription || ! $subscription->isActive()) {
            return $this->notAuthorized();
        }

        return response()->json([
            'authorized' => true,
            'tenant_id' => $customer->tenant_id,
            'client_id' => $customer->id,
            'rfc' => $customer->rfc,
            'razon_social' => $customer->razon_social,
            'allowed_actions' => [
                'cfdi_summary',
                'invoice_status',
                'csf_request',
                'opinion_32d_request',
            ],
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function notAuthorized(): JsonResponse
    {
        return response()->json([
            'authorized' => false,
            'message' => 'Cliente no encontrado, celular no autorizado o plan inactivo.',
        ], 403);
    }
}
