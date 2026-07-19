<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            // status = true means blocked by super admin
            if ($user->status) {
                Auth::guard('web')->logout();
                return redirect()->route('shop.login')->with('error', 'Your account has been blocked by the Super Admin.');
            }
            // blocked_by_admin = true means blocked by admin
            if ($user->blocked_by_admin) {
                Auth::guard('web')->logout();
                return redirect()->route('shop.login')->with('error', 'Your account has been blocked by the Admin.');
            }
            return $next($request);
        }

        return redirect()->route('shop.login')->with('error', 'Please log in to access this page.');
    }
}
