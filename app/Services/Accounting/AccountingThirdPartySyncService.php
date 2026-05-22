<?php

namespace App\Services\Accounting;

use App\Models\AccountingThirdParty;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class AccountingThirdPartySyncService
{
    public function syncFromCfdis(Customer $customer): int
    {
        $rows = DB::table('sat_cfdis')
            ->where('customer_id', $customer->id)
            ->whereNotNull('rfc_emisor')
            ->whereNotNull('rfc_receptor')
            ->selectRaw("
                CASE
                    WHEN UPPER(rfc_emisor) = ? THEN UPPER(rfc_receptor)
                    ELSE UPPER(rfc_emisor)
                END as rfc,
                MAX(CASE
                    WHEN UPPER(rfc_emisor) = ? THEN razon_social_receptor
                    ELSE razon_social_emisor
                END) as name,
                MAX(CASE
                    WHEN UPPER(rfc_emisor) = ? THEN 'client'
                    ELSE 'supplier'
                END) as detected_type,
                COUNT(*) as cfdis_count,
                COALESCE(SUM(total), 0) as total_amount,
                MAX(fecha_emision) as last_cfdi_at,
                SUM(CASE WHEN UPPER(rfc_emisor) = ? THEN 1 ELSE 0 END) as issued_count,
                SUM(CASE WHEN UPPER(rfc_receptor) = ? THEN 1 ELSE 0 END) as received_count
            ", [
                strtoupper($customer->rfc),
                strtoupper($customer->rfc),
                strtoupper($customer->rfc),
                strtoupper($customer->rfc),
                strtoupper($customer->rfc),
            ])
            ->where(function ($query) use ($customer) {
                $query->whereRaw('UPPER(rfc_emisor) = ?', [strtoupper($customer->rfc)])
                    ->orWhereRaw('UPPER(rfc_receptor) = ?', [strtoupper($customer->rfc)]);
            })
            ->groupBy('rfc')
            ->get();

        $synced = 0;

        foreach ($rows as $row) {
            if (! $row->rfc || $row->rfc === strtoupper($customer->rfc)) {
                continue;
            }

            $type = ((int) $row->issued_count > 0 && (int) $row->received_count > 0)
                ? 'both'
                : $row->detected_type;

            AccountingThirdParty::updateOrCreate(
                [
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                    'rfc' => $row->rfc,
                ],
                [
                    'name' => $row->name,
                    'type' => $type,
                    'cfdis_count' => (int) $row->cfdis_count,
                    'total_amount' => round((float) $row->total_amount, 2),
                    'last_cfdi_at' => $row->last_cfdi_at,
                    'is_active' => true,
                ]
            );

            $synced++;
        }

        return $synced;
    }
}
