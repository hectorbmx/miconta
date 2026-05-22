<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->string('billing_mode', 20)->default('manual')->after('status');
            $table->string('payment_status', 20)->default('pending')->after('stripe_payment_status');
            $table->string('payment_method', 40)->nullable()->after('paid_at');
            $table->decimal('paid_amount', 10, 2)->nullable()->after('payment_method');
            $table->string('payment_reference')->nullable()->after('paid_amount');
            $table->text('payment_notes')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'billing_mode',
                'payment_status',
                'payment_method',
                'paid_amount',
                'payment_reference',
                'payment_notes',
            ]);
        });
    }
};
