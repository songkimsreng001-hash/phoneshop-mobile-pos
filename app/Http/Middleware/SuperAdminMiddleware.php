<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('superadmin')->check()) {
            return $next($request);
        }

        return redirect()->route('superadmin.login')->with('error', 'Super Admin access required. Please log in.');
    }
}
