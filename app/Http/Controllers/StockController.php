<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryStock::with(['item', 'branch'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('item', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->branch_id);
            });

        $stocks = $query->paginate(10)
            ->withQueryString(); // Preserves filters in pagination links

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.stock.index', compact('stocks', 'branches'));
    }

    public function create()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.stock.create', compact('items', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|numeric|min:0',
            'transaction_type' => 'required|in:purchase,transfer_in,adjustment',
            'notes' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // Create or update stock record
            $stock = InventoryStock::firstOrCreate(
                [
                    'inventory_item_id' => $validated['inventory_item_id'],
                    'branch_id' => $validated['branch_id']
                ],
                [
                    'current_quantity' => 0,
                    'committed_quantity' => 0,
                    'available_quantity' => 0,
                    'is_active' => true
                ]
            );

            $stock->addStock($validated['quantity']);

            // Create transaction record
            InventoryTransaction::create([
                'branch_id' => $validated['branch_id'],
                'inventory_item_id' => $validated['inventory_item_id'],
                'transaction_type' => $validated['transaction_type'],
                'quantity' => $validated['quantity'],
                'user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? 'Stock adjustment',
                'is_active' => true
            ]);

            DB::commit();
            return redirect()->route('admin.inventory.stock.index')
                ->with('success', 'Stock updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update stock. ' . $e->getMessage());
        }
    }

    public function edit(InventoryStock $stock)
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.stock.edit', compact('stock', 'items', 'branches'));
    }

    public function update(Request $request, InventoryStock $stock)
    {
        $validated = $request->validate([
            'adjustment_quantity' => 'required|numeric',
            'transaction_type' => 'required|in:adjustment,transfer_in,transfer_out',
            'notes' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $originalQuantity = $stock->current_quantity;
            
            if ($validated['adjustment_quantity'] >= 0) {
                $stock->addStock($validated['adjustment_quantity']);
            } else {
                $stock->deductStock(abs($validated['adjustment_quantity']));
            }

            // Record transaction
            InventoryTransaction::create([
                'branch_id' => $stock->branch_id,
                'inventory_item_id' => $stock->inventory_item_id,
                'transaction_type' => $validated['transaction_type'],
                'quantity' => $validated['adjustment_quantity'],
                'user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? 'Stock adjustment',
                'is_active' => true
            ]);

            DB::commit();
            return redirect()->route('admin.inventory.stock.index')
                ->with('success', 'Stock adjusted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to adjust stock. ' . $e->getMessage());
        }
    }

    public function destroy(InventoryStock $stock)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                throw new \Exception('User not authenticated');
            }

            DB::beginTransaction();

            // Record the deletion in transactions table
            InventoryTransaction::create([
                'branch_id' => $stock->branch_id,
                'inventory_item_id' => $stock->inventory_item_id,
                'transaction_type' => 'adjustment',
                'quantity' => -$stock->current_quantity, // Negative quantity to indicate removal
                'user_id' => Auth::id(), // Use Auth facade instead of auth() helper
                'notes' => 'Stock removed from inventory',
                'is_active' => true
            ]);

            // Soft delete the stock
            $stock->delete();

            DB::commit();
            return redirect()->route('admin.inventory.stock.index')
                ->with('success', 'Stock deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete stock. ' . $e->getMessage());
        }
    }

}

