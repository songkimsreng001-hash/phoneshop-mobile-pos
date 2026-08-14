<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [];

        $rec = Auth::guard('superadmin')->user();
        $data['rec'] = $rec;

        $admins = Admin::all();
        $data['Admins'] = $admins;

        $shops = User::all();
        $data['Shops'] = $shops;

        // ── Stat cards: Sales / Products / Orders / Customers ──────────────
        $data['totalSales']     = (float) Invoice::sum('final_bill');
        $data['totalProducts']  = Product::count();
        $data['totalOrders']    = Invoice::count();
        $data['totalCustomers'] = DB::table('customers')->count();

        $data['salesDelta']     = $this->weekOverWeekDelta(fn ($from, $to) => Invoice::whereBetween('created_at', [$from, $to])->sum('final_bill'));
        $data['ordersDelta']    = $this->weekOverWeekDelta(fn ($from, $to) => Invoice::whereBetween('created_at', [$from, $to])->count());
        $data['productsDelta']  = $this->weekOverWeekDelta(fn ($from, $to) => Product::whereBetween('created_at', [$from, $to])->count());
        $data['customersDelta'] = $this->weekOverWeekDelta(fn ($from, $to) => DB::table('customers')->whereBetween('created_at', [$from, $to])->count());

        // ── Monthly bar chart: Sales vs Orders vs New Customers (last 12 months) ──
        $months = collect(range(11, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        $salesByMonth = Invoice::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(final_bill) as total")
            ->groupBy('ym')->pluck('total', 'ym');
        $ordersByMonth = Invoice::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')->pluck('total', 'ym');
        $customersByMonth = DB::table('customers')->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')->pluck('total', 'ym');

        $data['chartLabels']    = $months->map->format('M')->values();
        $data['chartSales']     = $months->map(fn ($m) => (float) ($salesByMonth[$m->format('Y-m')] ?? 0))->values();
        $data['chartOrders']    = $months->map(fn ($m) => (int) ($ordersByMonth[$m->format('Y-m')] ?? 0))->values();
        $data['chartCustomers'] = $months->map(fn ($m) => (int) ($customersByMonth[$m->format('Y-m')] ?? 0))->values();

        // ── Order status donut (invoices.status: completed | voided | refunded) ──
        $data['statusCompleted'] = Invoice::where('status', 'completed')->count();
        $data['statusVoided']    = Invoice::where('status', 'voided')->count();
        $data['statusRefunded']  = Invoice::where('status', 'refunded')->count();

        // ── Recent orders / top shops ──────────────────────────────────
        $data['recentInvoices'] = Invoice::with('shop')->latest()->take(5)->get();
        $data['topShops'] = User::withCount('products')->orderByDesc('products_count')->take(5)->get();

        return view('superadmin.layouts.dashboard', $data);
    }

    /**
     * Compares this-week vs last-week totals for a given metric callback
     * and returns a signed percentage change (0 when there's no baseline).
     */
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
