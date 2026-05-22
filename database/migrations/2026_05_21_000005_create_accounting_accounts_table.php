<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->string('type', 30);
            $table->string('nature', 20);
            $table->string('sat_group_code', 30)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_id', 'code']);
            $table->index(['tenant_id', 'customer_id', 'is_active']);
            $table->index('sat_group_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_accounts');
    }
};
