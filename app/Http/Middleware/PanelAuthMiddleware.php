<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects API routes (routes/api.php) using the panels' existing
 * session-based guards instead of a separate token API.
 *
 * Usage:
 *   Route::middleware('panel.auth')->group(...);              // any logged-in panel
 *   Route::middleware('panel.auth:shop')->group(...);          // shop users only
 *   Route::middleware('panel.auth:admin,superadmin')->group(...); // admin or superadmin
 *
 * On success it stores the resolved role and user on the request so
 * controllers can do ownership checks without re-resolving guards:
 *   $request->attributes->get('panel_role');  // 'shop' | 'admin' | 'superadmin'
 *   $request->attributes->get('panel_user');  // the authenticated model
 */
class PanelAuthMiddleware
{
    /**
     * Role name (as used in route middleware params) => auth guard name.
     */
    protected array $guards = [
        'shop'       => 'web',
        'admin'      => 'admin',
        'superadmin' => 'superadmin',
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = empty($roles) ? array_keys($this->guards) : $roles;

        foreach ($allowedRoles as $role) {
            $guardName = $this->guards[$role] ?? null;

            if (!$guardName || !Auth::guard($guardName)->check()) {
                continue;
            }

            $user = Auth::guard($guardName)->user();

            // Respect the same block flags the page middleware already enforces,
            // so a blocked account can't reach the API even with a live session.
            if ($guardName === 'web' && ($user->status || $user->blocked_by_admin)) {
                continue;
            }
            if ($guardName === 'admin' && $user->status) {
                continue;
            }

            $request->attributes->set('panel_role', $role);
            $request->attributes->set('panel_user', $user);

            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}