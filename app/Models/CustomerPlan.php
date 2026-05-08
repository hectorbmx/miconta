<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPlan extends Model
{
    use HasFactory;

    protected $table = 'customer_plans';

   protected $fillable = [
    'tenant_id',
    'name',
    'slug',
    'description',
    'price',
    'billing_period',
    'duration_days',
    'max_downloads',
    'max_companies',
    'stripe_product_id',
    'stripe_price_id',
    'is_active',
];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'max_downloads' => 'integer',
        'max_companies' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}