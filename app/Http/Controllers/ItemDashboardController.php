<?php

namespace App\Http\Controllers;

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemDashboardController extends Controller
{
    public function index()
    {
        // Total Items
        $totalItems = ItemMaster::active()->count();

        // New Items Today
        $newItemsToday = ItemMaster::active()->whereDate('created_at', now()->format('Y-m-d'))->count();

        // Total Stock Value
        $totalStockValue = DB::table('item_master')
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->select(DB::raw('SUM(item_transactions.quantity * item_master.buying_price) as total_stock_value'))
            ->first()->total_stock_value ?? 0;

        // Stock Value Change (from yesterday)
        $yesterdayStockValue = DB::table('item_master')
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->whereDate('item_transactions.created_at', now()->subDay())
            ->select(DB::raw('SUM(item_transactions.quantity * item_master.buying_price) as total_stock_value'))
            ->first()->total_stock_value ?? 0;
        $stockValueChange = $totalStockValue - $yesterdayStockValue;

        // Purchase Orders
        $purchaseOrders = ItemTransaction::where('transaction_type', 'purchase_order')->get();
        $purchaseOrdersTotal = $purchaseOrders->sum('cost_price');
        $purchaseOrdersCount = $purchaseOrders->count();

        // Sales Orders
        $salesOrders = ItemTransaction::where('transaction_type', 'sales_order')->get();
        $salesOrdersTotal = $salesOrders->sum('unit_price');
        $salesOrdersCount = $salesOrders->count();

        // Low Stock Items
        $lowStockItems = ItemMaster::with('category')
            ->active()
            ->whereHas('transactions', function ($query) {
                return $query->where('quantity', '<=', DB::raw('item_master.reorder_level'));
            })
            ->get();

        // Top Selling Items
        $topSellingItems = ItemMaster::with('category')
            ->active()
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->where('item_transactions.transaction_type', 'sales_order')
            ->select(
                'item_master.*',
                DB::raw('SUM(item_transactions.quantity) as quantity_sold'),
                DB::raw('SUM(item_transactions.unit_price * item_transactions.quantity) as revenue')
            )
            ->groupBy('item_master.id')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get();

        // Purchase Order Summary
        $purchaseOrderQuantity = ItemTransaction::where('transaction_type', 'purchase_order')->sum('quantity');
        $purchaseOrderTotalCost = ItemTransaction::where('transaction_type', 'purchase_order')->sum('cost_price');

        // Sales Order Details
        $salesOrders = ItemTransaction::with(['item', 'branch'])
            ->where('transaction_type', 'sales_order')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.inventory.dashboard', compact(
            'totalItems',
            'newItemsToday',
            'totalStockValue',
            'stockValueChange',
            'purchaseOrdersTotal',
            'purchaseOrdersCount',
            'salesOrdersTotal',
            'salesOrdersCount',
            'lowStockItems',
            'topSellingItems',
            'purchaseOrderQuantity',
            'purchaseOrderTotalCost',
            'salesOrders'
        ));
    }
}