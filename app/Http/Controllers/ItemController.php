<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryStock;
use App\Models\InventoryCategory;
use App\Models\Branch;
use App\Models\Supplier;
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
        $categories = InventoryCategory::all();
        $branches = Branch::all();
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('inventory.items.create', compact('categories', 'branches', 'suppliers'));
    }

    /**
     * Store a newly created item and initial stock in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items.*.name' => 'required|string|max:255',
            'items.*.sku' => 'required|string|max:50|unique:inventory_items,sku',
            'items.*.inventory_category_id' => 'required|exists:inventory_categories,id',
            'items.*.unit_of_measurement' => 'required|string|max:50',
            'items.*.reorder_level' => 'required|numeric|min:0',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $item = InventoryItem::create([
                    'name' => $itemData['name'],
                    'sku' => $itemData['sku'],
                    'inventory_category_id' => $itemData['inventory_category_id'],
                    'unit_of_measurement' => $itemData['unit_of_measurement'],
                    'reorder_level' => $itemData['reorder_level'],
                    'purchase_price' => $itemData['purchase_price'],
                    'selling_price' => $itemData['selling_price'],
                    'is_active' => true
                ]);
            }

            DB::commit();
            return redirect()->route('inventory.items.index')
                ->with('success', 'Items created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create items. ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of items.
     */
    public function index()
    {
        $items = InventoryItem::latest()->paginate(15);
        return view('inventory.items.index', compact('items'));
    }

    /**
     * Display the specified item.
     */
    public function show(InventoryItem $item)
    {
        $item->load(['inventoryCategory', 'stocks.branch']);
        return view('inventory.items.show', compact('item'));
    }
}
