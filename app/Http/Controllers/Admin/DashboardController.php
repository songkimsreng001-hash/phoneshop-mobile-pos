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
use Carbon\Carbon;

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

        // ── Stat cards: Sales / Products / Orders / Customers (scoped to this admin's shops) ──
        $data['totalSales']    = (float) Invoice::whereIn('shop_id', $shopIds)->sum('final_bill');
        $data['totalProducts'] = Product::whereIn('shop_id', $shopIds)->count();
        $data['totalOrders']   = Invoice::whereIn('shop_id', $shopIds)->count();
        $data['totalCustomers'] = DB::table('customers')->count();

        $data['salesDelta']     = $this->weekOverWeekDelta(fn ($from, $to) => Invoice::whereIn('shop_id', $shopIds)->whereBetween('created_at', [$from, $to])->sum('final_bill'));
        $data['ordersDelta']    = $this->weekOverWeekDelta(fn ($from, $to) => Invoice::whereIn('shop_id', $shopIds)->whereBetween('created_at', [$from, $to])->count());
        $data['productsDelta']  = $this->weekOverWeekDelta(fn ($from, $to) => Product::whereIn('shop_id', $shopIds)->whereBetween('created_at', [$from, $to])->count());
        $data['customersDelta'] = $this->weekOverWeekDelta(fn ($from, $to) => DB::table('customers')->whereBetween('created_at', [$from, $to])->count());

        // ── Monthly bar chart: Sales vs Orders vs New Customers (last 12 months) ──
        $months = collect(range(11, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        $salesByMonth = Invoice::whereIn('shop_id', $shopIds)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(final_bill) as total")
            ->groupBy('ym')->pluck('total', 'ym');
        $ordersByMonth = Invoice::whereIn('shop_id', $shopIds)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')->pluck('total', 'ym');
        $customersByMonth = DB::table('customers')->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')->pluck('total', 'ym');

        $data['chartLabels']    = $months->map->format('M')->values();
        $data['chartSales']     = $months->map(fn ($m) => (float) ($salesByMonth[$m->format('Y-m')] ?? 0))->values();
        $data['chartOrders']    = $months->map(fn ($m) => (int) ($ordersByMonth[$m->format('Y-m')] ?? 0))->values();
        $data['chartCustomers'] = $months->map(fn ($m) => (int) ($customersByMonth[$m->format('Y-m')] ?? 0))->values();

        // ── Order status donut ──────────────────────────────────────────
        $data['statusCompleted'] = Invoice::whereIn('shop_id', $shopIds)->where('status', 'completed')->count();
        $data['statusVoided']    = Invoice::whereIn('shop_id', $shopIds)->where('status', 'voided')->count();
        $data['statusRefunded']  = Invoice::whereIn('shop_id', $shopIds)->where('status', 'refunded')->count();

        // ── Recent orders / top products ────────────────────────────────
        $data['recentInvoices'] = Invoice::with('shop')->whereIn('shop_id', $shopIds)->latest()->take(5)->get();
        $data['topProducts'] = Product::whereIn('shop_id', $shopIds)->latest()->take(5)->get();

        return view('admin.layouts.dashboard', $data);
    }

    private function weekOverWeekDelta(\Closure $metric): float
    {
        $thisWeek = (float) $metric(Carbon::now()->startOfWeek(), Carbon::now());
        $lastWeek = (float) $metric(Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek());

        if ($lastWeek <= 0) {
            return $thisWeek > 0 ? 100.0 : 0.0;
        }

        return round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
    }
}
