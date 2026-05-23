<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'billing_period',
        'billing_mode',
        'stripe_product_id', 
        'max_users',
        'max_customers',
        'stripe_price_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function isMonthly(): bool
    {
        return $this->billing_period === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_period === 'yearly';
    }

    public function isManual(): bool
    {
        return ($this->billing_mode ?? 'manual') === 'manual';
    }

    public function isStripe(): bool
    {
        return ($this->billing_mode ?? 'manual') === 'stripe';
    }
}
