<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatCfdi extends Model
{
    use HasFactory;

    protected $fillable = [
        'sat_download_request_id',
        'customer_id',
        'uuid',
        'serie',
        'folio',
        'rfc_emisor',
        'razon_social_emisor',
        'regimen_fiscal_emisor',
        'rfc_receptor',
        'razon_social_receptor',
        'regimen_fiscal_receptor',
        'uso_cfdi',
        'domicilio_fiscal_receptor',
        'fecha_emision',
        'fecha_certificacion',
        'fecha_cancelacion',
        'tipo_comprobante',
        'tipo_descarga',
        'metodo_pago',
        'forma_pago',
        'condiciones_pago',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'descuento',
        'total_impuestos_trasladados',
        'total_impuestos_retenidos',
        'total',
        'estado_sat',
        'estatus_cancelacion',
        'motivo_cancelacion',
        'folio_fiscal_sustitucion',
        'tipo_relacion',
        'cfdis_relacionados',
        'tiene_complemento_pago',
        'tiene_complemento_nomina',
        'tiene_complemento_comercio_exterior',
        'complementos_json',
        'xml_path',
        'package_id',
        'meta_json',
    ];

    protected $casts = [
        'fecha_emision'                      => 'datetime',
        'fecha_certificacion'                => 'datetime',
        'fecha_cancelacion'                  => 'datetime',
        'subtotal'                           => 'decimal:6',
        'descuento'                          => 'decimal:6',
        'total_impuestos_trasladados'        => 'decimal:6',
        'total_impuestos_retenidos'          => 'decimal:6',
        'total'                              => 'decimal:6',
        'tipo_cambio'                        => 'decimal:6',
        'tiene_complemento_pago'             => 'boolean',
        'tiene_complemento_nomina'           => 'boolean',
        'tiene_complemento_comercio_exterior'=> 'boolean',
        'cfdis_relacionados'                 => 'array',
        'complementos_json'                  => 'array',
        'meta_json'                          => 'array',
    ];

    // Relaciones
    public function downloadRequest()
    {
        return $this->belongsTo(SatDownloadRequest::class, 'sat_download_request_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function conceptos()
    {
        return $this->hasMany(SatCfdiConcepto::class);
    }

    public function pagos()
    {
        return $this->hasMany(SatCfdiPago::class);
    }

    // Scopes para dashboard
    public function scopeVigentes($query)
    {
        return $query->where('estado_sat', 'vigente');
    }

    public function scopeCancelados($query)
    {
        return $query->where('estado_sat', 'cancelado');
    }

    public function scopeEmitidas($query)
    {
        return $query->where('tipo_descarga', 'emitidas');
    }

    public function scopeRecibidas($query)
    {
        return $query->where('tipo_descarga', 'recibidas');
    }

    public function scopeDelPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_emision', [$inicio, $fin]);
    }
}