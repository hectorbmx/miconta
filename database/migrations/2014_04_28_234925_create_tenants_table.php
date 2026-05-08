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
    Schema::create('tenants', function (Blueprint $table) {
        $table->id();
        $table->string('name');         // Nombre de la empresa (Cliente de tu SaaS)
        $table->string('domain')->unique()->nullable(); // Por si quieres usar subdominios
        $table->string('plan')->default('free');        // Plan de suscripción
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
