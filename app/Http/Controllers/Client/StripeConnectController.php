<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    public function connect()
    {
        $tenant = Auth::user()->tenant;

        abort_if(!$tenant, 403);

        $stripe = new StripeClient(config('services.stripe.secret'));

        if (!$tenant->stripe_account_id) {
            $account = $stripe->accounts->create([
                'type' => 'standard',
                'email' => $tenant->billing_email,
                'business_type' => 'company',
                'metadata' => [
                    'tenant_id' => $tenant->id,
                ],
            ]);

            $tenant->update([
                'stripe_account_id' => $account->id,
            ]);
        }

        $accountLink = $stripe->accountLinks->create([
            'account' => $tenant->stripe_account_id,
            'refresh_url' => route('client.configuracion.index'),
            'return_url' => route('client.configuracion.index'),
            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }
}
