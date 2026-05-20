<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Stripe\CustomerStripeWebhookController;

use App\Http\Controllers\Client\StripeConnectController;
use App\Http\Controllers\Client\CustomerPlanController;
use App\Http\Controllers\Client\ClienteController as ClientClienteController;
use App\Http\Controllers\Client\Sat\SatDownloadRequestController;
use App\Http\Controllers\Client\Sat\SatCfdiController;

use App\Http\Controllers\Client\Sat\CsfController;
use App\Http\Controllers\Client\Sat\SatCsfRequestController;



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
    return view('dashboard');
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
    Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

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

        Route::get('/dashboard', function () {
            return view('client.dashboard');
        })->name('dashboard');

        Route::resource('clientes', ClientClienteController::class)
            ->parameters(['clientes' => 'customer'])
            ->names('clientes');

        Route::resource('customer-plans', CustomerPlanController::class)
            ->names('customer-plans');

        Route::post('clientes/{customer}/assign-plan', [ClientClienteController::class, 'assignPlan'])
            ->name('clientes.assign-plan');

        Route::get('/stripe/connect', [StripeConnectController::class, 'connect'])
            ->name('stripe.connect');
    });
// Rutas SAT
Route::middleware(['auth', 'verified'])
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
                Route::get('cfdis/{cfdi}', [SatCfdiController::class, 'show'])->name('cfdis.show');
                Route::get('cfdis/{cfdi}/json', [SatCfdiController::class, 'json'])->name('cfdis.json');

            });
    });
Route::middleware(['auth', 'verified'])
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


            });
    });

require __DIR__.'/auth.php';
