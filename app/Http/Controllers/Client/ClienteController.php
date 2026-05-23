<?php

    namespace App\Http\Controllers\Client;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\Customer;
    use App\Models\CustomerPlan;
    use App\Models\CustomerSubscription;
    use Carbon\Carbon;
    use Stripe\StripeClient;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\Storage;
    use App\Models\SatComplianceOpinionRequest;
    use App\Models\SatCsfRequest;
    use App\Models\AccountingJournal;
    use App\Services\Sat\MonthlyTaxSummaryService;

    class ClienteController extends Controller
    {
        /**
         * Listado de clientes del tenant
         */
    public function index()
    {
        $clientes = Customer::with('activeSubscription.plan')
            ->withCount('accountingJournals')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(10);

        $plans = CustomerPlan::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->get();

        return view('client.clientes.index', compact('clientes', 'plans'));
    }

        /**
         * Formulario de alta
         */
        public function create()
        {
            return view('client.clientes.create');
        }

        /**
         * Guardar cliente
         */
    public function store(Request $request)
    {
        if ($request->filled('rfc')) {
            $request->merge(['rfc' => strtoupper($request->input('rfc'))]);
        }

        $request->validate([
            'rfc' => ['required', 'string', 'min:12', 'max:13', 'regex:/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/u'],
            'razon_social' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'digits:5'],
            'fiel_password' => 'nullable|string',
        ]);

        Customer::create([
            'tenant_id' => auth()->user()->tenant_id,
            'rfc' => $request->rfc,
            'razon_social' => $request->razon_social,
            'email' => $request->email,
            'phone' => $request->phone,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'fiel_password' => $request->fiel_password,
            
        ]);

        return redirect()
            ->route('client.clientes.index')
            ->with('success', 'Cliente creado correctamente');
    }

        /**
         * Ver cliente
         */
   public function show(Request $request, Customer $customer, MonthlyTaxSummaryService $monthlyTaxSummary)
    {
        $this->authorizeTenant($customer);

        $customer->load([
            'satDownloadRequests',
            'satCfdis',
            'accountingAccounts' => fn ($query) => $query->orderBy('code'),
        ]);
        $selectedMonth = $request->input(
            'month',
            optional($customer->satCfdis->max('fecha_emision'))->format('Y-m') ?? now()->format('Y-m')
        );

        $taxSummary = $monthlyTaxSummary->forCustomer($customer, $selectedMonth);

        $csfRequests = SatCsfRequest::where('customer_id', $customer->id)
        ->latest()
        ->take(10)
        ->get();

        $complianceOpinionRequests = SatComplianceOpinionRequest::where('customer_id', $customer->id)
        ->latest()
        ->take(10)
        ->get();

        $journalStatsBase = AccountingJournal::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id);

        $accountingJournalStats = [
            'total' => (clone $journalStatsBase)->count(),
            'draft' => (clone $journalStatsBase)->where('status', 'draft')->count(),
            'posted' => (clone $journalStatsBase)->where('status', 'posted')->count(),
        ];

        return view('client.clientes.show', [
            'customer' => $customer,
            'cliente'  => $customer,
            'csfRequests' => $csfRequests,
            'complianceOpinionRequests' => $complianceOpinionRequests,
            'selectedMonth' => $selectedMonth,
            'taxSummary' => $taxSummary,
            'accountingJournalStats' => $accountingJournalStats,

        ]);
    }
        /**
         * Formulario edición
         */
       public function edit(Customer $customer) {
            $this->authorizeTenant($customer);
            return view('client.clientes.edit', compact('customer'));
        }

        /**
         * Actualizar cliente
         */
        public function update(Request $request, Customer $customer)
    {
        $this->authorizeTenant($customer);

        if ($request->filled('rfc')) {
            $request->merge(['rfc' => strtoupper($request->input('rfc'))]);
        }

        $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc' => ['nullable', 'string', 'min:12', 'max:13', 'regex:/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/u'],
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'digits:5'],

            // 'certificate' => 'nullable|file|mimes:cer',
            'certificate' => 'nullable|file|extensions:cer',

            // 'private_key' => 'nullable|file|mimes:key',
            'private_key' => 'nullable|file|extensions:key',
            'fiel_password' => 'nullable|string|max:255',
            'ciec_password' => 'nullable|string|max:255',
        ]);

        $data = [
            'razon_social' => $request->razon_social,
            'rfc' => $request->rfc,
            'email' => $request->email,
            'phone' => $request->phone,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ];

        // 📄 Certificado
        if ($request->hasFile('certificate')) {

            // borrar anterior si existe
            if ($customer->certificate_path) {
                Storage::disk('local')->delete($customer->certificate_path);
            }

            $data['certificate_path'] = $request->file('certificate')
                ->store("clientes/{$customer->id}/fiel");
                
        }

        // 🔑 Llave privada
        if ($request->hasFile('private_key')) {

            if ($customer->private_key_path) {
                Storage::disk('local')->delete($customer->private_key_path);
            }

            $data['private_key_path'] = $request->file('private_key')
                ->store("clientes/{$customer->id}/fiel");
        }

        // 🔒 Password (solo si viene)
    if ($request->filled('fiel_password')) {

        $password = $request->fiel_password;

        // Si NO marcó el checkbox → limpiamos normal
        if (!$request->boolean('password_has_spaces')) {
            $password = trim($password);
        }

        // Si marcó el checkbox → respetamos EXACTO lo que escribió
        // (incluyendo espacios)

        $data['fiel_password'] = $password;
    }
    if ($request->filled('ciec_password')) {
    $data['ciec_password'] = $request->ciec_password;
}

        $customer->update($data);

        return redirect()
            ->route('client.clientes.show', $customer)
            ->with('success', 'Cliente actualizado correctamente');
    }
        /**
         * Eliminar cliente
         */
        public function destroy(Cliente $cliente)
        {
            $this->authorizeTenant($cliente);

            $cliente->delete();

            return redirect()
                ->route('client.clientes.index')
                ->with('success', 'Cliente eliminado');
        }

        /**
         * 🔒 Protección multi-tenant
         */
        private function authorizeTenant($model)
        {
            if ($model->tenant_id !== auth()->user()->tenant_id) {
                abort(403, 'No autorizado');
            }
        }
        public function assignPlan(Request $request, Customer $customer)
        {
            abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);

            $validated = $request->validate([
                'customer_plan_id' => ['required', 'exists:customer_plans,id'],
                'starts_at' => ['required', 'date'],
            ]);

            $plan = CustomerPlan::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_active', true)
                ->findOrFail($validated['customer_plan_id']);

            $activeSubscriptionIds = CustomerSubscription::where('tenant_id', auth()->user()->tenant_id)
                ->where('customer_id', $customer->id)
                ->where('status', 'active')
                ->pluck('id');

            // Cancelar suscripción activa anterior si existía
            CustomerSubscription::whereIn('id', $activeSubscriptionIds)
                ->update(['status' => 'canceled']);

            $startsAt = Carbon::parse($validated['starts_at']);

            $endsAt = $plan->duration_days
                ? $startsAt->copy()->addDays($plan->duration_days)
                : null;

            // CustomerSubscription::create([
            $subscription = CustomerSubscription::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $customer->id,
                'customer_plan_id' => $plan->id,
                'status' => 'active',
                'billing_mode' => $plan->billing_mode ?? 'manual',
                'payment_status' => (($plan->billing_mode ?? 'manual') === 'manual' || ! $plan->stripe_price_id) ? 'pending' : 'pending',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'price_snapshot' => $plan->price,
                'max_downloads_snapshot' => $plan->max_downloads,
                'max_companies_snapshot' => $plan->max_companies,
            ]);

            if (($plan->billing_mode ?? 'manual') === 'manual' || ! $plan->stripe_price_id) {
                return redirect()
                    ->route('client.clientes.index')
                    ->with('success', 'Plan manual asignado correctamente.');
            }

            $tenant = auth()->user()->tenant;

    if (!$tenant || !$tenant->stripe_account_id || !$tenant->stripe_charges_enabled) {
        $subscription->update(['status' => 'canceled']);
        CustomerSubscription::whereIn('id', $activeSubscriptionIds)->update(['status' => 'active']);

        return back()->with('error', 'Configura o completa tu cuenta de Stripe primero, o asigna un plan manual.');
    }

    $stripe = new StripeClient(config('services.stripe.secret'));

                if ($customer->email) {

                //    $session = $stripe->checkout->sessions->create([
                //         'mode' => 'subscription',
                //         'customer_email' => $customer->email,
                //         'line_items' => [[
                //             'price' => $plan->stripe_price_id,
                //             'quantity' => 1,
                //         ]],
                //         'success_url' => url('/client/dashboard?paid=1'),
                //         'cancel_url' => url('/client/dashboard?cancel=1'),

                //         'metadata' => [
                //             'tenant_id' => $subscription->tenant_id,
                //             'customer_id' => $subscription->customer_id,
                //             'subscription_id' => $subscription->id,
                //         ],
                //     ]);
                    $session = $stripe->checkout->sessions->create([
                        'mode' => 'subscription',
                        'customer_email' => $customer->email,
                        'line_items' => [[
                            'price' => $plan->stripe_price_id,
                            'quantity' => 1,
                        ]],
                        'success_url' => url('/client/dashboard?paid=1'),
                        'cancel_url' => url('/client/dashboard?cancel=1'),

                        'metadata' => [
                            'tenant_id' => $subscription->tenant_id,
                            'customer_id' => $subscription->customer_id,
                            'subscription_id' => $subscription->id,
                        ],
                    ], [
                        'stripe_account' => $tenant->stripe_account_id,
                    ]);
                    $subscription->update([
                        'stripe_checkout_session_id' => $session->id,
                        'stripe_payment_status' => $session->payment_status,
                    ]);

                    // Simulación de envío de correo (MAIL_MAILER=log)
                    Mail::raw("Hola {$customer->razon_social},

                Se te ha asignado un plan.

                Completa tu pago aquí:
                {$session->url}

                Gracias.", function ($message) use ($customer) {
                        $message->to($customer->email)
                                ->subject('Completa tu pago');
                    });
                } else {
                    $subscription->update(['status' => 'canceled']);
                    CustomerSubscription::whereIn('id', $activeSubscriptionIds)->update(['status' => 'active']);

                    return back()->with('error', 'El cliente necesita email para enviar el checkout de Stripe.');
                }

            return redirect()
                ->route('client.clientes.index')
                ->with('success', 'Plan asignado correctamente.');
        }

        public function registerManualPayment(Request $request, Customer $customer, CustomerSubscription $subscription)
        {
            abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
            abort_if($subscription->tenant_id !== auth()->user()->tenant_id || $subscription->customer_id !== $customer->id, 403);
            abort_if($subscription->billing_mode !== 'manual', 422, 'Solo se pueden registrar pagos manuales en suscripciones manuales.');

            $validated = $request->validate([
                'paid_amount' => ['required', 'numeric', 'min:0'],
                'paid_at' => ['required', 'date'],
                'payment_method' => ['required', 'in:cash,transfer,deposit,card_external,other'],
                'payment_reference' => ['nullable', 'string', 'max:255'],
                'payment_notes' => ['nullable', 'string', 'max:2000'],
            ]);

            $subscription->update([
                'payment_status' => 'paid',
                'paid_amount' => $validated['paid_amount'],
                'paid_at' => Carbon::parse($validated['paid_at']),
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_notes' => $validated['payment_notes'] ?? null,
            ]);

            return redirect()
                ->route('client.clientes.index')
                ->with('success', 'Pago manual registrado correctamente.');
        }
    }
