<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubscription extends Model
{
    use HasFactory;

    protected $table = 'customer_subscriptions';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'customer_plan_id',
        'status',
        'billing_mode',
        'starts_at',
        'ends_at',
        'price_snapshot',
        'stripe_checkout_session_id',
        'stripe_subscription_id',
        'stripe_payment_status',
        'payment_status',
        'paid_at',
        'payment_method',
        'paid_amount',
        'payment_reference',
        'payment_notes',
        'max_downloads_snapshot',
        'max_companies_snapshot',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'price_snapshot' => 'decimal:2',
        'paid_at' => 'datetime',
        'paid_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
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

    public function plan()
    {
        return $this->belongsTo(CustomerPlan::class, 'customer_plan_id');
    }

    //HELPERS
    public function isExpired(): bool
        {
            return $this->ends_at && now()->gt($this->ends_at);
        }

        public function isActive(): bool
        {
            return $this->status === 'active' && !$this->isExpired();
        }

        public function hasDownloadLimit(): bool
        {
            return !is_null($this->max_downloads_snapshot);
        }

        public function hasCompanyLimit(): bool
        {
            return !is_null($this->max_companies_snapshot);
        }
}
