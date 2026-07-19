<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\ShopAdmin;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::guard('web')->check()) {
            return redirect('shop/dashboard')->with('success', 'Already Logged In');
        }
        return view('shop.login');
    }

    public function login_validate(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find the shop by email
        $shop = User::where('email', $credentials['email'])->first();

        if (!$shop) {
            return redirect()->back()->with('error', 'No account found with this email.');
        }

        // Check if blocked by super admin
        if ($shop->status) {
            return redirect()->back()->with('error', 'You have been blocked by the super admin.');
        }

        // Check if blocked by admin
        if ($shop->blocked_by_admin) {
            return redirect()->back()->with('error', 'You have been blocked by the admin.');
        }

        // Check if shop has an assigned admin
        $shopAdminRelation = ShopAdmin::where('shop_id', $shop->id)->first();
        if (!$shopAdminRelation) {
            return redirect()->back()->with('error', 'This shop has no admin assigned. Please contact the Super Admin.');
        }

        // Check if the assigned admin is blocked
        $admin = Admin::find($shopAdminRelation->admin_id);
        if ($admin && $admin->status) {
            return redirect()->back()->with('error', 'Your shop admin has been blocked by the super admin.');
        }

        // Attempt login using hashed password (Laravel handles bcrypt comparison)
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('shop/dashboard')->with('success', 'You have successfully logged in');
        }

        return redirect()->back()->with('error', 'Login credentials are incorrect.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        return redirect('shop/login')->with('success', 'You have been logged out successfully');
    }
}
