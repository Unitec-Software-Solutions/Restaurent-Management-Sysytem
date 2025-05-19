<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;

class ItemTransactionController extends Controller
{
    /**
     * Display a listing of item transactions grouped by item & branch.
     */
    public function index()
    {
        // Get search and branch filter
        $search = request('search');
        $branchId = request('branch_id');

        // Start query for ItemMaster
        $query = ItemMaster::with(['category']);

        // Apply search filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('item_code', 'like', "%{$search}%");
        }

        // Fetch all items based on filters
        $items = $query->get();

        // Prepare stock data grouped by item and optionally filtered by branch
        $stockData = [];

        foreach ($items as $item) {
            // Get branches where this item has transactions
            $branchesQuery = $branchId
                ? Branch::where('id', $branchId)
                : Branch::active();

            foreach ($branchesQuery->get() as $branch) {
                // Calculate stock on hand
                $currentStock = ItemTransaction::stockOnHand($item->id, $branch->id);

                // Skip if no stock and branch has no transactions
                if ($currentStock <= 0 && !$item->transactions()->where('branch_id', $branch->id)->exists()) {
                    continue;
                }

                // Add to final list
                $stockData[] = [
                    'item' => $item,
                    'branch' => $branch,
                    'current_stock' => $currentStock,
                    'reorder_level' => $item->reorder_level,
                    'status' => $currentStock <= $item->reorder_level ? 'low_stock' : 'in_stock',
                ];
            }
        }

        // Paginate manually (since we’re building the array manually)
        $transactions = $query->paginate(25);
        $perPage = 25;
        $currentPage = request()->input('page', 1);
        $paginatedData = array_slice($stockData, ($currentPage - 1) * $perPage, $perPage);

        // Create LengthAwarePaginator instance
        $stocks = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            count($stockData),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Fetch active branches for filter dropdown
        $branches = Branch::active()->get();
        
        return view('admin.inventory.stock.index', compact('stocks','transactions', 'branches'));
    }

    public function transactions()
    {
        $query = ItemTransaction::with(['item', 'branch'])->latest();

        if (request('search')) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
        }

        if (request('branch_id')) {
            $query->where('branch_id', request('branch_id'));
        }

        $transactions = $query->paginate(25);
        $branches = Branch::active()->get(); // Make sure Branch model has active() scope

        return view('admin.inventory.stock.transactions.index', compact('transactions', 'branches'));
    }

    public function stockSummary()
    {
        $items = ItemMaster::with('category')->get();

        $stockData = $items->map(function ($item) {
            $stock = ItemTransaction::stockOnHand($item->id);
            return [
                'name' => $item->name,
                'category' => optional($item->category)->name ?? '-',
                'reorder_level' => $item->reorder_level,
                'stock' => $stock,
                'status' => $stock <= $item->reorder_level ? 'Warning' : 'OK',
            ];
        });

        return response()->json($stockData);
    }

    public function create()
    {
        $items = ItemMaster::all();
        $branches = Branch::active()->get();
        return view('admin.inventory.stock.create', compact('items', 'branches'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:item_master,id',
            'branch_id' => 'required|exists:branches,id',
            'transaction_type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by_user_id'] = auth()->id();
        $validated['is_active'] = true;

        ItemTransaction::create($validated);

        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(ItemTransaction $transaction)
    {
        $transaction->load(['item', 'branch']);
        return view('admin.inventory.stock.show', compact('transaction'));
    }

    public function edit(ItemTransaction $transaction)
    {
        $transaction->load(['item', 'branch']);
        $items = ItemMaster::all();
        $branches = Branch::active()->get();
        return view('admin.inventory.stock.edit', compact('transaction', 'items', 'branches'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, ItemTransaction $transaction)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:item_master,id',
            'branch_id' => 'required|exists:branches,id',
            'transaction_type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(ItemTransaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction deleted successfully.');
    }

}
