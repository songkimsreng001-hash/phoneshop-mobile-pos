<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Show the categories management page as a parent/child tree.
     */
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $parents = Category::orderBy('name')->get();

        return view('shop.layouts.categories', compact('categories', 'parents'));
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
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        Category::create([
            'name'        => $request->name,
            'parent_id'   => $request->parent_id,
            'icon'        => $request->icon,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Category created successfully.');
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
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $category->update($request->only(['name', 'parent_id', 'icon', 'description']));
        $category->is_active = $request->boolean('is_active', $category->is_active);
        $category->save();

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->children()->exists()) {
            return redirect()->back()->with('error', 'Delete or reassign its subcategories first.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }
}
