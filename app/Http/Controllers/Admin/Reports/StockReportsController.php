<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockMultiSheetExport;

class StockReportsController extends Controller
{
    /**
     * Get organization ID for filtering
     */
    protected function getOrganizationId()
    {
        $user = Auth::guard('admin')->user();
        return $user->is_super_admin ? null : $user->organization_id;
    }

    /**
     * Apply organization filter to query
     */
    protected function applyOrganizationFilter($query, $orgId = null)
    {
        $organizationId = $orgId ?? $this->getOrganizationId();
        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }
        return $query;
    }

    /**
     * Stock Reports Main Index
     */
    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        return view('admin.reports.stock.index', compact('branches', 'items'));
    }

    /**
     * Current Stock Levels Report
     */
    public function currentStock(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $branchId = $request->get('branch_id');
        $itemId = $request->get('item_id');
        $categoryId = $request->get('category_id');
        $lowStockOnly = $request->get('low_stock_only', false);
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        // Generate current stock report
        $reportData = $this->generateCurrentStockReport($branchId, $itemId, $categoryId, $lowStockOnly, $orgId);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportCurrentStockReport($reportData, $exportFormat, $request);
        }

        return view('admin.reports.stock.current', compact(
            'reportData', 'branches', 'items',
            'branchId', 'itemId', 'categoryId', 'lowStockOnly'
        ));
    }

    /**
     * Stock Movement Report
     */
    public function stockMovement(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $itemId = $request->get('item_id');
        $transactionType = $request->get('transaction_type');
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        // Generate stock movement report
        $reportData = $this->generateStockMovementReport($dateFrom, $dateTo, $branchId, $itemId, $transactionType, $orgId);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportStockMovementReport($reportData, $dateFrom, $dateTo, $exportFormat, $request);
        }

        return view('admin.reports.stock.movement', compact(
            'reportData', 'branches', 'items',
            'dateFrom', 'dateTo', 'branchId', 'itemId', 'transactionType'
        ));
    }

    /**
     * Stock Valuation Report
     */
    public function stockValuation(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $itemId = $request->get('item_id');
        $valuationMethod = $request->get('valuation_method', 'fifo'); // fifo, lifo, weighted_average
        $exportFormat = $request->get('export');

        // Get filter options
        $branches = $this->getBranches($orgId);
        $items = $this->getItems($orgId);

        // Generate stock valuation report
        $reportData = $this->generateStockValuationReport($asOfDate, $branchId, $itemId, $valuationMethod, $orgId);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportStockValuationReport($reportData, $asOfDate, $exportFormat, $request);
        }

        return view('admin.reports.stock.valuation', compact(
            'reportData', 'branches', 'items',
            'asOfDate', 'branchId', 'itemId', 'valuationMethod'
        ));
    }

    /**
     * Generate Current Stock Report - FIXED
     */
    private function generateCurrentStockReport($branchId = null, $itemId = null, $categoryId = null, $lowStockOnly = false, $orgId = null)
    {
        // Build base item query with proper relationships
        $itemQuery = ItemMaster::with(['category', 'organization'])
            ->where('is_active', true);

        $this->applyOrganizationFilter($itemQuery, $orgId);

        if ($itemId) {
            $itemQuery->where('id', $itemId);
        }

        if ($categoryId) {
            $itemQuery->where('item_category_id', $categoryId);
        }

        $items = $itemQuery->get();

        // Build branch query
        $branchQuery = Branch::where('is_active', true);
        $this->applyOrganizationFilter($branchQuery, $orgId);

        if ($branchId) {
            $branchQuery->where('id', $branchId);
        }

        $branches = $branchQuery->get();

        $stockData = [];
        $totalValue = 0;

        foreach ($items as $item) {
            foreach ($branches as $branch) {
                $currentStock = $this->getCurrentStock($item->id, $branch->id);
                $stockValue = $currentStock * ($item->buying_price ?? 0);

                // Apply low stock filter
                if ($lowStockOnly && $currentStock > ($item->reorder_level ?? 0)) {
                    continue;
                }

                $lastTransactionDate = $this->getLastTransactionDate($item->id, $branch->id);
                $lastMovementType = $this->getLastMovementType($item->id, $branch->id);
                $daysSinceUpdate = $lastTransactionDate ? Carbon::parse($lastTransactionDate)->diffInDays(Carbon::now()) : 0;

                $stockData[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->item_code ?? 'N/A',
                    'category' => $item->category->name ?? 'Uncategorized',
                    'category_name' => $item->category->name ?? 'Uncategorized',
                    'unit' => $item->unit_of_measurement ?? 'N/A',
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'branch_code' => $branch->code ?? '',
                    'current_stock' => $currentStock,
                    'reserved_stock' => 0, // TODO: Implement reserved stock calculation
                    'minimum_stock' => $item->minimum_stock ?? 0,
                    'reorder_level' => $item->reorder_level ?? 0,
                    'maximum_stock' => $item->maximum_stock_level ?? 0,
                    'unit_cost' => $item->buying_price ?? 0,
                    'buying_price' => $item->buying_price ?? 0,
                    'selling_price' => $item->selling_price ?? 0,
                    'total_value' => $stockValue,
                    'stock_value' => $stockValue,
                    'average_cost' => $item->buying_price ?? 0, // TODO: Calculate weighted average cost
                    'stock_status' => $this->getStockStatus($currentStock, $item->reorder_level ?? 0, $item->maximum_stock_level ?? 0),
                    'last_transaction_date' => $lastTransactionDate,
                    'last_updated' => $lastTransactionDate ?: Carbon::now(),
                    'last_movement_type' => $lastMovementType,
                    'days_since_update' => $daysSinceUpdate,
                    'unit_of_measurement' => $item->unit_of_measurement ?? 'N/A',
                ];

                $totalValue += $stockValue;
            }
        }

        return [
            'data' => collect($stockData)->sortBy(['item_name', 'branch_name']),
            'summary' => [
                'total_items' => count($stockData),
                'total_stock' => collect($stockData)->sum('current_stock'),
                'total_value' => $totalValue,
                'low_stock_items' => collect($stockData)->where('stock_status', 'Low Stock')->count(),
                'out_of_stock_items' => collect($stockData)->where('current_stock', 0)->count(),
                'overstock_items' => collect($stockData)->where('stock_status', 'Overstock')->count(),
                'normal_stock_items' => collect($stockData)->where('stock_status', 'Normal')->count(),
            ]
        ];
    }    /**
     * Generate Stock Movement Report
     */
    private function generateStockMovementReport($dateFrom, $dateTo, $branchId = null, $itemId = null, $transactionType = null, $orgId = null)
    {
        // Build transaction query
        $query = ItemTransaction::with(['itemMaster', 'branch'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('is_active', true);

        $this->applyOrganizationFilter($query, $orgId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($itemId) {
            $query->where('item_master_id', $itemId);
        }

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $movementData = [];
        $totalStockIn = 0;
        $totalStockOut = 0;
        $totalValue = 0;

        foreach ($transactions as $transaction) {
            $movementData[] = [
                'transaction_date' => $transaction->created_at,
                'transaction_type' => $transaction->transaction_type,
                'item_name' => $transaction->itemMaster->name ?? 'N/A',
                'item_code' => $transaction->itemMaster->code ?? 'N/A',
                'branch_name' => $transaction->branch->name ?? 'N/A',
                'reference_type' => $transaction->reference_type,
                'reference_number' => $transaction->reference_number,
                'quantity' => $transaction->quantity,
                'stock_before' => $transaction->stock_before,
                'stock_after' => $transaction->stock_after,
                'unit_price' => $transaction->unit_price ?? 0,
                'total_amount' => $transaction->total_amount ?? 0,
                'batch_code' => $transaction->batch_code,
                'expiry_date' => $transaction->expiry_date,
            ];

            if ($transaction->quantity > 0) {
                $totalStockIn += $transaction->quantity;
            } else {
                $totalStockOut += abs($transaction->quantity);
            }

            $totalValue += $transaction->total_amount ?? 0;
        }

        return [
            'data' => $movementData,
            'summary' => [
                'total_movements' => count($movementData),
                'total_transactions' => count($movementData),
                'total_in_qty' => $totalStockIn,
                'total_stock_in' => $totalStockIn,
                'total_out_qty' => $totalStockOut,
                'total_stock_out' => $totalStockOut,
                'net_movement' => $totalStockIn - $totalStockOut,
                'total_value' => $totalValue,
                'transaction_types' => $this->getTransactionTypeSummary($transactions),
            ]
        ];
    }

    /**
     * Generate Stock Valuation Report
     */
    private function generateStockValuationReport($asOfDate, $branchId = null, $itemId = null, $valuationMethod = 'fifo', $orgId = null)
    {
        // Build base item query
        $itemQuery = ItemMaster::with(['category'])
            ->where('is_active', true);

        $this->applyOrganizationFilter($itemQuery, $orgId);

        if ($itemId) {
            $itemQuery->where('id', $itemId);
        }

        $items = $itemQuery->get();

        // Build branch query
        $branchQuery = Branch::where('is_active', true);
        $this->applyOrganizationFilter($branchQuery, $orgId);

        if ($branchId) {
            $branchQuery->where('id', $branchId);
        }

        $branches = $branchQuery->get();

        $valuationData = [];
        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($items as $item) {
            foreach ($branches as $branch) {
                $currentStock = $this->getCurrentStock($item->id, $branch->id);

                if ($currentStock <= 0) {
                    continue;
                }

                $valuationPrice = $this->calculateValuationPrice($item->id, $branch->id, $asOfDate, $valuationMethod);
                $stockValue = $currentStock * $valuationPrice;

                $valuationData[] = [
                    'item_name' => $item->name,
                    'item_code' => $item->item_code ?? 'N/A',
                    'category_name' => $item->category->name ?? 'N/A',
                    'branch_name' => $branch->name,
                    'current_stock' => $currentStock,
                    'valuation_price' => $valuationPrice,
                    'stock_value' => $stockValue,
                    'cost_price' => $item->cost_price ?? 0,
                    'variance' => $valuationPrice - ($item->cost_price ?? 0),
                    'variance_percentage' => ($item->cost_price ?? 0) > 0 ? (($valuationPrice - ($item->cost_price ?? 0)) / ($item->cost_price ?? 0)) * 100 : 0,
                ];

                $totalQuantity += $currentStock;
                $totalValue += $stockValue;
            }
        }

        // Find top category by value
        $categoryData = collect($valuationData)->groupBy('category_name');
        $topCategory = $categoryData->map(function($items, $categoryName) {
            return [
                'category' => $categoryName,
                'value' => $items->sum('stock_value')
            ];
        })->sortByDesc('value')->first();

        return [
            'data' => collect($valuationData)->sortBy(['item_name', 'branch_name']),
            'summary' => [
                'total_items' => count($valuationData),
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'valuation_method' => $valuationMethod,
                'as_of_date' => $asOfDate,
                'average_unit_value' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0,
                'top_category' => $topCategory ? $topCategory['category'] : 'N/A',
            ]
        ];
    }

    /**
     * Get current stock for an item in a branch
     */
    private function getCurrentStock($itemId, $branchId)
    {
        return ItemTransaction::where('item_master_id', $itemId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->sum('quantity');
    }

    /**
     * Get stock status based on levels
     */
    private function getStockStatus($currentStock, $reorderLevel, $maxLevel)
    {
        if ($currentStock <= 0) {
            return 'Out of Stock';
        } elseif ($currentStock <= $reorderLevel) {
            return 'Low Stock';
        } elseif ($maxLevel && $currentStock >= $maxLevel) {
            return 'Overstock';
        } else {
            return 'Normal';
        }
    }

    /**
     * Get last transaction date for an item in a branch
     */
    private function getLastTransactionDate($itemId, $branchId)
    {
        $transaction = ItemTransaction::where('item_master_id', $itemId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();

        return $transaction ? $transaction->created_at : null;
    }

    /**
     * Get last movement type for an item in a branch
     */
    private function getLastMovementType($itemId, $branchId)
    {
        $transaction = ItemTransaction::where('item_master_id', $itemId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();

        return $transaction ? ucfirst(str_replace('_', ' ', $transaction->transaction_type)) : null;
    }

    /**
     * Calculate valuation price based on method
     */
    private function calculateValuationPrice($itemId, $branchId, $asOfDate, $method)
    {
        $transactions = ItemTransaction::where('item_master_id', $itemId)
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0) // Only stock-in transactions
            ->where('created_at', '<=', $asOfDate . ' 23:59:59')
            ->where('is_active', true)
            ->orderBy('created_at', $method === 'fifo' ? 'asc' : 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            $item = ItemMaster::find($itemId);
            return $item ? $item->cost_price ?? 0 : 0;
        }

        switch ($method) {
            case 'fifo':
            case 'lifo':
                return $transactions->first()->unit_price ?? 0;

            case 'weighted_average':
                $totalValue = $transactions->sum(function($t) {
                    return $t->quantity * ($t->unit_price ?? 0);
                });
                $totalQuantity = $transactions->sum('quantity');
                return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;

            default:
                return $transactions->avg('unit_price') ?? 0;
        }
    }

    /**
     * Get transaction type summary
     */
    private function getTransactionTypeSummary($transactions)
    {
        $summary = [];
        foreach ($transactions as $transaction) {
            $type = $transaction->transaction_type;
            if (!isset($summary[$type])) {
                $summary[$type] = [
                    'count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                ];
            }
            $summary[$type]['count']++;
            $summary[$type]['total_quantity'] += abs($transaction->quantity);
            $summary[$type]['total_value'] += $transaction->total_amount ?? 0;
        }
        return $summary;
    }

    /**
     * Export Current Stock Report
     */
    private function exportCurrentStockReport($reportData, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportCurrentStockPdf($reportData, $request->get('preview', false));
        } elseif ($format === 'excel') {
            return Excel::download(new StockMultiSheetExport(null, null, 'current_stock'),
                'current-stock-report-' . Carbon::now()->format('Y-m-d') . '.xlsx');
        }
    }

    /**
     * Export other stock reports (similar pattern)
     */
    private function exportStockMovementReport($reportData, $dateFrom, $dateTo, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportStockMovementPdf($reportData, $dateFrom, $dateTo, $request->get('preview', false));
        }
    }

    private function exportStockValuationReport($reportData, $asOfDate, $format, $request)
    {
        if ($format === 'pdf') {
            return $this->exportStockValuationPdf($reportData, $asOfDate, $request->get('preview', false));
        }
    }

    /**
     * PDF Export methods (placeholder - implement based on requirements)
     */
    private function exportCurrentStockPdf($reportData, $preview = false)
    {
        if ($preview) {
            return view('admin.reports.stock.pdf.current-preview', compact('reportData'));
        }

        $pdf = PDF::loadView('admin.reports.stock.pdf.current', compact('reportData'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-smart-shrinking' => true,
            ]);

        return $pdf->download('current-stock-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    // Similar methods for other PDF exports...

    /**
     * Get branches for filter
     */
    private function getBranches($orgId)
    {
        $query = Branch::where('is_active', true);
        $this->applyOrganizationFilter($query, $orgId);
        return $query->orderBy('name')->get();
    }

    /**
     * Get items for filter
     */
    private function getItems($orgId)
    {
        $query = ItemMaster::where('is_active', true);
        $this->applyOrganizationFilter($query, $orgId);
        return $query->orderBy('name')->get();
    }
}
