<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Stripe\StripePlanService;  
use Stripe\StripeClient;


class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::latest()->paginate(10);

        return view('admin.planes.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.planes.create');
    }

public function store(Request $request, StripePlanService $stripeService)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
        'currency' => ['required', 'string', 'max:3'],
        'billing_period' => ['required', 'in:monthly,yearly'],
        'max_users' => ['nullable', 'integer'],
        'max_customers' => ['nullable', 'integer'],
        'description' => ['nullable', 'string'],
    ]);

    $validated['slug'] = \Str::slug($validated['name']);
    $validated['is_active'] = true;

    $plan = \App\Models\Plan::create($validated);

    // 🔥 Aquí se crea en Stripe
    $stripeService->createProductAndPrice($plan);

    return redirect()
        ->route('admin.planes.index')
        ->with('success', 'Plan creado y sincronizado con Stripe.');
}

    public function edit(Plan $plan)
    {
        return view('admin.planes.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'billing_period' => ['required', 'in:monthly,yearly'],
            'max_users' => ['nullable', 'integer'],
            'max_customers' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $plan->update($validated);

        return redirect()
            ->route('admin.planes.index')
            ->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan)
    {
        $plan->update([
            'is_active' => !$plan->is_active
        ]);

        return back()->with('success', 'Estado del plan actualizado.');
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
}