<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\MenuItem;
use App\Models\ItemMaster;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RealtimeDashboardController extends Controller
{
    protected $inventoryService;
    protected $orderService;
    protected $catalogService;

    public function __construct(
        InventoryService $inventoryService,
        OrderService $orderService,
        ProductCatalogService $catalogService
    ) {
        $this->inventoryService = $inventoryService;
        $this->orderService = $orderService;
        $this->catalogService = $catalogService;
    }

    /**
     * Show the real-time inventory dashboard
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $branchId = $admin->branch_id ?? $request->input('branch_id', 1);

        // Get dashboard statistics
        $stats = $this->getDashboardStats($branchId);
        
        // Get recent orders
        $recentOrders = $this->getRecentOrders($branchId, 10);
        
        // Get low stock items
        $lowStockItems = $this->inventoryService->getLowStockItems($branchId);
        
        // Get menu availability stats
        $menuStats = $this->getMenuAvailabilityStats($branchId);

        return view('admin.dashboard.realtime-inventory', compact(
            'stats',
            'recentOrders', 
            'lowStockItems',
            'menuStats'
        ));
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats(int $branchId): array
    {
        $today = Carbon::today();
        
        // Order statistics
        $todayOrders = Order::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->get();

        $orderStats = $this->orderService->getOrderStatistics($branchId, 1);
        
        // Stock statistics  
        $stockSummary = $this->inventoryService->getStockSummary($branchId);

        return [
            'total_orders' => $todayOrders->count(),
            'total_revenue' => $todayOrders->where('status', 'confirmed')->sum('total_amount'),
            'pending_orders' => $todayOrders->where('status', 'pending')->count(),
            'cancelled_orders' => $todayOrders->where('status', 'cancelled')->count(),
            'low_stock_count' => $stockSummary['low_stock_count'],
            'out_of_stock_count' => $stockSummary['out_of_stock_count'],
            'unavailable_menu_items_count' => $stockSummary['unavailable_menu_items_count'],
            'cancellation_rate' => $orderStats['cancellation_rate'],
            'average_order_value' => $orderStats['average_order_value'],
        ];
    }

    /**
     * Get recent orders for the dashboard
     */
    protected function getRecentOrders(int $branchId, int $limit = 10)
    {
        return Order::with(['reservation'])
            ->where('branch_id', $branchId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get menu availability statistics
     */
    protected function getMenuAvailabilityStats(int $branchId): array
    {
        $catalogService = app(ProductCatalogService::class);
        return $catalogService->getProductAnalytics($branchId)['availability_stats'];
    }

    /**
     * API endpoint: Get recent orders
     */
    public function getRecentOrdersApi(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        $limit = $request->input('limit', 10);
        
        $orders = $this->getRecentOrders($branchId, $limit);
        
        return response()->json($orders->map(function($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number ?? 'ORD-' . $order->id,
                'customer_name' => $order->customer_name ?? $order->reservation->name ?? 'Guest',
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at->toISOString(),
            ];
        }));
    }

    /**
     * API endpoint: Get low stock items
     */
    public function getLowStockItemsApi(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        $lowStockItems = $this->inventoryService->getLowStockItems($branchId);
        
        return response()->json($lowStockItems);
    }

    /**
     * API endpoint: Get stock levels for chart
     */
    public function getStockLevelsChart(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        
        // Get top items by usage/importance
        $items = ItemMaster::where('branch_id', $branchId)
            ->active()
            ->limit(15) // Show top 15 items
            ->get();

        $labels = [];
        $currentStock = [];
        $reorderLevels = [];

        foreach ($items as $item) {
            $labels[] = $item->name;
            $currentStock[] = $this->inventoryService->getCurrentStock($item->id, $branchId);
            $reorderLevels[] = $item->reorder_level;
        }

        return response()->json([
            'labels' => $labels,
            'current_stock' => $currentStock,
            'reorder_levels' => $reorderLevels,
        ]);
    }

    /**
     * API endpoint: Get menu availability stats
     */
    public function getMenuAvailabilityStatsApi(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        $stats = $this->getMenuAvailabilityStats($branchId);
        
        return response()->json($stats);
    }

    /**
     * API endpoint: Export stock report
     */
    public function exportStockReport(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        
        // Get all inventory items with current stock levels
        $items = ItemMaster::where('branch_id', $branchId)
            ->active()
            ->with(['category'])
            ->get();

        $exportData = [];
        foreach ($items as $item) {
            $currentStock = $this->inventoryService->getCurrentStock($item->id, $branchId);
            $stockStatus = $item->getStockStatus($branchId);
            
            $exportData[] = [
                'Item Name' => $item->name,
                'Category' => $item->category->name ?? 'Uncategorized',
                'Current Stock' => $currentStock,
                'Unit' => $item->unit_of_measurement,
                'Reorder Level' => $item->reorder_level,
                'Stock Status' => ucfirst(str_replace('_', ' ', $stockStatus)),
                'Buying Price' => $item->buying_price,
                'Selling Price' => $item->selling_price,
                'Last Updated' => $item->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // Create Excel file
        $filename = 'stock-report-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx';
        
        // Use a simple CSV export for now (can be enhanced with proper Excel library)
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            if (!empty($exportData)) {
                fputcsv($file, array_keys($exportData[0]));
                
                // Write data
                foreach ($exportData as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API endpoint: Get dashboard alerts
     */
    public function getDashboardAlerts(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        
        $alerts = [];
        
        // Order alerts
        $orderAlerts = $this->orderService->getOrderAlerts($branchId);
        $alerts = array_merge($alerts, $orderAlerts);
        
        // Stock alerts
        $stockSummary = $this->inventoryService->getStockSummary($branchId);
        
        if ($stockSummary['out_of_stock_count'] > 0) {
            $alerts[] = [
                'type' => 'error',
                'message' => $stockSummary['out_of_stock_count'] . ' items are out of stock',
                'action' => 'Review inventory'
            ];
        }

        if ($stockSummary['low_stock_count'] > 5) {
            $alerts[] = [
                'type' => 'warning', 
                'message' => $stockSummary['low_stock_count'] . ' items are running low',
                'action' => 'Consider reordering'
            ];
        }

        if ($stockSummary['unavailable_menu_items_count'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => $stockSummary['unavailable_menu_items_count'] . ' menu items are unavailable',
                'action' => 'Update menu or restock ingredients'
            ];
        }

        return response()->json($alerts);
    }

    /**
     * API endpoint: Get real-time dashboard summary
     */
    public function getDashboardSummary(Request $request)
    {
        $branchId = Auth::guard('admin')->user()->branch_id ?? $request->input('branch_id');
        
        $stats = $this->getDashboardStats($branchId);
        $alerts = $this->getDashboardAlerts($request)->getData();
        $stockSummary = $this->inventoryService->getStockSummary($branchId);
        $menuStats = $this->getMenuAvailabilityStats($branchId);

        return response()->json([
            'stats' => $stats,
            'alerts' => $alerts,
            'stock_summary' => $stockSummary,
            'menu_stats' => $menuStats,
            'last_updated' => Carbon::now()->toISOString(),
        ]);
    }
}
