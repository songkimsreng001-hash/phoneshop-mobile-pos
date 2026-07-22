<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Claim;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClaimController extends Controller
{
    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;
        $shop_name = $rec->name;
        $products = Product::where('shop_id', $shop_id)->get();

        return view('shop.layouts.claims', ['rec' => $rec, 'products' => $products, 'shop_id' => $shop_id, 'shop_name' => $shop_name]);
    }

    public function getClaimsByShop($shop_id)
    {
        $claims = DB::table('claims')
            ->join('products', 'claims.product_id', '=', 'products.id')
            ->where('claims.shop_id', $shop_id)
            ->select('claims.id', 'products.name as product_name', 'claims.invoice_id', 'claims.quantity', 'claims.created_at')
            ->get();

        return response()->json($claims);
    }

    /**
     * Register a new claim against a product on an invoice.
     * Validates that the invoice belongs to this shop and that the
     * claimed quantity does not exceed what was actually sold on it.
     */
    public function store(Request $request)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'invoice_id' => 'required|integer|exists:invoices,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Confirm the invoice belongs to this shop
        $invoiceBelongsToShop = DB::table('invoices')
            ->where('id', $request->invoice_id)
            ->where('shop_id', $shop_id)
            ->exists();

        if (!$invoiceBelongsToShop) {
            return response()->json(['message' => 'This invoice does not belong to your shop.'], 403);
        }

        // Confirm the product was actually sold on this invoice
        $purchasedQuantity = Sale::where('invoice_id', $request->invoice_id)
            ->where('product_id', $request->product_id)
            ->sum('quantity');

        if ($purchasedQuantity <= 0) {
            return response()->json(['message' => 'This product was not sold on the given invoice.'], 422);
        }

        $alreadyClaimed = Claim::where('invoice_id', $request->invoice_id)
            ->where('product_id', $request->product_id)
            ->sum('quantity');

        $remaining = $purchasedQuantity - $alreadyClaimed;

        if ($request->quantity > $remaining) {
            return response()->json([
                'message' => "Cannot claim more than the remaining claimable quantity ({$remaining}).",
            ], 422);
        }

        $claim = Claim::create([
            'product_id' => $request->product_id,
            'invoice_id' => $request->invoice_id,
            'shop_id'    => $shop_id,
            'quantity'   => $request->quantity,
        ]);

        return response()->json(['message' => 'Claim submitted successfully.', 'claim' => $claim], 201);
    }
}
