<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingThirdParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'default_account_id',
        'rfc',
        'name',
        'type',
        'is_active',
        'cfdis_count',
        'total_amount',
        'last_cfdi_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_amount' => 'decimal:2',
        'last_cfdi_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function defaultAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'default_account_id');
    }
}
