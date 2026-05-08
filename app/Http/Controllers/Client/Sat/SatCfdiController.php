<?php

namespace App\Http\Controllers\Client\Sat;

use App\Http\Controllers\Controller;
use App\Models\SatCfdi;
use Illuminate\Http\Request;

class SatCfdiController extends Controller
{
    /**
     * Lista CFDIs del tenant con filtros para el dashboard
     */
    public function index(Request $request)
    {
        $query = SatCfdi::with('customer')
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
                $q->where('rfc_emisor', $request->rfc)
                  ->orWhere('rfc_receptor', $request->rfc);
            });
        }

        $cfdis = $query->latest('fecha_emision')->paginate(50)->withQueryString();

        // Totales para el dashboard
        $totales = SatCfdi::whereHas('customer', fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))
            ->selectRaw("
                SUM(CASE WHEN tipo_descarga = 'emitidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN tipo_descarga = 'recibidas' AND estado_sat = 'vigente' THEN total ELSE 0 END) as total_gastos,
                COUNT(CASE WHEN estado_sat = 'vigente' THEN 1 END) as total_vigentes,
                COUNT(CASE WHEN estado_sat = 'cancelado' THEN 1 END) as total_cancelados
            ")
            ->first();

        return view('client.sat.cfdis.index', compact('cfdis', 'totales'));
    }

    /**
     * Detalle de un CFDI con conceptos y pagos
     */
    public function show(SatCfdi $cfdi)
    {
        $this->authorizeTenant($cfdi);

        $cfdi->load(['conceptos', 'pagos', 'customer', 'downloadRequest']);

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

    $cfdi->load(['conceptos', 'pagos', 'customer', 'downloadRequest']);

    return response()->json($cfdi);
}
}