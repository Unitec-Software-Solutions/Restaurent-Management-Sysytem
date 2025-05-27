<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryStock;
use App\Models\InventoryCategory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        $categories = DB::connection('test_db')
            ->table('item_categories')
            ->select('id', 'name')
            ->get();

        return view('frontend.items.create', compact('categories'));
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_category_id' => 'required|integer',
            'selling_price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('item_images', 'public');
            }

            DB::connection('test_db')
                ->table('item_master')
                ->insert($validated);

            return redirect()->route('frontend')
                ->with('success', 'Item created successfully!');
        } catch (\Exception $e) {
            return back()->withError('Error creating item: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of items.
     */
    public function index()
    {
        $items = Item::all(); // Make sure you're only calling this once
        return view('frontend.items.index', compact('items'));
    }

    /**
     * Display the specified item.
     */
    public function show(InventoryItem $item)
    {
        return view('items.show', compact('item'));
    }

    public function getItemList()
    {
        try {
            // Fetch items with their category names and sort by item ID
            $items = DB::connection('test_db')
                ->table('item_master')
                ->join('item_categories', 'item_master.item_category_id', '=', 'item_categories.id')
                ->select(
                    'item_master.id',
                    'item_master.name',
                    'item_master.selling_price',
                    'item_categories.name as category_name'
                )
                ->orderBy('item_master.id', 'asc') // Sort by item ID
                ->get();

            // Group items by category
            $groupedItems = $items->groupBy('category_name');

            return view('frontend.items', compact('groupedItems'));
        } catch (\Exception $e) {
            return back()->withError('Database connection error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = DB::connection('test_db')
            ->table('item_master')
            ->select('id', 'item_category_id', 'name', 'selling_price')
            ->where('id', $id)
            ->first();

        if (!$item) {
            return redirect()->route('frontend.items')
                ->with('error', 'Item not found');
        }

        $categories = DB::connection('test_db')
            ->table('item_categories')
            ->select('id', 'name')
            ->get();

        // Ensure the image property exists
        if (!$item->image) {
            $item->image = null; // or set a default image path
        }

        return view('frontend.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_category_id' => 'required|integer',
            'selling_price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('item_images', 'public');
            }

            DB::connection('test_db')
                ->table('item_master')
                ->where('id', $id)
                ->update($validated);

            return redirect()->route('frontend')
                ->with('success', 'Item updated successfully!');
        } catch (\Exception $e) {
            return back()->withError('Error updating item: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::connection('test_db')
                ->table('item_master')
                ->where('id', $id)
                ->delete();

            return redirect()->route('frontend')
                ->with('success', 'Item deleted successfully!');
        } catch (\Exception $e) {
            return back()->withError('Error deleting item: ' . $e->getMessage());
        }
    }
}