<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatComplianceOpinionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'rfc',
        'estado',
        'pdf_path',
        'error_message',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isCompleted(): bool
    {
        return $this->estado === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->estado === 'failed';
    }
}
