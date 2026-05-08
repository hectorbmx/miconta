<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatDownloadRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'rfc_solicitante',
        'fecha_inicio',
        'fecha_fin',
        'tipo_descarga',
        'tipo_solicitud',
        'request_id_sat',
        'packages_ids',
        'total_xml',
        'estado',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'fecha_inicio'   => 'datetime',
        'fecha_fin'      => 'datetime',
        'completed_at'   => 'datetime',
        'packages_ids'   => 'array',
    ];

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cfdis()
    {
        return $this->hasMany(SatCfdi::class);
    }

    // Scopes útiles para el dashboard
    public function scopePendiente($query)
    {
        return $query->where('estado', 'pending');
    }

    public function scopeCompletada($query)
    {
        return $query->where('estado', 'completed');
    }

    public function scopeFallida($query)
    {
        return $query->where('estado', 'failed');
    }
}