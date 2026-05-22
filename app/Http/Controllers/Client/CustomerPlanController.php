<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerPlan;
use Illuminate\Support\Str;
use App\Services\Stripe\StripeCustomerPlanService;

class CustomerPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $plans = CustomerPlan::where('tenant_id', auth()->user()->tenant_id)
        ->latest()
        ->paginate(10);

    return view('client.customer-plans.index', compact('plans'));
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
public function store(Request $request, StripeCustomerPlanService $stripeService)
{
    $tenant = auth()->user()->tenant;

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
        'billing_period' => ['required', 'in:monthly,yearly,one_time'],
        'billing_mode' => ['required', 'in:manual,stripe'],
        'duration_days' => ['nullable', 'integer', 'min:1'],
        'max_downloads' => ['nullable', 'integer', 'min:0'],
        'max_companies' => ['nullable', 'integer', 'min:0'],
        'description' => ['nullable', 'string'],
    ]);

    if ($validated['billing_mode'] === 'stripe' && (! $tenant || ! $tenant->stripe_account_id || ! $tenant->stripe_charges_enabled)) {
        return back()
            ->withInput()
            ->with('error', 'Conecta Stripe antes de crear planes con cobro automatico. Puedes crear este plan como manual.');
    }

    $validated['tenant_id'] = auth()->user()->tenant_id;
    $validated['slug'] = Str::slug($validated['name']);
    $validated['is_active'] = true;

    $plan = CustomerPlan::create($validated);

    if ($plan->billing_mode === 'stripe') {
        $stripeService->createProductAndPrice($plan);
    }

    return redirect()
        ->route('client.customer-plans.index')
        ->with('success', $plan->billing_mode === 'stripe'
            ? 'Plan creado y sincronizado con Stripe.'
            : 'Plan manual creado correctamente.');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
