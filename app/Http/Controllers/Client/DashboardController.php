<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Models\CustomerSubscription;
use App\Models\SatCfdi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $tenantId = auth()->user()->tenant_id;

        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        $activeCustomerIds = CustomerSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', now());
            })
            ->distinct()
            ->pluck('customer_id');

        $activeCustomers = $activeCustomerIds->count();
        $inactiveCustomers = max(0, $totalCustomers - $activeCustomers);

        $fielReady = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('certificate_path')
            ->whereNotNull('private_key_path')
            ->whereNotNull('fiel_password')
            ->count();

        $plansTotal = CustomerPlan::where('tenant_id', $tenantId)->count();
        $plansActive = CustomerPlan::where('tenant_id', $tenantId)->where('is_active', true)->count();
        $plansInactive = max(0, $plansTotal - $plansActive);
        $stripePlans = CustomerPlan::where('tenant_id', $tenantId)->whereNotNull('stripe_price_id')->count();
        $manualPlans = max(0, $plansTotal - $stripePlans);

        $subscriptionsActive = CustomerSubscription::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $subscriptionsInactive = CustomerSubscription::where('tenant_id', $tenantId)->where('status', '<>', 'active')->count();
        $subscriptionsWithCard = CustomerSubscription::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNotNull('stripe_subscription_id')
                    ->orWhereNotNull('stripe_checkout_session_id')
                    ->orWhere('stripe_payment_status', 'paid');
            })
            ->count();

        $monthlyRevenue = CustomerSubscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', now());
            })
            ->sum('price_snapshot');

        $cfdiTotals = SatCfdi::whereHas('customer', fn ($query) => $query->where('tenant_id', $tenantId))
            ->selectRaw('COUNT(*) as total_xml, COALESCE(SUM(total), 0) as total_amount')
            ->first();

        $planDistribution = CustomerSubscription::with('plan')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('customer_plan_id', DB::raw('COUNT(*) as subscriptions'), DB::raw('COALESCE(SUM(price_snapshot), 0) as revenue'))
            ->groupBy('customer_plan_id')
            ->orderByDesc('subscriptions')
            ->get();

        $recentCustomers = Customer::where('tenant_id', $tenantId)
            ->latest()
            ->take(6)
            ->get();

        $customersPendingFiel = Customer::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('certificate_path')
                    ->orWhereNull('private_key_path')
                    ->orWhereNull('fiel_password');
            })
            ->latest()
            ->take(6)
            ->get();

        $cfdiByCustomer = SatCfdi::query()
            ->join('customers', 'customers.id', '=', 'sat_cfdis.customer_id')
            ->where('customers.tenant_id', $tenantId)
            ->selectRaw('customers.id, customers.razon_social, customers.rfc, COUNT(sat_cfdis.id) as xml_count, COALESCE(SUM(sat_cfdis.total), 0) as total')
            ->groupBy('customers.id', 'customers.razon_social', 'customers.rfc')
            ->orderByDesc('xml_count')
            ->limit(6)
            ->get();

        $dashboard = [
            'tenant' => $tenant,
            'kpis' => [
                'customers_total' => $totalCustomers,
                'customers_active' => $activeCustomers,
                'customers_inactive' => $inactiveCustomers,
                'fiel_ready' => $fielReady,
                'fiel_pending' => max(0, $totalCustomers - $fielReady),
                'plans_total' => $plansTotal,
                'plans_active' => $plansActive,
                'plans_inactive' => $plansInactive,
                'stripe_plans' => $stripePlans,
                'manual_plans' => $manualPlans,
                'subscriptions_active' => $subscriptionsActive,
                'subscriptions_inactive' => $subscriptionsInactive,
                'subscriptions_with_card' => $subscriptionsWithCard,
                'subscriptions_manual' => max(0, $subscriptionsActive + $subscriptionsInactive - $subscriptionsWithCard),
                'monthly_revenue' => (float) $monthlyRevenue,
                'total_xml' => (int) ($cfdiTotals->total_xml ?? 0),
            ],
            'stripe_ready' => (bool) ($tenant?->stripe_charges_enabled),
            'plan_distribution' => $planDistribution,
            'recent_customers' => $recentCustomers,
            'customers_pending_fiel' => $customersPendingFiel,
            'cfdi_by_customer' => $cfdiByCustomer,
        ];

        return view('client.dashboard', compact('dashboard'));
    }
}
