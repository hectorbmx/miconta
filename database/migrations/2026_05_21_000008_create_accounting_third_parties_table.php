<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_third_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('default_account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->string('rfc', 13);
            $table->string('name')->nullable();
            $table->string('type', 20);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('cfdis_count')->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->dateTime('last_cfdi_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_id', 'rfc']);
            $table->index(['tenant_id', 'customer_id', 'type']);
            $table->index(['tenant_id', 'customer_id', 'default_account_id'], 'atp_customer_account_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_third_parties');
    }
};
