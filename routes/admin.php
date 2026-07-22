<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Report\ReportsController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\CategoryController;

Route::controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('admin.home');
    Route::get('/login', 'index')->name('admin.login');
    Route::get('/logout', 'logout')->name('admin.logout');
    Route::post('/submit_login', 'login_validate')->name('admin.login.submit');
});

Route::group(['middleware' => 'admin'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('admin.dashboard');
    });

    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');

    Route::controller(ShopController::class)->group(function () {
        Route::get('/shops', 'index')->name('admin.shops');
        Route::post('shop/edit', 'edit')->name('admin.edit.shop');
        Route::post('shop/update-password', 'updatePassword')->name('admin.updatepass.shop');
        Route::post('shop/delete', 'delete')->name('admin.delete.shop');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::get('/shops/{shop_id}/inventory', 'index')->name('admin.inventory.show');
        Route::post('shops/storeProduct', 'storeProduct')->name('admin.shops.products.store');
        Route::post('shops/deleteProduct', 'delete')->name('admin.shops.products.delete');
        Route::post('shops/updateProduct', 'updateProduct')->name('admin.shops.products.update');
    });

    Route::controller(PurchaseController::class)->group(function () {
        Route::get('/shops/{shop_id}/purchases', 'index')->name('admin.purchases.index');
        Route::post('/shops/purchases/store', 'store')->name('admin.purchases.store');
        Route::post('/shops/purchases/{id}/update', 'update')->name('admin.purchases.update');
        Route::delete('/shops/purchases/{id}', 'destroy')->name('admin.purchases.destroy');
    });

    Route::get('/shops/{shop_id}/invoices', [InvoiceController::class, 'index'])->name('admin.invoice.show');
    Route::get('/invoices/{id}/details', [InvoiceController::class, 'getInvoiceDetails'])->name('admin.invoices.details');
    Route::get('/shops/{shop_id}/claims', [ClaimController::class, 'index'])->name('admin.claims.show');

    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('admin.categories.index');
        Route::post('/categories', 'store')->name('admin.categories.store');
        Route::post('/categories/{id}', 'update')->name('admin.categories.update');
        Route::delete('/categories/{id}', 'destroy')->name('admin.categories.destroy');
    });

    Route::controller(ProductController::class)->group(function () {
        Route::get('/shops/{shop_id}/products', 'index')->name('admin.products.index');
        Route::post('/shops/products/store', 'store')->name('admin.products.store');
        Route::post('/shops/products/update', 'update')->name('admin.products.update');
        Route::delete('/shops/products/{id}', 'destroy')->name('admin.products.destroy');
    });

    Route::controller(StockController::class)->group(function () {
        Route::get('/shops/{shop_id}/stock', 'index')->name('admin.stock.index');
        Route::post('/shops/stock/adjust', 'store')->name('admin.stock.adjust');
    });

    Route::controller(SupplierController::class)->group(function () {
        Route::get('/suppliers', 'index')->name('admin.suppliers.index');
        Route::post('/suppliers', 'store')->name('admin.suppliers.store');
        Route::post('/suppliers/{id}', 'update')->name('admin.suppliers.update');
        Route::delete('/suppliers/{id}', 'destroy')->name('admin.suppliers.destroy');
    });

    Route::controller(CustomerController::class)->group(function () {
        Route::get('/customers', 'index')->name('admin.customers.index');
        Route::post('/customers', 'store')->name('admin.customers.store');
        Route::post('/customers/{id}', 'update')->name('admin.customers.update');
        Route::delete('/customers/{id}', 'destroy')->name('admin.customers.destroy');
        Route::get('/customers/{id}', 'show')->name('admin.customers.show');
    });
});