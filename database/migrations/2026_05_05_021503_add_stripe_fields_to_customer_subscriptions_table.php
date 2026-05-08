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
    Schema::table('customer_subscriptions', function (Blueprint $table) {
        $table->string('stripe_checkout_session_id')->nullable()->after('status');
        $table->string('stripe_subscription_id')->nullable()->after('stripe_checkout_session_id');
        $table->string('stripe_payment_status')->nullable()->after('stripe_subscription_id');
        $table->timestamp('paid_at')->nullable()->after('stripe_payment_status');
    });
}

public function down(): void
{
    Schema::table('customer_subscriptions', function (Blueprint $table) {
        $table->dropColumn([
            'stripe_checkout_session_id',
            'stripe_subscription_id',
            'stripe_payment_status',
            'paid_at',
        ]);
    });
}
};
