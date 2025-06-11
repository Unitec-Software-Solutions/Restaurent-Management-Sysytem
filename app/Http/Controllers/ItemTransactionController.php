<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ItemTransactionController extends Controller
{
    protected function getOrganizationId()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'Unauthorized access');
        }
        return $user->organization_id;
    }

    protected function getStockStatistics()
    {
        $orgId = $this->getOrganizationId();

        $totalItems = ItemMaster::where('organization_id', $orgId)->count();
        $inStockCount = 0;
        $nearReorderCount = 0;
        $outOfStockCount = 0;

        $items = ItemMaster::where('organization_id', $orgId)->get();
        foreach ($items as $item) {
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

    public function index()
    {
        $orgId = $this->getOrganizationId();
        $stats = $this->getStockStatistics();
        $search = request('search');
        $branchId = request('branch_id');
        $status = request('status');

        $query = ItemMaster::with(['category', 'transactions'])
            ->where('organization_id', $orgId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->get();
        $stockData = [];

        foreach ($items as $item) {
            $branchesQuery = Branch::where('organization_id', $orgId)
                ->when($branchId, fn($q) => $q->where('id', $branchId))
                ->active();

            foreach ($branchesQuery->get() as $branch) {
                $currentStock = ItemTransaction::stockOnHand($item->id, $branch->id);

                if ($currentStock <= 0 && !$item->transactions()->where('branch_id', $branch->id)->exists()) {
                    continue;
                }

                $statusValue = $currentStock <= 0 ? 'out_of_stock' : ($currentStock <= $item->reorder_level ? 'low_stock' : 'in_stock');

                if ($status && $statusValue !== $status) continue;

                $stockData[] = [
                    'item' => $item,
                    'branch' => $branch,
                    'current_stock' => $currentStock,
                    'reorder_level' => $item->reorder_level,
                    'status' => $statusValue,
                ];
            }
        }

        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemsForPage = array_slice($stockData, ($currentPage - 1) * $perPage, $perPage);
        $stocks = new LengthAwarePaginator(
            $itemsForPage,
            count($stockData),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $branches = Branch::where('organization_id', $orgId)->active()->get();

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
        $orgId = $this->getOrganizationId();

        $query = ItemTransaction::with(['item', 'branch'])
            ->where('organization_id', $orgId)
            ->latest();

        if (request('search')) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('item_code', 'like', '%' . request('search') . '%');
            });
        }

        if (request('branch_id')) {
            $query->where('branch_id', request('branch_id'));
        }

        $transactions = $query->paginate(25);
        $branches = Branch::where('organization_id', $orgId)->active()->get();

        return view('admin.inventory.stock.transactions.index', compact('transactions', 'branches'));
    }

    public function stockSummary()
    {
        $orgId = $this->getOrganizationId();
        $items = ItemMaster::with('category')
            ->where('organization_id', $orgId)
            ->get();

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
        $orgId = $this->getOrganizationId();
        $items = ItemMaster::where('organization_id', $orgId)->get();
        $branches = Branch::where('organization_id', $orgId)->active()->get();
        return view('admin.inventory.stock.create', compact('items', 'branches'));
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:item_master,id,organization_id,' . $orgId,
            'branch_id' => 'required|exists:branches,id,organization_id,' . $orgId,
            'transaction_type' => 'required|in:purchase_order,return,adjustment,audit,transfer_in,sales_order,write_off,transfer,usage,transfer_out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by_user_id'] = optional(Auth::user())->id;
        $validated['organization_id'] = $orgId;
        $validated['is_active'] = true;

        if ($this->isStockOut($validated['transaction_type'])) {
            $validated['quantity'] = -abs($validated['quantity']);
        }

        ItemTransaction::create($validated);

        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(ItemTransaction $transaction)
    {
        if ($transaction->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $transaction->load(['item', 'branch']);
        return view('admin.inventory.stock.show', compact('transaction'));
    }

    public function edit($item_id, $branch_id)
    {
        $orgId = $this->getOrganizationId();

        // Validate the item belongs to organization
        $item = ItemMaster::where('id', $item_id)
            ->where('organization_id', $orgId)
            ->firstOrFail();

        // Validate the branch belongs to organization
        $branch = Branch::where('id', $branch_id)
            ->where('organization_id', $orgId)
            ->active()
            ->firstOrFail();

        // Get the latest transaction for this item+branch combination
        $transaction = ItemTransaction::where('inventory_item_id', $item_id)
            ->where('branch_id', $branch_id)
            ->where('organization_id', $orgId)
            ->latest()
            ->first();

        // If no transaction exists, create a new empty transaction model
        if (!$transaction) {
            $transaction = new ItemTransaction([
                'inventory_item_id' => $item_id,
                'branch_id' => $branch_id,
                'organization_id' => $orgId,
                'created_by_user_id' => optional(Auth::user())->id,
                'is_active' => true
            ]);
        }

        $items = ItemMaster::where('organization_id', $orgId)->get();
        $branches = Branch::where('organization_id', $orgId)->active()->get();

        return view('admin.inventory.stock.edit', [
            'transaction' => $transaction,
            'item' => $item,
            'branch' => $branch,
            'items' => $items,
            'branches' => $branches,
            'current_stock' => ItemTransaction::stockOnHand($item_id, $branch_id)
        ]);
    }

    public function update(Request $request, $item_id, $branch_id)
    {
        $orgId = $this->getOrganizationId();

        // Validate organization ownership
        ItemMaster::where('id', $item_id)
            ->where('organization_id', $orgId)
            ->firstOrFail();

        Branch::where('id', $branch_id)
            ->where('organization_id', $orgId)
            ->active()
            ->firstOrFail();

        $validated = $request->validate([
            'transaction_type' => 'required|in:purchase_order,return,adjustment,audit,transfer_in,sales_order,write_off,transfer,usage,transfer_out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $validated['inventory_item_id'] = $item_id;
        $validated['branch_id'] = $branch_id;
        $validated['created_by_user_id'] = optional(Auth::user())->id;
        $validated['organization_id'] = $orgId;
        $validated['is_active'] = true;

        if ($this->isStockOut($validated['transaction_type'])) {
            $validated['quantity'] = -abs($validated['quantity']);
        }

        // Create a new transaction record (history)
        ItemTransaction::create($validated);

        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Stock transaction recorded successfully.');
    }

    public function destroy(ItemTransaction $transaction)
    {
        if ($transaction->organization_id !== $this->getOrganizationId()) {
            abort(403, 'Unauthorized access');
        }

        $transaction->delete();
        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function isStockOut($type)
    {
        $outTypes = ['sales_order', 'write_off', 'transfer', 'usage', 'transfer_out'];
        return in_array($type, $outTypes);
    }
}
