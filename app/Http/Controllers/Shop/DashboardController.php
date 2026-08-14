<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect()->route('shop.login')->with('error', 'Please log in to access this page.');
        }

        $shopId = $user->id;
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $completedInvoices = Invoice::where('shop_id', $shopId)->where('status', 'completed');
        $monthInvoices = (clone $completedInvoices)->whereBetween('created_at', [$monthStart, $monthEnd]);

        $revenue = (float) (clone $completedInvoices)->sum('final_bill');
        $monthlyRevenue = (float) (clone $monthInvoices)->sum('final_bill');
        $monthlyPaid = (float) (clone $monthInvoices)->sum('amount_paid');
        $outstanding = (float) (clone $completedInvoices)
            ->selectRaw('COALESCE(SUM(GREATEST(final_bill - amount_paid, 0)), 0) as total')
            ->value('total');

        $productsCount = Product::where('shop_id', $shopId)->where('isDeleted', false)->count();
        $stockUnits = (int) Product::where('shop_id', $shopId)->where('isDeleted', false)->sum('qty');
        $lowStockCount = Product::where('shop_id', $shopId)
            ->where('isDeleted', false)
            ->whereColumn('qty', '<=', 'reorder_level')
            ->count();
        $salesCount = (clone $completedInvoices)->count();
        $customersCount = Customer::whereHas('invoices', fn ($q) => $q->where('shop_id', $shopId))->count();
        $purchasesCount = Purchase::where('shop_id', $shopId)->count();

        $paymentMethods = (clone $completedInvoices)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount_paid) as amount'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $monthlyChart = collect(range(5, 0))->map(function ($monthsAgo) use ($shopId, $now) {
            $date = $now->copy()->subMonths($monthsAgo);
            return [
                'label' => $date->format('M'),
                'amount' => (float) Invoice::where('shop_id', $shopId)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                    ->sum('final_bill'),
            ];
        })->values();

        $maxChart = max($monthlyChart->max('amount'), 1);

        $recentInvoices = Invoice::where('shop_id', $shopId)
            ->with('customer')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $lowStockProducts = Product::where('shop_id', $shopId)
            ->where('isDeleted', false)
            ->whereColumn('qty', '<=', 'reorder_level')
            ->orderBy('qty')
            ->limit(5)
            ->get();



        return view('shop.layouts.dashboard', compact(
            'user', 'productsCount', 'stockUnits', 'lowStockCount', 'salesCount',
            'customersCount', 'purchasesCount', 'revenue', 'monthlyRevenue',
            'monthlyPaid', 'outstanding', 'paymentMethods', 'monthlyChart',
            'maxChart', 'recentInvoices', 'lowStockProducts'
        ));
    }
}
