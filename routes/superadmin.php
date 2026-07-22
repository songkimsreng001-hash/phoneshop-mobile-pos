<?php

use App\Http\Controllers\SuperAdmin\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\ShopController;
use App\Http\Controllers\SuperAdmin\InventoryController;
use App\Http\Controllers\Report\ReportsController;
use App\Http\Controllers\SuperAdmin\InvoiceController;
use App\Http\Controllers\SuperAdmin\ClaimController;
use App\Http\Controllers\SuperAdmin\PermissionController;
use App\Http\Controllers\SuperAdmin\BrandController;

Route::controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('superadmin.home');
    Route::get('/login', 'index')->name('superadmin.login');
    Route::get('/logout', 'logout')->name('superadmin.logout');
    Route::post('/submit_login', 'login_validate')->name('superadmin.login.submit');
});

Route::group(['middleware' => 'superadmin'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('superadmin.dashboard');
    });

    Route::get('/reports', [ReportsController::class, 'index'])->name('superadmin.reports.index');

    Route::controller(AdminController::class)->group(function () {
        Route::get('/admins', 'index')->name('superadmin.admins');
        Route::post('/admin/add', 'store')->name('superadmin.add.admin');
        Route::post('admin/edit', 'edit')->name('superadmin.edit.admin');
        Route::post('admin/update-password', 'updatePassword')->name('superadmin.updatepass.admin');
        Route::post('admin/delete', 'delete')->name('superadmin.delete.admin');
    });

    Route::controller(ShopController::class)->group(function () {
        Route::get('/shops', 'index')->name('superadmin.shops');
        Route::post('/shop/add', 'store')->name('superadmin.add.shop');
        Route::post('shop/edit', 'edit')->name('superadmin.edit.shop');
        Route::post('shop/update-password', 'updatePassword')->name('superadmin.updatepass.shop');
        Route::post('shop/delete', 'delete')->name('superadmin.delete.shop');
        Route::get('admin/view-admins/{shopId}', 'viewAdmins')->name('superadmin.viewadmins.shop');
        Route::post('shop/add-admin', 'addShopAdmin')->name('superadmin.addShopAdmin.shop');
        Route::delete('shop/delete-admin', 'deleteShopAdmin')->name('superadmin.deleteShopAdmin.shop');
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::get('/shops/{shop_id}/inventory', 'index')->name('superadmin.inventory.show');
        Route::post('shops/storeProduct', 'storeProduct')->name('superadmin.shops.products.store');
        Route::post('shops/deleteProduct', 'delete')->name('superadmin.shops.products.delete');
        Route::post('shops/updateProduct', 'updateProduct')->name('superadmin.shops.products.update');
    });

    Route::get('/shops/{shop_id}/invoices', [InvoiceController::class, 'index'])->name('superadmin.invoice.show');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('superadmin.invoices.index');
    Route::get('/shops/{shop_id}/claims', [ClaimController::class, 'index'])->name('superadmin.claims.show');

    Route::controller(\App\Http\Controllers\SuperAdmin\RoleController::class)->group(function () {
        Route::get('/roles', 'index')->name('superadmin.roles.index');
        Route::post('/roles', 'store')->name('superadmin.roles.store');
        Route::post('/roles/{id}', 'update')->name('superadmin.roles.update');
        Route::post('/roles/assign', 'assignToAdmin')->name('superadmin.roles.assign');
        Route::delete('/roles/{id}', 'destroy')->name('superadmin.roles.destroy');
    });

    Route::controller(PermissionController::class)->group(function () {
        Route::get('/permissions', 'index')->name('superadmin.permissions.index');
        Route::post('/permissions', 'store')->name('superadmin.permissions.store');
        Route::post('/permissions/{id}', 'update')->name('superadmin.permissions.update');
        Route::delete('/permissions/{id}', 'destroy')->name('superadmin.permissions.destroy');
    });

    Route::controller(BrandController::class)->group(function () {
        Route::get('/brands', 'index')->name('superadmin.brands.index');
        Route::post('/brands', 'store')->name('superadmin.brands.store');
        Route::post('/brands/{id}', 'update')->name('superadmin.brands.update');
        Route::delete('/brands/{id}', 'destroy')->name('superadmin.brands.destroy');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('superadmin.categories.index');
        Route::post('/categories', 'store')->name('superadmin.categories.store');
        Route::post('/categories/{id}', 'update')->name('superadmin.categories.update');
        Route::delete('/categories/{id}', 'destroy')->name('superadmin.categories.destroy');
    });
});