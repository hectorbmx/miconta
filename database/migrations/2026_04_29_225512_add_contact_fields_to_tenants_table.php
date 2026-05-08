<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('rfc', 13)->nullable()->after('name');
            $table->string('phone')->nullable()->after('billing_email');
            $table->string('state')->nullable()->after('phone');
            $table->string('city')->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'rfc',
                'phone',
                'state',
                'city',
            ]);
        });
    }
};