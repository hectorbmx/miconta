<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\Customer;
use App\Services\Accounting\AccountingAccountSeederService;
use Illuminate\Http\Request;

class AccountingAccountController extends Controller
{
    public function seed(Customer $customer, AccountingAccountSeederService $seeder)
    {
        $this->authorizeCustomer($customer);

        $created = $seeder->seedBaseCatalog($customer);

        return redirect()
            ->route('client.clientes.show', $customer)
            ->with('success', $created > 0
                ? "Catalogo contable base generado: {$created} cuentas."
                : 'El catalogo contable base ya estaba generado.');
    }

    public function store(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,income,expense,cost,order'],
            'nature' => ['required', 'in:debit,credit'],
            'sat_group_code' => ['nullable', 'string', 'max:30'],
            'parent_id' => ['nullable', 'exists:accounting_accounts,id'],
        ]);

        if (! empty($validated['parent_id'])) {
            $parent = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
                ->where('customer_id', $customer->id)
                ->findOrFail($validated['parent_id']);

            $validated['parent_id'] = $parent->id;
        }

        $exists = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Ya existe una cuenta con ese codigo para este cliente.');
        }

        AccountingAccount::create($validated + [
            'tenant_id' => auth()->user()->tenant_id,
            'customer_id' => $customer->id,
            'is_default' => false,
            'is_active' => true,
        ]);

        return redirect()
            ->route('client.clientes.show', $customer)
            ->with('success', 'Cuenta contable creada correctamente.');
    }

    public function toggle(Customer $customer, AccountingAccount $account)
    {
        $this->authorizeCustomer($customer);
        abort_if($account->tenant_id !== auth()->user()->tenant_id || $account->customer_id !== $customer->id, 403);

        $account->update(['is_active' => ! $account->is_active]);

        return redirect()
            ->route('client.clientes.show', $customer)
            ->with('success', 'Cuenta contable actualizada.');
    }

    private function authorizeCustomer(Customer $customer): void
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
