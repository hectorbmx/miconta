<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingThirdParty;
use App\Models\Customer;
use App\Models\SatCfdi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CfdiAccountingJournalService
{
    public function __construct(
        private readonly AccountingAccountSeederService $accountSeeder,
    ) {
    }

    public function createDraftFromCfdi(SatCfdi $cfdi): AccountingJournal
    {
        $cfdi->loadMissing(['customer', 'conceptos', 'journalEntries.journal']);

        if ($cfdi->journalEntries->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cfdi' => 'Este XML ya tiene una poliza generada.',
            ]);
        }

        if ($cfdi->estado_sat !== 'vigente') {
            throw ValidationException::withMessages([
                'cfdi' => 'Solo se pueden generar polizas de XML vigentes.',
            ]);
        }

        if ($cfdi->tipo_comprobante !== 'I') {
            throw ValidationException::withMessages([
                'cfdi' => 'Por ahora solo se generan polizas automaticas de CFDI de ingreso.',
            ]);
        }

        $customer = $cfdi->customer;
        $isIssued = $this->isIssuedByCustomer($cfdi, $customer);
        $accounts = $this->accountsFor($customer);
        $thirdPartyAccount = $this->thirdPartyAccountFor($cfdi, $customer, $isIssued);
        $amounts = $this->amountsFor($cfdi);

        $entries = $isIssued
            ? $this->incomeEntries($cfdi, $accounts, $amounts, $thirdPartyAccount)
            : $this->expenseEntries($cfdi, $accounts, $amounts, $thirdPartyAccount);

        $totals = [
            'debit' => round(array_sum(array_column($entries, 'debit')), 2),
            'credit' => round(array_sum(array_column($entries, 'credit')), 2),
        ];

        if (abs($totals['debit'] - $totals['credit']) > 0.009) {
            throw ValidationException::withMessages([
                'cfdi' => 'No fue posible generar una poliza cuadrada con los importes del XML.',
            ]);
        }

        return DB::transaction(function () use ($cfdi, $customer, $isIssued, $entries, $totals) {
            $journal = AccountingJournal::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'created_by' => auth()->id(),
                'number' => $this->nextNumber($customer, $isIssued ? 'income' : 'expense', $cfdi->fecha_emision),
                'type' => $isIssued ? 'income' : 'expense',
                'date' => $cfdi->fecha_emision ?: now(),
                'concept' => $this->conceptFor($cfdi, $isIssued),
                'status' => 'draft',
                'source' => 'cfdi',
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
            ]);

            foreach ($entries as $entry) {
                $journal->entries()->create($entry + [
                    'sat_cfdi_id' => $cfdi->id,
                    'reference' => $cfdi->uuid,
                ]);
            }

            return $journal;
        });
    }

    private function isIssuedByCustomer(SatCfdi $cfdi, Customer $customer): bool
    {
        return strtoupper((string) $cfdi->rfc_emisor) === strtoupper((string) $customer->rfc)
            || $cfdi->tipo_descarga === 'emitidas';
    }

    private function accountsFor(Customer $customer): array
    {
        $this->accountSeeder->seedBaseCatalog($customer);

        $accounts = AccountingAccount::where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->whereIn('code', [
                '105.01',
                '118.01',
                '201.01',
                '208.01',
                '209.01',
                '216.01',
                '401.01',
                '601.01',
            ])
            ->get()
            ->keyBy('code');

        foreach (['105.01', '118.01', '201.01', '208.01', '209.01', '216.01', '401.01', '601.01'] as $code) {
            if (! $accounts->has($code)) {
                throw ValidationException::withMessages([
                    'cfdi' => "Falta la cuenta contable activa {$code} para generar la poliza.",
                ]);
            }
        }

        return $accounts->all();
    }

    private function amountsFor(SatCfdi $cfdi): array
    {
        $subtotal = round((float) $cfdi->subtotal - (float) $cfdi->descuento, 2);
        $vatTransferred = round((float) $cfdi->total_impuestos_trasladados, 2);
        $vatWithheld = round((float) $cfdi->conceptos->sum('importe_iva_retenido'), 2);
        $isrWithheld = round((float) $cfdi->conceptos->sum('importe_isr_retenido'), 2);
        $total = round((float) $cfdi->total, 2);

        return compact('subtotal', 'vatTransferred', 'vatWithheld', 'isrWithheld', 'total');
    }

    private function incomeEntries(SatCfdi $cfdi, array $accounts, array $amounts, ?AccountingAccount $thirdPartyAccount): array
    {
        $incomeAccount = $thirdPartyAccount ?: $accounts['401.01'];

        $entries = [
            $this->entry($accounts['105.01'], 'Cliente por cobrar', $amounts['total'], 0),
            $this->entry($incomeAccount, $thirdPartyAccount ? 'Ingreso facturado segun tercero' : 'Ingreso facturado', 0, $amounts['subtotal']),
        ];

        if ($amounts['vatTransferred'] > 0) {
            $entries[] = $this->entry($accounts['208.01'], 'IVA trasladado', 0, $amounts['vatTransferred']);
        }

        if ($amounts['vatWithheld'] > 0) {
            $entries[] = $this->entry($accounts['209.01'], 'IVA retenido por cliente', $amounts['vatWithheld'], 0);
        }

        if ($amounts['isrWithheld'] > 0) {
            $entries[] = $this->entry($accounts['216.01'], 'ISR retenido por cliente', $amounts['isrWithheld'], 0);
        }

        return $entries;
    }

    private function expenseEntries(SatCfdi $cfdi, array $accounts, array $amounts, ?AccountingAccount $thirdPartyAccount): array
    {
        $expenseAccount = $thirdPartyAccount ?: $accounts['601.01'];

        $entries = [
            $this->entry($expenseAccount, $thirdPartyAccount ? 'Gasto facturado segun tercero' : 'Gasto facturado', $amounts['subtotal'], 0),
            $this->entry($accounts['201.01'], 'Proveedor por pagar', 0, $amounts['total']),
        ];

        if ($amounts['vatTransferred'] > 0) {
            $entries[] = $this->entry($accounts['118.01'], 'IVA acreditable', $amounts['vatTransferred'], 0);
        }

        if ($amounts['vatWithheld'] > 0) {
            $entries[] = $this->entry($accounts['209.01'], 'IVA retenido a proveedor', 0, $amounts['vatWithheld']);
        }

        if ($amounts['isrWithheld'] > 0) {
            $entries[] = $this->entry($accounts['216.01'], 'ISR retenido a proveedor', 0, $amounts['isrWithheld']);
        }

        return $entries;
    }

    private function entry(AccountingAccount $account, string $description, float $debit, float $credit): array
    {
        return [
            'accounting_account_id' => $account->id,
            'description' => $description,
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }

    private function thirdPartyAccountFor(SatCfdi $cfdi, Customer $customer, bool $isIssued): ?AccountingAccount
    {
        $counterpartyRfc = strtoupper((string) ($isIssued ? $cfdi->rfc_receptor : $cfdi->rfc_emisor));

        if (! $counterpartyRfc) {
            return null;
        }

        $thirdParty = AccountingThirdParty::with('defaultAccount')
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('rfc', $counterpartyRfc)
            ->where('is_active', true)
            ->whereNotNull('default_account_id')
            ->first();

        if (! $thirdParty?->defaultAccount) {
            return null;
        }

        if ($thirdParty->defaultAccount->tenant_id !== $customer->tenant_id || $thirdParty->defaultAccount->customer_id !== $customer->id || ! $thirdParty->defaultAccount->is_active) {
            return null;
        }

        return $thirdParty->defaultAccount;
    }

    private function conceptFor(SatCfdi $cfdi, bool $isIssued): string
    {
        $counterparty = $isIssued
            ? ($cfdi->razon_social_receptor ?: $cfdi->rfc_receptor)
            : ($cfdi->razon_social_emisor ?: $cfdi->rfc_emisor);

        return trim(($isIssued ? 'Venta CFDI ' : 'Gasto CFDI ') . ($cfdi->serie ? $cfdi->serie . '-' : '') . ($cfdi->folio ?: $cfdi->uuid) . ' ' . $counterparty);
    }

    private function nextNumber(Customer $customer, string $type, mixed $date): string
    {
        $prefix = $type === 'income' ? 'ING' : 'EGR';
        $base = $prefix . '-' . Carbon::parse($date ?: now())->format('Ym');
        $next = AccountingJournal::where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->where('number', 'like', $base . '-%')
            ->count() + 1;

        return sprintf('%s-%04d', $base, $next);
    }
}
