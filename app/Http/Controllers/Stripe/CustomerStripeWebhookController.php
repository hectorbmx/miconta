<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use Illuminate\Http\Request;
use Stripe\Webhook;

class CustomerStripeWebhookController extends Controller
{
    public function handle(Request $request)
{
    $payload = $request->getContent();
    $signature = $request->header('Stripe-Signature');

    $secret = config('services.stripe.customer_webhook_secret');

    try {
        $event = Webhook::constructEvent($payload, $signature, $secret);
    } catch (\Throwable $e) {
        return response('Invalid webhook', 400);
    }

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $metadata = $session->metadata ?? null;
        $subscription = null;

        if ($metadata && isset($metadata->subscription_id)) {
            $subscription = CustomerSubscription::find($metadata->subscription_id);
        }

        if ($subscription) {
            $subscription->update([
                'stripe_subscription_id' => $session->subscription ?? null,
                'stripe_payment_status' => $session->payment_status ?? 'paid',
                'paid_at' => now(),
                'status' => 'active',
            ]);
        }
    }

    if ($event->type === 'account.updated') {
        $account = $event->data->object;

        $tenant = \App\Models\Tenant::where('stripe_account_id', $account->id)->first();

        if ($tenant) {
            $tenant->update([
                'stripe_charges_enabled' => $account->charges_enabled,
                'stripe_payouts_enabled' => $account->payouts_enabled,
                'stripe_details_submitted' => $account->details_submitted,
            ]);
        }
    }

    return response('Webhook received', 200);
}
}