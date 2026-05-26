<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Stripe\CustomerStripeWebhookController;

use App\Http\Controllers\Client\StripeConnectController;
use App\Http\Controllers\Client\CustomerPlanController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\BillingController;
use App\Http\Controllers\Client\ConfigurationController as ClientConfigurationController;
use App\Http\Controllers\Client\UserController as ClientUserController;
use App\Http\Controllers\Client\AccountingAccountController;
use App\Http\Controllers\Client\AccountingJournalController;
use App\Http\Controllers\Client\AccountingThirdPartyController;
use App\Http\Controllers\Client\ClienteController as ClientClienteController;
use App\Http\Controllers\Client\Sat\SatDownloadRequestController;
use App\Http\Controllers\Client\Sat\SatCfdiController;

use App\Http\Controllers\Client\Sat\CsfController;
use App\Http\Controllers\Client\Sat\SatCsfRequestController;
use App\Http\Controllers\Client\Sat\SatComplianceOpinionRequestController;



/*

|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::post('/stripe/customer-webhook', [CustomerStripeWebhookController::class, 'handle'])
    ->name('stripe.customer-webhook');
Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/stripe/webhook', [TenantController::class, 'webhook']);

Route::middleware(['auth', 'verified'])->group(function () {

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->tenant_id) {
        return redirect()->route('client.dashboard');
    }

    if ($user?->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    abort(403, 'Tu usuario no tiene permisos para acceder al sistema.');
})->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

    // Route::prefix('admin')->name('admin.')->group(function () {

    //     Route::resource('clientes', ClienteController::class);

    //     Route::resource('planes', PlanController::class);
    //     Route::resource('tenants', TenantController::class);

    //     Route::get('configuracion', function () {
    //         return view('admin.configuracion.index');
    //     })->name('configuracion.index');

    // Route::get('tenants/{tenant}/subscribe', [TenantController::class, 'subscribe'])->name('tenants.subscribe');
    // Route::post('tenants/{tenant}/resend-invitation', [TenantController::class, 'resendInvitation'])->name('tenants.resendInvitation');
    // });
    Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('clientes', ClienteController::class);
        Route::resource('planes', PlanController::class);
        Route::resource('tenants', TenantController::class);

        Route::get('configuracion', function () {
            return view('admin.configuracion.index');
        })->name('configuracion.index');

        Route::get('tenants/{tenant}/subscribe', [TenantController::class, 'subscribe'])->name('tenants.subscribe');
        Route::post('tenants/{tenant}/resend-invitation', [TenantController::class, 'resendInvitation'])->name('tenants.resendInvitation');
    });
//rutas clientes
   Route::middleware(['auth', 'verified'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('billing/pending', [BillingController::class, 'pending'])->name('billing.pending');
        Route::get('billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    });

   Route::middleware(['auth', 'verified'])
    ->middleware('tenant.saas.active')
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

        Route::resource('clientes', ClientClienteController::class)
            ->parameters(['clientes' => 'customer'])
            ->names('clientes');

        Route::resource('customer-plans', CustomerPlanController::class)
            ->names('customer-plans');

        Route::resource('users', ClientUserController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('permission:tenant.manage_users')
            ->names('users');

        Route::get('configuracion', [ClientConfigurationController::class, 'index'])
            ->name('configuracion.index');

        Route::post('clientes/{customer}/assign-plan', [ClientClienteController::class, 'assignPlan'])
            ->name('clientes.assign-plan');

        Route::post('clientes/{customer}/subscriptions/{subscription}/manual-payment', [ClientClienteController::class, 'registerManualPayment'])
            ->name('clientes.subscriptions.manual-payment');

        Route::post('clientes/{customer}/accounting-accounts/seed', [AccountingAccountController::class, 'seed'])
            ->name('clientes.accounting-accounts.seed');
        Route::post('clientes/{customer}/accounting-accounts', [AccountingAccountController::class, 'store'])
            ->name('clientes.accounting-accounts.store');
        Route::patch('clientes/{customer}/accounting-accounts/{account}/toggle', [AccountingAccountController::class, 'toggle'])
            ->name('clientes.accounting-accounts.toggle');

        Route::get('clientes/{customer}/polizas', [AccountingJournalController::class, 'index'])
            ->name('clientes.accounting-journals.index');
        Route::get('clientes/{customer}/reportes-contables', [AccountingJournalController::class, 'reports'])
            ->name('clientes.accounting-journals.reports');
        Route::post('clientes/{customer}/accounting-journals', [AccountingJournalController::class, 'store'])
            ->name('clientes.accounting-journals.store');
        Route::get('clientes/{customer}/accounting-journals/{journal}/edit', [AccountingJournalController::class, 'edit'])
            ->name('clientes.accounting-journals.edit');
        Route::patch('clientes/{customer}/accounting-journals/{journal}', [AccountingJournalController::class, 'update'])
            ->name('clientes.accounting-journals.update');
        Route::patch('clientes/{customer}/accounting-journals/{journal}/post', [AccountingJournalController::class, 'post'])
            ->name('clientes.accounting-journals.post');

        Route::get('clientes/{customer}/terceros', [AccountingThirdPartyController::class, 'index'])
            ->name('clientes.third-parties.index');
        Route::post('clientes/{customer}/terceros/sync', [AccountingThirdPartyController::class, 'sync'])
            ->name('clientes.third-parties.sync');
        Route::patch('clientes/{customer}/terceros/{thirdParty}', [AccountingThirdPartyController::class, 'update'])
            ->name('clientes.third-parties.update');

        Route::get('/stripe/connect', [StripeConnectController::class, 'connect'])
            ->name('stripe.connect');
    });
// Rutas SAT
Route::middleware(['auth', 'verified'])
    ->middleware('tenant.saas.active')
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::prefix('sat')
            ->name('sat.')
            ->group(function () {

                // Solicitudes de descarga
                Route::resource('download-requests', SatDownloadRequestController::class)
                    ->only(['index', 'create', 'store', 'show']);

                // Iniciar proceso de descarga (query → verify → download)
                Route::post('download-requests/{downloadRequest}/process', [SatDownloadRequestController::class, 'process'])
                    ->name('download-requests.process');

                // CFDIs
                Route::get('cfdis', [SatCfdiController::class, 'index'])
                    ->name('cfdis.index');
                Route::post('cfdis/{cfdi}/accounting-journal', [SatCfdiController::class, 'generateAccountingJournal'])
                    ->name('cfdis.accounting-journal');
                Route::get('cfdis/{cfdi}', [SatCfdiController::class, 'show'])->name('cfdis.show');
                Route::get('cfdis/{cfdi}/json', [SatCfdiController::class, 'json'])->name('cfdis.json');

            });
    });
Route::middleware(['auth', 'verified'])
    ->middleware('tenant.saas.active')
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::prefix('sat')
            ->name('sat.')
            ->group(function () {

                // ... tus rutas existentes ...

                // CSF - agregar aquí
                
                Route::post('csf/consultar', [CsfController::class, 'consultarPorRfc'])->name('csf.consultar');
                Route::post('csf/pdf', [CsfController::class, 'consultarDesdePdf'])->name('csf.pdf');
                Route::resource('download-requests', SatDownloadRequestController::class);
                Route::post('download-requests/{downloadRequest}/process',[SatDownloadRequestController::class, 'process'])->name('download-requests.process');
                Route::get('customers/{customer}/csf',[SatCsfRequestController::class, 'index'])->name('csf.index');
                Route::post('customers/{customer}/csf',[SatCsfRequestController::class, 'store'])->name('csf.store');
                Route::get('customers/{customer}/csf/{satCsfRequest}',[SatCsfRequestController::class, 'show'])->name('csf.show');
                Route::get('customers/{customer}/csf/{satCsfRequest}/download-pdf',[SatCsfRequestController::class, 'downloadPdf'])->name('csf.download-pdf');
                Route::get('customers/{customer}/compliance-opinions', [SatComplianceOpinionRequestController::class, 'index'])->name('compliance-opinions.index');
                Route::post('customers/{customer}/compliance-opinions', [SatComplianceOpinionRequestController::class, 'store'])->name('compliance-opinions.store');
                Route::get('customers/{customer}/compliance-opinions/{satComplianceOpinionRequest}/download-pdf', [SatComplianceOpinionRequestController::class, 'downloadPdf'])->name('compliance-opinions.download-pdf');


            });
    });

require __DIR__.'/auth.php';
