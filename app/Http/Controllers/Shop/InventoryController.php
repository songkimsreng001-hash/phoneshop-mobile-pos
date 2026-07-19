<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        $rec = Auth::guard()->user();
        $shop_id = $rec->id;

        // Get products that are not deleted, with related models
        $products = Product::where('shop_id', $shop_id)
            ->where('isDeleted', 0)
            ->with(['brand', 'category', 'supplier'])
            ->get();

        // Pass dropdown options for the Add / Edit modals
        $brands     = Brand::where('is_active', 1)->orderBy('name')->get();
        $categories = Category::where('is_active', 1)->orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', 1)->orderBy('name')->get();

        return view('shop.inventory', compact('rec', 'products', 'shop_id', 'brands', 'categories', 'suppliers'));
    }

    public function storeProduct(Request $request)
    {
        $rules = [
            'id'               => 'required|exists:users,id',
            'name'             => 'required|string|max:255|unique:products,name',
            'sku'              => 'nullable|string|max:100|unique:products,sku',
            'barcode'          => 'nullable|string|max:100',
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description'      => 'nullable|string',
            'warranty_unit'    => 'nullable|integer|min:0',
            'warranty_duration'=> 'nullable|integer|min:0',
            'qty'              => 'required|integer|min:0',
            'sold_qty'         => 'required|integer|min:0',
            'reorder_level'    => 'nullable|integer|min:0',
            'brand_id'         => 'nullable|exists:brands,id',
            'category_id'      => 'nullable|exists:categories,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $shop_id   = $request->id;
        $imageName = null;

        if ($request->hasFile('image')) {
            $originalName = pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName    = $originalName . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('products'), $imageName);
        }

        $product = new Product();
        $product->shop_id          = $shop_id;
        $product->name             = $request->name;
        $product->sku              = $request->sku;
        $product->barcode          = $request->barcode;
        $product->price            = $request->price;
        $product->cost_price       = $request->cost_price;
        $product->description      = $request->description;
        $product->warranty_unit    = $request->warranty_unit;
        $product->warranty_duration= $request->warranty_duration;
        $product->qty              = $request->qty;
        $product->sold_qty         = $request->sold_qty;
        $product->reorder_level    = $request->reorder_level ?? 5;
        $product->brand_id         = $request->brand_id;
        $product->category_id      = $request->category_id;
        $product->supplier_id      = $request->supplier_id;
        if ($imageName) {
            $product->image = $imageName;
        }
        $product->save();

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    public function delete(Request $request)
    {
        $rules = ['id' => 'required|exists:products,id'];

        $messages = [
            'id.required' => 'The ID field is required.',
            'id.exists'   => 'The specified ID does not exist.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $product = Product::find($request->input('id'));

        if ($product) {
            if ($product->image) {
                $imagePath = public_path('products/' . $product->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $product->isDeleted = true;
            $product->save();

            return redirect()->back()->with('success', 'Product deleted successfully.');
        }

        return redirect()->back()->with('error', 'Product not found.');
    }

    public function updateProduct(Request $request)
    {
        $rules = [
            'id'               => 'required|exists:products,id',
            'name'             => 'required|string|max:255|unique:products,name,' . $request->id,
            'sku'              => 'nullable|string|max:100|unique:products,sku,' . $request->id,
            'barcode'          => 'nullable|string|max:100',
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description'      => 'nullable|string',
            'warranty_unit'    => 'nullable|integer|min:0',
            'warranty_duration'=> 'nullable|integer|min:0',
            'qty'              => 'required|integer|min:0',
            'sold_qty'         => 'required|integer|min:0',
            'reorder_level'    => 'nullable|integer|min:0',
            'brand_id'         => 'nullable|exists:brands,id',
            'category_id'      => 'nullable|exists:categories,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
        ];

        $messages = [
            'id.required' => 'The ID field is required.',
            'id.exists'   => 'The specified ID does not exist.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $product = Product::find($request->id);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        $product->name             = $request->input('name');
        $product->sku              = $request->input('sku');
        $product->barcode          = $request->input('barcode');
        $product->price            = $request->input('price');
        $product->cost_price       = $request->input('cost_price');
        $product->description      = $request->input('description');
        $product->warranty_unit    = $request->input('warranty_unit');
        $product->warranty_duration= $request->input('warranty_duration');
        $product->qty              = $request->input('qty');
        $product->sold_qty         = $request->input('sold_qty');
        $product->reorder_level    = $request->input('reorder_level', 5);
        $product->brand_id         = $request->input('brand_id');
        $product->category_id      = $request->input('category_id');
        $product->supplier_id      = $request->input('supplier_id');

        if ($request->hasFile('image')) {
            if ($product->image) {
                $old = public_path('products/' . $product->image);
                if (file_exists($old)) {
                    unlink($old);
                }
            }
            $originalName  = pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName     = $originalName . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('products'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        return redirect()->back()->with('success', 'Product updated successfully.');
    }
}
