<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('tenants', function (Blueprint $table) {
        $table->string('status')->default('active')->after('plan');
        $table->string('billing_email')->nullable()->after('status');

        // Stripe
        $table->string('stripe_customer_id')->nullable()->index()->after('billing_email');
        $table->string('stripe_subscription_id')->nullable()->index()->after('stripe_customer_id');
        $table->string('stripe_status')->nullable()->after('stripe_subscription_id');
        $table->timestamp('trial_ends_at')->nullable()->after('stripe_status');
        $table->timestamp('current_period_ends_at')->nullable()->after('trial_ends_at');
        $table->timestamp('cancel_at')->nullable()->after('current_period_ends_at');
        $table->timestamp('canceled_at')->nullable()->after('cancel_at');
    });
}

public function down(): void
{
    Schema::table('tenants', function (Blueprint $table) {
        $table->dropColumn([
            'status',
            'billing_email',
            'stripe_customer_id',
            'stripe_subscription_id',
            'stripe_status',
            'trial_ends_at',
            'current_period_ends_at',
            'cancel_at',
            'canceled_at',
        ]);
    });
}
};
