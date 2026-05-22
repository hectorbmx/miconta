<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_plans')
            ->whereNotNull('stripe_price_id')
            ->update(['billing_mode' => 'stripe']);
    }

    public function down(): void
    {
        DB::table('customer_plans')
            ->whereNotNull('stripe_price_id')
            ->update(['billing_mode' => 'manual']);
    }
};
