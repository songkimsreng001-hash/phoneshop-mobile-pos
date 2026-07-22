<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|unique:suppliers,email',
            'phone'        => 'required|string|max:50',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:255',
            'country'      => 'nullable|string|max:255',
            'tax_number'   => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $supplier = Supplier::create($request->only([
            'name', 'company_name', 'email', 'phone', 'address',
            'city', 'country', 'tax_number', 'opening_balance', 'notes',
        ]) + ['is_active' => $request->boolean('is_active', true)]);

        return response()->json($supplier, 201);
    }

    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $supplier->update($request->only([
            'name', 'company_name', 'email', 'phone', 'address',
            'city', 'country', 'tax_number', 'notes',
        ]));
        $supplier->is_active = $request->boolean('is_active', $supplier->is_active);
        $supplier->save();

        return response()->json($supplier);
    }

    public function destroy(string $id)
    {
        Supplier::findOrFail($id)->delete();

        return response()->json(['message' => 'Supplier deleted.']);
    }
}
