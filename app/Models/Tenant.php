<?php

namespace App\Models;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerPlan;
use App\Models\TenantPaymentSetting;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
    'name',
    'rfc',
    'domain',
    'plan_id',
    'status',
    'billing_email',
    'phone',
    'state',
    'city',

    'stripe_customer_id',
    'stripe_subscription_id',
    'stripe_status',
    'trial_ends_at',
    'current_period_ends_at',
    'cancel_at',
    'canceled_at',

    'stripe_account_id',
    'stripe_charges_enabled',
    'stripe_payouts_enabled',
    'stripe_details_submitted',
];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'cancel_at' => 'datetime',
        'canceled_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | HELPERS SaaS
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    public function isSubscribed(): bool
    {
        return in_array($this->stripe_status, ['active', 'trialing']);
    }

    public function isPastDue(): bool
    {
        return $this->stripe_status === 'past_due';
    }

    public function isCanceled(): bool
    {
        return $this->stripe_status === 'canceled';
    }
    
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
        public function currentPlan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function ownerUser()
    {
        return $this->hasOne(User::class)->oldestOfMany();
    }
    public function customerPlans()
    {
        return $this->hasMany(CustomerPlan::class);
    }
    public function paymentSetting()
    {
        return $this->hasOne(TenantPaymentSetting::class);
    }
}