<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id', auth()->user()->branch_id ?? null);
        
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
            $item->stock_status = $item->stocks->sum('current_quantity') <= ($item->reorder_level / 2) 
                ? 'critical' 
                : 'warning';
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

        // Get inventory value by category with trends
        $inventoryValueByCategory = InventoryItem::with(['category', 'stocks' => function ($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        }])
        ->get()
        ->groupBy('category.name')
        ->map(function ($items) use ($branchId) {
            $currentValue = $items->sum(function ($item) use ($branchId) {
                return $item->stocks->sum(function ($stock) use ($item) {
                    return $stock->current_quantity * $item->getLastPurchasePrice($stock->branch_id);
                });
            });

            // Calculate 30-day trend
            $lastMonthValue = $this->calculateLastMonthValue($items, $branchId);
            $trend = $lastMonthValue > 0 ? (($currentValue - $lastMonthValue) / $lastMonthValue) * 100 : 0;

            return [
                'value' => $currentValue,
                'trend' => $trend,
                'items_count' => $items->count()
            ];
        });

        $totalItems = InventoryItem::count();
        $totalStockValue = $inventoryValueByCategory->sum('value');

        // Calculate global stock health score
        $stockHealthScore = $this->calculateStockHealthScore($lowStockItems, $soonToExpireItems);

        return view('inventory.dashboard', compact(
            'soonToExpireItems',
            'lowStockItems',
            'recentTransactions',
            'inventoryValueByCategory',
            'totalItems',
            'totalStockValue',
            'stockHealthScore'
        ));
    }

    private function calculateLastMonthValue($items, $branchId)
    {
        $lastMonth = Carbon::now()->subDays(30);
        $value = 0;

        foreach ($items as $item) {
            $historicalTransactions = $item->transactions()
                ->where('created_at', '<=', $lastMonth)
                ->when($branchId, function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($historicalTransactions) {
                $value += $historicalTransactions->current_quantity * $historicalTransactions->unit_price;
            }
        }

        return $value;
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
        $branchId = $request->get('branch_id', auth()->user()->branch_id ?? null);
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());

        $transactions = InventoryTransaction::with(['item', 'branch', 'user'])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('inventory.transactions', compact('transactions'));
    }

    public function getExpiryReport(Request $request)
    {
        $branchId = $request->get('branch_id', auth()->user()->branch_id ?? null);
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