<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Customer;

class PosController extends Controller
{
    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;
        $shop_name = $rec->name;

        // get products that are not deleted
        $products = Product::where('shop_id', $shop_id)->where('isDeleted', 0)->get();

        return view('shop.layouts.pos', ['rec' => $rec, 'products' => $products, 'shop_id' => $shop_id, 'shop_name' => $shop_name]);
    }

    /**
     * Complete a POS sale: creates the invoice, one Sale row per cart
     * item, decrements product stock, logs the stock movement, and
     * (optionally) updates customer loyalty points.
     */
    public function store(Request $request)
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $validator = Validator::make($request->all(), [
            'products'              => 'required|array|min:1',
            'products.*.id'         => 'required|integer|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'discount'              => 'nullable|numeric|min:0',
            'customer_id'           => 'nullable|integer|exists:customers,id',
            'customer_name'         => 'nullable|string|max:255',
            'customer_phone'        => 'nullable|string|max:255',
            'payment_method'        => 'nullable|string|max:50',
            'amount_paid'           => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $cart = $request->input('products');

        // Verify every product belongs to this shop and there's enough stock
        foreach ($cart as $item) {
            $product = Product::where('id', $item['id'])->where('shop_id', $shop_id)->first();
            if (!$product) {
                return response()->json(['message' => "Product #{$item['id']} does not belong to this shop."], 422);
            }
            if ($product->qty < $item['quantity']) {
                return response()->json(['message' => "Not enough stock for {$product->name}. Available: {$product->qty}."], 422);
            }
        }

        $totalBill = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $discount  = $request->input('discount', 0);
        $finalBill = max($totalBill - $discount, 0);
        $amountPaid = $request->input('amount_paid', $finalBill);

        $invoice = null;

        DB::transaction(function () use (&$invoice, $request, $cart, $shop_id, $totalBill, $discount, $finalBill, $amountPaid) {
            $invoice = Invoice::create([
                'shop_id'        => $shop_id,
                'customer_id'    => $request->input('customer_id'),
                'total_bill'     => $totalBill,
                'discount'       => $discount,
                'final_bill'     => $finalBill,
                'customer_name'  => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'payment_method' => $request->input('payment_method', 'cash'),
                'payment_status' => $amountPaid >= $finalBill ? 'paid' : 'partial',
                'amount_paid'    => $amountPaid,
                'change_amount'  => max($amountPaid - $finalBill, 0),
                'status'         => 'completed',
            ]);

            foreach ($cart as $item) {
                $product = Product::where('id', $item['id'])->lockForUpdate()->first();

                Sale::create([
                    'product_id'  => $product->id,
                    'shop_id'     => $shop_id,
                    'sale_date'   => now(),
                    'sale_price'  => $item['price'],
                    'invoice_id'  => $invoice->id,
                    'quantity'    => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                $product->qty -= $item['quantity'];
                $product->sold_qty = ($product->sold_qty ?? 0) + $item['quantity'];
                $product->save();

                Stock::create([
                    'product_id'     => $product->id,
                    'shop_id'        => $shop_id,
                    'quantity'       => -$item['quantity'],
                    'type'           => Stock::TYPE_SALE,
                    'reference_type' => Invoice::class,
                    'reference_id'   => $invoice->id,
                    'notes'          => 'Sold via POS',
                ]);
            }

            if ($request->filled('customer_id')) {
                $customer = Customer::find($request->input('customer_id'));
                if ($customer) {
                    $customer->recalculateTotals();
                }
            }
        });

        return response()->json([
            'message'    => 'Sale completed successfully.',
            'invoice_id' => $invoice->id,
            'final_bill' => $finalBill,
        ], 201);
    }
}
