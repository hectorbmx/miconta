<?php

namespace App\Services\Sat;

use App\Models\Customer;
use App\Models\SatCfdi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyTaxSummaryService
{
    public function forTenant(int $tenantId, string|Carbon $month): array
    {
        [$start, $end] = $this->periodFromMonth($month);

        $customers = Customer::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('razon_social')
            ->get();

        $customerSummaries = $customers
            ->map(fn (Customer $customer) => $this->forCustomer($customer, $start))
            ->values();

        return [
            'period' => $this->periodPayload($start, $end),
            'tenant_id' => $tenantId,
            'customers_count' => $customers->count(),
            'customers' => $customerSummaries->all(),
            'totals' => $this->sumTenantTotals($customerSummaries),
        ];
    }

    public function forCustomer(Customer|int $customer, string|Carbon $month): array
    {
        $customer = $customer instanceof Customer
            ? $customer
            : Customer::query()->findOrFail($customer);

        [$start, $end] = $this->periodFromMonth($month);

        $issuedIncome = $this->cfdiTotals($customer->id, $start, $end, 'emitidas', 'I');
        $issuedCreditNotes = $this->cfdiTotals($customer->id, $start, $end, 'emitidas', 'E');
        $receivedExpenses = $this->cfdiTotals($customer->id, $start, $end, 'recibidas', 'I');
        $receivedCreditNotes = $this->cfdiTotals($customer->id, $start, $end, 'recibidas', 'E');
        $retentions = $this->retentionTotals($customer->id, $start, $end);
        $alerts = $this->alerts($customer->id, $start, $end);

        $netIssuedIva = $issuedIncome['iva_trasladado'] - $issuedCreditNotes['iva_trasladado'];
        $netCreditableIva = $receivedExpenses['iva_trasladado'] - $receivedCreditNotes['iva_trasladado'];
        $estimatedMonthlyIva = $netIssuedIva - $netCreditableIva;
        $estimatedWithheldTaxes = $retentions['iva_retenido_recibidas'] + $retentions['isr_retenido_recibidas'];

        return [
            'period' => $this->periodPayload($start, $end),
            'customer' => [
                'id' => $customer->id,
                'rfc' => $customer->rfc,
                'razon_social' => $customer->razon_social,
            ],
            'cfdis_count' => $this->baseCfdiQuery($customer->id, $start, $end)->count(),
            'issued' => [
                'income' => $issuedIncome,
                'credit_notes' => $issuedCreditNotes,
                'net_income_total' => $this->money($issuedIncome['total'] - $issuedCreditNotes['total']),
                'net_iva_trasladado' => $this->money($netIssuedIva),
            ],
            'received' => [
                'expenses' => $receivedExpenses,
                'credit_notes' => $receivedCreditNotes,
                'net_expense_total' => $this->money($receivedExpenses['total'] - $receivedCreditNotes['total']),
                'net_iva_acreditable' => $this->money($netCreditableIva),
            ],
            'retentions' => $retentions,
            'estimated' => [
                'iva_periodo' => $this->money($estimatedMonthlyIva),
                'retenciones_a_pagar' => $this->money($estimatedWithheldTaxes),
                'total_estimado_a_pagar' => $this->money($estimatedMonthlyIva + $estimatedWithheldTaxes),
            ],
            'alerts' => $alerts,
        ];
    }

    private function cfdiTotals(
        int $customerId,
        Carbon $start,
        Carbon $end,
        string $tipoDescarga,
        string $tipoComprobante
    ): array {
        $row = $this->baseCfdiQuery($customerId, $start, $end)
            ->where('tipo_descarga', $tipoDescarga)
            ->where('tipo_comprobante', $tipoComprobante)
            ->selectRaw('
                COUNT(*) as cfdis,
                COALESCE(SUM(subtotal), 0) as subtotal,
                COALESCE(SUM(descuento), 0) as descuento,
                COALESCE(SUM(total), 0) as total,
                COALESCE(SUM(total_impuestos_trasladados), 0) as iva_trasladado,
                COALESCE(SUM(total_impuestos_retenidos), 0) as impuestos_retenidos
            ')
            ->first();

        return [
            'cfdis' => (int) $row->cfdis,
            'subtotal' => $this->money($row->subtotal),
            'descuento' => $this->money($row->descuento),
            'total' => $this->money($row->total),
            'iva_trasladado' => $this->money($row->iva_trasladado),
            'impuestos_retenidos' => $this->money($row->impuestos_retenidos),
        ];
    }

    private function retentionTotals(int $customerId, Carbon $start, Carbon $end): array
    {
        $row = DB::table('sat_cfdi_conceptos')
            ->join('sat_cfdis', 'sat_cfdis.id', '=', 'sat_cfdi_conceptos.sat_cfdi_id')
            ->where('sat_cfdis.customer_id', $customerId)
            ->where('sat_cfdis.estado_sat', 'vigente')
            ->whereBetween('sat_cfdis.fecha_emision', [$start, $end])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN sat_cfdis.tipo_descarga = 'emitidas' THEN sat_cfdi_conceptos.importe_iva_retenido ELSE 0 END), 0) as iva_retenido_emitidas,
                COALESCE(SUM(CASE WHEN sat_cfdis.tipo_descarga = 'emitidas' THEN sat_cfdi_conceptos.importe_isr_retenido ELSE 0 END), 0) as isr_retenido_emitidas,
                COALESCE(SUM(CASE WHEN sat_cfdis.tipo_descarga = 'recibidas' THEN sat_cfdi_conceptos.importe_iva_retenido ELSE 0 END), 0) as iva_retenido_recibidas,
                COALESCE(SUM(CASE WHEN sat_cfdis.tipo_descarga = 'recibidas' THEN sat_cfdi_conceptos.importe_isr_retenido ELSE 0 END), 0) as isr_retenido_recibidas
            ")
            ->first();

        return [
            'iva_retenido_emitidas' => $this->money($row->iva_retenido_emitidas),
            'isr_retenido_emitidas' => $this->money($row->isr_retenido_emitidas),
            'iva_retenido_recibidas' => $this->money($row->iva_retenido_recibidas),
            'isr_retenido_recibidas' => $this->money($row->isr_retenido_recibidas),
        ];
    }

    private function alerts(int $customerId, Carbon $start, Carbon $end): array
    {
        $base = $this->baseCfdiQuery($customerId, $start, $end);

        $missingTaxes = (clone $base)
            ->whereIn('tipo_comprobante', ['I', 'E'])
            ->whereNull('total_impuestos_trasladados')
            ->whereNull('total_impuestos_retenidos')
            ->count();

        $foreignCurrency = (clone $base)
            ->whereNotIn('moneda', ['MXN', 'XXX'])
            ->count();

        $ppdWithoutPayment = (clone $base)
            ->where('metodo_pago', 'PPD')
            ->whereIn('tipo_comprobante', ['I', 'E'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sat_cfdi_pagos')
                    ->whereColumn('sat_cfdi_pagos.id_documento', 'sat_cfdis.uuid');
            })
            ->count();

        $paymentComplements = (clone $base)
            ->where('tipo_comprobante', 'P')
            ->count();

        return [
            'missing_taxes_cfdis' => $missingTaxes,
            'foreign_currency_cfdis' => $foreignCurrency,
            'ppd_without_payment_cfdis' => $ppdWithoutPayment,
            'payment_complements_cfdis' => $paymentComplements,
            'has_warnings' => $missingTaxes > 0 || $foreignCurrency > 0 || $ppdWithoutPayment > 0,
        ];
    }

    private function baseCfdiQuery(int $customerId, Carbon $start, Carbon $end): Builder
    {
        return SatCfdi::query()
            ->where('customer_id', $customerId)
            ->where('estado_sat', 'vigente')
            ->whereBetween('fecha_emision', [$start, $end]);
    }

    private function periodFromMonth(string|Carbon $month): array
    {
        $date = $month instanceof Carbon
            ? $month->copy()
            : Carbon::parse($month);

        return [
            $date->copy()->startOfMonth()->startOfDay(),
            $date->copy()->endOfMonth()->endOfDay(),
        ];
    }

    private function periodPayload(Carbon $start, Carbon $end): array
    {
        return [
            'month' => $start->format('Y-m'),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    private function sumTenantTotals(Collection $customerSummaries): array
    {
        return [
            'cfdis_count' => $customerSummaries->sum('cfdis_count'),
            'issued_income_total' => $this->money($customerSummaries->sum('issued.income.total')),
            'issued_net_iva_trasladado' => $this->money($customerSummaries->sum('issued.net_iva_trasladado')),
            'received_expense_total' => $this->money($customerSummaries->sum('received.expenses.total')),
            'received_net_iva_acreditable' => $this->money($customerSummaries->sum('received.net_iva_acreditable')),
            'iva_periodo' => $this->money($customerSummaries->sum('estimated.iva_periodo')),
            'retenciones_a_pagar' => $this->money($customerSummaries->sum('estimated.retenciones_a_pagar')),
            'total_estimado_a_pagar' => $this->money($customerSummaries->sum('estimated.total_estimado_a_pagar')),
            'customers_with_warnings' => $customerSummaries
                ->filter(fn (array $summary) => data_get($summary, 'alerts.has_warnings'))
                ->count(),
        ];
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
