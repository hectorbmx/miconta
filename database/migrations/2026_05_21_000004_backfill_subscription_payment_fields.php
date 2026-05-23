<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $planBillingModes = DB::table('customer_plans')
            ->pluck('billing_mode', 'id');

        DB::table('customer_subscriptions')
            ->select('id', 'customer_plan_id')
            ->orderBy('id')
            ->get()
            ->each(function ($subscription) use ($planBillingModes) {
                DB::table('customer_subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'billing_mode' => $planBillingModes[$subscription->customer_plan_id] ?? 'manual',
                    ]);
            });

        DB::table('customer_subscriptions')
            ->where('stripe_payment_status', 'paid')
            ->update(['payment_status' => 'paid']);
    }

    public function down(): void
    {
        DB::table('customer_subscriptions')->update([
            'billing_mode' => 'manual',
            'payment_status' => 'pending',
        ]);
    }
};
