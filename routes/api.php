<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\InvoiceController;
use App\Http\Controllers\Shop\ClaimController;
use App\Http\Controllers\Report\ReportsController;

// Shop-only: POS checkout and warranty claim submission.
Route::middleware('panel.auth:shop')->group(function () {
    Route::post('/pos/invoice/store', [InvoiceController::class, 'store'])->name('pos.invoice.store');
    Route::post('/warranty/claim/store', [InvoiceController::class, 'storeClaim']);
});

// Shared: any logged-in panel can read invoice/claim data, but the
// controllers enforce that shop users only ever see their own records.
Route::middleware('panel.auth:shop,admin,superadmin')->group(function () {
    Route::get('/invoice/{id}/details', [InvoiceController::class, 'getInvoiceDetails'])->name('api.invoices.details');
    Route::get('/warranty/invoice/{id}', [InvoiceController::class, 'getInvoiceDetailsWithWarranty']);
    Route::get('/claims/{shop_id}', [ClaimController::class, 'getClaimsByShop']);
});

// Admin & SuperAdmin only: cross-shop reporting.
Route::middleware('panel.auth:admin,superadmin')->group(function () {
    Route::get('/reports/data', [ReportsController::class, 'getReportData'])->name('reports.data');
});