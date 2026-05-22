<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use App\Models\Customer;

class AccountingAccountSeederService
{
    public function seedBaseCatalog(Customer $customer): int
    {
        $accounts = [
            ['code' => '102.01', 'name' => 'Bancos', 'type' => 'asset', 'nature' => 'debit', 'sat_group_code' => '102.01'],
            ['code' => '105.01', 'name' => 'Clientes nacionales', 'type' => 'asset', 'nature' => 'debit', 'sat_group_code' => '105.01'],
            ['code' => '118.01', 'name' => 'IVA acreditable', 'type' => 'asset', 'nature' => 'debit', 'sat_group_code' => '118.01'],
            ['code' => '201.01', 'name' => 'Proveedores nacionales', 'type' => 'liability', 'nature' => 'credit', 'sat_group_code' => '201.01'],
            ['code' => '208.01', 'name' => 'IVA trasladado', 'type' => 'liability', 'nature' => 'credit', 'sat_group_code' => '208.01'],
            ['code' => '209.01', 'name' => 'IVA retenido', 'type' => 'liability', 'nature' => 'credit', 'sat_group_code' => '209.01'],
            ['code' => '216.01', 'name' => 'ISR retenido', 'type' => 'liability', 'nature' => 'credit', 'sat_group_code' => '216.01'],
            ['code' => '401.01', 'name' => 'Ingresos por servicios', 'type' => 'income', 'nature' => 'credit', 'sat_group_code' => '401.01'],
            ['code' => '501.01', 'name' => 'Costos', 'type' => 'cost', 'nature' => 'debit', 'sat_group_code' => '501.01'],
            ['code' => '601.01', 'name' => 'Gastos generales', 'type' => 'expense', 'nature' => 'debit', 'sat_group_code' => '601.01'],
        ];

        $created = 0;

        foreach ($accounts as $account) {
            $model = AccountingAccount::firstOrCreate(
                [
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                    'code' => $account['code'],
                ],
                $account + [
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            if ($model->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
