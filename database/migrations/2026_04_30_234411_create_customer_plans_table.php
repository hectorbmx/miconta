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
       Schema::create('customer_plans', function (Blueprint $table) {
    $table->id();

    $table->foreignId('tenant_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('name');
    $table->text('description')->nullable();

    $table->decimal('price', 10, 2)->default(0);

    // mensual, anual, único, etc.
    $table->string('billing_cycle')->default('monthly');

    // duración real del plan
    $table->unsignedInteger('duration_days')->nullable();

    // límites del plan
    $table->unsignedInteger('max_downloads')->nullable();
    $table->unsignedInteger('max_companies')->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();

    $table->index(['tenant_id', 'is_active']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_plans');
    }
};
