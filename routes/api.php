<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\N8N\CustomerCfdiController;
use App\Http\Controllers\Api\N8N\CustomerSatDocumentController;
use App\Http\Controllers\Api\N8N\CustomerTaxSummaryController;
use App\Http\Controllers\Api\WhatsAppClientValidationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('n8n.secret')->post('/whatsapp/validate-client', WhatsAppClientValidationController::class);

Route::middleware('n8n.secret')
    ->prefix('n8n/customers/{customer}')
    ->name('api.n8n.customers.')
    ->group(function () {
        Route::get('/cfdis', [CustomerCfdiController::class, 'index'])->name('cfdis.index');
        Route::get('/cfdis/{uuid}', [CustomerCfdiController::class, 'show'])->name('cfdis.show');
        Route::get('/tax-summary', CustomerTaxSummaryController::class)->name('tax-summary');

        Route::get('/documents/csf/latest', [CustomerSatDocumentController::class, 'latestCsf'])->name('documents.csf.latest');
        Route::post('/documents/csf/request', [CustomerSatDocumentController::class, 'requestCsf'])->name('documents.csf.request');
        Route::get('/documents/csf/{satCsfRequest}/download', [CustomerSatDocumentController::class, 'downloadCsf'])->name('documents.csf.download');

        Route::get('/documents/opinion-32d/latest', [CustomerSatDocumentController::class, 'latestComplianceOpinion'])->name('documents.compliance.latest');
        Route::post('/documents/opinion-32d/request', [CustomerSatDocumentController::class, 'requestComplianceOpinion'])->name('documents.compliance.request');
        Route::get('/documents/opinion-32d/{satComplianceOpinionRequest}/download', [CustomerSatDocumentController::class, 'downloadComplianceOpinion'])->name('documents.compliance.download');
    });
