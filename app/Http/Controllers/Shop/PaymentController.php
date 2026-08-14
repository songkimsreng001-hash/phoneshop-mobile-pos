<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        $shopId = Auth::guard('web')->id();

        $invoices = Invoice::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderByDesc('created_at')
            ->get();

        $paidThisMonth = Invoice::where('shop_id', $shopId)
            ->where('amount_paid', '>', 0)
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->sum('amount_paid');

        $outstanding = Invoice::where('shop_id', $shopId)
            ->where('status', 'completed')
            ->sum(DB::raw('GREATEST(final_bill - amount_paid, 0)'));

        return view('shop.layouts.payments', [
            'rec' => Auth::guard('web')->user(),
            'invoices' => $invoices,
            'paidThisMonth' => $paidThisMonth,
            'outstanding' => $outstanding,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|integer|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $shopId = Auth::guard('web')->id();

        $result = DB::transaction(function () use ($request, $shopId) {
            $invoice = Invoice::where('id', $request->integer('invoice_id'))
                ->where('shop_id', $shopId)
                ->where('status', 'completed')
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                return ['error' => 'Invoice not found or does not belong to this shop.', 'status' => 404];
            }

            $currentPaid = (float) $invoice->amount_paid;
            $finalBill = (float) $invoice->final_bill;
            $balance = max($finalBill - $currentPaid, 0);

            if ($balance <= 0) {
                return ['error' => 'This invoice is already fully paid.', 'status' => 422];
            }

            $amount = (float) $request->input('amount');
            if ($amount > $balance) {
                return ['error' => 'Payment cannot be greater than the outstanding balance.', 'status' => 422];
            }

            $newPaid = round($currentPaid + $amount, 2);
            $paymentStatus = $newPaid >= $finalBill ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
            $method = $currentPaid > 0 && $invoice->payment_method !== $request->input('payment_method')
                ? 'mixed'
                : $request->input('payment_method');

            $note = 'Payment ' . number_format($amount, 2) . ' ' . strtoupper($request->input('payment_method'));
            if ($request->input('reference')) {
                $note .= ' Ref: ' . $request->input('reference');
            }
            if ($request->input('notes')) {
                $note .= ' Note: ' . $request->input('notes');
            }

            $invoice->update([
                'payment_method' => $method,
                'payment_status' => $paymentStatus,
                'amount_paid' => $newPaid,
                'change_amount' => 0,
                'customer_info' => trim(($invoice->customer_info ? $invoice->customer_info . "\n" : '') . $note),
            ]);

            if ($invoice->customer_id) {
                Customer::find($invoice->customer_id)?->recalculateTotals();
            }

            return [
                'invoice_id' => $invoice->id,
                'amount_paid' => (float) $invoice->amount_paid,
                'remaining' => max($finalBill - (float) $invoice->amount_paid, 0),
                'payment_status' => $paymentStatus,
            ];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json([
            'message' => 'Payment recorded successfully.',
            ...$result,
        ]);
    }
}
