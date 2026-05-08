<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatCfdiPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'sat_cfdi_id',
        'cfdi_uuid',
        'fecha_pago',
        'forma_pago_p',
        'moneda_p',
        'tipo_cambio_p',
        'monto',
        'num_operacion',
        'rfc_emisor_cta_ord',
        'nom_banco_ord_ext',
        'cta_ordenante',
        'rfc_emisor_cta_ben',
        'cta_beneficiario',
        'id_documento',
        'serie_dr',
        'folio_dr',
        'moneda_dr',
        'tipo_cambio_dr',
        'num_parcialidad',
        'imp_saldo_ant',
        'imp_pagado',
        'imp_saldo_insoluto',
        'objeto_impuesto_dr',
        'impuestos_p_json',
        'meta_json',
    ];

    protected $casts = [
        'fecha_pago'        => 'datetime',
        'tipo_cambio_p'     => 'decimal:6',
        'monto'             => 'decimal:6',
        'tipo_cambio_dr'    => 'decimal:6',
        'imp_saldo_ant'     => 'decimal:6',
        'imp_pagado'        => 'decimal:6',
        'imp_saldo_insoluto'=> 'decimal:6',
        'impuestos_p_json'  => 'array',
        'meta_json'         => 'array',
    ];

    // Relaciones
    public function cfdi()
    {
        return $this->belongsTo(SatCfdi::class, 'sat_cfdi_id');
    }

    // El CFDI de la factura original que se está pagando
    public function cfdiDocumentoRelacionado()
    {
        return $this->belongsTo(SatCfdi::class, 'id_documento', 'uuid');
    }

    // Scopes
    public function scopeDelPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_pago', [$inicio, $fin]);
    }

    public function scopePorFormaPago($query, string $forma)
    {
        return $query->where('forma_pago_p', $forma);
    }
}