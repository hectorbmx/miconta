<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatCfdiConcepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'sat_cfdi_id',
        'clave_prod_serv',
        'no_identificacion',
        'cantidad',
        'clave_unidad',
        'unidad',
        'descripcion',
        'valor_unitario',
        'importe',
        'descuento',
        'objeto_impuesto',
        'importe_iva_trasladado',
        'importe_isr_retenido',
        'importe_iva_retenido',
        'impuestos_json',
        'informacion_aduanera_json',
        'cuenta_predial_json',
        'parte_json',
        'complemento_concepto_json',
        'meta_json',
    ];

    protected $casts = [
        'cantidad'                  => 'decimal:6',
        'valor_unitario'            => 'decimal:6',
        'importe'                   => 'decimal:6',
        'descuento'                 => 'decimal:6',
        'importe_iva_trasladado'    => 'decimal:6',
        'importe_isr_retenido'      => 'decimal:6',
        'importe_iva_retenido'      => 'decimal:6',
        'impuestos_json'            => 'array',
        'informacion_aduanera_json' => 'array',
        'cuenta_predial_json'       => 'array',
        'parte_json'                => 'array',
        'complemento_concepto_json' => 'array',
        'meta_json'                 => 'array',
    ];

    // Relaciones
    public function cfdi()
    {
        return $this->belongsTo(SatCfdi::class, 'sat_cfdi_id');
    }

    // Scopes
    public function scopeConIva($query)
    {
        return $query->whereNotNull('importe_iva_trasladado')
                     ->where('importe_iva_trasladado', '>', 0);
    }

    public function scopePorClave($query, string $clave)
    {
        return $query->where('clave_prod_serv', $clave);
    }
}