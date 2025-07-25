<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\GoodsTransferNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display inventory dashboard - Fixed to avoid redirect loops
     */
    public function index()
    {
        // Directly serve the inventory dashboard instead of redirecting
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the inventory dashboard.');
        }

        // Super admin check - bypass organization requirements
        $isSuperAdmin = $admin->is_super_admin;

        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete. Contact support.');
        }

        // Get inventory summary data - Simplified to avoid non-existent columns
        $orgId = $isSuperAdmin ? null : $admin->organization_id;

        $totalItems = $isSuperAdmin ?
            ItemMaster::active()->count() :
            ItemMaster::active()->where('organization_id', $orgId)->count();

        // Low stock calculation would need to be done via ItemTransaction model
        // For now, set to 0 to avoid database errors
        $lowStockItems = 0;

        return view('admin.inventory.index', compact('totalItems', 'lowStockItems'));
    }

    /**
     * Display items management - Fixed to avoid redirect loops
     */
    public function items()
    {
        // Handle the actual items listing instead of redirecting
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in.');
        }

        $isSuperAdmin = $admin->is_super_admin;

        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete.');
        }

        $orgId = $isSuperAdmin ? null : $admin->organization_id;

        $items = $isSuperAdmin ?
            ItemMaster::active()->paginate(15) :
            ItemMaster::active()->where('organization_id', $orgId)->paginate(15);

        return view('admin.inventory.items.index', compact('items'));
    }

    /**
     * Display stock management - Fixed to avoid redirect loops
     */
    public function stock()
    {
        // Handle stock management directly
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in.');
        }

        $isSuperAdmin = $admin->is_super_admin;

        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete.');
        }

        $orgId = $isSuperAdmin ? null : $admin->organization_id;

        $transactions = $isSuperAdmin ?
            ItemTransaction::latest()->paginate(15) :
            ItemTransaction::where('organization_id', $orgId)->latest()->paginate(15);

        return view('admin.inventory.stock.index', compact('transactions'));
    }

    /**
     * Display GTN management - Fixed to avoid redirect loops
     */
    public function gtn()
    {
        // Handle GTN management directly
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in.');
        }

        $isSuperAdmin = $admin->is_super_admin;

        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete.');
        }

        $orgId = $isSuperAdmin ? null : $admin->organization_id;

        $gtns = $isSuperAdmin ?
            GoodsTransferNote::latest()->paginate(15) :
            GoodsTransferNote::where('organization_id', $orgId)->latest()->paginate(15);

        return view('admin.inventory.gtn.index', compact('gtns'));
    }
}
