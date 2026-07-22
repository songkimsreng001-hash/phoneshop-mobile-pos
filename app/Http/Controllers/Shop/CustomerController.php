<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * List customers who have purchased from this shop's invoices.
     */
    public function index()
    {
        $rec = Auth::guard('web')->user();
        $shop_id = $rec->id;

        $customers = Customer::whereHas('invoices', function ($q) use ($shop_id) {
            $q->where('shop_id', $shop_id);
        })->orderBy('name')->get();

        return response()->json($customers);
    }

    /**
     * Create a customer (used from POS for a new walk-in).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Avoid duplicate walk-ins with the same phone number
        if ($request->filled('phone')) {
            $existing = Customer::where('phone', $request->phone)->first();
            if ($existing) {
                return response()->json($existing);
            }
        }

        $customer = Customer::create([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email,
            'address' => $request->address,
        ]);

        return response()->json($customer, 201);
    }

    /**
     * AJAX typeahead: search customers by name or phone for the POS screen.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $customers = Customer::where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit(10)
            ->get(['id', 'name', 'phone', 'loyalty_points']);

        return response()->json($customers);
    }

    public function show(string $id)
    {
        $customer = Customer::with('invoices')->findOrFail($id);

        return response()->json($customer);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $customer->update($request->only(['name', 'phone', 'email', 'address']));

        return response()->json($customer);
    }

    public function destroy(string $id)
    {
        Customer::findOrFail($id)->delete();

        return response()->json(['message' => 'Customer deleted.']);
    }
}
