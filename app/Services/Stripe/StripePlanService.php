<?php

namespace App\Services\Stripe;

use App\Models\Plan;
use Stripe\StripeClient;

class StripePlanService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createProductAndPrice(Plan $plan): Plan
    {
        $product = $this->stripe->products->create([
            'name' => $plan->name,
            'description' => $plan->description,
            'metadata' => [
                'local_plan_id' => $plan->id,
            ],
        ]);

        $price = $this->stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => (int) ($plan->price * 100),
            'currency' => strtolower($plan->currency),
            'recurring' => [
                'interval' => $plan->billing_period === 'yearly' ? 'year' : 'month',
            ],
            'metadata' => [
                'local_plan_id' => $plan->id,
            ],
        ]);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);

        return $plan;
    }
}