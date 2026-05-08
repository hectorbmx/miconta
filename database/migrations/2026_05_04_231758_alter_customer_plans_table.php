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
    Schema::table('customer_plans', function (Blueprint $table) {

        // 🔥 agregar slug
        $table->string('slug')->after('name');

        // 🔥 estandarizar naming
        $table->renameColumn('billing_cycle', 'billing_period');

        // 🔥 unique por tenant
        $table->unique(['tenant_id', 'slug']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
