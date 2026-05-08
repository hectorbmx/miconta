<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Plan;
use Stripe\Webhook;
use Illuminate\Validation\Rule;
use Stripe\StripeClient;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;


class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    // $tenants = Tenant::with('plan')->latest()->paginate(10);
    $tenants = Tenant::with(['plan', 'ownerUser'])
    ->latest()
    ->paginate(10);

    $plans = Plan::where('is_active', true)
        ->orderBy('price')
        ->get();

    return view('admin.tenants.index', compact('tenants', 'plans'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'rfc' => ['nullable', 'string', 'max:13', Rule::unique('tenants', 'rfc')],
        'billing_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        'phone' => ['nullable', 'string'],
        'state' => ['nullable', 'string'],
        'city' => ['nullable', 'string'],
        'plan_id' => ['nullable', 'exists:plans,id'],
    ]);

    DB::transaction(function () use ($validated) {
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'rfc' => $validated['rfc'] ?? null,
            'billing_email' => $validated['billing_email'],
            'phone' => $validated['phone'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'plan_id' => $validated['plan_id'] ?? null,
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'email' => $tenant->billing_email,
            'password' => bcrypt(Str::random(32)),
        ]);
        Password::sendResetLink([
        'email' => $tenant->billing_email
        ]);
    });

    return redirect()
        ->route('admin.tenants.index')
        ->with('success', 'Cliente SaaS y usuario administrador creados correctamente.');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
 public function edit(Tenant $tenant)
{
    $plans = Plan::where('is_active', true)
        ->orderBy('price')
        ->get();

    return view('admin.tenants.edit', compact('tenant', 'plans'));
}


    /**
     * Update the specified resource in storage.
     */
 public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:13', Rule::unique('tenants', 'rfc')->ignore($tenant->id)],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'domain' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'plan_id' => ['nullable', 'exists:plans,id'],
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function subscribe(Tenant $tenant)
{
    if (!$tenant->plan || !$tenant->plan->stripe_price_id) {
        return back()->with('error', 'El cliente no tiene un plan válido.');
    }

    $stripe = new StripeClient(config('services.stripe.secret'));

    // 🔥 Crear customer si no existe
    if (!$tenant->stripe_customer_id) {
        $customer = $stripe->customers->create([
            'email' => $tenant->billing_email,
            'name' => $tenant->name,
        ]);

        $tenant->update([
            'stripe_customer_id' => $customer->id
        ]);
    }

    // 🔥 Crear checkout session
    $session = $stripe->checkout->sessions->create([
        'mode' => 'subscription',
        'customer' => $tenant->stripe_customer_id,
        'line_items' => [[
            'price' => $tenant->plan->stripe_price_id,
            'quantity' => 1,
        ]],
        'success_url' => route('admin.tenants.index') . '?success=1',
        'cancel_url' => route('admin.tenants.index') . '?cancel=1',
    ]);

    return redirect($session->url);
}
// public function webhook(Request $request)
// {
//     \Log::info('Webhook hit', [
//         'payload' => $request->all()
//     ]);

//     return response('OK', 200);
// }
public function webhook(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');

    try {
        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    } catch (\Exception $e) {
        return response('Invalid', 400);
    }

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $tenant = Tenant::where('stripe_customer_id', $session->customer)->first();

        if ($tenant && $session->subscription) {
            $stripe = new StripeClient(config('services.stripe.secret'));

            $subscription = $stripe->subscriptions->retrieve($session->subscription);
            \Log::info('Stripe subscription debug', [
    'subscription_id' => $subscription->id,
    'status' => $subscription->status,
    'current_period_end' => $subscription->current_period_end ?? null,
    'raw' => $subscription->toArray(),
]);

            $tenant->update([
                'stripe_subscription_id' => $subscription->id,
                'stripe_status' => $subscription->status,

               'current_period_ends_at' => isset($subscription->items->data[0]->current_period_end)
                    ? \Carbon\Carbon::createFromTimestamp($subscription->items->data[0]->current_period_end)
                    : null,

                'cancel_at' => $subscription->cancel_at
                    ? \Carbon\Carbon::createFromTimestamp($subscription->cancel_at)
                    : null,

                'canceled_at' => $subscription->canceled_at
                    ? \Carbon\Carbon::createFromTimestamp($subscription->canceled_at)
                    : null,
            ]);
        }
    }

    return response('OK', 200);
}
public function resendInvitation(Tenant $tenant)
{
    $user = $tenant->ownerUser;

    if (!$user) {
        return back()->with('error', 'Este cliente no tiene usuario administrador.');
    }

    if ($user->email_verified_at) {
        return back()->with('error', 'El usuario ya activó su cuenta.');
    }

    Password::sendResetLink([
        'email' => $user->email,
    ]);

    return back()->with('success', 'Invitación reenviada correctamente.');
}
}
