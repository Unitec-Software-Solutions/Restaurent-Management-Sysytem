<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('admin.inventory.dashboard');
    }
    
    //         return redirect()->route('admin.login')->with('error', 'Please log in to access the inventory dashboard.');
    //     }

    //     // Super admin check - bypass organization requirements
    //     $isSuperAdmin = $admin->is_super_admin;

    //     // Enhanced validation - only non-super admins need organization
    //     if (!$isSuperAdmin && !$admin->organization_id) {
    //         return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
    //     }

    //     // Super admins can see all items, others see their organization's
    //     $orgId = $isSuperAdmin ? null : $admin->organization_id;

    //     // Total Items with proper super admin handling
    //     $totalItems = $isSuperAdmin ?
    //         ItemMaster::active()->count() :
    //         ItemMaster::active()->where('organization_id', $orgId)->count();

    //     // New Items Today with proper super admin handling
    //     $newItemsToday = $isSuperAdmin ?
    //         ItemMaster::active()->whereDate('created_at', now()->format('Y-m-d'))->count() :
    //         ItemMaster::active()->where('organization_id', $orgId)->whereDate('created_at', now()->format('Y-m-d'))->count();


    //     // Total Stock Value
    //     $totalStockValue = DB::table('item_masters')
    //         ->join('item_transactions', 'item_masters.id', '=', 'item_transactions.inventory_item_id')
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('item_masters.organization_id', $orgId);
    //         })
    //         ->select(DB::raw('SUM(item_transactions.quantity * item_masters.buying_price) as total_stock_value'))
    //         ->first()->total_stock_value ?? 0;

    //     // Stock Value Change (from yesterday)
    //     $yesterdayStockValue = DB::table('item_masters')
    //         ->join('item_transactions', 'item_masters.id', '=', 'item_transactions.inventory_item_id')
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('item_masters.organization_id', $orgId);
    //         })
    //         ->whereDate('item_transactions.created_at', now()->subDay())
    //         ->select(DB::raw('SUM(item_transactions.quantity * item_masters.buying_price) as total_stock_value'))
    //         ->first()->total_stock_value ?? 0;

    //     // Total Stock Value - calculate using current stock levels
    //     $totalStockValue = 0;
    //     $items = ItemMaster::where('organization_id', $orgId)->get();
    //     foreach ($items as $item) {
    //         $currentStock = ItemTransaction::stockOnHand($item->id);
    //         $totalStockValue += $currentStock * ($item->buying_price ?? 0);
    //     }

    //     // Stock Value Change (from yesterday) - simplified approach
    //     $yesterdayTransactions = ItemTransaction::where('organization_id', $orgId)
    //         ->whereDate('created_at', now()->subDay())
    //         ->get();


    //     $yesterdayStockChange = 0;
    //     foreach ($yesterdayTransactions as $transaction) {
    //         $item = $transaction->item;
    //         if ($item) {
    //             $yesterdayStockChange += $transaction->quantity * ($item->buying_price ?? 0);
    //         }
    //     }
    //     $stockValueChange = $yesterdayStockChange;

    //     // Purchase Orders
    //     $purchaseOrders = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->where('transaction_type', 'purchase_order')
    //         ->get();

    //     $purchaseOrdersTotal = $purchaseOrders->sum(function($transaction) {
    //         return $transaction->quantity * ($transaction->cost_price ?? 0);
    //     });
    //     $purchaseOrdersCount = $purchaseOrders->count();

    //     // Sales Orders
    //     $salesOrders = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->where('transaction_type', 'sales_order')
    //         ->get();

    //     $salesOrdersTotal = $salesOrders->sum('unit_price');
    //     $salesOrdersCount = $salesOrders->count();

    //     // Low Stock Items
    //     $lowStockItems = ItemMaster::with('category')
    //         ->active()
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->whereHas('transactions', function ($query) {
    //             $query->whereColumn('quantity', '<=', 'item_masters.reorder_level');
    //         })
    //         ->get();

    //     // Top Selling Items
    //     $topSellingItems = ItemMaster::with('category')
    //         ->active()
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('item_masters.organization_id', $orgId);
    //         })
    //         ->join('item_transactions', 'item_masters.id', '=', 'item_transactions.inventory_item_id')
    //         ->where('item_transactions.transaction_type', 'sales_order')
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('item_transactions.organization_id', $orgId);
    //         })
    //         ->select(
    //             'item_masters.*',
    //             DB::raw('SUM(item_transactions.quantity) as quantity_sold'),
    //             DB::raw('SUM(item_transactions.unit_price * item_transactions.quantity) as revenue')
    //         )
    //         ->groupBy('item_masters.id')
    //         ->orderByDesc('quantity_sold')
    //         ->limit(10)
    //         ->get();

    //     // Purchase Order Summary
    //     $purchaseOrderQuantity = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->where('transaction_type', 'purchase_order')
    //         ->sum('quantity');

    //     $purchaseOrderTotalCost = ItemTransaction::when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->where('transaction_type', 'purchase_order')
    //         ->sum('cost_price');

    //     // Sales Order Details
    //     $salesOrders = ItemTransaction::with(['item', 'branch'])
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->where('transaction_type', 'sales_order')
    //         ->latest()
    //         ->take(10)
    //         ->get();

    //     // Item list with filters
    //     $items = ItemMaster::with('category')
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->when(request('search'), function ($query) {
    //             $search = strtolower(request('search'));
    //             $query->where(function ($q) use ($search) {
    //                 $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
    //                     ->orWhereRaw('LOWER(item_code) LIKE ?', ['%' . $search . '%']);
    //             });
    //         })
    //         ->when(request('category'), function ($query) {
    //             return $query->where('item_category_id', request('category'));
    //         })
    //         ->when(request()->has('status'), function ($query) {
    //             return $query->where('is_active', request('status'));
    //         })
    //         ->paginate(15);

    //     // Active Categories
    //     $categories = ItemCategory::active()
    //         ->when(!$isSuperAdmin, function($q) use ($orgId) {
    //             return $q->where('organization_id', $orgId);
    //         })
    //         ->get();

    //     return view('admin.inventory.dashboard', compact(
    //         'totalItems',
    //         'newItemsToday',
    //         'totalStockValue',
    //         'stockValueChange',
    //         'purchaseOrdersTotal',
    //         'purchaseOrdersCount',
    //         'salesOrdersTotal',
    //         'salesOrdersCount',
    //         'lowStockItems',
    //         'topSellingItems',
    //         'purchaseOrderQuantity',
    //         'purchaseOrderTotalCost',
    //         'salesOrders',
    //         'items',
    //         'categories'
    //     ));
    // }
}
