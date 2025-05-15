<?php

namespace App\Http\Controllers;

use App\Models\ItemMaster;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ItemMasterController extends Controller
{
    /**
     * Display a listing of the items.
     */
    public function index(Request $request)
    {
        $query = ItemMaster::with(['organization', 'branch']);
        
        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }
        
        $items = $query->latest()->paginate(15);
        
        return view('inventory.items.index', compact('items'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        $organizations = Organization::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        
        return view('inventory.items.create', compact('organizations', 'branches', 'suppliers'));
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:item_master,sku',
            'type' => 'required|in:food,inventory,other',
            'reorder_level' => 'required|integer|min:0',
            'organization_id' => 'nullable|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'attributes' => 'nullable|array',
        ]);
        
        DB::beginTransaction();
        try {
            $attributes = $request->attributes ?? [];
            
            // Add additional attributes based on type
            if ($request->type === 'food') {
                $attributes['category'] = $request->category;
                $attributes['unit'] = $request->unit;
                $attributes['shelf_life'] = $request->shelf_life;
                $attributes['is_prepared'] = $request->has('is_prepared');
                $attributes['is_beverage'] = $request->has('is_beverage');
                $attributes['is_ingredient'] = $request->has('is_ingredient');
                $attributes['buy_price'] = $request->buy_price;
                $attributes['sell_price'] = $request->sell_price;
                $attributes['supplier_id'] = $request->supplier_id;
            } else if ($request->type === 'inventory') {
                $attributes['category'] = $request->category;
                $attributes['supplier_id'] = $request->supplier_id;
                $attributes['location'] = $request->location;
            }
            
            // Handle translations if provided
            if ($request->has('translations')) {
                $attributes['name_translations'] = $request->translations;
            }
            
            $item = ItemMaster::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'type' => $request->type,
                'reorder_level' => $request->reorder_level,
                'organization_id' => $request->organization_id,
                'branch_id' => $request->branch_id,
                'attributes' => $attributes,
                'is_active' => $request->is_active ?? true,
            ]);
            
            // Create initial stock if provided
            if ($request->has('initial_stock') && $request->branch_id) {
                InventoryStock::create([
                    'branch_id' => $request->branch_id,
                    'item_id' => $item->id,
                    'current_quantity' => $request->initial_stock,
                    'committed_quantity' => 0,
                    'available_quantity' => $request->initial_stock,
                    'is_active' => true,
                ]);
                
                // Create initial stock transaction
                InventoryTransaction::create([
                    'branch_id' => $request->branch_id,
                    'item_id' => $item->id,
                    'transaction_type' => 'adjustment',
                    'quantity' => $request->initial_stock,
                    'unit_price' => $attributes['buy_price'] ?? 0,
                    'user_id' => Auth::id(),
                    'notes' => 'Initial stock setup',
                    'is_active' => true,
                ]);
            }
            
            DB::commit();
            return redirect()->route('inventory.items.index')
                ->with('success', 'Item created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create item: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified item.
     */
    public function show(ItemMaster $item)
    {
        $item->load(['organization', 'branch', 'stocks.branch']);
        
        // Get recent transactions
        $transactions = $item->transactions()
            ->with(['branch', 'user'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('inventory.items.show', compact('item', 'transactions'));
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(ItemMaster $item)
    {
        $organizations = Organization::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        
        return view('inventory.items.edit', compact('item', 'organizations', 'branches', 'suppliers'));
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, ItemMaster $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:item_master,sku,' . $item->id,
            'type' => 'required|in:food,inventory,other',
            'reorder_level' => 'required|integer|min:0',
            'organization_id' => 'nullable|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'attributes' => 'nullable|array',
        ]);
        
        DB::beginTransaction();
        try {
            $attributes = $request->attributes ?? [];
            
            // Update attributes based on type
            if ($request->type === 'food') {
                $attributes['category'] = $request->category;
                $attributes['unit'] = $request->unit;
                $attributes['shelf_life'] = $request->shelf_life;
                $attributes['is_prepared'] = $request->has('is_prepared');
                $attributes['is_beverage'] = $request->has('is_beverage');
                $attributes['is_ingredient'] = $request->has('is_ingredient');
                $attributes['buy_price'] = $request->buy_price;
                $attributes['sell_price'] = $request->sell_price;
                $attributes['supplier_id'] = $request->supplier_id;
            } else if ($request->type === 'inventory') {
                $attributes['category'] = $request->category;
                $attributes['supplier_id'] = $request->supplier_id;
                $attributes['location'] = $request->location;
            }
            
            // Handle translations if provided
            if ($request->has('translations')) {
                $attributes['name_translations'] = $request->translations;
            }
            
            $item->update([
                'name' => $request->name,
                'sku' => $request->sku,
                'type' => $request->type,
                'reorder_level' => $request->reorder_level,
                'organization_id' => $request->organization_id,
                'branch_id' => $request->branch_id,
                'attributes' => $attributes,
                'is_active' => $request->is_active ?? true,
            ]);
            
            DB::commit();
            return redirect()->route('inventory.items.show', $item)
                ->with('success', 'Item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update item: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(ItemMaster $item)
    {
        DB::beginTransaction();
        try {
            // Check if the item has any related stock or transactions
            if ($item->stocks()->exists() || $item->transactions()->exists()) {
                // Instead of deleting, mark as inactive
                $item->update(['is_active' => false]);
                $message = 'Item has been marked as inactive.';
            } else {
                // If no related records, we can safely delete
                $item->delete();
                $message = 'Item has been deleted.';
            }
            
            DB::commit();
            return redirect()->route('inventory.items.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete item: ' . $e->getMessage());
            return redirect()->route('inventory.items.index')
                ->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    /**
     * Display items that are low in stock.
     */
    public function lowStock(Request $request)
    {
        $branchId = $request->branch_id ?? auth()->user()->branch_id;
        
        // Get all items with their stock at the specified branch
        $items = ItemMaster::with(['stocks' => function($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        }])->active()->get();
        
        // Filter to only include items that are low in stock
        $lowStockItems = $items->filter(function($item) use ($branchId) {
            return $item->isLowStock($branchId);
        });
        
        return view('inventory.items.low-stock', [
            'items' => $lowStockItems,
            'branch' => Branch::find($branchId)
        ]);
    }

    /**
     * Update stock levels for an item.
     */
    public function updateStock(Request $request, ItemMaster $item)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|numeric',
            'adjustment_type' => 'required|in:add,subtract,set',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $branchId = $request->branch_id;
            $quantity = $request->quantity;
            $notes = $request->notes ?? '';
            
            // Get or create stock record
            $stock = InventoryStock::firstOrNew([
                'branch_id' => $branchId,
                'item_id' => $item->id,
            ]);
            
            $oldQuantity = $stock->current_quantity ?? 0;
            $newQuantity = $oldQuantity;
            
            // Calculate new quantity based on adjustment type
            switch ($request->adjustment_type) {
                case 'add':
                    $newQuantity = $oldQuantity + $quantity;
                    $transactionType = 'adjustment';
                    $transactionQuantity = $quantity;
                    break;
                case 'subtract':
                    $newQuantity = max(0, $oldQuantity - $quantity);
                    $transactionType = 'adjustment';
                    $transactionQuantity = -$quantity;
                    break;
                case 'set':
                    $newQuantity = max(0, $quantity);
                    $transactionType = 'adjustment';
                    $transactionQuantity = $newQuantity - $oldQuantity;
                    break;
            }
            
            // Update or create stock record
            if (!$stock->exists) {
                $stock->fill([
                    'current_quantity' => $newQuantity,
                    'committed_quantity' => 0,
                    'available_quantity' => $newQuantity,
                    'is_active' => true,
                ]);
                $stock->save();
            } else {
                $stock->current_quantity = $newQuantity;
                $stock->available_quantity = $newQuantity - $stock->committed_quantity;
                $stock->save();
            }
            
            // Create transaction record
            if ($transactionQuantity != 0) {
                InventoryTransaction::create([
                    'branch_id' => $branchId,
                    'item_id' => $item->id,
                    'transaction_type' => $transactionType,
                    'quantity' => abs($transactionQuantity),
                    'unit_price' => $item->getBuyPrice(),
                    'user_id' => Auth::id(),
                    'notes' => $notes ?: 'Stock adjustment',
                    'is_active' => true,
                ]);
            }
            
            DB::commit();
            return redirect()->route('inventory.items.show', $item)
                ->with('success', 'Stock updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Export items to Excel.
     */
    public function export(Request $request)
    {
        $query = ItemMaster::query();
        
        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        $items = $query->get();
        
        return Excel::download(new ItemsExport($items), 'items.xlsx');
    }

    /**
     * Show import form.
     */
    public function importForm()
    {
        return view('inventory.items.import');
    }

    /**
     * Import items from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        try {
            Excel::import(new ItemsImport, $request->file('file'));
            
            return redirect()->route('inventory.items.index')
                ->with('success', 'Items imported successfully');
        } catch (\Exception $e) {
            Log::error('Failed to import items: ' . $e->getMessage());
            return back()->with('error', 'Failed to import items: ' . $e->getMessage());
        }
    }
}