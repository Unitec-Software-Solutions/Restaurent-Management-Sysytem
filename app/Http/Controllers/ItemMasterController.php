<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index()
    {
       $items = ItemMaster::with('category')
                ->when(request('search'), function($query) {
                    return $query->where('name', 'like', '%'.request('search').'%')
                                 ->orWhere('item_code', 'like', '%'.request('search').'%');
                })
                ->when(request('category'), function($query) {
                    return $query->where('item_category_id', request('category'));
                })
                ->when(request()->has('status'), function($query) {
                    return $query->where('is_active', request('status'));
                })
                ->paginate(15);
                
    $categories = ItemCategory::active()->get();
    
    return view('admin.inventory.index', compact('items', 'categories'));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string',
            'unicode_name'       => 'nullable|string',
            'item_category_id'   => 'required|exists:item_categories,id',
            'item_code'          => 'required|string|unique:item_master,item_code',
            'unit_of_measurement'=> 'required|string',
            'reorder_level'      => 'nullable|integer',
            'is_perishable'      => 'boolean',
            'shelf_life_in_days' => 'nullable|integer',
            'branch_id'          => 'nullable|exists:branches,id',
            'organization_id'    => 'required|exists:organizations,id',
            'buying_price'       => 'required|numeric',
            'selling_price'      => 'required|numeric',
            'is_menu_item'       => 'boolean',
            'additional_notes'   => 'nullable|string',
            'description'        => 'nullable|string',
            'attributes'         => 'nullable|json',
        ]);

        $item = ItemMaster::create($data);

        return response()->json($item, 201);
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $item = ItemMaster::with(['category', 'branch', 'organization'])->findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $item = ItemMaster::findOrFail($id);

        $data = $request->validate([
            'name'               => 'sometimes|string',
            'unicode_name'       => 'nullable|string',
            'item_category_id'   => 'sometimes|exists:item_categories,id',
            'item_code'          => 'sometimes|string|unique:item_master,item_code,' . $id,
            'unit_of_measurement'=> 'sometimes|string',
            'reorder_level'      => 'nullable|integer',
            'is_perishable'      => 'boolean',
            'shelf_life_in_days' => 'nullable|integer',
            'branch_id'          => 'nullable|exists:branches,id',
            'organization_id'    => 'sometimes|exists:organizations,id',
            'buying_price'       => 'sometimes|numeric',
            'selling_price'      => 'sometimes|numeric',
            'is_menu_item'       => 'boolean',
            'additional_notes'   => 'nullable|string',
            'description'        => 'nullable|string',
            'attributes'         => 'nullable|json',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * Soft delete the specified item.
     */
    public function destroy($id)
    {
        $item = ItemMaster::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully.']);
    }
}
