<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_cfdi_pagos', function (Blueprint $table) {
            $table->id();

            // Relación principal
            $table->foreignId('sat_cfdi_id')
                ->constrained('sat_cfdis')
                ->cascadeOnDelete();

            // Snapshot para búsquedas/auditoría
            $table->string('cfdi_uuid', 50)->nullable()->index();

            // Datos del pago (Complemento Pago 2.0)
            $table->dateTime('fecha_pago');
            $table->string('forma_pago_p', 5)->nullable(); // clave SAT: 01, 02, 03...
            $table->string('moneda_p', 10)->default('MXN');
            $table->decimal('tipo_cambio_p', 18, 6)->nullable();
            $table->decimal('monto', 18, 6);

            // Datos bancarios
            $table->string('num_operacion')->nullable();
            $table->string('rfc_emisor_cta_ord')->nullable();
            $table->string('nom_banco_ord_ext')->nullable();
            $table->string('cta_ordenante')->nullable();
            $table->string('rfc_emisor_cta_ben')->nullable();
            $table->string('cta_beneficiario')->nullable();

            // Documento relacionado (factura que se está pagando)
            $table->string('id_documento', 50)->nullable(); // UUID de la factura pagada
            $table->string('serie_dr')->nullable();
            $table->string('folio_dr')->nullable();
            $table->string('moneda_dr', 10)->nullable();
            $table->decimal('tipo_cambio_dr', 18, 6)->nullable();
            $table->decimal('num_parcialidad', 5, 0)->nullable();
            $table->decimal('imp_saldo_ant', 18, 6)->nullable();
            $table->decimal('imp_pagado', 18, 6)->nullable();
            $table->decimal('imp_saldo_insoluto', 18, 6)->nullable();
            $table->string('objeto_impuesto_dr', 10)->nullable();

            // Impuestos del pago
            $table->json('impuestos_p_json')->nullable();

            // Extra
            $table->json('meta_json')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['sat_cfdi_id']);
            $table->index('fecha_pago');
            $table->index('id_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_cfdi_pagos');
    }
};