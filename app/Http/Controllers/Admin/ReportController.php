<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\ItemCategory;
use App\Models\GoodsTransferNote;
use App\Models\GrnMaster;
use App\Models\StockReleaseNoteMaster;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Supplier;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Get the organization ID based on user role
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

    public function index()
    {
        return view('admin.reports.index');
    }

    public function salesReport()
    {
        return view('admin.reports.sales.index');
    }

    public function inventoryReport()
    {
        return view('admin.reports.inventory.index');
    }

    // =================================================================================
    // INVENTORY STOCK REPORT - Detailed stock analysis per item
    // =================================================================================

    public function inventoryStock(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $itemId = $request->get('item_id');
        $categoryId = $request->get('category_id');
        $branchId = $request->get('branch_id');
        $transactionType = $request->get('transaction_type');
        $exportFormat = $request->get('export');

        // Get available items for filter
        $itemsQuery = ItemMaster::query();
        $this->applyOrganizationFilter($itemsQuery, $orgId);
        $items = $itemsQuery->where('is_active', true)->orderBy('name')->get();

        // Get available categories for filter
        $categoriesQuery = ItemCategory::query();
        $this->applyOrganizationFilter($categoriesQuery, $orgId);
        $categories = $categoriesQuery->orderBy('name')->get();

        // Get available branches for filter
        $branchesQuery = Branch::query();
        $this->applyOrganizationFilter($branchesQuery, $orgId);
        $branches = $branchesQuery->where('is_active', true)->orderBy('name')->get();

        // Build the main report query
        $reportData = $this->generateStockReport($dateFrom, $dateTo, $itemId, $categoryId, $branchId, $transactionType, $orgId);

        // Handle export requests
        if ($exportFormat) {
            return $this->exportStockReport($reportData, $exportFormat, $dateFrom, $dateTo);
        }

        return view('admin.reports.inventory.stock.index', compact(
            'reportData', 'items', 'categories', 'branches',
            'dateFrom', 'dateTo', 'itemId', 'categoryId', 'branchId', 'transactionType'
        ));
    }

    protected function generateStockReport($dateFrom, $dateTo, $itemId = null, $categoryId = null, $branchId = null, $transactionType = null, $orgId = null)
    {
        $stockData = [];

        // Get base item query
        $itemQuery = ItemMaster::with(['itemCategory'])
            ->where('is_active', true);

        $this->applyOrganizationFilter($itemQuery, $orgId);

        if ($itemId) {
            $itemQuery->where('id', $itemId);
        }

        if ($categoryId) {
            $itemQuery->where('item_category_id', $categoryId);
        }

        $items = $itemQuery->get();

        foreach ($items as $item) {
            // Get branches to analyze
            $branchQuery = Branch::where('is_active', true);
            $this->applyOrganizationFilter($branchQuery, $orgId);

            if ($branchId) {
                $branchQuery->where('id', $branchId);
            }

            $branchesToAnalyze = $branchQuery->get();

            foreach ($branchesToAnalyze as $branch) {
                $stockInfo = $this->calculateStockForItem($item, $branch, $dateFrom, $dateTo, $transactionType);
                if ($stockInfo['has_activity'] || $stockInfo['current_stock'] > 0) {
                    $stockData[] = $stockInfo;
                }
            }
        }

        return collect($stockData)->sortBy(['item_name', 'branch_name']);
    }

    protected function calculateStockForItem($item, $branch, $dateFrom, $dateTo, $transactionType = null)
    {
        // Calculate opening stock (stock before date_from)
        $openingStockQuery = ItemTransaction::where('inventory_item_id', $item->id)
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->where('created_at', '<', $dateFrom);

        $openingStock = $openingStockQuery->sum('quantity');

        // Get transactions within the period
        $transactionQuery = ItemTransaction::where('inventory_item_id', $item->id)
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($transactionType) {
            $transactionQuery->where('transaction_type', $transactionType);
        }

        $transactions = $transactionQuery->get();

        // Calculate stock movements
        $stockIn = $transactions->where('quantity', '>', 0)->sum('quantity');
        $stockOut = abs($transactions->where('quantity', '<', 0)->sum('quantity'));
        $currentStock = ItemTransaction::stockOnHand($item->id, $branch->id);

        // Break down by transaction types
        $salesQuantity = abs($transactions->whereIn('transaction_type', ['sales_order', 'takeaway_order'])->sum('quantity'));
        $productionUsed = abs($transactions->where('transaction_type', 'production_issue')->sum('quantity'));
        $productionReceived = $transactions->where('transaction_type', 'production_in')->sum('quantity');
        $wastage = abs($transactions->where('transaction_type', 'waste')->sum('quantity'));
        $transfers = $transactions->whereIn('transaction_type', ['gtn_outgoing', 'gtn_incoming'])->sum('quantity');
        $adjustments = $transactions->where('transaction_type', 'adjustment')->sum('quantity');
        $grn = $transactions->where('transaction_type', 'grn_stock_in')->sum('quantity');

        return [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_code' => $item->item_code,
            'category_name' => $item->itemCategory->name ?? 'Uncategorized',
            'unit' => $item->unit_of_measurement,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'opening_stock' => $openingStock,
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
            'current_stock' => $currentStock,
            'closing_stock' => $openingStock + $stockIn - $stockOut,
            'sales_quantity' => $salesQuantity,
            'production_used' => $productionUsed,
            'production_received' => $productionReceived,
            'wastage' => $wastage,
            'transfers' => $transfers,
            'adjustments' => $adjustments,
            'grn_received' => $grn,
            'total_cost' => $transactions->sum('total_amount'),
            'avg_cost_per_unit' => $transactions->where('quantity', '>', 0)->avg('unit_price') ?? 0,
            'has_activity' => $transactions->count() > 0,
            'transaction_count' => $transactions->count(),
            'reorder_level' => $item->reorder_level,
            'stock_status' => $this->getStockStatus($currentStock, $item->reorder_level)
        ];
    }

    protected function getStockStatus($currentStock, $reorderLevel)
    {
        if ($currentStock <= 0) return 'out_of_stock';
        if ($currentStock <= $reorderLevel) return 'low_stock';
        return 'in_stock';
    }

    // =================================================================================
    // CATEGORY REPORT - Aggregated data by categories
    // =================================================================================

    public function categoryReport(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $branchId = $request->get('branch_id');
        $exportFormat = $request->get('export');

        // Get available categories
        $categoriesQuery = ItemCategory::query();
        $this->applyOrganizationFilter($categoriesQuery, $orgId);
        $categories = $categoriesQuery->orderBy('name')->get();

        // Get available branches
        $branchesQuery = Branch::query();
        $this->applyOrganizationFilter($branchesQuery, $orgId);
        $branches = $branchesQuery->where('is_active', true)->orderBy('name')->get();

        // Generate category report
        $reportData = $this->generateCategoryReport($dateFrom, $dateTo, $categoryId, $branchId, $orgId);

        if ($exportFormat) {
            return $this->exportCategoryReport($reportData, $exportFormat, $dateFrom, $dateTo);
        }

        return view('admin.reports.inventory.category.index', compact(
            'reportData', 'categories', 'branches',
            'dateFrom', 'dateTo', 'categoryId', 'branchId'
        ));
    }

    protected function generateCategoryReport($dateFrom, $dateTo, $categoryId = null, $branchId = null, $orgId = null)
    {
        $categoryData = [];

        // Get categories to analyze
        $categoryQuery = ItemCategory::query();
        $this->applyOrganizationFilter($categoryQuery, $orgId);

        if ($categoryId) {
            $categoryQuery->where('id', $categoryId);
        }

        $categories = $categoryQuery->get();

        foreach ($categories as $category) {
            // Get items in this category
            $itemsInCategory = ItemMaster::where('item_category_id', $category->id)
                ->where('is_active', true);

            $this->applyOrganizationFilter($itemsInCategory, $orgId);
            $items = $itemsInCategory->get();

            if ($items->isEmpty()) continue;

            $itemIds = $items->pluck('id');

            // Build transaction query for this category
            $transactionQuery = ItemTransaction::whereIn('inventory_item_id', $itemIds)
                ->where('is_active', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

            if ($branchId) {
                $transactionQuery->where('branch_id', $branchId);
            }

            $transactions = $transactionQuery->get();

            // Calculate aggregated metrics
            $totalValue = $transactions->sum('total_amount');
            $totalStockIn = $transactions->where('quantity', '>', 0)->sum('quantity');
            $totalStockOut = abs($transactions->where('quantity', '<', 0)->sum('quantity'));
            $totalWastage = abs($transactions->where('transaction_type', 'waste')->sum('quantity'));
            $totalSales = abs($transactions->whereIn('transaction_type', ['sales_order', 'takeaway_order'])->sum('quantity'));

            $categoryData[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'total_items' => $items->count(),
                'total_transactions' => $transactions->count(),
                'total_value' => $totalValue,
                'total_stock_in' => $totalStockIn,
                'total_stock_out' => $totalStockOut,
                'total_wastage' => $totalWastage,
                'total_sales' => $totalSales,
                'avg_transaction_value' => $transactions->count() > 0 ? $totalValue / $transactions->count() : 0,
                'current_total_stock' => $this->getCurrentStockForCategory($items, $branchId),
                'wastage_percentage' => $totalStockOut > 0 ? ($totalWastage / $totalStockOut) * 100 : 0,
            ];
        }

        return collect($categoryData)->sortByDesc('total_value');
    }

    protected function getCurrentStockForCategory($items, $branchId = null)
    {
        $totalStock = 0;
        foreach ($items as $item) {
            if ($branchId) {
                $totalStock += ItemTransaction::stockOnHand($item->id, $branchId);
            } else {
                // Sum across all branches for this organization
                $branches = Branch::where('organization_id', $item->organization_id)
                    ->where('is_active', true)->get();
                foreach ($branches as $branch) {
                    $totalStock += ItemTransaction::stockOnHand($item->id, $branch->id);
                }
            }
        }
        return $totalStock;
    }

    // =================================================================================
    // GRN (GOODS RECEIPT NOTE) REPORTS
    // =================================================================================

    public function inventoryGrn(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status');
        $supplierId = $request->get('supplier_id');
        $branchId = $request->get('branch_id');
        $exportFormat = $request->get('export');

        // Get available branches
        $branchesQuery = Branch::query();
        $this->applyOrganizationFilter($branchesQuery, $orgId);
        $branches = $branchesQuery->where('is_active', true)->orderBy('name')->get();

        // Get available suppliers
        $suppliersQuery = Supplier::query();
        $this->applyOrganizationFilter($suppliersQuery, $orgId);
        $suppliers = $suppliersQuery->orderBy('name')->get();

        // Generate GRN report
        $reportData = $this->generateGrnReport($dateFrom, $dateTo, $status, $supplierId, $branchId, $orgId);

        if ($exportFormat) {
            return $this->exportGrnReport($reportData, $exportFormat, $dateFrom, $dateTo);
        }

        $paymentStatus = $request->get('payment_status');

        return view('admin.reports.inventory.grn.index', compact(
            'reportData', 'branches', 'dateFrom', 'dateTo', 'status', 'paymentStatus', 'supplierId', 'branchId', 'suppliers'
        ));
    }

    protected function generateGrnReport($dateFrom, $dateTo, $status = null, $supplierId = null, $branchId = null, $orgId = null)
    {
        // Build GRN query
        $grnQuery = GrnMaster::with(['items', 'supplier', 'branch'])
            ->whereBetween('received_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($grnQuery, $orgId);

        if ($status) {
            $grnQuery->where('status', $status);
        }

        if ($supplierId) {
            $grnQuery->where('supplier_id', $supplierId);
        }

        if ($branchId) {
            $grnQuery->where('branch_id', $branchId);
        }

        $grns = $grnQuery->get();

        $reportData = [];
        foreach ($grns as $grn) {
            $totalAmount = $grn->items->sum('line_total') ?? $grn->total_amount ?? 0;
            $paidAmount = $grn->paid_amount ?? 0;
            $outstandingAmount = max(0, $totalAmount - $paidAmount);
            
            $reportData[] = [
                'grn_id' => $grn->grn_id,
                'grn_number' => $grn->grn_number,
                'supplier_name' => $grn->supplier->name ?? 'N/A',
                'supplier_contact' => $grn->supplier->phone ?? '',
                'branch_name' => $grn->branch->name ?? 'N/A',
                'received_date' => $grn->received_date,
                'receipt_date' => $grn->received_date,
                'due_date' => $grn->due_date ?? null,
                'status' => strtolower($grn->status),
                'items_count' => $grn->items->count(),
                'total_quantity' => $grn->items->sum('received_quantity'),
                'total_purchase_value' => $totalAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => $this->getPaymentStatus($totalAmount, $paidAmount),
                'notes' => $grn->notes,
            ];
        }

        return [
            'grns' => collect($reportData)->sortByDesc('received_date'),
            'summary' => [
                'total_grns' => count($reportData),
                'total_purchase_value' => collect($reportData)->sum('total_purchase_value'),
                'total_paid' => collect($reportData)->sum('paid_amount'),
                'total_outstanding' => collect($reportData)->sum('outstanding_amount'),
                'payment_percentage' => count($reportData) > 0 ? 
                    (collect($reportData)->sum('paid_amount') / collect($reportData)->sum('total_purchase_value')) * 100 : 0,
            ]
        ];
    }

    protected function getPaymentStatus($totalAmount, $paidAmount)
    {
        if ($paidAmount <= 0) return 'unpaid';
        if ($paidAmount >= $totalAmount) return 'paid';
        return 'partial';
    }

    // =================================================================================
    // GTN (GOODS TRANSFER NOTE) REPORTS
    // =================================================================================

    public function inventoryGtn(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $originStatus = $request->get('origin_status');
        $receiverStatus = $request->get('receiver_status');
        $fromBranchId = $request->get('from_branch_id');
        $toBranchId = $request->get('to_branch_id');
        $exportFormat = $request->get('export');

        // Get available branches
        $branchesQuery = Branch::query();
        $this->applyOrganizationFilter($branchesQuery, $orgId);
        $branches = $branchesQuery->where('is_active', true)->orderBy('name')->get();

        // Generate GTN report
        $reportData = $this->generateGtnReport($dateFrom, $dateTo, $originStatus, $receiverStatus, $fromBranchId, $toBranchId, $orgId);

        if ($exportFormat) {
            return $this->exportGtnReport($reportData, $exportFormat, $dateFrom, $dateTo);
        }

        return view('admin.reports.inventory.gtn.index', compact(
            'reportData', 'branches', 'dateFrom', 'dateTo',
            'originStatus', 'receiverStatus', 'fromBranchId', 'toBranchId'
        ));
    }

    protected function generateGtnReport($dateFrom, $dateTo, $originStatus = null, $receiverStatus = null, $fromBranchId = null, $toBranchId = null, $orgId = null)
    {
        // Build GTN query
        $gtnQuery = GoodsTransferNote::with(['items', 'fromBranch', 'toBranch'])
            ->whereBetween('transfer_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($gtnQuery, $orgId);

        if ($originStatus) {
            $gtnQuery->where('origin_status', $originStatus);
        }

        if ($receiverStatus) {
            $gtnQuery->where('receiver_status', $receiverStatus);
        }

        if ($fromBranchId) {
            $gtnQuery->where('from_branch_id', $fromBranchId);
        }

        if ($toBranchId) {
            $gtnQuery->where('to_branch_id', $toBranchId);
        }

        $gtns = $gtnQuery->get();

        $reportData = [];
        foreach ($gtns as $gtn) {
            $totalTransferValue = $gtn->items->sum(function($item) {
                return $item->transfer_quantity * $item->transfer_price;
            });

            $totalAcceptedValue = $gtn->items->sum(function($item) {
                return ($item->quantity_accepted ?? 0) * $item->transfer_price;
            });

            $totalRejectedValue = $gtn->items->sum(function($item) {
                return ($item->quantity_rejected ?? 0) * $item->transfer_price;
            });

            $reportData[] = [
                'gtn_id' => $gtn->gtn_id,
                'gtn_number' => $gtn->gtn_number,
                'from_branch' => $gtn->fromBranch->name ?? 'N/A',
                'to_branch' => $gtn->toBranch->name ?? 'N/A',
                'transfer_date' => $gtn->transfer_date,
                'origin_status' => $gtn->origin_status,
                'receiver_status' => $gtn->receiver_status,
                'items_count' => $gtn->items->count(),
                'total_quantity' => $gtn->items->sum('transfer_quantity'),
                'accepted_quantity' => $gtn->items->sum('quantity_accepted'),
                'rejected_quantity' => $gtn->items->sum('quantity_rejected'),
                'total_transfer_value' => $totalTransferValue,
                'total_accepted_value' => $totalAcceptedValue,
                'total_rejected_value' => $totalRejectedValue,
                'acceptance_rate' => $gtn->items->sum('transfer_quantity') > 0 ?
                    ($gtn->items->sum('quantity_accepted') / $gtn->items->sum('transfer_quantity')) * 100 : 0,
            ];
        }

        return [
            'gtns' => collect($reportData)->sortByDesc('transfer_date'),
            'summary' => [
                'total_gtns' => $gtns->count(),
                'total_transfer_value' => collect($reportData)->sum('total_transfer_value'),
                'total_accepted_value' => collect($reportData)->sum('total_accepted_value'),
                'total_rejected_value' => collect($reportData)->sum('total_rejected_value'),
                'overall_acceptance_rate' => collect($reportData)->avg('acceptance_rate'),
            ]
        ];
    }

    // =================================================================================
    // SRN (STOCK RELEASE NOTE) REPORTS
    // =================================================================================

    public function inventorySrn(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $orgId = $this->getOrganizationId();

        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $releaseType = $request->get('release_type');
        $branchId = $request->get('branch_id');
        $itemId = $request->get('item_id');
        $exportFormat = $request->get('export');

        // Get available branches
        $branchesQuery = Branch::query();
        $this->applyOrganizationFilter($branchesQuery, $orgId);
        $branches = $branchesQuery->where('is_active', true)->orderBy('name')->get();

        // Get available items
        $itemsQuery = ItemMaster::query();
        $this->applyOrganizationFilter($itemsQuery, $orgId);
        $items = $itemsQuery->where('is_active', true)->orderBy('name')->get();

        // Generate SRN report
        $reportData = $this->generateSrnReport($dateFrom, $dateTo, $releaseType, $branchId, $itemId, $orgId);

        if ($exportFormat) {
            return $this->exportSrnReport($reportData, $exportFormat, $dateFrom, $dateTo);
        }

        return view('admin.reports.inventory.srn.index', compact(
            'reportData', 'branches', 'items', 'dateFrom', 'dateTo',
            'releaseType', 'branchId', 'itemId'
        ));
    }

    protected function generateSrnReport($dateFrom, $dateTo, $releaseType = null, $branchId = null, $itemId = null, $orgId = null)
    {
        // Build SRN query
        $srnQuery = StockReleaseNoteMaster::with(['items', 'branch'])
            ->whereBetween('release_date', [$dateFrom, $dateTo]);

        $this->applyOrganizationFilter($srnQuery, $orgId);

        if ($releaseType) {
            $srnQuery->where('release_type', $releaseType);
        }

        if ($branchId) {
            $srnQuery->where('branch_id', $branchId);
        }

        $srns = $srnQuery->get();

        $reportData = [];
        foreach ($srns as $srn) {
            $itemsData = $srn->items;

            if ($itemId) {
                $itemsData = $itemsData->where('item_id', $itemId);
            }

            if ($itemsData->isEmpty()) continue;

            $totalValue = $itemsData->sum('line_total') ?? 0;
            $totalQuantity = $itemsData->sum('release_quantity') ?? 0;

            $reportData[] = [
                'srn_id' => $srn->id,
                'srn_number' => $srn->srn_number,
                'branch_name' => $srn->branch->name ?? 'N/A',
                'release_date' => $srn->release_date,
                'approved_date' => $srn->verified_at,
                'release_type' => $srn->release_type,
                'reason' => $srn->notes ?? 'N/A',
                'items_count' => $itemsData->count(),
                'released_quantity' => $totalQuantity,
                'cost_impact' => $totalValue,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'avg_unit_cost' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0,
                'status' => $srn->status ?? 'completed',
            ];
        }

        return [
            'srns' => collect($reportData)->sortByDesc('release_date'),
            'summary' => [
                'total_srns' => collect($reportData)->count(),
                'total_quantity_released' => collect($reportData)->sum('total_quantity'),
                'total_released_quantity' => collect($reportData)->sum('released_quantity'),
                'total_cost_impact' => collect($reportData)->sum('cost_impact'),
                'total_value_released' => collect($reportData)->sum('total_value'),
                'avg_release_value' => collect($reportData)->count() > 0 ? collect($reportData)->avg('total_value') : 0,
                'release_types' => collect($reportData)->groupBy('release_type')->map->count(),
            ]
        ];
    }

    // =================================================================================
    // EXPORT FUNCTIONS
    // =================================================================================

    protected function exportStockReport($reportData, $format, $dateFrom, $dateTo)
    {
        $filename = "stock_report_{$dateFrom}_to_{$dateTo}";

        if ($format === 'csv') {
            return $this->exportToCsv($reportData, $filename, [
                'Item Name', 'Item Code', 'Category', 'Branch', 'Opening Stock',
                'Stock In', 'Stock Out', 'Current Stock', 'Sales Qty', 'Production Used',
                'Production Received', 'Wastage', 'Transfers', 'Stock Status'
            ], function($item) {
                return [
                    $item['item_name'], $item['item_code'], $item['category_name'],
                    $item['branch_name'], $item['opening_stock'], $item['stock_in'],
                    $item['stock_out'], $item['current_stock'], $item['sales_quantity'],
                    $item['production_used'], $item['production_received'], $item['wastage'],
                    $item['transfers'], $item['stock_status']
                ];
            });
        }

        // Default to CSV for now
        return $this->exportToCsv($reportData, $filename, [
            'Item Name', 'Branch', 'Current Stock', 'Stock Status'
        ], function($item) {
            return [$item['item_name'], $item['branch_name'], $item['current_stock'], $item['stock_status']];
        });
    }

    protected function exportToCsv($data, $filename, $headers, $rowCallback)
    {
        $output = fopen('php://temp', 'w');
        fputcsv($output, $headers);

        foreach ($data as $item) {
            fputcsv($output, $rowCallback($item));
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    protected function exportCategoryReport($reportData, $format, $dateFrom, $dateTo)
    {
        $filename = "category_report_{$dateFrom}_to_{$dateTo}";

        return $this->exportToCsv($reportData['categories'] ?? $reportData, $filename, [
            'Category', 'Total Items', 'Total Value', 'Stock In', 'Stock Out', 'Wastage %'
        ], function($item) {
            return [
                $item['category_name'], $item['total_items'], $item['total_value'],
                $item['total_stock_in'], $item['total_stock_out'],
                number_format($item['wastage_percentage'], 2) . '%'
            ];
        });
    }

    protected function exportGrnReport($reportData, $format, $dateFrom, $dateTo)
    {
        $filename = "grn_report_{$dateFrom}_to_{$dateTo}";

        return $this->exportToCsv($reportData['grns'], $filename, [
            'GRN Number', 'Supplier', 'Branch', 'Date', 'Status',
            'Total Amount', 'Paid Amount', 'Outstanding', 'Payment Status'
        ], function($item) {
            return [
                $item['grn_number'], $item['supplier_name'], $item['branch_name'],
                $item['received_date'], $item['status'], $item['total_amount'],
                $item['paid_amount'], $item['outstanding_amount'], $item['payment_status']
            ];
        });
    }

    protected function exportGtnReport($reportData, $format, $dateFrom, $dateTo)
    {
        $filename = "gtn_report_{$dateFrom}_to_{$dateTo}";

        return $this->exportToCsv($reportData['gtns'], $filename, [
            'GTN Number', 'From Branch', 'To Branch', 'Date', 'Origin Status',
            'Receiver Status', 'Total Quantity', 'Accepted Quantity', 'Acceptance Rate %'
        ], function($item) {
            return [
                $item['gtn_number'], $item['from_branch'], $item['to_branch'],
                $item['transfer_date'], $item['origin_status'], $item['receiver_status'],
                $item['total_quantity'], $item['accepted_quantity'],
                number_format($item['acceptance_rate'], 2) . '%'
            ];
        });
    }

    protected function exportSrnReport($reportData, $format, $dateFrom, $dateTo)
    {
        $filename = "srn_report_{$dateFrom}_to_{$dateTo}";

        return $this->exportToCsv($reportData['srns'], $filename, [
            'SRN Number', 'Branch', 'Date', 'Release Type', 'Reason',
            'Total Quantity', 'Total Value', 'Status'
        ], function($item) {
            return [
                $item['srn_number'], $item['branch_name'], $item['release_date'],
                $item['release_type'], $item['reason'], $item['total_quantity'],
                $item['total_value'], $item['status']
            ];
        });
    }
}
