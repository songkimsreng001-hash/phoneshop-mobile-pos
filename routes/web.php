<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\LoginController;

// Root redirects to shop login
Route::controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('welcome.login');
});
