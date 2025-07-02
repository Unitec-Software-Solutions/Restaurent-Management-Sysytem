<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\ItemTransaction;
use App\Models\ItemMaster;
use App\Models\Branch;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ItemTransactionController extends Controller
{
    use Exportable;
    protected function getOrganizationId()
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // For super admin, return null to allow access to all organizations
        if ($user->is_super_admin) {
            return null;
        }

        if (!$user->organization_id) {
            abort(403, 'No organization assigned');
        }

        return $user->organization_id;
    }

    /**
     * Apply organization filter to query if user is not super admin
     */
    protected function applyOrganizationFilter($query, $orgId)
    {
        if ($orgId !== null) {
            return $query->where('organization_id', $orgId);
        }
        return $query;
    }

    /**
     * Check if user can access record from specific organization
     */
    protected function canAccessOrganization($recordOrgId, $userOrgId)
    {
        return $userOrgId === null || $recordOrgId === $userOrgId;
    }

    protected function getStockStatistics()
    {
        $orgId = $this->getOrganizationId();

        $totalItemsQuery = ItemMaster::query();
        if ($orgId !== null) {
            $totalItemsQuery->where('organization_id', $orgId);
        }
        $totalItems = $totalItemsQuery->count();

        $inStockCount = 0;
        $nearReorderCount = 0;
        $outOfStockCount = 0;

        $itemsQuery = ItemMaster::query();
        if ($orgId !== null) {
            $itemsQuery->where('organization_id', $orgId);
        }
        $items = $itemsQuery->get();

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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access inventory stock.');
        }

        $orgId = $this->getOrganizationId();
        $stats = $this->getStockStatistics();
        $search = request('search');
        $branchId = request('branch_id');
        $status = request('status');

        $query = ItemMaster::with(['category', 'transactions']);

        // Apply organization filter for non-super admins
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->get();
        $stockData = [];

        foreach ($items as $item) {
            $branchesQuery = Branch::query()
                ->when($branchId, fn($q) => $q->where('id', $branchId))
                ->active();

            // Apply organization filter for branches
            if ($orgId !== null) {
                $branchesQuery->where('organization_id', $orgId);
            } else {
                // For super admin, filter branches by item's organization
                $branchesQuery->where('organization_id', $item->organization_id);
            }

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

        $branchQuery = Branch::active();
        if ($orgId !== null) {
            $branchQuery->where('organization_id', $orgId);
        }
        $branches = $branchQuery->get();

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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access transactions.');
        }

        $orgId = $this->getOrganizationId();

        $query = ItemTransaction::with(['item', 'branch'])
            ->latest();

        // Apply organization filter for non-super admins
        if ($orgId !== null) {
            $query->where('organization_id', $orgId);
        }

        if (request('search')) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('item_code', 'like', '%' . request('search') . '%');
            });
        }

        if (request('branch_id')) {
            $query->where('branch_id', request('branch_id'));
        }

        // Add transaction_type filter
        if (request('transaction_type')) {
            $query->where('transaction_type', request('transaction_type'));
        }

        // Add date range filter
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Apply filters and search for potential export
        $query = $this->applyFiltersToQuery($query, request());

        // Handle export
        if (request()->has('export')) {
            return $this->exportToExcel(request(), $query, 'inventory_transactions_export.xlsx', [
                'ID', 'Item', 'Type', 'Quantity', 'Branch', 'Date', 'Created By', 'Reference'
            ]);
        }

        $transactions = $query->paginate(25);

        $branchQuery = Branch::active();
        if ($orgId !== null) {
            $branchQuery->where('organization_id', $orgId);
        }
        $branches = $branchQuery->get();

        return view('admin.inventory.stock.transactions.index', compact('transactions', 'branches'));
    }

    /**
     * Get searchable columns for inventory transactions
     */
    protected function getSearchableColumns(): array
    {
        return ['transaction_type', 'quantity', 'reference_id'];
    }

    /**
     * Apply filters to query for exports and searches
     */
    protected function applyFiltersToQuery($query, $request)
    {
        // Apply search filter
        if ($request->filled('search')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('item_code', 'like', '%' . $request->search . '%');
            });
        }

        // Apply branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Apply transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Apply date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    public function stockSummary()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $orgId = $this->getOrganizationId();

        $itemsQuery = ItemMaster::with('category');
        if ($orgId !== null) {
            $itemsQuery->where('organization_id', $orgId);
        }
        $items = $itemsQuery->get();

        $stockData = $items->map(function ($item) {
            $stock = ItemTransaction::stockOnHand($item->id);
            return [
                'name' => $item->name,
                'category' => optional($item->category)->name ?? '-',
                'reorder_level' => $item->reorder_level,
                'stock' => $stock,
                'status' => $stock <= ($item->reorder_level ?? 0) ? 'Warning' : 'OK',
            ];
        });

        return response()->json($stockData);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create transactions.');
        }

        $orgId = $this->getOrganizationId();

        $itemsQuery = ItemMaster::query();
        if ($orgId !== null) {
            $itemsQuery->where('organization_id', $orgId);
        }
        $items = $itemsQuery->get();

        $branchesQuery = Branch::active();
        if ($orgId !== null) {
            $branchesQuery->where('organization_id', $orgId);
        }
        $branches = $branchesQuery->get();

        return view('admin.inventory.stock.create', compact('items', 'branches'));
    }

    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to create transactions.');
        }

        $orgId = $this->getOrganizationId();

        $validationRules = [
            'transaction_type' => 'required|in:purchase_order,return,adjustment,audit,transfer_in,sales_order,write_off,transfer,usage,transfer_out,grn_stock_in,gtn_stock_in,gtn_stock_out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];

        // For non-super admin, validate organization ownership
        if ($orgId !== null) {
            $validationRules['inventory_item_id'] = 'required|exists:item_master,id,organization_id,' . $orgId;
            $validationRules['branch_id'] = 'required|exists:branches,id,organization_id,' . $orgId;
        } else {
            // For super admin, just check existence
            $validationRules['inventory_item_id'] = 'required|exists:item_master,id';
            $validationRules['branch_id'] = 'required|exists:branches,id';
        }

        $validated = $request->validate($validationRules);

        // For super admin, determine organization from the selected item
        if ($orgId === null) {
            $item = ItemMaster::find($validated['inventory_item_id']);
            $orgId = $item->organization_id;
        }

        $validated['created_by_user_id'] = Auth::guard('admin')->id();
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to view transactions.');
        }

        $orgId = $this->getOrganizationId();

        // Super admin can view any transaction, others only their organization's
        if ($orgId !== null && $transaction->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $transaction->load(['item', 'branch']);
        return view('admin.inventory.stock.show', compact('transaction'));
    }

    public function edit($item_id, $branch_id)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to edit transactions.');
        }

        $orgId = $this->getOrganizationId();

        // Validate the item exists and belongs to organization (for non-super admin)
        $itemQuery = ItemMaster::where('id', $item_id);
        if ($orgId !== null) {
            $itemQuery->where('organization_id', $orgId);
        }
        $item = $itemQuery->firstOrFail();

        // Validate the branch exists and belongs to organization (for non-super admin)
        $branchQuery = Branch::where('id', $branch_id)->active();
        if ($orgId !== null) {
            $branchQuery->where('organization_id', $orgId);
        } else {
            // For super admin, ensure branch belongs to same org as item
            $branchQuery->where('organization_id', $item->organization_id);
        }
        $branch = $branchQuery->firstOrFail();

        // Use the item's organization for the transaction search
        $transactionOrgId = $orgId ?? $item->organization_id;

        // Get the latest transaction for this item+branch combination
        $transaction = ItemTransaction::where('inventory_item_id', $item_id)
            ->where('branch_id', $branch_id)
            ->where('organization_id', $transactionOrgId)
            ->latest()
            ->first();

        // If no transaction exists, create a new empty transaction model
        if (!$transaction) {
            $transaction = new ItemTransaction([
                'inventory_item_id' => $item_id,
                'branch_id' => $branch_id,
                'organization_id' => $transactionOrgId,
                'created_by_user_id' => Auth::guard('admin')->id(),
                'is_active' => true
            ]);
        }

        // Get items and branches for dropdowns
        $itemsQuery = ItemMaster::query();
        $branchesQuery = Branch::active();

        if ($orgId !== null) {
            $itemsQuery->where('organization_id', $orgId);
            $branchesQuery->where('organization_id', $orgId);
        } else {
            // For super admin, show items and branches from the same org as selected item
            $itemsQuery->where('organization_id', $item->organization_id);
            $branchesQuery->where('organization_id', $item->organization_id);
        }

        $items = $itemsQuery->get();
        $branches = $branchesQuery->get();

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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to update transactions.');
        }

        $orgId = $this->getOrganizationId();

        // Validate item exists and organization ownership (for non-super admin)
        $itemQuery = ItemMaster::where('id', $item_id);
        if ($orgId !== null) {
            $itemQuery->where('organization_id', $orgId);
        }
        $item = $itemQuery->firstOrFail();

        // Validate branch exists and organization ownership (for non-super admin)
        $branchQuery = Branch::where('id', $branch_id)->active();
        if ($orgId !== null) {
            $branchQuery->where('organization_id', $orgId);
        } else {
            // For super admin, ensure branch belongs to same org as item
            $branchQuery->where('organization_id', $item->organization_id);
        }
        $branchQuery->firstOrFail();

        $validated = $request->validate([
            'transaction_type' => 'required|in:purchase_order,return,adjustment,audit,transfer_in,sales_order,write_off,transfer,usage,transfer_out,grn_stock_added,gtn_stock_out',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        // Use the item's organization for the transaction
        $transactionOrgId = $orgId ?? $item->organization_id;

        $validated['inventory_item_id'] = $item_id;
        $validated['branch_id'] = $branch_id;
        $validated['created_by_user_id'] = Auth::guard('admin')->id();
        $validated['organization_id'] = $transactionOrgId;
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Please log in to delete transactions.');
        }

        $orgId = $this->getOrganizationId();

        // Super admin can delete any transaction, others only their organization's
        if ($orgId !== null && $transaction->organization_id !== $orgId) {
            abort(403, 'Unauthorized access');
        }

        $transaction->delete();
        return redirect()->route('admin.inventory.stock.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function isStockOut($type)
    {
        $outTypes = ['sales_order', 'write_off', 'transfer', 'usage', 'gtn_outgoing', 'production_issue'];
        return in_array($type, $outTypes);
    }
}
