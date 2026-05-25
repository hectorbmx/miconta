<?php

namespace App\Http\Controllers\Api\N8N\Concerns;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

trait AuthorizesN8NCustomer
{
    private function ensureCustomerCanUseN8N(Customer $customer): ?JsonResponse
    {
        $customer->loadMissing(['tenant.plan', 'activeSubscription']);

        if (! $customer->tenant || ! $customer->tenant->hasActiveSaasAccess()) {
            return $this->notAuthorizedForN8N();
        }

        if (! $customer->activeSubscription || ! $customer->activeSubscription->isActive()) {
            return $this->notAuthorizedForN8N();
        }

        return null;
    }

    private function notAuthorizedForN8N(): JsonResponse
    {
        return response()->json([
            'authorized' => false,
            'message' => 'Cliente no encontrado, celular no autorizado o plan inactivo.',
        ], 403);
    }
}
