<?php

use App\Http\Controllers\Shop\CustomerController;
use App\Http\Controllers\Shop\SaleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\LoginController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\InventoryController;
use App\Http\Controllers\Shop\PosController;
use App\Http\Controllers\Shop\InvoiceController;
use App\Http\Controllers\Shop\WarrantyController;
use App\Http\Controllers\Shop\ClaimController;
use App\Http\Controllers\Shop\CategoryController;

Route::controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('shop.home');
    Route::get('/login', 'index')->name('shop.login');
    Route::get('/logout', 'logout')->name('shop.logout');
    Route::post('/submit_login', 'login_validate')->name('shop.login.submit');
});

Route::group(['middleware' => 'user'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('shop.dashboard');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::get('inventory', 'index')->name('shop.inventory.show');
        Route::post('storeProduct', 'storeProduct')->name('shop.products.store');
        Route::post('deleteProduct', 'delete')->name('shop.products.delete');
        Route::post('updateProduct', 'updateProduct')->name('shop.products.update');
    });

    Route::get('pos', [PosController::class, 'index'])->name('shop.pos.show');
    Route::get('warranty', [WarrantyController::class, 'index'])->name('shop.warranty.show');
    Route::get('claims', [ClaimController::class, 'index'])->name('shop.claims.show');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('shop.invoices.index');
    Route::get('/invoices/{id}/details', [InvoiceController::class, 'getInvoiceDetails'])->name('shop.invoices.details');
    Route::get('/invoices/{id}/details-with-warranty', [InvoiceController::class, 'getInvoiceDetailsWithWarranty'])->name('shop.invoices.detailsWithWarranty');

    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('shop.categories.index');
        Route::post('/categories', 'store')->name('shop.categories.store');
        Route::post('/categories/{id}', 'update')->name('shop.categories.update');
        Route::delete('/categories/{id}', 'destroy')->name('shop.categories.destroy');
    });
});

Route::controller(SaleController::class)->group(function () {
    Route::get('/sales', 'index')->name('shop.sales.index');
    Route::get('/sales/{id}', 'show')->name('shop.sales.show');
});

Route::controller(CustomerController::class)->group(function () {
    Route::get('/customers', 'index')->name('shop.customers.index');
    Route::post('/customers', 'store')->name('shop.customers.store');
    Route::get('/customers/search', 'search')->name('shop.customers.search');
});

Route::post('/invoices', [InvoiceController::class, 'store'])->name('shop.invoices.store');
Route::post('/invoices/claim', [InvoiceController::class, 'storeClaim'])->name('shop.invoices.storeClaim');

Route::post('pos/store', [PosController::class, 'store'])->name('shop.pos.store');
Route::post('claims/store', [ClaimController::class, 'store'])->name('shop.claims.store');
Route::post('warranty/store', [WarrantyController::class, 'store'])->name('shop.warranty.store');
Route::get('warranty/check', [WarrantyController::class, 'check'])->name('shop.warranty.check');