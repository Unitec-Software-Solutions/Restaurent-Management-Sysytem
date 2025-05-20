<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ItemTransactionController extends Controller
{

    /**
     * Get stock summary statistics for dashboard
     */
    protected function getStockStatistics()
    {
        $totalItems      = ItemMaster::count();
        $inStockCount    = 0;
        $nearReorderCount = 0;
        $outOfStockCount = 0;

        // Loop through each item (across all branches) to compute these stats
        $items = ItemMaster::all();
        foreach ($items as $item) {
            // stock across all branches
            $stock = ItemTransaction::stockOnHand($item->id);

            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($stock <= $item->reorder_level) {
                $nearReorderCount++;
            } else {
                $inStockCount++;
            }
        }

        return compact('totalItems', 'inStockCount', 'nearReorderCount', 'outOfStockCount');
    }

    /**
     * Display a listing of item transactions grouped by item & branch.
     */
    public function index()
    {
        // Get statistics for dashboard
        $stats    = $this->getStockStatistics();
        $search   = request('search');
        $branchId = request('branch_id');
        $status   = request('status');

        $query = ItemMaster::with(['category', 'transactions']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('item_code', 'like', "%{$search}%");
        }

        $items = $query->get();

        $stockData = [];

        foreach ($items as $item) {
            $branchesQuery = $branchId
                ? Branch::where('id', $branchId)
                : Branch::active();

            foreach ($branchesQuery->get() as $branch) {
                $currentStock = ItemTransaction::stockOnHand($item->id, $branch->id);

                // skip if nothing at all
                if (
                    $currentStock <= 0
                    && ! $item->transactions()->where('branch_id', $branch->id)->exists()
                ) {
                    continue;
                }

                // determine status
                $statusValue = $currentStock <= 0
                    ? 'out_of_stock'
                    : ($currentStock <= $item->reorder_level ? 'low_stock' : 'in_stock');

                // apply status filter
                if ($status && $statusValue !== $status) {
                    continue;
                }

                $stockData[] = [
                    'item'          => $item,
                    'branch'        => $branch,
                    'current_stock' => $currentStock,
                    'reorder_level' => $item->reorder_level,
                    'status'        => $statusValue,
                ];
            }
        }

        // Paginate the resulting array
        $perPage       = 25;
        $currentPage   = LengthAwarePaginator::resolveCurrentPage();
        $itemsForPage  = array_slice($stockData, ($currentPage - 1) * $perPage, $perPage);
        $stocks        = new LengthAwarePaginator(
            $itemsForPage,
            count($stockData),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Fetch branches for the filter dropdown
        $branches = Branch::active()->get();

        return view('admin.inventory.stock.index', [
            'stocks' => $stocks,
            'stats' => $stats,
            'branches' => $branches,
            'itemsCount' => $stats['totalItems'],
            'inStockCount' => $stats['inStockCount'],
            'nearReorderCount' => $stats['nearReorderCount'],
            'outOfStockCount' => $stats['outOfStockCount']
        ]);
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
        $validated['organization_id'] = 1;

        // Adjust quantity based on transaction type
        if ($validated['transaction_type'] === 'out') {
            $validated['quantity'] = -abs($validated['quantity']);
        } else {
            $validated['quantity'] = abs($validated['quantity']);
        }

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

        // Adjust quantity based on transaction type
        if ($validated['transaction_type'] === 'out') {
            $validated['quantity'] = -abs($validated['quantity']);
        } else {
            $validated['quantity'] = abs($validated['quantity']);
        }

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
