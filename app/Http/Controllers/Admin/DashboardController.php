<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $soon = now()->addDays(7);

        $totalTenants = Tenant::count();

        $activeTenants = Tenant::query()
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereHas('plan', fn ($plan) => $plan->where('price', 0))
                    ->orWhereIn('stripe_status', ['active', 'trialing'])
                    ->orWhere(function ($period) use ($now) {
                        $period->whereNotNull('current_period_ends_at')
                            ->where('current_period_ends_at', '>=', $now);
                    });
            })
            ->count();

        $expiredTenants = Tenant::query()
            ->where(function ($query) use ($now) {
                $query->whereIn('stripe_status', ['past_due', 'unpaid', 'canceled', 'incomplete', 'incomplete_expired'])
                    ->orWhere(function ($period) use ($now) {
                        $period->whereNotNull('current_period_ends_at')
                            ->where('current_period_ends_at', '<', $now);
                    });
            })
            ->count();

        $expiringSoonTenants = Tenant::query()
            ->whereNotNull('current_period_ends_at')
            ->whereBetween('current_period_ends_at', [$now, $soon])
            ->count();

        $automaticPaymentTenants = Tenant::query()
            ->whereHas('plan', fn ($plan) => $plan->where('billing_mode', 'stripe'))
            ->count();

        $manualPaymentTenants = Tenant::query()
            ->where(function ($query) {
                $query->whereDoesntHave('plan')
                    ->orWhereHas('plan', fn ($plan) => $plan->where('billing_mode', 'manual'));
            })
            ->count();

        $monthlyRevenue = Tenant::query()
            ->where('status', 'active')
            ->whereHas('plan')
            ->join('plans', 'plans.id', '=', 'tenants.plan_id')
            ->sum('plans.price');

        $latestTenant = Tenant::with('plan')
            ->latest()
            ->first();

        $expiringTenants = Tenant::with('plan')
            ->whereNotNull('current_period_ends_at')
            ->whereBetween('current_period_ends_at', [$now, $soon])
            ->orderBy('current_period_ends_at')
            ->take(6)
            ->get();

        $recentTenants = Tenant::with('plan')
            ->latest()
            ->take(6)
            ->get();

        $planDistribution = Tenant::query()
            ->leftJoin('plans', 'plans.id', '=', 'tenants.plan_id')
            ->selectRaw('COALESCE(plans.name, ?) as plan_name, COUNT(tenants.id) as tenants_count', ['Sin plan'])
            ->groupBy('plan_name')
            ->orderByDesc('tenants_count')
            ->take(6)
            ->get();

        $dashboard = [
            'kpis' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'expired_tenants' => $expiredTenants,
                'expiring_soon_tenants' => $expiringSoonTenants,
                'automatic_payment_tenants' => $automaticPaymentTenants,
                'manual_payment_tenants' => $manualPaymentTenants,
                'monthly_revenue' => (float) $monthlyRevenue,
            ],
            'latest_tenant' => $latestTenant,
            'expiring_tenants' => $expiringTenants,
            'recent_tenants' => $recentTenants,
            'plan_distribution' => $planDistribution,
        ];

        return view('admin.dashboard', compact('dashboard'));
    }
}
