<?php

namespace App\Models;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerPlan;
use App\Models\TenantPaymentSetting;
use App\Models\TenantPayment;

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
    'postal_code',

    'stripe_customer_id',
    'stripe_subscription_id',
    'stripe_status',
    'trial_ends_at',
    'current_period_ends_at',
    'cancel_at',
    'canceled_at',
    'grace_days',

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
        'grace_days' => 'integer',
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

    public function gracePeriodEndsAt()
    {
        if (! $this->current_period_ends_at || $this->grace_days <= 0) {
            return null;
        }

        return $this->current_period_ends_at->copy()->addDays($this->grace_days);
    }

    public function isInGracePeriod(): bool
    {
        $graceEndsAt = $this->gracePeriodEndsAt();

        return $graceEndsAt
            && now()->gt($this->current_period_ends_at)
            && now()->lte($graceEndsAt);
    }

    public function graceDaysRemaining(): int
    {
        if (! $this->isInGracePeriod()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInDays($this->gracePeriodEndsAt(), false)));
    }

    public function shouldShowPaymentReminder(): bool
    {
        if (! $this->stripe_subscription_id || ! $this->stripe_status) {
            return false;
        }

        return $this->isInGracePeriod()
            || in_array($this->stripe_status, ['past_due', 'unpaid', 'incomplete']);
    }

    public function hasActiveSaasAccess(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->plan && $this->plan->isFree()) {
            return true;
        }

        if ($this->plan && $this->plan->isManual()) {
            return true;
        }

        if (! $this->stripe_subscription_id) {
            return false;
        }

        if (! in_array($this->stripe_status, ['active', 'trialing', 'past_due', 'unpaid'])) {
            return false;
        }

        if (! $this->current_period_ends_at) {
            return $this->isSubscribed();
        }

        return now()->lte($this->current_period_ends_at) || $this->isInGracePeriod();
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

    public function payments()
    {
        return $this->hasMany(TenantPayment::class);
    }
}
