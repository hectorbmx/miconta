<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_plans', function (Blueprint $table) {
            $table->string('billing_mode', 20)->default('manual')->after('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('customer_plans', function (Blueprint $table) {
            $table->dropColumn('billing_mode');
        });
    }
};
