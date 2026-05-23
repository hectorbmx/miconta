<?php

namespace App\Services\Stripe;

use App\Models\Tenant;
use App\Models\TenantPayment;
use Carbon\Carbon;
use Stripe\StripeClient;

class TenantStripeBillingService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function syncCheckoutSession(string $sessionId, ?string $eventId = null): ?Tenant
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId);
        $tenant = $this->findTenantForCustomer($session->customer ?? null, $session->metadata->tenant_id ?? null);

        if (! $tenant) {
            return null;
        }

        if ($session->subscription) {
            $this->syncSubscription($tenant, $session->subscription, 'checkout.session.completed');
        }

        $this->recordPaymentFromCheckoutSession($tenant, $session, $eventId);

        return $tenant->refresh();
    }

    public function syncInvoice($invoice, ?string $eventId = null): ?Tenant
    {
        $tenant = $this->findTenantForCustomer($invoice->customer ?? null);

        if (! $tenant) {
            return null;
        }

        $subscriptionId = $invoice->subscription
            ?? ($invoice->parent->subscription_details->subscription ?? null);

        if ($subscriptionId) {
            $this->syncSubscription($tenant, $subscriptionId, 'invoice.paid');
        }

        $this->recordPaymentFromInvoice($tenant, $invoice, $eventId);

        return $tenant->refresh();
    }

    public function syncSubscription(Tenant $tenant, string $subscriptionId, string $source): Tenant
    {
        $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);

        return $this->updateTenantFromSubscriptionObject($tenant, $subscription, $source);
    }

    public function syncLatestActiveSubscriptionForTenant(Tenant $tenant): Tenant
    {
        if (! $tenant->stripe_customer_id) {
            return $tenant;
        }

        $subscriptions = $this->stripe->subscriptions->all([
            'customer' => $tenant->stripe_customer_id,
            'status' => 'all',
            'limit' => 10,
        ]);

        $subscription = collect($subscriptions->data)
            ->first(fn ($item) => in_array($item->status, ['active', 'trialing']));

        if (! $subscription && count($subscriptions->data) > 0) {
            $subscription = $subscriptions->data[0];
        }

        if ($subscription) {
            $this->updateTenantFromSubscriptionObject($tenant, $subscription, 'manual-sync');
        }

        return $tenant->refresh();
    }

    public function syncLatestInvoiceForTenant(Tenant $tenant): ?Tenant
    {
        if (! $tenant->stripe_customer_id) {
            return $tenant;
        }

        $invoices = $this->stripe->invoices->all([
            'customer' => $tenant->stripe_customer_id,
            'limit' => 1,
        ]);

        if (count($invoices->data) === 0) {
            return $tenant->refresh();
        }

        return $this->syncInvoice($invoices->data[0]);
    }

    public function updateTenantFromSubscriptionObject(Tenant $tenant, $subscription, string $source): Tenant
    {
        $currentPeriodEnd = $subscription->current_period_end
            ?? ($subscription->items->data[0]->current_period_end ?? null);

        $tenant->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_status' => $subscription->status,
            'current_period_ends_at' => $currentPeriodEnd
                ? Carbon::createFromTimestamp($currentPeriodEnd)
                : null,
            'cancel_at' => $subscription->cancel_at
                ? Carbon::createFromTimestamp($subscription->cancel_at)
                : null,
            'canceled_at' => $subscription->canceled_at
                ? Carbon::createFromTimestamp($subscription->canceled_at)
                : null,
        ]);

        logger()->info('Tenant actualizado desde Stripe', [
            'source' => $source,
            'tenant_id' => $tenant->id,
            'stripe_subscription_id' => $subscription->id,
            'stripe_status' => $subscription->status,
            'current_period_end' => $currentPeriodEnd,
        ]);

        return $tenant->refresh();
    }

    private function findTenantForCustomer(?string $stripeCustomerId, ?int $metadataTenantId = null): ?Tenant
    {
        if ($metadataTenantId) {
            $tenant = Tenant::find($metadataTenantId);

            if ($tenant) {
                return $tenant;
            }
        }

        if (! $stripeCustomerId) {
            return null;
        }

        return Tenant::where('stripe_customer_id', $stripeCustomerId)->first();
    }

    private function recordPaymentFromCheckoutSession(Tenant $tenant, $session, ?string $eventId = null): void
    {
        TenantPayment::updateOrCreate(
            [
                'stripe_checkout_session_id' => $session->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'provider' => 'stripe',
                'stripe_event_id' => $eventId,
                'stripe_subscription_id' => $session->subscription ?? null,
                'stripe_customer_id' => $session->customer ?? null,
                'status' => $session->payment_status ?? 'pending',
                'amount' => (($session->amount_total ?? 0) / 100),
                'currency' => strtoupper($session->currency ?? 'MXN'),
                'paid_at' => ($session->payment_status ?? null) === 'paid' ? now() : null,
                'description' => 'Checkout de suscripcion SaaS',
                'payload' => $session->toArray(),
            ]
        );
    }

    private function recordPaymentFromInvoice(Tenant $tenant, $invoice, ?string $eventId = null): void
    {
        TenantPayment::updateOrCreate(
            [
                'stripe_invoice_id' => $invoice->id,
            ],
            [
                'tenant_id' => $tenant->id,
                'provider' => 'stripe',
                'stripe_event_id' => $eventId,
                'stripe_payment_intent_id' => $invoice->payment_intent ?? null,
                'stripe_subscription_id' => $invoice->subscription
                    ?? ($invoice->parent->subscription_details->subscription ?? null),
                'stripe_customer_id' => $invoice->customer ?? null,
                'status' => $invoice->status ?? 'paid',
                'amount' => (($invoice->amount_paid ?? $invoice->amount_due ?? 0) / 100),
                'currency' => strtoupper($invoice->currency ?? 'MXN'),
                'paid_at' => isset($invoice->status_transitions->paid_at)
                    ? Carbon::createFromTimestamp($invoice->status_transitions->paid_at)
                    : now(),
                'description' => $invoice->description ?? 'Pago de suscripcion SaaS',
                'payload' => $invoice->toArray(),
            ]
        );
    }
}
