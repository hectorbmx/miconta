<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_cfdis', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('sat_download_request_id')
                ->constrained('sat_download_requests')
                ->cascadeOnDelete();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Identificación
            $table->string('uuid', 50)->unique();
            $table->string('serie', 20)->nullable();
            $table->string('folio', 40)->nullable();

            // Emisor
            $table->string('rfc_emisor', 13)->nullable();
            $table->string('razon_social_emisor')->nullable();
            $table->string('regimen_fiscal_emisor', 10)->nullable();

            // Receptor
            $table->string('rfc_receptor', 13)->nullable();
            $table->string('razon_social_receptor')->nullable();
            $table->string('regimen_fiscal_receptor', 10)->nullable();
            $table->string('uso_cfdi', 10)->nullable();
            $table->string('domicilio_fiscal_receptor', 10)->nullable();

            // Fechas
            $table->dateTime('fecha_emision')->nullable();
            $table->dateTime('fecha_certificacion')->nullable();
            $table->dateTime('fecha_cancelacion')->nullable();

            // Tipo y clasificación
            $table->string('tipo_comprobante', 5)->nullable();  // I, E, T, P, N
            $table->string('tipo_descarga', 20)->nullable();    // emitidas, recibidas
            $table->string('metodo_pago', 5)->nullable();       // PUE, PPD
            $table->string('forma_pago', 5)->nullable();        // 01, 02, 03...
            $table->string('condiciones_pago')->nullable();

            // Moneda
            $table->string('moneda', 10)->default('MXN');
            $table->decimal('tipo_cambio', 18, 6)->nullable();

            // Importes
            $table->decimal('subtotal', 18, 6)->nullable();
            $table->decimal('descuento', 18, 6)->nullable();
            $table->decimal('total_impuestos_trasladados', 18, 6)->nullable();
            $table->decimal('total_impuestos_retenidos', 18, 6)->nullable();
            $table->decimal('total', 18, 6)->nullable();

            // Estado SAT
            $table->string('estado_sat', 20)->default('vigente'); // vigente, cancelado
            $table->string('estatus_cancelacion', 50)->nullable();
            $table->string('motivo_cancelacion', 10)->nullable();
            $table->string('folio_fiscal_sustitucion', 50)->nullable();

            // CFDI relacionados
            $table->string('tipo_relacion', 10)->nullable();
            $table->json('cfdis_relacionados')->nullable();

            // Complementos
            $table->boolean('tiene_complemento_pago')->default(false);
            $table->boolean('tiene_complemento_nomina')->default(false);
            $table->boolean('tiene_complemento_comercio_exterior')->default(false);
            $table->json('complementos_json')->nullable();

            // Archivo
            $table->string('xml_path')->nullable();
            $table->string('package_id')->nullable();

            // Extra
            $table->json('meta_json')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['rfc_emisor', 'rfc_receptor']);
            $table->index('fecha_emision');
            $table->index('tipo_comprobante');
            $table->index('tipo_descarga');
            $table->index('estado_sat');
            $table->index(['customer_id', 'tipo_descarga', 'estado_sat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_cfdis');
    }
};