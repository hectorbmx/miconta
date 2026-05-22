<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SatDownloadRequest;
use App\Models\SatCfdi;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'rfc',
        'razon_social',
        'email',
        'certificate_path',
        'private_key_path',
        'fiel_password',
        'ciec_password',
    ];

    protected $casts = [
        'fiel_password' => 'encrypted',
        'ciec_password' => 'encrypted',

    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function satDownloadRequests()
    {
        return $this->hasMany(SatDownloadRequest::class);
    }

    public function satCfdis()
    {
        return $this->hasMany(SatCfdi::class);
    }

    public function accountingAccounts()
    {
        return $this->hasMany(AccountingAccount::class);
    }

    public function accountingJournals()
    {
        return $this->hasMany(AccountingJournal::class);
    }

    public function accountingThirdParties()
    {
        return $this->hasMany(AccountingThirdParty::class);
    }
}
