<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    public function index()
    {
        $data = [];

        $rec = Auth::guard('admin')->user();
        $data['rec'] = $rec;

        $shops = $rec->shops()->get();
        $shopIds = $shops->pluck('id');

        $data['Shops'] = $shops;
        $data['shopsCount'] = $shops->count();
        $data['productsCount'] = DB::table('products')->whereIn('shop_id', $shopIds)->count();
        $data['invoicesCount'] = Invoice::whereIn('shop_id', $shopIds)->count();
        $data['revenue'] = Invoice::whereIn('shop_id', $shopIds)->sum('final_bill');
        $data['monthlyRevenue'] = Invoice::whereIn('shop_id', $shopIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('final_bill');

        return view('admin.layouts.dashboard', $data);
    }

}
