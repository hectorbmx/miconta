<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('state', 100)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('state');
            $table->string('postal_code', 5)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'state',
                'city',
                'postal_code',
            ]);
        });
    }
};
