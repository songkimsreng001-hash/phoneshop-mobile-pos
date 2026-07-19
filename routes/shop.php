<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\LoginController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\InventoryController;
use App\Http\Controllers\Shop\PosController;
use App\Http\Controllers\Shop\InvoiceController;
use App\Http\Controllers\Shop\WarrantyController;
use App\Http\Controllers\Shop\ClaimsController;

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
    Route::get('claims', [ClaimsController::class, 'index'])->name('shop.claims.show');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('shop.invoices.index');
});
