<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Report\ReportsController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ClaimsController;

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

    Route::controller(\App\Http\Controllers\Admin\PurchaseController::class)->group(function () {
        Route::get('/shops/{shop_id}/purchases', 'index')->name('admin.purchases.index');
        Route::post('/shops/purchases/store', 'store')->name('admin.purchases.store');
    });

    Route::get('/shops/{shop_id}/invoices', [InvoiceController::class, 'index'])->name('admin.invoice.show');
    Route::get('/shops/{shop_id}/claims', [ClaimsController::class, 'index'])->name('admin.claims.show');
});
