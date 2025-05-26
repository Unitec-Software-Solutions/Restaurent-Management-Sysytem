<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use App\Models\Branch;

class ItemMasterController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index()
    {
        $items = ItemMaster::with('category')
            ->when(request('search'), function ($query) {
                return $query->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('item_code', 'like', '%' . request('search') . '%');
            })
            ->when(request('category'), function ($query) {
                return $query->where('item_category_id', request('category'));
            })
            ->when(request()->has('status'), function ($query) {
                return $query->where('is_active', request('status'));
            })
            ->paginate(15);

        $categories = ItemCategory::active()->get();

        return view('admin.inventory.items.index', compact('items', 'categories'));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.unicode_name' => 'nullable|string|max:255',
            'items.*.item_category_id' => 'required|exists:item_categories,id',
            'items.*.item_code' => 'required|string|unique:item_master,item_code',
            'items.*.unit_of_measurement' => 'required|string|max:50',
            'items.*.reorder_level' => 'nullable|numeric|min:0',
            'items.*.is_perishable' => 'nullable|boolean',
            'items.*.shelf_life_in_days' => 'nullable|integer|min:0',
            'items.*.branch_id' => 'nullable|exists:branches,id',
            'items.*.organization_id' => 'nullable|exists:organizations,id',
            'items.*.buying_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.is_menu_item' => 'nullable|boolean',
            'items.*.is_active' => 'nullable|boolean',
            'items.*.additional_notes' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.attributes' => 'nullable|json',
        ]);

        $createdItems = [];

        foreach ($validated['items'] as $itemData) {
            $itemData['organization_id'] = 1; // Override organization_id with 1 for testing

            $createdItems[] = ItemMaster::create([
                'name' => $itemData['name'],
                'unicode_name' => $itemData['unicode_name'] ?? null,
                'item_category_id' => $itemData['item_category_id'],
                'item_code' => $itemData['item_code'],
                'unit_of_measurement' => $itemData['unit_of_measurement'],
                'reorder_level' => $itemData['reorder_level'] ?? 0,
                'is_perishable' => $itemData['is_perishable'] ?? false,
                'shelf_life_in_days' => $itemData['shelf_life_in_days'] ?? null,
                'branch_id' => $itemData['branch_id'] ?? null,
                'organization_id' => $itemData['organization_id'],
                'buying_price' => $itemData['buying_price'],
                'selling_price' => $itemData['selling_price'],
                'is_menu_item' => $itemData['is_menu_item'] ?? false,
                'is_active' => $itemData['is_active'] ?? true,
                'additional_notes' => $itemData['additional_notes'] ?? null,
                'description' => $itemData['description'] ?? null,
                'attributes' => $itemData['attributes'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Items created successfully',
            'data' => $createdItems
        ], 201);
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $item = ItemMaster::with(['category', 'branch', 'organization'])->findOrFail($id);
        
        if (request()->wantsJson()) {
            return response()->json($item);
        }

        return view('admin.inventory.items.show', compact('item'));
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $item = ItemMaster::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string',
            'unicode_name' => 'nullable|string',
            'item_category_id' => 'sometimes|exists:item_categories,id',
            'item_code' => 'sometimes|string|unique:item_master,item_code,' . $id,
            'unit_of_measurement' => 'sometimes|string',
            'reorder_level' => 'nullable|integer',
            'is_perishable' => 'boolean',
            'shelf_life_in_days' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id',
            'organization_id' => 'sometimes|exists:organizations,id',
            'buying_price' => 'sometimes|numeric',
            'selling_price' => 'sometimes|numeric',
            'is_menu_item' => 'boolean',
            'additional_notes' => 'nullable|string',
            'description' => 'nullable|string',
            'attributes' => 'nullable|json',
        ]);

        $item->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item updated successfully',
                'data' => $item
            ]);
        }

        return redirect()->route('admin.inventory.items.index')
            ->with('success', 'Item updated successfully');
    }

    public function getItemFormPartial($index)
    {
        return view('admin.inventory.items.partials.item-form', ['index' => $index]);
    }

    public function create()
    {
        $categories = ItemCategory::active()->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.inventory.items.create', compact('categories', 'branches'));
    }

    public function edit($id)
    {
        $item = ItemMaster::findOrFail($id);
        $categories = ItemCategory::active()->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.inventory.items.edit', compact('item', 'categories', 'branches'));
    }

    /**
     * Soft delete the specified item.
     */
    public function destroy($id)
    {
        $item = ItemMaster::findOrFail($id);
        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Item deleted successfully.']);
        }

        return redirect()->route('admin.inventory.items.index')
            ->with('success', 'Item deleted successfully');
    }
}