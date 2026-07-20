<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('admin-panel/dashboard')->with('success', 'Already Logged In');
        }
        return view('admin.layouts.login');
    }

    public function login_validate(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('admin-panel/dashboard')->with('success', 'You have successfully logged in');
        }

        return redirect()->back()->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        return redirect('admin-panel/login')->with('success', 'You have been logged out successfully');
    }
}
