<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = ItemCategory::paginate(20);
        return response()->json($categories);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:item_categories,name',
            'code'        => 'required|string|unique:item_categories,code',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category = ItemCategory::create($data);

        return response()->json($category, 201);
    }

    /**
     * Display a specific category.
     */
    public function show($id)
    {
        $category = ItemCategory::with('items')->findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        $data = $request->validate([
            'name'        => 'sometimes|string|unique:item_categories,name,' . $id,
            'code'        => 'sometimes|string|unique:item_categories,code,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Delete the specified category.
     */
    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
