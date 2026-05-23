<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_compliance_opinion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('rfc', 13);
            $table->string('estado')->default('pending');
            $table->string('pdf_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['rfc', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_compliance_opinion_requests');
    }
};
