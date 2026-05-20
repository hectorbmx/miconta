<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // CIEC auth
            $table->string('ciec_password', 255)->nullable()->after('fiel_password');

            // CSD (Certificado de Sello Digital) - para timbrado, diferente a FIEL
            $table->string('csd_certificate_path', 255)->nullable()->after('ciec_password');
            $table->string('csd_private_key_path', 255)->nullable()->after('csd_certificate_path');
            $table->text('csd_password')->nullable()->after('csd_private_key_path');

            // Método de auth preferido para el scraper
            $table->enum('sat_auth_method', ['ciec', 'fiel'])->default('fiel')->after('csd_password');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'ciec_password',
                'csd_certificate_path',
                'csd_private_key_path',
                'csd_password',
                'sat_auth_method',
            ]);
        });
    }
};