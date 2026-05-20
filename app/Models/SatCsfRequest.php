<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SatCsfRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',

        'rfc',

        'estado',

        'pdf_path',

        'datos_fiscales',

        'error_message',

        'downloaded_at',
    ];

    protected $casts = [
        'datos_fiscales' => 'array',
        'downloaded_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->estado === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->estado === 'failed';
    }
}