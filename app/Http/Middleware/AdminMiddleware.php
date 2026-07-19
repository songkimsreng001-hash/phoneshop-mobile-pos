<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            // status = true means blocked by super admin
            if ($admin->status) {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->with('error', 'Your account has been blocked by the Super Admin.');
            }
            return $next($request);
        }

        return redirect()->route('admin.login')->with('error', 'Admin access required. Please log in.');
    }
}
