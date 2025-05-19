<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()?->branch_id ?? null);
        
        // Get soon to expire items (within next 7 days)
        $soonToExpireItems = InventoryItem::where('is_perishable', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(7))
            ->where('expiry_date', '>=', Carbon::now())
            ->with(['category', 'stocks' => function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            }])
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Get low stock items with additional warning levels
        $lowStockItems = InventoryItem::whereHas('stocks', function ($query) use ($branchId) {
                $query->whereRaw('current_quantity <= reorder_level');
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->with(['category', 'stocks'])
            ->get()
            ->map(function ($item) {
                $currentStock = $item->stocks->sum('current_quantity');
                $item->stock_status = $currentStock <= ($item->reorder_level / 2) ? 'critical' : 'warning';
                return $item;
            });

        // Get recent transactions with expanded details
        $recentTransactions = InventoryTransaction::with(['item', 'branch', 'user'])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calculate total inventory value
        $totalStockValue = InventoryItem::with(['stocks' => function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            }])
            ->get()
            ->sum(function ($item) {
                return $item->stocks->sum(function ($stock) use ($item) {
                    return $stock->current_quantity * $item->unit_cost;
                });
            });

        // Get total number of items
        $totalItems = InventoryItem::count();

        // Calculate stock health score
        $stockHealthScore = $this->calculateStockHealthScore($lowStockItems, $soonToExpireItems);

        // Get top selling items (last 30 days)
        $topSellingItems = InventoryItem::with(['category'])
            ->withCount(['transactions as quantity_sold' => function($query) use ($branchId) {
                $query->select(DB::raw('SUM(quantity)'))
                    ->where('transaction_type', 'out')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->when($branchId, function($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    });
            }])
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->each(function($item) {
                $item->revenue = $item->quantity_sold * $item->price;
            });

        // Get purchase order count (last 30 days)
        $purchaseOrdersCount = InventoryTransaction::where('transaction_type', 'in')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->when($branchId, function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->count();

        // Get sales order count (last 30 days)
        $salesOrdersCount = InventoryTransaction::where('transaction_type', 'out')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->when($branchId, function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->count();

        // Get purchase order summary (last 30 days)
        $purchaseOrderSummary = [
            'quantity_ordered' => InventoryTransaction::where('transaction_type', 'in')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->when($branchId, function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->sum('quantity'),
            'total_cost' => InventoryTransaction::where('transaction_type', 'in')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->when($branchId, function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->sum(DB::raw('quantity * unit_price'))
        ];

        // Simplified sales order channels data (since status column doesn't exist)
        $salesOrderChannels = [
            [
                'name' => 'All Channels',
                'draft' => 0,
                'confirmed' => $salesOrdersCount,
                'packed' => 0,
                'shipped' => 0,
                'invoiced' => $salesOrdersCount
            ]
        ];

        // Active items (items with recent transactions)
        $activeItems = InventoryItem::with(['stocks'])
            ->whereHas('transactions', function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            })
            ->limit(5)
            ->get()
            ->pluck('name')
            ->toArray();

        return view('inventory.dashboard', [
            'soonToExpireItems' => $soonToExpireItems,
            'lowStockItems' => $lowStockItems,
            'recentTransactions' => $recentTransactions,
            'totalItems' => $totalItems,
            'totalStockValue' => $totalStockValue,
            'stockHealthScore' => $stockHealthScore,
            'topSellingItems' => $topSellingItems,
            'activeItems' => $activeItems,
            'purchaseOrdersCount' => $purchaseOrdersCount,
            'salesOrdersCount' => $salesOrdersCount,
            'purchaseOrderSummary' => $purchaseOrderSummary,
            'salesOrderChannels' => $salesOrderChannels
        ]);
    }

    private function calculateStockHealthScore($lowStockItems, $soonToExpireItems)
    {
        $totalItems = InventoryItem::count();
        if ($totalItems === 0) return 100;

        $criticalLowStock = $lowStockItems->where('stock_status', 'critical')->count();
        $warningLowStock = $lowStockItems->where('stock_status', 'warning')->count();
        $expiringItems = $soonToExpireItems->count();

        $score = 100;
        $score -= ($criticalLowStock / $totalItems) * 50; // Heavy penalty for critical items
        $score -= ($warningLowStock / $totalItems) * 25; // Medium penalty for warning items
        $score -= ($expiringItems / $totalItems) * 25; // Medium penalty for expiring items

        return max(0, min(100, $score)); // Ensure score is between 0 and 100
    }

    public function getTransactionHistory(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()?->branch_id ?? null);
        $transactions = InventoryTransaction::with(['item', 'branch', 'user'])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->latest()
            ->paginate(15);

        return view('inventory.transactions.index', compact('transactions'));
    }

    public function getExpiryReport(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()?->branch_id ?? null);
        $daysThreshold = $request->get('days', 30);

        $expiringItems = InventoryItem::where('is_perishable', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays($daysThreshold))
            ->where('expiry_date', '>=', Carbon::now())
            ->with(['category', 'stocks' => function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            }])
            ->get();

        return view('inventory.expiry-report', compact('expiringItems', 'daysThreshold'));
    }
}