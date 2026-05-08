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
    Schema::create('customer_subscriptions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('tenant_id')
            ->constrained('tenants')
            ->cascadeOnDelete();

        $table->foreignId('customer_id')
            ->constrained('customers')
            ->cascadeOnDelete();

        $table->foreignId('customer_plan_id')
            ->constrained('customer_plans')
            ->restrictOnDelete();

        $table->string('status')->default('active');
        // active, expired, canceled, suspended

        $table->date('starts_at');
        $table->date('ends_at')->nullable();

        $table->decimal('price_snapshot', 10, 2)->default(0);

        $table->integer('max_downloads_snapshot')->nullable();
        $table->integer('max_companies_snapshot')->nullable();

        $table->timestamps();

        $table->index(['tenant_id', 'customer_id']);
        $table->index(['tenant_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('customer_subscriptions');
}
};
