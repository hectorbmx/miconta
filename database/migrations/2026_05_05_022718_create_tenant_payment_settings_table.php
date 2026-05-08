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
    Schema::create('tenant_payment_settings', function (Blueprint $table) {
        $table->id();

        $table->foreignId('tenant_id')
            ->constrained('tenants')
            ->cascadeOnDelete();

        $table->string('provider')->default('stripe');

        $table->text('stripe_secret_key')->nullable();
        $table->text('stripe_publishable_key')->nullable();
        $table->text('stripe_webhook_secret')->nullable();

        $table->boolean('is_active')->default(false);

        $table->timestamps();

        $table->unique('tenant_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('tenant_payment_settings');
}
};
