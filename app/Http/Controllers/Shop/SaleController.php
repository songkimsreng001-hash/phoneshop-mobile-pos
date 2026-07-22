<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleDetail;

class SaleController extends Controller
{
    /**
     * List sales for this shop, optionally filtered by date range and/or product.
     */
    public function index(Request $request)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $query = Sale::where('shop_id', $shop_id)->with('product', 'invoice');

        if ($request->filled('from')) {
            $query->whereDate('sale_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sale_date', '<=', $request->query('to'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        $sales = $query->orderByDesc('sale_date')->paginate(50);

        return response()->json($sales);
    }

    /**
     * A single sale record, plus its line-level detail (if recorded).
     */
    public function show($id)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $sale = Sale::where('shop_id', $shop_id)->with('product', 'invoice')->findOrFail($id);

        $detail = SaleDetail::where('invoice_id', $sale->invoice_id)
            ->where('product_id', $sale->product_id)
            ->first();

        return response()->json([
            'sale'   => $sale,
            'detail' => $detail,
        ]);
    }
}
