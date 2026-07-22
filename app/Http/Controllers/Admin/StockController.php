<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Stock movement ledger for a shop.
     */
    public function index($shop_id)
    {
        $rec = Auth::guard('admin')->user();

        if (!$rec->canAccessShop((int) $shop_id)) {
            return redirect()->route('admin.shops')->with('error', 'Unauthorized access or shop does not exist.');
        }

        $movements = Stock::where('shop_id', $shop_id)
            ->with('product')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($movements);
    }

    /**
     * Manual stock adjustment (adjustment / return / transfer).
     */
    public function store(Request $request)
    {
        $rec = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'shop_id'    => 'required|exists:users,id',
            'quantity'   => 'required|integer|not_in:0',
            'type'       => 'required|in:adjustment,return,transfer',
            'notes'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if (!$rec->canAccessShop((int) $request->shop_id)) {
            return response()->json(['message' => 'Unauthorized access to this shop.'], 403);
        }

        $product = Product::where('id', $request->product_id)->where('shop_id', $request->shop_id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product does not belong to this shop.'], 422);
        }

        if ($product->qty + $request->quantity < 0) {
            return response()->json(['message' => 'Adjustment would result in negative stock.'], 422);
        }

        $movement = Stock::create([
            'product_id' => $request->product_id,
            'shop_id'    => $request->shop_id,
            'quantity'   => $request->quantity,
            'type'       => $request->type,
            'notes'      => $request->notes,
            'created_by' => $rec->id,
        ]);

        $product->qty += $request->quantity;
        $product->save();

        return response()->json(['message' => 'Stock adjusted.', 'movement' => $movement, 'new_qty' => $product->qty], 201);
    }
}
