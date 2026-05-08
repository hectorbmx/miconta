<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'tenant_payment_settings';

    protected $fillable = [
        'tenant_id',
        'provider',
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_webhook_secret',
        'is_active',
    ];

    protected $casts = [
        'stripe_secret_key' => 'encrypted',
        'stripe_webhook_secret' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}