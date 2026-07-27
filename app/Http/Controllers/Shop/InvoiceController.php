<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;

class InvoiceController extends Controller
{

    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;
        $shop_name = $rec->name;
        $invoices = Invoice::where('shop_id', $shop_id)
                           ->orderBy('created_at', 'desc')
                           ->get();

    // Calculate total sales till now
    $totalSalesTillNow = Invoice::where('shop_id', $shop_id)->sum('final_bill');

    // Calculate total sales for the current month
    $totalSalesThisMonth = Invoice::where('shop_id', $shop_id)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->sum('final_bill');

        return view('shop.layouts.invoices', [
            'rec' => $rec,
            'shop_id' => $shop_id,
            'shop_name' => $shop_name,
            'invoices' => $invoices,
            'totalSalesTillNow' => $totalSalesTillNow,
            'totalSalesThisMonth' => $totalSalesThisMonth
        ]);
    }

    public function getInvoiceDetails(Request $request, $id)
    {
        // Fetch the invoice by its ID, including related sales and products
        $invoice = Invoice::with('sales.product')->findorFail($id);

        // Shop users may only view invoices that belong to their own shop.
        // Admin/superadmin (resolved by PanelAuthMiddleware on the api.php
        // routes) are not restricted here. When this method is hit via the
        // shop.php web route (no panel_role attribute set), fall back to
        // checking the logged-in shop session directly.
        $panelRole = $request->attributes->get('panel_role');
        $shopUser = Auth::guard('web')->user();

        if (($panelRole === 'shop' || ($panelRole === null && $shopUser)) && $shopUser) {
            if ((int) $invoice->shop_id !== (int) $shopUser->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        // Return the invoice data with sales and products
        return response()->json($invoice);
    }

    /**
     * Store a new invoice and associated sales.
     */
    public function store(Request $request)
    {
        // shop_id always comes from the authenticated session, never from
        // client input — a shop user must not be able to write invoices
        // against another shop's id.
        $shopId = Auth::guard('web')->id();

        // Validate incoming request data
        $validated = $request->validate([
            'products' => 'required|array', // Expect an array of products
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'total_bill' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'final_bill' => 'required|numeric',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'customer_info' => 'nullable|string|max:500',
            
        ]);

        // Create the invoice
        $invoice = Invoice::create([
            'shop_id' => $shopId,
            'total_bill' => $validated['total_bill'],
            'discount' => $validated['discount'] ?? 0,
            'final_bill' => $validated['final_bill'],
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_info' => $validated['customer_info'] ?? null,
        ]);

        // Process each sale item (product)
        foreach ($validated['products'] as $productData) {
            // Create sale record
            Sale::create([
                'product_id' => $productData['id'],
                'shop_id' => $shopId,
                'sale_date' => now(),
                'sale_price' => $productData['price'],
                'invoice_id' => $invoice->id,
                'quantity' => $productData['quantity'],
                'total_price' => $productData['price'] * $productData['quantity'],
            ]);

            // Update product inventory
            $product = Product::findOrFail($productData['id']);
            $product->qty -= $productData['quantity']; // Reduce stock
            $product->sold_qty += $productData['quantity']; // Increase sold quantity
            $product->save();
        }

        return response()->json(['message' => 'Invoice and sales created successfully', 'invoice_id' => $invoice->id], 201);
    }



    public function getInvoiceDetailsWithWarranty(Request $request, $id)
    {
        // Fetch the invoice by its ID, including related sales and products
        $invoice = Invoice::with('sales.product')->findOrFail($id);
    
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Shop users may only view warranty details for their own invoices.
        $panelRole = $request->attributes->get('panel_role');
        $shopUser = Auth::guard('web')->user();

        if (($panelRole === 'shop' || ($panelRole === null && $shopUser)) && $shopUser) {
            if ((int) $invoice->shop_id !== (int) $shopUser->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        // Current date for warranty validation
        $currentDate = Carbon::now();

        $customer_name = $invoice->customer_name;
        $customer_phone = $invoice->customer_phone;
        $sale_date = $invoice->created_at->setTimezone('Asia/Dubai')->format('F j, Y - g:i A');
    
        $salesWithWarranty = $invoice->sales->map(function ($sale) use ($currentDate) {
            $product = $sale->product;
            $saleDate = Carbon::parse($sale->sale_date);
            $warrantyStatus = 'N/A';
            $warrantyEndDate = null;
    
            // Check if warranty duration is not null
            if ($product->warranty_duration !== null && $product->warranty_duration > 0) {
                $warrantyEndDate = match ($product->warranty_unit) {
                    0 => $saleDate->addDays($product->warranty_duration),   // Days
                    1 => $saleDate->addMonths($product->warranty_duration), // Months
                    2 => $saleDate->addYears($product->warranty_duration),  // Years
                    default => null,
                };
    
                // Determine warranty status
                if ($warrantyEndDate) {
                    $warrantyStatus = $currentDate <= $warrantyEndDate ? 'Valid' : 'Expired';
                }
            }
    
            return [
                'id' => $product->id,
                'name' => $product->name,
                'warranty_unit' => $product->warranty_unit, // 0 = Day, 1 = Month, 2 = Year
                'warranty_duration' => $product->warranty_duration,
                'warranty_end_date' => $warrantyEndDate ? $warrantyEndDate->format('Y-m-d') : null,
                'quantity' => $sale->quantity,
                'warranty_status' => $warrantyStatus,
            ];
        });
    
        return response()->json(['invoice_id' => $invoice->id, 'products' => $salesWithWarranty, 'customer_name' => $customer_name, 'customer_phone' => $customer_phone, 'sale_date' => $sale_date]);
    }

    public function storeClaim(Request $request)
    {
        // shop_id always comes from the authenticated session, never from
        // client input.
        $shopId = Auth::guard('web')->id();

        $validated = $request->validate([
            'claims' => 'required|array',
            'claims.*.product_id' => 'required|integer|exists:products,id',
            'claims.*.invoice_id' => 'required|integer|exists:invoices,id',
            'claims.*.quantity' => 'required|integer|min:1', // Validate quantity
        ]);

        foreach ($validated['claims'] as $claim) {
            // Confirm the invoice actually belongs to this shop before
            // allowing a claim to be filed against it.
            $invoiceBelongsToShop = \DB::table('invoices')
                ->where('id', $claim['invoice_id'])
                ->where('shop_id', $shopId)
                ->exists();

            if (!$invoiceBelongsToShop) {
                return response()->json([
                    'message' => "Invoice ID {$claim['invoice_id']} does not belong to your shop.",
                ], 403);
            }

            // Fetch the total purchased quantity for the product in the invoice
            $purchasedQuantity = \DB::table('sales')
                ->where('invoice_id', $claim['invoice_id'])
                ->where('product_id', $claim['product_id'])
                ->sum('quantity');

            // Fetch the total claimed quantity for the product in the invoice
            $claimedQuantity = \DB::table('claims')
                ->where('invoice_id', $claim['invoice_id'])
                ->where('product_id', $claim['product_id'])
                ->sum('quantity');

            // Calculate remaining claimable quantity
            $remainingQuantity = $purchasedQuantity - $claimedQuantity;

            // Check if the requested claim quantity exceeds the remaining claimable quantity
            if ($claim['quantity'] > $remainingQuantity) {
                return response()->json([
                    'message' => "Cannot claim more than the remaining quantity ({$remainingQuantity}) for product ID: {$claim['product_id']}.",
                ], 422);
            }

            // Insert claim into the claims table
            \DB::table('claims')->insert([
                'product_id' => $claim['product_id'],
                'invoice_id' => $claim['invoice_id'],
                'shop_id' => $shopId,
                'quantity' => $claim['quantity'], // Save claimed quantity
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update the product stock and sold quantity
            \DB::table('products')
                ->where('id', $claim['product_id'])
                ->update([
                    'qty' => \DB::raw("qty - {$claim['quantity']}"), // Decrease stock
                    'sold_qty' => \DB::raw("sold_qty + {$claim['quantity']}"), // Increase sold quantity
                ]);
        }

        return response()->json(['message' => 'Claims saved successfully'], 201);
    }

}