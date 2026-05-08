<?php

namespace App\Services\Stripe;

use App\Models\CustomerPlan;
use Stripe\StripeClient;

class StripeCustomerPlanService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }
public function createProductAndPrice(CustomerPlan $plan): void
{
    $tenant = $plan->tenant;

    if (!$tenant || !$tenant->stripe_account_id || !$tenant->stripe_charges_enabled) {
        throw new \Exception('El tenant no tiene Stripe Connect configurado.');
    }

    $data = [
        'name' => $plan->name,
    ];

    if (!empty($plan->description)) {
        $data['description'] = $plan->description;
    }

    // Crear producto en la cuenta conectada del tenant
    $product = $this->stripe->products->create($data, [
        'stripe_account' => $tenant->stripe_account_id,
    ]);

    $priceData = [
        'unit_amount' => (int) ($plan->price * 100),
        'currency' => 'mxn',
        'product' => $product->id,
    ];

    if (in_array($plan->billing_period, ['monthly', 'yearly'])) {
        $priceData['recurring'] = [
            'interval' => $plan->billing_period === 'monthly' ? 'month' : 'year',
        ];
    }

    // Crear precio en la cuenta conectada del tenant
    $price = $this->stripe->prices->create($priceData, [
        'stripe_account' => $tenant->stripe_account_id,
    ]);

    $plan->update([
        'stripe_product_id' => $product->id,
        'stripe_price_id' => $price->id,
    ]);
}
}