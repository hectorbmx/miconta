<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->updateOrInsert(
            ['slug' => 'prueba-gratis'],
            [
                'name' => 'Prueba gratis',
                'price' => 0,
                'currency' => 'MXN',
                'billing_period' => 'monthly',
                'billing_mode' => 'manual',
                'max_users' => 1,
                'max_customers' => 3,
                'stripe_price_id' => null,
                'is_active' => true,
                'description' => 'Plan gratuito para nuevos clientes.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('plans')
            ->where('slug', 'prueba-gratis')
            ->where('price', 0)
            ->delete();
    }
};
