<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * List categories as a parent/child tree.
     */
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:categories,id',
            'icon'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $category = Category::create([
            'name'        => $request->name,
            'parent_id'   => $request->parent_id,
            'icon'        => $request->icon,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:categories,id|not_in:' . $id,
            'icon'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $category->update($request->only(['name', 'parent_id', 'icon', 'description']));
        $category->is_active = $request->boolean('is_active', $category->is_active);
        $category->save();

        return response()->json($category);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->children()->exists()) {
            return response()->json(['message' => 'Delete or reassign its subcategories first.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
