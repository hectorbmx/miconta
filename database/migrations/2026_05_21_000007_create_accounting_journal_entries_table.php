<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_journal_id')->constrained('accounting_journals')->cascadeOnDelete();
            $table->foreignId('accounting_account_id')->constrained('accounting_accounts')->restrictOnDelete();
            $table->foreignId('sat_cfdi_id')->nullable()->constrained('sat_cfdis')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['accounting_journal_id', 'accounting_account_id'], 'aje_journal_account_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entries');
    }
};
