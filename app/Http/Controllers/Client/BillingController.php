<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Stripe\TenantStripeBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function pending(Request $request, TenantStripeBillingService $billing): View|RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if ($tenant && $request->filled('session_id')) {
            try {
                $billing->syncCheckoutSession($request->string('session_id'));
                $tenant->refresh();
            } catch (\Throwable $e) {
                logger()->error('No se pudo sincronizar checkout de Stripe al volver', [
                    'tenant_id' => $tenant->id,
                    'session_id' => $request->input('session_id'),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($tenant && ! $tenant->hasActiveSaasAccess() && $tenant->stripe_customer_id) {
            try {
                $billing->syncLatestActiveSubscriptionForTenant($tenant);
                $tenant->refresh();
            } catch (\Throwable $e) {
                logger()->error('No se pudo sincronizar suscripcion activa desde Stripe', [
                    'tenant_id' => $tenant->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($tenant?->hasActiveSaasAccess()) {
            return redirect()->route('client.dashboard');
        }

        return view('client.billing.pending', compact('tenant'));
    }

    public function checkout(): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        abort_if(! $tenant, 403);

        if ($tenant->hasActiveSaasAccess()) {
            return redirect()->route('client.dashboard');
        }

        if ($tenant->plan?->isManual()) {
            return back()->with('error', 'Tu plan es de pago manual. Contacta al administrador para activar o renovar tu acceso.');
        }

        if (! $tenant->plan || ! $tenant->plan->stripe_price_id) {
            return back()->with('error', 'Tu cuenta no tiene un plan de Stripe configurado. Contacta al administrador.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        if (! $tenant->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email' => $tenant->billing_email,
                'name' => $tenant->name,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                ],
            ]);

            $tenant->update([
                'stripe_customer_id' => $customer->id,
            ]);
        }

        $session = $stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $tenant->stripe_customer_id,
            'line_items' => [[
                'price' => $tenant->plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => route('client.billing.pending') . '?success=1&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.billing.pending') . '?cancel=1',
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        return redirect($session->url);
    }
}
