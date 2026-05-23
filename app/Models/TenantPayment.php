<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'provider',
        'stripe_event_id',
        'stripe_checkout_session_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'amount',
        'currency',
        'paid_at',
        'description',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payload' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
