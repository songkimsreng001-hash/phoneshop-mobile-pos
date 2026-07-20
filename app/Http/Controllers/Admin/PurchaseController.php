<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ShopAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index($shop_id)
    {
        $rec = Auth::guard('admin')->user();
        $isAuthorized = $rec->canAccessShop((int) $shop_id);

        if (! $isAuthorized) {
            return redirect()->route('admin.shops')->with('error', 'Unauthorized access or shop does not exist.');
        }

        $purchases = Purchase::where('shop_id', $shop_id)->with('supplier')->orderByDesc('created_at')->get();
        $suppliers = Supplier::where('is_active', 1)->orderBy('name')->get();
        $products = Product::where('shop_id', $shop_id)->orderBy('name')->get();

        return view('admin.layouts.purchases', compact('rec', 'purchases', 'suppliers', 'products', 'shop_id'));
    }

    public function store(Request $request)
    {
        $shopId = $request->input('shop_id');
        $purchase = Purchase::create([
            'reference_no' => 'PO-' . now()->format('YmdHis'),
            'shop_id' => $shopId,
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date ?? now()->toDateString(),
            'status' => 'received',
            'payment_status' => 'paid',
            'subtotal' => $request->subtotal ?? 0,
            'discount' => $request->discount ?? 0,
            'tax' => $request->tax ?? 0,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'grand_total' => $request->grand_total ?? 0,
            'amount_paid' => $request->grand_total ?? 0,
            'amount_due' => 0,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        foreach ($request->products ?? [] as $item) {
            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'unit_price' => $item['unit_price'] ?? $item['unit_cost'],
                'subtotal' => ($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0),
            ]);

            $product = Product::find($item['product_id']);
            if ($product) {
                $product->qty = ($product->qty ?? 0) + ($item['quantity'] ?? 0);
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Purchase created successfully.');
    }
}
