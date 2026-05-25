<?php

namespace App\Http\Controllers\Api\N8N;

use App\Http\Controllers\Api\N8N\Concerns\AuthorizesN8NCustomer;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SatCfdi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCfdiController extends Controller
{
    use AuthorizesN8NCustomer;

    public function index(Request $request, Customer $customer): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'tipo_descarga' => ['nullable', 'in:emitidas,recibidas'],
            'tipo_comprobante' => ['nullable', 'string', 'max:5'],
            'estado_sat' => ['nullable', 'in:vigente,cancelado'],
            'uuid' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = SatCfdi::query()
            ->where('customer_id', $customer->id)
            ->latest('fecha_emision');

        if (! empty($validated['month'])) {
            $start = Carbon::parse($validated['month'])->startOfMonth();
            $end = Carbon::parse($validated['month'])->endOfMonth();
            $query->whereBetween('fecha_emision', [$start, $end]);
        }

        if (! empty($validated['fecha_inicio'])) {
            $query->where('fecha_emision', '>=', Carbon::parse($validated['fecha_inicio'])->startOfDay());
        }

        if (! empty($validated['fecha_fin'])) {
            $query->where('fecha_emision', '<=', Carbon::parse($validated['fecha_fin'])->endOfDay());
        }

        foreach (['tipo_descarga', 'tipo_comprobante', 'estado_sat'] as $filter) {
            if (! empty($validated[$filter])) {
                $query->where($filter, $validated[$filter]);
            }
        }

        if (! empty($validated['uuid'])) {
            $query->where('uuid', 'like', '%' . $validated['uuid'] . '%');
        }

        if (! empty($validated['q'])) {
            $term = $validated['q'];
            $query->where(function ($query) use ($term) {
                $query->where('uuid', 'like', "%{$term}%")
                    ->orWhere('rfc_emisor', 'like', "%{$term}%")
                    ->orWhere('rfc_receptor', 'like', "%{$term}%")
                    ->orWhere('razon_social_emisor', 'like', "%{$term}%")
                    ->orWhere('razon_social_receptor', 'like', "%{$term}%")
                    ->orWhere('serie', 'like', "%{$term}%")
                    ->orWhere('folio', 'like', "%{$term}%");
            });
        }

        $perPage = (int) ($validated['per_page'] ?? 10);
        $cfdis = $query->paginate($perPage);

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'data' => $cfdis->getCollection()->map(fn (SatCfdi $cfdi) => $this->cfdiPayload($cfdi))->values(),
            'pagination' => [
                'current_page' => $cfdis->currentPage(),
                'per_page' => $cfdis->perPage(),
                'total' => $cfdis->total(),
                'last_page' => $cfdis->lastPage(),
            ],
        ]);
    }

    public function show(Customer $customer, string $uuid): JsonResponse
    {
        if ($response = $this->ensureCustomerCanUseN8N($customer)) {
            return $response;
        }

        $cfdi = SatCfdi::query()
            ->with(['conceptos', 'pagos'])
            ->where('customer_id', $customer->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'customer' => $this->customerPayload($customer),
            'cfdi' => $this->cfdiPayload($cfdi) + [
                'conceptos' => $cfdi->conceptos,
                'pagos' => $cfdi->pagos,
            ],
        ]);
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

    private function cfdiPayload(SatCfdi $cfdi): array
    {
        return [
            'id' => $cfdi->id,
            'uuid' => $cfdi->uuid,
            'serie' => $cfdi->serie,
            'folio' => $cfdi->folio,
            'tipo_descarga' => $cfdi->tipo_descarga,
            'tipo_comprobante' => $cfdi->tipo_comprobante,
            'estado_sat' => $cfdi->estado_sat,
            'fecha_emision' => $cfdi->fecha_emision?->toDateTimeString(),
            'rfc_emisor' => $cfdi->rfc_emisor,
            'razon_social_emisor' => $cfdi->razon_social_emisor,
            'rfc_receptor' => $cfdi->rfc_receptor,
            'razon_social_receptor' => $cfdi->razon_social_receptor,
            'metodo_pago' => $cfdi->metodo_pago,
            'forma_pago' => $cfdi->forma_pago,
            'moneda' => $cfdi->moneda,
            'subtotal' => (float) $cfdi->subtotal,
            'descuento' => (float) $cfdi->descuento,
            'total_impuestos_trasladados' => (float) $cfdi->total_impuestos_trasladados,
            'total_impuestos_retenidos' => (float) $cfdi->total_impuestos_retenidos,
            'total' => (float) $cfdi->total,
        ];
    }
}
