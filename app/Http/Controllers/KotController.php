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
            if (!$admin->isSuperAdmin() && $order->organization_id !== $admin->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }

            DB::beginTransaction();

            // Create KOT record
            $kot = Kot::create([
                'order_id' => $order->id,
                'kot_number' => $this->generateKotNumber(),
                'status' => 'pending',
                'created_by' => $admin->id,
                'organization_id' => $order->organization_id,
                'branch_id' => $order->branch_id,
            ]);

            // Create KOT items from order items
            foreach ($order->orderItems as $orderItem) {
                $kot->kotItems()->create([
                    'menu_item_id' => $orderItem->menu_item_id,
                    'quantity' => $orderItem->quantity,
                    'special_instructions' => $orderItem->special_instructions ?? '',
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            Log::info('KOT generated', [
                'kot_id' => $kot->id,
                'order_id' => $order->id,
                'created_by' => $admin->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KOT generated successfully',
                'kot' => $kot->load(['kotItems.menuItem', 'order.customer']),
                'print_url' => route('admin.kots.print', $kot->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to generate KOT', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate KOT'
            ]);
        }
    }

    /**
     * Print KOT view
     */
    public function printKot(Kot $kot)
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

        return view('admin.orders.print-kot', compact('kot'));
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
     * Generate unique KOT number
     */
    private function generateKotNumber()
    {
        $prefix = 'KOT';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }
}
