<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingThirdParty;
use App\Models\Customer;
use App\Models\SatCfdi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingJournalController extends Controller
{
    public function index(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $activeAccountingAccounts = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $query = AccountingJournal::with(['entries.account', 'entries.cfdi'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('number', 'like', '%' . $request->q . '%')
                    ->orWhere('concept', 'like', '%' . $request->q . '%')
                    ->orWhereHas('entries', function ($entryQuery) use ($request) {
                        $entryQuery->where('reference', 'like', '%' . $request->q . '%')
                            ->orWhere('description', 'like', '%' . $request->q . '%');
                    })
                    ->orWhereHas('entries.cfdi', function ($cfdiQuery) use ($request) {
                        $cfdiQuery->where('uuid', 'like', '%' . $request->q . '%');
                    });
            });
        }

        $statsBase = AccountingJournal::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id);

        $stats = [
            'total' => (clone $statsBase)->count(),
            'draft' => (clone $statsBase)->where('status', 'draft')->count(),
            'posted' => (clone $statsBase)->where('status', 'posted')->count(),
            'debit' => (clone $statsBase)->where('status', 'posted')->sum('total_debit'),
            'credit' => (clone $statsBase)->where('status', 'posted')->sum('total_credit'),
        ];

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $journals = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('client.clientes.polizas.index', [
            'customer' => $customer,
            'cliente' => $customer,
            'activeAccountingAccounts' => $activeAccountingAccounts,
            'journals' => $journals,
            'stats' => $stats,
            'perPage' => $perPage,
        ]);
    }

    public function reports(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->endOfYear()->format('Y-m-d'));

        $accounts = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->orderBy('code')
            ->get();

        $selectedAccount = null;

        if ($request->filled('account_id')) {
            $selectedAccount = $accounts->firstWhere('id', (int) $request->account_id);
        }

        $journalQuery = AccountingJournal::with(['entries.account', 'entries.cfdi'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('status', 'posted')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('number');

        $journals = $journalQuery->get();

        $entryQuery = AccountingJournalEntry::with(['journal', 'account', 'cfdi'])
            ->whereHas('journal', function ($query) use ($customer, $dateFrom, $dateTo) {
                $query->where('tenant_id', auth()->user()->tenant_id)
                    ->where('customer_id', $customer->id)
                    ->where('status', 'posted')
                    ->whereBetween('date', [$dateFrom, $dateTo]);
            });

        if ($selectedAccount) {
            $entryQuery->where('accounting_account_id', $selectedAccount->id);
        }

        $auxiliaryEntries = $entryQuery
            ->orderBy(
                AccountingJournal::select('date')
                    ->whereColumn('accounting_journals.id', 'accounting_journal_entries.accounting_journal_id')
            )
            ->orderBy('id')
            ->paginate(25, ['*'], 'aux_page')
            ->withQueryString();

        $periodEntries = AccountingJournalEntry::with('account')
            ->whereHas('journal', function ($query) use ($customer, $dateFrom, $dateTo) {
                $query->where('tenant_id', auth()->user()->tenant_id)
                    ->where('customer_id', $customer->id)
                    ->where('status', 'posted')
                    ->whereBetween('date', [$dateFrom, $dateTo]);
            })
            ->get()
            ->groupBy('accounting_account_id');

        $priorEntries = AccountingJournalEntry::with('account')
            ->whereHas('journal', function ($query) use ($customer, $dateFrom) {
                $query->where('tenant_id', auth()->user()->tenant_id)
                    ->where('customer_id', $customer->id)
                    ->where('status', 'posted')
                    ->whereDate('date', '<', $dateFrom);
            })
            ->get()
            ->groupBy('accounting_account_id');

        $trialBalance = $accounts
            ->map(function (AccountingAccount $account) use ($periodEntries, $priorEntries) {
                $period = $periodEntries->get($account->id, collect());
                $prior = $priorEntries->get($account->id, collect());

                $priorDebit = (float) $prior->sum('debit');
                $priorCredit = (float) $prior->sum('credit');
                $periodDebit = (float) $period->sum('debit');
                $periodCredit = (float) $period->sum('credit');

                $initialBalance = $account->nature === 'credit'
                    ? $priorCredit - $priorDebit
                    : $priorDebit - $priorCredit;

                $finalBalance = $account->nature === 'credit'
                    ? $initialBalance + $periodCredit - $periodDebit
                    : $initialBalance + $periodDebit - $periodCredit;

                return [
                    'account' => $account,
                    'initial_balance' => round($initialBalance, 2),
                    'debit' => round($periodDebit, 2),
                    'credit' => round($periodCredit, 2),
                    'final_balance' => round($finalBalance, 2),
                ];
            })
            ->filter(fn ($row) => $row['initial_balance'] != 0.0 || $row['debit'] != 0.0 || $row['credit'] != 0.0 || $row['final_balance'] != 0.0)
            ->values();

        return view('client.clientes.polizas.reports', [
            'customer' => $customer,
            'cliente' => $customer,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'journals' => $journals,
            'auxiliaryEntries' => $auxiliaryEntries,
            'trialBalance' => $trialBalance,
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:income,expense,diary'],
            'concept' => ['required', 'string', 'max:255'],
            'entries' => ['required', 'array'],
            'entries.*.accounting_account_id' => ['nullable', 'integer'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
            'entries.*.debit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.credit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.reference' => ['nullable', 'string', 'max:255'],
        ]);

        $entries = $this->normalizeEntries($validated['entries'], $customer);
        $totals = $this->calculateTotals($entries);

        if (count($entries) < 2) {
            throw ValidationException::withMessages([
                'entries' => 'La poliza necesita al menos dos movimientos.',
            ]);
        }

        if (abs($totals['debit'] - $totals['credit']) > 0.009) {
            throw ValidationException::withMessages([
                'entries' => 'La poliza no cuadra: el debe y el haber deben ser iguales.',
            ]);
        }

        DB::transaction(function () use ($customer, $validated, $entries, $totals) {
            $journal = AccountingJournal::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $customer->id,
                'created_by' => auth()->id(),
                'number' => $this->nextNumber($customer, $validated['type'], $validated['date']),
                'type' => $validated['type'],
                'date' => Carbon::parse($validated['date']),
                'concept' => $validated['concept'],
                'status' => 'draft',
                'source' => 'manual',
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
            ]);

            foreach ($entries as $entry) {
                $journal->entries()->create($entry);
            }
        });

        return redirect()
            ->route('client.clientes.accounting-journals.index', $customer)
            ->with('success', 'Poliza contable creada en borrador.');
    }

    public function edit(Customer $customer, AccountingJournal $journal)
    {
        $this->authorizeCustomer($customer);
        $this->authorizeJournal($customer, $journal);

        if ($journal->status !== 'draft') {
            return redirect()
                ->route('client.clientes.accounting-journals.index', $customer)
                ->with('error', 'Solo se pueden editar polizas en borrador.');
        }

        $journal->load(['entries.account', 'entries.cfdi']);
        $learningContext = $this->learningContextFor($customer, $journal);

        $activeAccountingAccounts = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('client.clientes.polizas.edit', [
            'customer' => $customer,
            'cliente' => $customer,
            'journal' => $journal,
            'activeAccountingAccounts' => $activeAccountingAccounts,
            'learningContext' => $learningContext,
        ]);
    }

    public function update(Request $request, Customer $customer, AccountingJournal $journal)
    {
        $this->authorizeCustomer($customer);
        $this->authorizeJournal($customer, $journal);

        if ($journal->status !== 'draft') {
            return redirect()
                ->route('client.clientes.accounting-journals.index', $customer)
                ->with('error', 'Solo se pueden editar polizas en borrador.');
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:income,expense,diary'],
            'concept' => ['required', 'string', 'max:255'],
            'entries' => ['required', 'array'],
            'entries.*.accounting_account_id' => ['nullable', 'integer'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
            'entries.*.debit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.credit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.reference' => ['nullable', 'string', 'max:255'],
            'entries.*.sat_cfdi_id' => ['nullable', 'integer'],
            'save_third_party_default' => ['nullable', 'boolean'],
            'third_party_rfc' => ['nullable', 'string', 'max:13'],
            'third_party_type' => ['nullable', 'in:client,supplier'],
        ]);

        $entries = $this->normalizeEntries($validated['entries'], $customer);
        $totals = $this->calculateTotals($entries);

        if (count($entries) < 2) {
            throw ValidationException::withMessages([
                'entries' => 'La poliza necesita al menos dos movimientos.',
            ]);
        }

        if (abs($totals['debit'] - $totals['credit']) > 0.009) {
            throw ValidationException::withMessages([
                'entries' => 'La poliza no cuadra: el debe y el haber deben ser iguales.',
            ]);
        }

        DB::transaction(function () use ($customer, $journal, $validated, $entries, $totals, $request) {
            $journal->update([
                'type' => $validated['type'],
                'date' => Carbon::parse($validated['date']),
                'concept' => $validated['concept'],
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
            ]);

            $journal->entries()->delete();

            foreach ($entries as $entry) {
                $journal->entries()->create($entry);
            }

            if ($request->boolean('save_third_party_default')) {
                $this->saveThirdPartyDefaultFromEntries($customer, $validated, $entries);
            }
        });

        return redirect()
            ->route('client.clientes.accounting-journals.index', $customer)
            ->with('success', "Poliza {$journal->number} actualizada correctamente.");
    }

    public function post(Customer $customer, AccountingJournal $journal)
    {
        $this->authorizeCustomer($customer);
        abort_if($journal->tenant_id !== auth()->user()->tenant_id || $journal->customer_id !== $customer->id, 403);

        if ($journal->status !== 'draft') {
            return back()->with('error', 'Solo se pueden contabilizar polizas en borrador.');
        }

        if (abs((float) $journal->total_debit - (float) $journal->total_credit) > 0.009 || $journal->entries()->count() < 2) {
            return back()->with('error', 'La poliza no cuadra o no tiene movimientos suficientes.');
        }

        $journal->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        return back()->with('success', 'Poliza contabilizada correctamente.');
    }

    private function normalizeEntries(array $rawEntries, Customer $customer): array
    {
        $entries = [];
        $accountIds = AccountingAccount::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        foreach ($rawEntries as $index => $entry) {
            $accountId = $entry['accounting_account_id'] ?? null;
            $debit = round((float) ($entry['debit'] ?? 0), 2);
            $credit = round((float) ($entry['credit'] ?? 0), 2);

            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            if (! $accountId || ! in_array((int) $accountId, $accountIds, true)) {
                throw ValidationException::withMessages([
                    "entries.{$index}.accounting_account_id" => 'Selecciona una cuenta activa de este cliente.',
                ]);
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "entries.{$index}.debit" => 'Un movimiento no puede tener debe y haber al mismo tiempo.',
                ]);
            }

            $entries[] = [
                'accounting_account_id' => (int) $accountId,
                'sat_cfdi_id' => ! empty($entry['sat_cfdi_id']) ? (int) $entry['sat_cfdi_id'] : null,
                'description' => $entry['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'reference' => $entry['reference'] ?? null,
            ];
        }

        return $entries;
    }

    private function calculateTotals(array $entries): array
    {
        return [
            'debit' => round(array_sum(array_column($entries, 'debit')), 2),
            'credit' => round(array_sum(array_column($entries, 'credit')), 2),
        ];
    }

    private function nextNumber(Customer $customer, string $type, string $date): string
    {
        $prefix = [
            'income' => 'ING',
            'expense' => 'EGR',
            'diary' => 'DIA',
        ][$type] ?? 'POL';

        $base = $prefix . '-' . Carbon::parse($date)->format('Ym');
        $next = AccountingJournal::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('number', 'like', $base . '-%')
            ->count() + 1;

        return sprintf('%s-%04d', $base, $next);
    }

    private function authorizeCustomer(Customer $customer): void
    {
        abort_if($customer->tenant_id !== auth()->user()->tenant_id, 403);
    }

    private function authorizeJournal(Customer $customer, AccountingJournal $journal): void
    {
        abort_if($journal->tenant_id !== auth()->user()->tenant_id || $journal->customer_id !== $customer->id, 403);
    }

    private function learningContextFor(Customer $customer, AccountingJournal $journal): ?array
    {
        $cfdi = $journal->entries
            ->map(fn ($entry) => $entry->cfdi)
            ->filter()
            ->first();

        if (! $cfdi) {
            return null;
        }

        $isIssued = strtoupper((string) $cfdi->rfc_emisor) === strtoupper((string) $customer->rfc)
            || $cfdi->tipo_descarga === 'emitidas';

        $rfc = strtoupper((string) ($isIssued ? $cfdi->rfc_receptor : $cfdi->rfc_emisor));
        $name = $isIssued ? $cfdi->razon_social_receptor : $cfdi->razon_social_emisor;
        $type = $isIssued ? 'client' : 'supplier';
        $principalEntry = $this->principalEntryForLearning($journal, $type);

        $thirdParty = AccountingThirdParty::with('defaultAccount')
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('rfc', $rfc)
            ->first();

        return [
            'rfc' => $rfc,
            'name' => $name ?: 'Sin razon social',
            'type' => $type,
            'type_label' => $type === 'client' ? 'cliente' : 'proveedor',
            'current_account' => $thirdParty?->defaultAccount
                ? $thirdParty->defaultAccount->code . ' - ' . $thirdParty->defaultAccount->name
                : 'Sin cuenta asignada',
            'principal_account_id' => $principalEntry?->accounting_account_id,
        ];
    }

    private function principalEntryForLearning(AccountingJournal $journal, string $type): ?AccountingJournalEntry
    {
        if ($type === 'client') {
            return $journal->entries
                ->filter(fn ($entry) => (float) $entry->credit > 0 && in_array($entry->account?->type, ['income'], true))
                ->first()
                ?: $journal->entries->filter(fn ($entry) => (float) $entry->credit > 0)->sortByDesc('credit')->first();
        }

        return $journal->entries
            ->filter(fn ($entry) => (float) $entry->debit > 0 && in_array($entry->account?->type, ['expense', 'cost'], true))
            ->first()
            ?: $journal->entries->filter(fn ($entry) => (float) $entry->debit > 0)->sortByDesc('debit')->first();
    }

    private function saveThirdPartyDefaultFromEntries(Customer $customer, array $validated, array $entries): void
    {
        $rfc = strtoupper((string) ($validated['third_party_rfc'] ?? ''));
        $type = $validated['third_party_type'] ?? null;

        if (! $rfc || ! in_array($type, ['client', 'supplier'], true)) {
            return;
        }

        $candidate = collect($entries)
            ->filter(function ($entry) use ($type) {
                $account = AccountingAccount::find($entry['accounting_account_id']);

                if (! $account) {
                    return false;
                }

                return $type === 'client'
                    ? (float) $entry['credit'] > 0 && $account->type === 'income'
                    : (float) $entry['debit'] > 0 && in_array($account->type, ['expense', 'cost'], true);
            })
            ->sortByDesc(fn ($entry) => $type === 'client' ? (float) $entry['credit'] : (float) $entry['debit'])
            ->first();

        if (! $candidate) {
            $candidate = collect($entries)
                ->filter(fn ($entry) => $type === 'client' ? (float) $entry['credit'] > 0 : (float) $entry['debit'] > 0)
                ->sortByDesc(fn ($entry) => $type === 'client' ? (float) $entry['credit'] : (float) $entry['debit'])
                ->first();
        }

        if (! $candidate) {
            return;
        }

        $account = AccountingAccount::where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->find($candidate['accounting_account_id']);

        if (! $account) {
            return;
        }

        $cfdi = SatCfdi::where('customer_id', $customer->id)
            ->where(function ($query) use ($rfc) {
                $query->whereRaw('UPPER(rfc_emisor) = ?', [$rfc])
                    ->orWhereRaw('UPPER(rfc_receptor) = ?', [$rfc]);
            })
            ->latest('fecha_emision')
            ->first();

        $name = $type === 'client'
            ? ($cfdi?->razon_social_receptor ?: null)
            : ($cfdi?->razon_social_emisor ?: null);

        AccountingThirdParty::updateOrCreate(
            [
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'rfc' => $rfc,
            ],
            [
                'name' => $name,
                'type' => $type,
                'default_account_id' => $account->id,
                'is_active' => true,
            ]
        );
    }
}
