<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index($shop_id)
    {
        $rec = Auth::guard('admin')->user();

        if (!$rec->canAccessShop((int) $shop_id)) {
            return redirect()->route('admin.shops')->with('error', 'Unauthorized access or shop does not exist.');
        }

        $products = Product::where('shop_id', $shop_id)
            ->where('isDeleted', 0)
            ->with(['brand', 'category', 'supplier'])
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $rec = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'shop_id'     => 'required|exists:users,id',
            'name'        => 'required|string|max:255',
            'sku'         => 'nullable|string|max:100|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'qty'         => 'required|integer|min:0',
            'brand_id'    => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if (!$rec->canAccessShop((int) $request->shop_id)) {
            return response()->json(['message' => 'Unauthorized access to this shop.'], 403);
        }

        $product = Product::create([
            'shop_id'       => $request->shop_id,
            'name'          => $request->name,
            'sku'           => $request->sku,
            'barcode'       => $request->barcode,
            'price'         => $request->price,
            'cost_price'    => $request->cost_price,
            'description'   => $request->description,
            'qty'           => $request->qty,
            'sold_qty'      => 0,
            'reorder_level' => $request->reorder_level ?? 5,
            'brand_id'      => $request->brand_id,
            'category_id'   => $request->category_id,
            'supplier_id'   => $request->supplier_id,
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request)
    {
        $rec = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'id'          => 'required|exists:products,id',
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'qty'         => 'required|integer|min:0',
            'brand_id'    => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $product = Product::findOrFail($request->id);

        if (!$rec->canAccessShop((int) $product->shop_id)) {
            return response()->json(['message' => 'Unauthorized access to this shop.'], 403);
        }

        $product->update($request->only([
            'name', 'sku', 'barcode', 'price', 'cost_price', 'description',
            'qty', 'reorder_level', 'brand_id', 'category_id', 'supplier_id',
        ]));

        return response()->json($product);
    }

    public function destroy($id)
    {
        $rec = Auth::guard('admin')->user();
        $product = Product::findOrFail($id);

        if (!$rec->canAccessShop((int) $product->shop_id)) {
            return response()->json(['message' => 'Unauthorized access to this shop.'], 403);
        }

        $product->isDeleted = true;
        $product->save();

        return response()->json(['message' => 'Product deleted.']);
    }
}
