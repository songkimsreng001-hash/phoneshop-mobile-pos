<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->route('shop.login')->with('error', 'Please log in to access this page.');
        }

        $products = DB::table('products')->where('shop_id', $user->id)->get();
        $sales = Invoice::where('shop_id', $user->id)->count();
        $purchases = Purchase::where('shop_id', $user->id)->count();
        $stockValue = DB::table('products')->where('shop_id', $user->id)->sum('qty');
        $monthlySales = Invoice::where('shop_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('final_bill');

        $data = [
            'rec' => $user,
            'products' => $products,
            'salesCount' => $sales,
            'purchasesCount' => $purchases,
            'stockValue' => $stockValue,
            'monthlySales' => $monthlySales,
        ];

        return view('shop.layouts.dashboard', $data);
    }

}
