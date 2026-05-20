<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SatCfdi extends Model
{
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
    ];

    protected $casts = [
        'fecha_emision'       => 'datetime',
        'fecha_certificacion' => 'datetime',
        'fecha_cancelacion'   => 'datetime',
        'subtotal'            => 'decimal:6',
        'descuento'           => 'decimal:6',
        'total_impuestos_trasladados' => 'decimal:6',
        'total_impuestos_retenidos'   => 'decimal:6',
        'total'               => 'decimal:6',
        'tipo_cambio'         => 'decimal:6',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function downloadRequest(): BelongsTo
    {
        return $this->belongsTo(SatDownloadRequest::class, 'sat_download_request_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SatCfdiFile::class);
    }

    public function xmlFile(): HasOne
    {
        return $this->hasOne(SatCfdiFile::class)->where('tipo', 'xml');
    }

    public function pdfFile(): HasOne
    {
        return $this->hasOne(SatCfdiFile::class)->where('tipo', 'pdf');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isVigente(): bool    { return $this->estado_sat === 'vigente'; }
    public function isCancelado(): bool  { return $this->estado_sat === 'cancelado'; }
    public function isEmitido(): bool    { return $this->tipo_descarga === 'emitidos'; }
    public function isRecibido(): bool   { return $this->tipo_descarga === 'recibidos'; }

    public function hasXml(): bool { return $this->xmlFile()->exists(); }
    public function hasPdf(): bool { return $this->pdfFile()->exists(); }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeVigentes($query)   { return $query->where('estado_sat', 'vigente'); }
    public function scopeCancelados($query) { return $query->where('estado_sat', 'cancelado'); }
    public function scopeEmitidos($query)   { return $query->where('tipo_descarga', 'emitidos'); }
    public function scopeRecibidos($query)  { return $query->where('tipo_descarga', 'recibidos'); }
    public function scopeByUuid($query, string $uuid) { return $query->where('uuid', $uuid); }
    public function scopeByRfcEmisor($query, string $rfc) { return $query->where('rfc_emisor', $rfc); }
    public function scopeByPeriod($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_emision', [$desde, $hasta]);
    }
}


class SatCfdiFile extends Model
{
    protected $fillable = [
        'sat_cfdi_id',
        'customer_id',
        'tipo',
        'disk',
        'path',
        'mime_type',
        'size',
        'is_valid',
        'downloaded_at',
    ];

    protected $casts = [
        'is_valid'      => 'boolean',
        'downloaded_at' => 'datetime',
        'size'          => 'integer',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function cfdi(): BelongsTo
    {
        return $this->belongsTo(SatCfdi::class, 'sat_cfdi_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getContents(): ?string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->get($this->path);
    }

    public function getUrl(): ?string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeHuman(): string
    {
        if (!$this->size) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $this->size;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}