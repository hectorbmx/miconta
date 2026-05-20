<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_cfdi_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sat_cfdi_id')->constrained('sat_cfdis')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->enum('tipo', ['xml', 'pdf', 'cancel_request', 'cancel_voucher']);
            $table->string('disk', 50)->default('local');  // local, s3, etc.
            $table->string('path', 500);                   // ruta en el disco
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->boolean('is_valid')->default(true);    // false si el archivo está corrupto

            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['sat_cfdi_id', 'tipo']); // un archivo por tipo por CFDI
            $table->index(['customer_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_cfdi_files');
    }
};