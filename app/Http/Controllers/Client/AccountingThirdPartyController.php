<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingThirdParty;
use App\Models\Customer;
use App\Services\Accounting\AccountingThirdPartySyncService;
use Illuminate\Http\Request;

class AccountingThirdPartyController extends Controller
{
    public function index(Request $request, Customer $customer, AccountingThirdPartySyncService $syncService)
    {
        $this->authorizeCustomer($customer);

        if (! AccountingThirdParty::where('tenant_id', auth()->user()->tenant_id)->where('customer_id', $customer->id)->exists()) {
            $syncService->syncFromCfdis($customer);
        }

        $query = AccountingThirdParty::with('defaultAccount')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('configured')) {
            $request->configured === 'yes'
                ? $query->whereNotNull('default_account_id')
                : $query->whereNull('default_account_id');
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('rfc', 'like', '%' . $request->q . '%')
                    ->orWhere('name', 'like', '%' . $request->q . '%');
            });
        }

        $statsBase = AccountingThirdParty::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id);

        $stats = [
            'total' => (clone $statsBase)->count(),
            'clients' => (clone $statsBase)->whereIn('type', ['client', 'both'])->count(),
            'suppliers' => (clone $statsBase)->whereIn('type', ['supplier', 'both'])->count(),
            'configured' => (clone $statsBase)->whereNotNull('default_account_id')->count(),
        ];

        $thirdParties = $query
            ->orderByRaw('default_account_id IS NULL DESC')
            ->orderByDesc('total_amount')
            ->paginate(25)
            ->withQueryString();

        $accounts = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('client.clientes.terceros.index', [
            'customer' => $customer,
            'cliente' => $customer,
            'thirdParties' => $thirdParties,
            'accounts' => $accounts,
            'stats' => $stats,
        ]);
    }

    public function sync(Customer $customer, AccountingThirdPartySyncService $syncService)
    {
        $this->authorizeCustomer($customer);

        $synced = $syncService->syncFromCfdis($customer);

        return redirect()
            ->route('client.clientes.third-parties.index', $customer)
            ->with('success', "Terceros sincronizados desde XML: {$synced}.");
    }

    public function update(Request $request, Customer $customer, AccountingThirdParty $thirdParty)
    {
        $this->authorizeCustomer($customer);
        abort_if($thirdParty->tenant_id !== auth()->user()->tenant_id || $thirdParty->customer_id !== $customer->id, 403);

        $validated = $request->validate([
            'default_account_id' => ['nullable', 'integer', 'exists:accounting_accounts,id'],
            'type' => ['required', 'in:client,supplier,both'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['default_account_id'])) {
            AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->findOrFail($validated['default_account_id']);
        }

        $thirdParty->update([
            'default_account_id' => $validated['default_account_id'] ?? null,
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('client.clientes.third-parties.index', $customer)
            ->with('success', 'Tercero contable actualizado.');
    }

    private function authorizeCustomer(Customer $customer): void
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
