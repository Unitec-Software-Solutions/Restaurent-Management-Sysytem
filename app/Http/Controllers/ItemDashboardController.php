<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ItemDashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the inventory dashboard.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $admin->isSuperAdmin();
        
        // Basic validation - super admins don't need organization
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.login')->with('error', 'Account setup incomplete. Contact support.');
        }

        // Super admins can see all items, others see their organization's
        $orgId = $isSuperAdmin ? null : $admin->organization_id;

        // Total Items
        $totalItems = ItemMaster::active()
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->count();

        // New Items Today
        $newItemsToday = ItemMaster::active()
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->count();

        // Total Stock Value
        $totalStockValue = DB::table('item_master')
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('item_master.organization_id', $orgId);
            })
            ->select(DB::raw('SUM(item_transactions.quantity * item_master.buying_price) as total_stock_value'))
            ->first()->total_stock_value ?? 0;

        // Stock Value Change (from yesterday)
        $yesterdayStockValue = DB::table('item_master')
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('item_master.organization_id', $orgId);
            })
            ->whereDate('item_transactions.created_at', now()->subDay())
            ->select(DB::raw('SUM(item_transactions.quantity * item_master.buying_price) as total_stock_value'))
            ->first()->total_stock_value ?? 0;

        $stockValueChange = $totalStockValue - $yesterdayStockValue;

        // Purchase Orders
        $purchaseOrders = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->where('transaction_type', 'purchase_order')
            ->get();

        $purchaseOrdersTotal = $purchaseOrders->sum('cost_price');
        $purchaseOrdersCount = $purchaseOrders->count();

        // Sales Orders
        $salesOrders = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->where('transaction_type', 'sales_order')
            ->get();

        $salesOrdersTotal = $salesOrders->sum('unit_price');
        $salesOrdersCount = $salesOrders->count();

        // Low Stock Items
        $lowStockItems = ItemMaster::with('category')
            ->active()
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->whereHas('transactions', function ($query) {
                $query->whereColumn('quantity', '<=', 'item_master.reorder_level');
            })
            ->get();

        // Top Selling Items
        $topSellingItems = ItemMaster::with('category')
            ->active()
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('item_master.organization_id', $orgId);
            })
            ->join('item_transactions', 'item_master.id', '=', 'item_transactions.inventory_item_id')
            ->where('item_transactions.transaction_type', 'sales_order')
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('item_transactions.organization_id', $orgId);
            })
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
        $purchaseOrderQuantity = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->where('transaction_type', 'purchase_order')
            ->sum('quantity');

        $purchaseOrderTotalCost = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->where('transaction_type', 'purchase_order')
            ->sum('cost_price');

        // Sales Order Details
        $salesOrders = ItemTransaction::with(['item', 'branch'])
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->where('transaction_type', 'sales_order')
            ->latest()
            ->take(10)
            ->get();

        // Item list with filters
        $items = ItemMaster::with('category')
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
            ->when(request('search'), function ($query) {
                $search = strtolower(request('search'));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(item_code) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->when(request('category'), function ($query) {
                return $query->where('item_category_id', request('category'));
            })
            ->when(request()->has('status'), function ($query) {
                return $query->where('is_active', request('status'));
            })
            ->paginate(15);

        // Active Categories
        $categories = ItemCategory::active()
            ->when(!$isSuperAdmin, function($q) use ($orgId) {
                return $q->where('organization_id', $orgId);
            })
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
            'salesOrders',
            'items',
            'categories'
        ));
    }
}
