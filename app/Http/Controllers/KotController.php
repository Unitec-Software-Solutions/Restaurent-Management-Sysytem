<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Kot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KotController extends Controller
{
    /**
     * Generate and print KOT for an order
     */
    public function generateKot(Request $request, Order $order)
    {
        try {
            $admin = auth('admin')->user();
            
            // Check if admin can access this order
            if (!$admin->is_super_admin && $order->organization_id !== $admin->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }

            // Check if KOT already exists for this order
            $existingKot = Kot::where('order_id', $order->id)->first();
            if ($existingKot) {
                return response()->json([
                    'success' => false,
                    'message' => 'KOT already exists for this order',
                    'kot' => $existingKot->load(['kotItems.menuItem', 'order.customer']),
                    'print_url' => route('admin.kots.print', $existingKot->id)
                ]);
            }

            DB::beginTransaction();

            // Filter order items that require KOT (only KOT type items)
            $kotRequiredItems = $order->orderItems()
                                     ->whereHas('menuItem', function($q) {
                                         $q->where('type', \App\Models\MenuItem::TYPE_KOT)
                                           ->where('requires_preparation', true);
                                     })
                                     ->get();

            if ($kotRequiredItems->isEmpty()) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'No items in this order require kitchen preparation'
                ]);
            }

            // Create KOT record
            $kot = Kot::create([
                'order_id' => $order->id,
                'kot_number' => $this->generateKotNumber($order->branch_id),
                'status' => 'pending',
                'created_by' => $admin->id,
                'organization_id' => $order->organization_id,
                'branch_id' => $order->branch_id,
                'table_number' => $order->reservation?->table_number,
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type,
                'priority' => $this->determineKotPriority($order),
                'special_instructions' => $order->special_instructions,
            ]);

            // Create KOT items from order items that require preparation
            foreach ($kotRequiredItems as $orderItem) {
                $menuItem = $orderItem->menuItem;
                
                $kot->kotItems()->create([
                    'order_item_id' => $orderItem->id,
                    'menu_item_id' => $orderItem->menu_item_id,
                    'item_master_id' => $menuItem->item_master_id,
                    'item_name' => $menuItem->name,
                    'item_description' => $menuItem->description,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'special_instructions' => $orderItem->special_instructions ?? '',
                    'customizations' => $orderItem->customizations ?? null,
                    'status' => 'pending',
                    'priority' => $this->determineItemPriority($menuItem, $orderItem),
                    'estimated_prep_time' => $menuItem->preparation_time ?? 15,
                ]);
            }

            // Update order to mark KOT as generated
            $order->update([
                'kot_generated' => true,
                'kot_generated_at' => now(),
                'status' => 'preparing'
            ]);

            DB::commit();

            Log::info('KOT generated', [
                'kot_id' => $kot->id,
                'order_id' => $order->id,
                'created_by' => $admin->id,
                'items_count' => $kotRequiredItems->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KOT generated successfully',
                'kot' => $kot->load(['kotItems.menuItem', 'order.customer']),
                'print_url' => route('admin.kots.print', $kot->id),
                'items_count' => $kotRequiredItems->count()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to generate KOT', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate KOT: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Print KOT view
     */
    public function print(Kot $kot)
    {
        $admin = auth('admin')->user();
        
        // Check if admin can access this KOT
        if (!$admin->isSuperAdmin() && $kot->organization_id !== $admin->organization_id) {
            abort(403, 'Unauthorized access');
        }

        $kot->load([
            'order.customer',
            'kotItems.menuItem',
            'branch',
            'organization'
        ]);

        // Pass the order for compatibility with the view
        $order = $kot->order;

        return view('admin.orders.print-kot', compact('kot', 'order'));
    }

    /**
     * Update KOT item status
     */
    public function updateKotItemStatus(Request $request, $kotId, $itemId)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,completed',
        ]);

        try {
            $kot = Kot::findOrFail($kotId);
            $admin = auth('admin')->user();
            
            // Check if admin can access this KOT
            if (!$admin->isSuperAdmin() && $kot->organization_id !== $admin->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }

            $kotItem = $kot->kotItems()->findOrFail($itemId);
            $kotItem->update([
                'status' => $request->status,
                'updated_by' => $admin->id,
            ]);

            // Check if all items are completed to update KOT status
            $pendingItems = $kot->kotItems()->where('status', '!=', 'completed')->count();
            if ($pendingItems === 0) {
                $kot->update(['status' => 'completed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'KOT item status updated',
                'kot_item' => $kotItem,
                'kot_status' => $kot->fresh()->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update KOT item status'
            ]);
        }
    }

    /**
     * Generate KOT number with branch prefix
     */
    private function generateKotNumber($branchId = null): string
    {
        $prefix = 'KOT';
        
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch) {
                $prefix = strtoupper(substr($branch->code ?? $branch->name, 0, 3)) . '-KOT';
            }
        }
        
        $todayCount = Kot::whereDate('created_at', today())
                         ->when($branchId, function($q) use ($branchId) {
                             $q->where('branch_id', $branchId);
                         })
                         ->count();
        
        $number = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . now()->format('Ymd') . '-' . $number;
    }

    /**
     * Determine KOT priority based on order characteristics
     */
    private function determineKotPriority(Order $order): string
    {
        // VIP customers or special occasions
        if ($order->customer && isset($order->customer->attributes['is_vip'])) {
            return 'high';
        }
        
        // Rush orders or late orders
        if ($order->order_type && str_contains((string)$order->order_type, 'rush')) {
            return 'urgent';
        }
        
        // Large orders
        if ($order->orderItems->sum('quantity') > 10) {
            return 'high';
        }
        
        return 'normal';
    }

    /**
     * Determine individual item priority
     */
    private function determineItemPriority($menuItem, $orderItem): string
    {
        // Long preparation time items get higher priority
        if ($menuItem->preparation_time > 30) {
            return 'high';
        }
        
        // Large quantities
        if ($orderItem->quantity > 5) {
            return 'high';
        }
        
        return 'normal';
    }
}
