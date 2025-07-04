<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemMaster;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    /**
     * Get inventory items for organization
     */
    public function index()
    {
        // Check if user is logged in and get organization
        $admin = auth('admin')->user();
        $organization = null;

        if ($admin) {
            // If logged in as super admin, check if organization is in session
            if ($admin->role === 'super_admin' && session('acting_as_org_admin')) {
                $organizationId = session('acting_as_org_admin');
                $organization = Organization::find($organizationId);
            } elseif ($admin->organization_id) {
                // If logged in as org admin
                $organization = $admin->organization;
            }
        }

        if (!$organization) {
            // If accessed directly without proper login, redirect to login
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login as organization admin first'
                ], 401);
            }
            
            return redirect()->route('admin.login')
                ->with('error', 'Please login as organization admin to access inventory');
        }

        $inventoryItems = InventoryItem::with(['itemMaster.itemCategory'])
            ->where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $inventoryItems
            ]);
        }

        return view('inventory.index', compact('inventoryItems', 'organization'));
    }

    /**
     * Store new inventory item (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_of_measurement' => 'required|string|max:50',
            'buying_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'item_category_id' => 'nullable|exists:item_categories,id',
        ]);

        try {
            $admin = auth('admin')->user();
            $organization = $admin->organization;

            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'No organization found'
                ]);
            }

            // Get first active branch
            $branch = $organization->branches()->where('is_active', true)->first();
            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active branch found for this organization'
                ]);
            }

            // Create ItemMaster first
            $itemMaster = ItemMaster::create([
                'name' => $request->name,
                'description' => $request->description,
                'unit_of_measurement' => $request->unit_of_measurement,
                'buying_price' => $request->buying_price,
                'selling_price' => $request->buying_price * 1.3, // 30% markup
                'current_stock' => $request->current_stock,
                'minimum_stock' => $request->minimum_stock,
                'reorder_level' => $request->minimum_stock,
                'maximum_stock' => $request->current_stock * 2,
                'item_category_id' => $request->item_category_id,
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'is_active' => true,
                'item_type' => 'ingredient',
            ]);

            // Create InventoryItem
            $inventoryItem = InventoryItem::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'item_master_id' => $itemMaster->id,
                'current_stock' => $request->current_stock,
                'reorder_level' => $request->minimum_stock,
                'max_stock' => $request->current_stock * 2,
                'cost_price' => $request->buying_price,
                'selling_price' => $request->buying_price * 1.3,
                'status' => 'active',
            ]);

            Log::info('Inventory item created', [
                'organization_id' => $organization->id,
                'inventory_item_id' => $inventoryItem->id,
                'item_master_id' => $itemMaster->id,
                'created_by' => $admin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory item created successfully',
                'inventory_item' => $inventoryItem->load('itemMaster.itemCategory')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create inventory item', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create inventory item: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update inventory item stock
     */
    public function updateStock(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'current_stock' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $admin = auth('admin')->user();
            
            // Check if admin can manage this inventory item's organization
            if (!$admin->isSuperAdmin() && $inventoryItem->organization_id !== $admin->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }

            $oldStock = $inventoryItem->current_stock;
            $inventoryItem->update([
                'current_stock' => $request->current_stock
            ]);

            // Also update ItemMaster stock
            if ($inventoryItem->itemMaster) {
                $inventoryItem->itemMaster->update([
                    'current_stock' => $request->current_stock
                ]);
            }

            Log::info('Inventory stock updated', [
                'inventory_item_id' => $inventoryItem->id,
                'old_stock' => $oldStock,
                'new_stock' => $request->current_stock,
                'updated_by' => $admin->id,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'inventory_item' => $inventoryItem->fresh()->load('itemMaster.itemCategory')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ]);
        }
    }
}
