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

class ItemController extends Controller
{
    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        $categories = InventoryCategory::all();
        $branches = Branch::all();
        $suppliers = \App\Models\Supplier::where('is_active', true)
                                   ->orderBy('name')
                                   ->get();
        return view('inventory.items.create', compact('categories', 'branches','suppliers'));
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku',
            'unit_of_measurement' => 'required|string|max:50',
            'reorder_level' => 'required|numeric',
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'is_perishable' => 'nullable|boolean',
            'shelf_life_days' => 'nullable|integer',
            'expiry_date' => 'nullable|date',
            'show_in_menu' => 'nullable|boolean',
            'branch_id' => 'required|exists:branches,id',
            'transaction_type' => 'required|in:purchase,transfer_in,adjustment',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Failed to create item. Please check the form for errors.');
        }

        try {
            $validated = $validator->validated();

            // Normalize checkboxes
            $validated['is_perishable'] = $request->has('is_perishable');
            $validated['show_in_menu'] = $request->has('show_in_menu');
            $validated['is_active'] = true; // default to active
            $validated['is_inactive'] = false;

            // Create the inventory item
            $item = InventoryItem::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'unit_of_measurement' => $validated['unit_of_measurement'],
                'reorder_level' => $validated['reorder_level'],
                'inventory_category_id' => $validated['inventory_category_id'],
                'is_perishable' => $validated['is_perishable'],
                'shelf_life_days' => $validated['shelf_life_days'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'show_in_menu' => $validated['show_in_menu'],
                'is_active' => true,
                'is_inactive' => false,
            ]);

            // Record initial inventory transaction
            $transaction = InventoryTransaction::create([
                'branch_id' => $validated['branch_id'],
                'inventory_item_id' => $item->id,
                'transaction_type' => $validated['transaction_type'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'] ?? null,
                'user_id' => Auth::id(),
                'notes' => 'Initial stock entry at item creation',
            ]);

            // Create initial stock record
            $stock = InventoryStock::create([
                'branch_id' => $validated['branch_id'],
                'inventory_item_id' => $item->id,
                'current_quantity' => $validated['quantity'],
                'committed_quantity' => 0,
                'available_quantity' => $validated['quantity'],
                'is_active' => true,
            ]);

            // Verify all records were created successfully
            if (!$item || !$transaction || !$stock) {
                throw new \Exception('Failed to create one or more required records');
            }

            return redirect()->route('items.index')
                ->with('success', 'Item created successfully with initial stock of ' . $validated['quantity'] . ' ' . $validated['unit_of_measurement']);

        } catch (\Exception $e) {
            // Log the error for debugging
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create inventory item. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Display a listing of items.
     */
    public function index()
    {
        $items = InventoryItem::latest()->paginate(15);
        return view('items.index', compact('items'));
    }

    /**
     * Display the specified item.
     */
    public function show(InventoryItem $item)
    {
        return view('items.show', compact('item'));
    }
}