<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shopId = $rec->id;

        $products = Product::where('shop_id', $shopId)
            ->where('isDeleted', false)
            ->where('qty', '>', 0)
            ->orderBy('name')
            ->get();

        return view('shop.layouts.pos', [
            'rec' => $rec,
            'products' => $products,
            'shop_id' => $shopId,
            'shop_name' => $rec->name,
        ]);
    }

    /**
     * Complete a POS sale atomically.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products'              => 'required|array|min:1',
            'products.*.id'         => 'required|integer|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'discount'              => 'nullable|numeric|min:0',
            'customer_id'           => 'nullable|integer|exists:customers,id',
            'customer_name'         => 'nullable|string|max:255',
            'customer_phone'        => 'nullable|string|max:255',
            'payment_method'        => 'required|in:cash,card,transfer',
            'amount_paid'           => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $shopId = Auth::guard('web')->id();
        $cart = collect($request->input('products'))
            ->groupBy('id')
            ->map(fn ($items) => [
                'id' => (int) $items->first()['id'],
                'quantity' => $items->sum(fn ($item) => (int) $item['quantity']),
                'price' => (float) $items->last()['price'],
            ])
            ->values();

        try {
            $result = DB::transaction(function () use ($cart, $request, $shopId) {
                $prepared = [];
                $totalBill = 0.0;

                foreach ($cart as $item) {
                    $product = Product::where('id', $item['id'])
                        ->where('shop_id', $shopId)
                        ->where('isDeleted', false)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \RuntimeException("Product #{$item['id']} does not belong to this shop.");
                    }

                    if ($product->qty < $item['quantity']) {
                        throw new \RuntimeException("Not enough stock for {$product->name}. Available: {$product->qty}.");
                    }

                    $requestedPrice = round((float) $item['price'], 2);
                    $basePrice = round((float) $product->price, 2);

                    if ($requestedPrice < $basePrice) {
                        throw new \RuntimeException("Sale price for {$product->name} cannot be below {$basePrice} AED.");
                    }

                    $lineTotal = round($requestedPrice * $item['quantity'], 2);
                    $totalBill += $lineTotal;

                    $prepared[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'price' => $requestedPrice,
                        'line_total' => $lineTotal,
                    ];
                }

                $discount = round((float) $request->input('discount', 0), 2);
                $discount = min($discount, $totalBill);
                $finalBill = round(max($totalBill - $discount, 0), 2);
                $amountPaid = round((float) $request->input('amount_paid'), 2);
                $paymentStatus = $amountPaid >= $finalBill
                    ? 'paid'
                    : ($amountPaid > 0 ? 'partial' : 'unpaid');

                $invoice = Invoice::create([
                    'shop_id'        => $shopId,
                    'customer_id'    => $request->input('customer_id'),
                    'total_bill'     => $totalBill,
                    'discount'       => $discount,
                    'final_bill'     => $finalBill,
                    'customer_name'  => $request->input('customer_name'),
                    'customer_phone' => $request->input('customer_phone'),
                    'payment_method' => $request->input('payment_method'),
                    'payment_status' => $paymentStatus,
                    'amount_paid'    => $amountPaid,
                    'change_amount'  => round(max($amountPaid - $finalBill, 0), 2),
                    'status'         => 'completed',
                ]);

                foreach ($prepared as $item) {
                    $product = $item['product'];

                    Sale::create([
                        'product_id'  => $product->id,
                        'shop_id'     => $shopId,
                        'sale_date'   => now(),
                        'sale_price'  => $item['price'],
                        'invoice_id'  => $invoice->id,
                        'quantity'    => $item['quantity'],
                        'total_price' => $item['line_total'],
                    ]);

                    SaleDetail::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'unit_cost' => $product->cost_price,
                        'discount' => 0,
                        'subtotal' => $item['line_total'],
                        'warranty_months' => $product->warranty_unit === 1 ? $product->warranty_duration : null,
                    ]);

                    $product->decrement('qty', $item['quantity']);
                    $product->increment('sold_qty', $item['quantity']);

                    Stock::create([
                        'product_id'     => $product->id,
                        'shop_id'        => $shopId,
                        'quantity'       => -$item['quantity'],
                        'type'           => Stock::TYPE_SALE,
                        'reference_type' => Invoice::class,
                        'reference_id'   => $invoice->id,
                        'notes'          => 'Sold via POS',
                    ]);
                }

                if ($request->filled('customer_id')) {
                    Customer::find($request->integer('customer_id'))?->recalculateTotals();
                }

                return $invoice;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to complete the sale. Please try again.'], 500);
        }

        return response()->json([
            'message'       => 'Sale completed successfully.',
            'invoice_id'    => $result->id,
            'final_bill'    => (float) $result->final_bill,
            'amount_paid'   => (float) $result->amount_paid,
            'change_amount' => (float) $result->change_amount,
            'payment_status'=> $result->payment_status,
        ], 201);
    }
}
