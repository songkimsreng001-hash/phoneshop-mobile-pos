<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;
        $shop_name = $rec->name;
        $products = Product::where('shop_id', $shop_id)->get();

        return view('shop.layouts.warranty', ['rec' => $rec, 'products' => $products, 'shop_id' => $shop_id, 'shop_name' => $shop_name]);
    }

    /**
     * Register/override the warranty period for a specific product on an
     * invoice. Stores it on the sale_details line (creating one if the
     * sale was only recorded in the legacy `sales` table).
     */
    public function store(Request $request)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $validator = Validator::make($request->all(), [
            'invoice_id'       => 'required|integer|exists:invoices,id',
            'product_id'       => 'required|integer|exists:products,id',
            'warranty_months'  => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $invoice = Invoice::where('id', $request->invoice_id)->where('shop_id', $shop_id)->first();
        if (!$invoice) {
            return response()->json(['message' => 'This invoice does not belong to your shop.'], 403);
        }

        $sale = Sale::where('invoice_id', $request->invoice_id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$sale) {
            return response()->json(['message' => 'This product was not sold on the given invoice.'], 422);
        }

        $saleDetail = SaleDetail::firstOrNew([
            'invoice_id' => $request->invoice_id,
            'product_id' => $request->product_id,
        ]);

        $saleDetail->quantity   = $saleDetail->quantity ?? $sale->quantity;
        $saleDetail->unit_price = $saleDetail->unit_price ?? $sale->sale_price;
        $saleDetail->subtotal   = $saleDetail->subtotal ?? $sale->total_price;
        $saleDetail->warranty_months = $request->warranty_months;
        $saleDetail->save();

        return response()->json(['message' => 'Warranty registered successfully.', 'sale_detail' => $saleDetail], 201);
    }

    /**
     * Look up the warranty status for a product on an invoice.
     */
    public function check(Request $request)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|integer|exists:invoices,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $invoice = Invoice::where('id', $request->invoice_id)->where('shop_id', $shop_id)->first();
        if (!$invoice) {
            return response()->json(['message' => 'This invoice does not belong to your shop.'], 403);
        }

        $sale = Sale::where('invoice_id', $request->invoice_id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$sale) {
            return response()->json(['message' => 'This product was not sold on the given invoice.'], 404);
        }

        $product = Product::find($request->product_id);
        $saleDetail = SaleDetail::where('invoice_id', $request->invoice_id)
            ->where('product_id', $request->product_id)
            ->first();

        $saleDate = Carbon::parse($sale->sale_date);
        $warrantyEndDate = null;
        $status = 'N/A';

        if ($saleDetail && $saleDetail->warranty_months) {
            // Explicitly registered warranty (in months) takes priority
            $warrantyEndDate = $saleDate->copy()->addMonths($saleDetail->warranty_months);
        } elseif ($product && $product->warranty_duration) {
            $warrantyEndDate = match ($product->warranty_unit) {
                0 => $saleDate->copy()->addDays($product->warranty_duration),
                1 => $saleDate->copy()->addMonths($product->warranty_duration),
                2 => $saleDate->copy()->addYears($product->warranty_duration),
                default => null,
            };
        }

        if ($warrantyEndDate) {
            $status = now()->lessThanOrEqualTo($warrantyEndDate) ? 'Valid' : 'Expired';
        }

        return response()->json([
            'invoice_id'        => $invoice->id,
            'product_id'        => $product->id ?? null,
            'product_name'      => $product->name ?? null,
            'sale_date'         => $saleDate->toDateString(),
            'warranty_end_date' => $warrantyEndDate?->toDateString(),
            'warranty_status'   => $status,
        ]);
    }
}
