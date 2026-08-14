<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level permission gate for the Admin guard.
 *
 * Usage in routes:
 *   Route::post('/settings', ...)->middleware('permission:manage_settings');
 *
 * Super Admin (separate guard) is never routed through this middleware, so it
 * always has full access by construction. Admins are checked against the
 * roles/permissions tables (Admin::hasPermission()).
 */
class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasPermission($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
