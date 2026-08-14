<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Role/permission checks for the 3-tier RBAC model:
        // Super Admin (guard: superadmin) -> always true, full access.
        // Admin       (guard: admin)      -> checked against roles/permissions tables.
        // Shop/Cashier(guard: web)        -> fixed capability set, see helpers.php can_shop_user().

        Blade::if('adminCan', function (string $permission) {
            if (Auth::guard('superadmin')->check()) {
                return true; // Super Admin bypasses granular permission checks
            }

            $admin = Auth::guard('admin')->user();

            return $admin && method_exists($admin, 'hasPermission') && $admin->hasPermission($permission);
        });

        // Shop/Cashier guard: a fixed set of actions cashiers are never allowed to do,
        // regardless of any future per-shop-user roles.
        Blade::if('shopCan', function (string $action) {
            $restricted = ['delete_product', 'manage_users', 'manage_settings'];

            if (in_array($action, $restricted, true)) {
                return false;
            }

            return Auth::guard('web')->check();
        });
    }
}
