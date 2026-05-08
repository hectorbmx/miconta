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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
        
        // Datos del Contribuyente
        $table->string('rfc', 13)->index(); 
        $table->string('razon_social');
        
        // Archivos y Llaves (Guardaremos la ruta del archivo en el storage)
        $table->string('certificate_path')->nullable(); // Ruta al archivo .cer
        $table->string('private_key_path')->nullable();  // Ruta al archivo .key
        
        // La contraseña NUNCA debe guardarse en texto plano
        // Usaremos el casting de Laravel para encriptarla
        $table->text('fiel_password')->nullable(); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
