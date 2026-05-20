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
        Schema::create('sat_csf_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('rfc', 13);

            $table->string('estado')->default('pending');
            // pending, downloading, completed, failed

            $table->string('pdf_path')->nullable();

            $table->json('datos_fiscales')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('downloaded_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['rfc', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sat_csf_requests');
    }
};
