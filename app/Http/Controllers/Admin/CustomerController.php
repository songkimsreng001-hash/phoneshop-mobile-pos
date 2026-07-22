<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * List customers who have an invoice on any shop this admin manages.
     */
    public function index()
    {
        $rec = Auth::guard('admin')->user();
        $shopIds = $rec->shops()->pluck('users.id');

        $customers = Customer::whereHas('invoices', function ($q) use ($shopIds) {
            $q->whereIn('shop_id', $shopIds);
        })->orderBy('name')->get();

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $customer = Customer::create($request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'notes']));

        return response()->json($customer, 201);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $customer->update($request->only(['name', 'email', 'phone', 'address', 'date_of_birth', 'notes']));

        return response()->json($customer);
    }

    public function destroy(string $id)
    {
        Customer::findOrFail($id)->delete();

        return response()->json(['message' => 'Customer deleted.']);
    }

    /**
     * Customer detail: invoice history and loyalty points.
     */
    public function show($id)
    {
        $customer = Customer::with(['invoices' => function ($q) {
            $q->orderByDesc('created_at');
        }])->findOrFail($id);

        return response()->json($customer);
    }
}
