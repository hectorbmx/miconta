<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'accounting_journal_id',
        'accounting_account_id',
        'sat_cfdi_id',
        'description',
        'debit',
        'credit',
        'reference',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'accounting_journal_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function cfdi()
    {
        return $this->belongsTo(SatCfdi::class, 'sat_cfdi_id');
    }
}
