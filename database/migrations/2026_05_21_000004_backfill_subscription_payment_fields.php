<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_subscriptions')
            ->join('customer_plans', 'customer_plans.id', '=', 'customer_subscriptions.customer_plan_id')
            ->update([
                'customer_subscriptions.billing_mode' => DB::raw("COALESCE(customer_plans.billing_mode, 'manual')"),
            ]);

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
