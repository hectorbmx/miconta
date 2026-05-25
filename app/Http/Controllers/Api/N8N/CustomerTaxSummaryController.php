<?php

namespace App\Http\Controllers\Api\N8N;

use App\Http\Controllers\Api\N8N\Concerns\AuthorizesN8NCustomer;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Sat\MonthlyTaxSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerTaxSummaryController extends Controller
{
    use AuthorizesN8NCustomer;

    public function __invoke(Request $request, Customer $customer, MonthlyTaxSummaryService $service): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return response()->json($service->forCustomer($customer, $validated['month']));
    }
}
