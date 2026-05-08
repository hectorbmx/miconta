<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_download_requests', function (Blueprint $table) {
            $table->id();

            // Relación con el contribuyente
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Usuario que disparó la solicitud
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // RFC de la FIEL usada
            $table->string('rfc_solicitante', 13);

            // Parámetros de consulta
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');

            // 'emitidas' o 'recibidas'
            $table->string('tipo_descarga', 20);

            // 'cfdi' o 'metadata'
            $table->string('tipo_solicitud', 20)->default('cfdi');

            // ID que regresa el SAT al hacer la solicitud
            $table->string('request_id_sat')->nullable();

            // Paquetes que el SAT pone disponibles para descargar
            $table->json('packages_ids')->nullable();

            // Conteo final de XMLs procesados
            $table->integer('total_xml')->default(0);

            // Flujo de estados
            $table->enum('estado', [
                'pending',
                'querying',
                'verifying',
                'downloading',
                'completed',
                'failed',
            ])->default('pending');

            $table->text('error_message')->nullable();

            // Cuándo terminó de procesarse
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'estado']);
            $table->index(['rfc_solicitante', 'estado']);
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_download_requests');
    }
};