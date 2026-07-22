<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(Brand::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:255|unique:brands,name',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country_of_origin'  => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'is_active'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $logoName = null;
        if ($request->hasFile('logo')) {
            $original = pathinfo($request->file('logo')->getClientOriginalName(), PATHINFO_FILENAME);
            $logoName = $original . '_' . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('brands'), $logoName);
        }

        $brand = Brand::create([
            'name'              => $request->name,
            'logo'              => $logoName,
            'country_of_origin' => $request->country_of_origin,
            'description'       => $request->description,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        return response()->json($brand, 201);
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country_of_origin'  => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'is_active'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $brand->name              = $request->name;
        $brand->country_of_origin = $request->country_of_origin;
        $brand->description       = $request->description;
        $brand->is_active         = $request->boolean('is_active', $brand->is_active);

        if ($request->hasFile('logo')) {
            if ($brand->logo && file_exists(public_path('brands/' . $brand->logo))) {
                unlink(public_path('brands/' . $brand->logo));
            }
            $original = pathinfo($request->file('logo')->getClientOriginalName(), PATHINFO_FILENAME);
            $brand->logo = $original . '_' . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('brands'), $brand->logo);
        }

        $brand->save();

        return response()->json($brand);
    }

    public function destroy(string $id)
    {
        Brand::findOrFail($id)->delete();

        return response()->json(['message' => 'Brand deleted.']);
    }
}
