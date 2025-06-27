<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\MenuItem;
use App\Models\Branch;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Unified Order Workflow Controller
 * 
 * This controller handles all order workflows in a centralized manner:
 * - Reservation-based orders (dine-in)
 * - Takeaway orders (in-call, online, walk-in)
 * - Admin order management
 * 
 * Implements state machine for order status transitions:
 * draft → pending → confirmed → completed/canceled
 */
class OrderWorkflowController extends Controller
{
    protected OrderService $orderService;
    protected InventoryService $inventoryService;

    public function __construct(OrderService $orderService, InventoryService $inventoryService)
    {
        $this->orderService = $orderService;
        $this->inventoryService = $inventoryService;
        
        // Apply admin middleware for admin-specific routes
        $this->middleware('admin.order.defaults')->only([
            'handleAdminFlow', 'createAdminOrder', 'updateAdminOrder'
        ]);
    }

    /**
     * Handle reservation-based order flow
     * Manages orders associated with table reservations
     */
    public function handleReservationFlow(Request $request, ?Reservation $reservation = null)
    {
        try {
            // Validate reservation exists and is valid
            if ($reservation && !$this->isReservationValid($reservation)) {
                return redirect()->route('reservations.create')
                    ->with('error', 'Invalid or expired reservation.');
            }

            $workflow = $this->initializeReservationWorkflow($reservation);
            
            return $this->processWorkflow($request, $workflow);
            
        } catch (\Exception $e) {
            Log::error('Reservation workflow error: ' . $e->getMessage());
            return $this->handleWorkflowError($e, 'reservation');
        }
    }

    /**
     * Handle takeaway order flow
     * Manages takeaway orders (call, online, walk-in)
     */
    public function handleTakeawayFlow(Request $request, string $takeawayType = 'takeaway')
    {
        try {
            // Validate takeaway type
            $validTypes = [
                Order::TYPE_TAKEAWAY_IN_CALL,
                Order::TYPE_TAKEAWAY_ONLINE,
                Order::TYPE_TAKEAWAY_WALKIN_SCHEDULED,
                Order::TYPE_TAKEAWAY_WALKIN_DEMAND
            ];

            if (!in_array($takeawayType, $validTypes)) {
                $takeawayType = Order::TYPE_TAKEAWAY_ONLINE;
            }

            $workflow = $this->initializeTakeawayWorkflow($takeawayType);
            
            return $this->processWorkflow($request, $workflow);
            
        } catch (\Exception $e) {
            Log::error('Takeaway workflow error: ' . $e->getMessage());
            return $this->handleWorkflowError($e, 'takeaway');
        }
    }

    /**
     * Handle admin order management flow
     * Enhanced workflow for admin users with pre-filled defaults
     */
    public function handleAdminFlow(Request $request, ?Order $order = null)
    {
        try {
            // Check admin permissions
            if (!Auth::guard('admin')->check()) {
                return redirect()->route('admin.login')
                    ->with('error', 'Admin authentication required.');
            }

            $workflow = $this->initializeAdminWorkflow($order);
            
            return $this->processWorkflow($request, $workflow, true);
            
        } catch (\Exception $e) {
            Log::error('Admin workflow error: ' . $e->getMessage());
            return $this->handleWorkflowError($e, 'admin');
        }
    }

    /**
     * Initialize reservation workflow context
     */
    private function initializeReservationWorkflow(?Reservation $reservation): array
    {
        return [
            'type' => 'reservation',
            'reservation' => $reservation,
            'order_type' => Order::TYPE_DINE_IN,
            'branch_id' => $reservation?->branch_id,
            'table_id' => $reservation?->table_id,
            'customer_name' => $reservation?->customer_name,
            'customer_phone' => $reservation?->customer_phone,
            'customer_email' => $reservation?->customer_email,
            'default_status' => Order::STATUS_PENDING,
            'redirect_routes' => [
                'success' => 'orders.summary',
                'error' => 'reservations.show'
            ]
        ];
    }

    /**
     * Initialize takeaway workflow context
     */
    private function initializeTakeawayWorkflow(string $takeawayType): array
    {
        return [
            'type' => 'takeaway',
            'order_type' => $takeawayType,
            'branch_id' => request('branch_id'),
            'default_status' => Order::STATUS_PENDING,
            'requires_pickup_time' => true,
            'redirect_routes' => [
                'success' => 'orders.summary',
                'error' => 'orders.takeaway.create'
            ]
        ];
    }

    /**
     * Initialize admin workflow context with defaults
     */
    private function initializeAdminWorkflow(?Order $order): array
    {
        $admin = Auth::guard('admin')->user();
        
        return [
            'type' => 'admin',
            'order' => $order,
            'order_type' => $order?->order_type ?? Order::TYPE_DINE_IN,
            'branch_id' => $admin->branch_id ?? request('branch_id'),
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'placed_by_admin' => true,
            'default_status' => Order::STATUS_CONFIRMED, // Admin orders start confirmed
            'redirect_routes' => [
                'success' => 'admin.orders.show',
                'error' => 'admin.orders.index'
            ]
        ];
    }

    /**
     * Process workflow based on context
     */
    private function processWorkflow(Request $request, array $workflow, bool $isAdmin = false): mixed
    {
        $action = $request->input('action', 'create');
        
        return match($action) {
            'create' => $this->createOrder($request, $workflow),
            'update' => $this->updateOrder($request, $workflow),
            'submit' => $this->submitOrder($request, $workflow),
            'confirm' => $this->confirmOrder($request, $workflow),
            'cancel' => $this->cancelOrder($request, $workflow),
            'complete' => $this->completeOrder($request, $workflow),
            default => $this->showOrderForm($request, $workflow)
        };
    }

    /**
     * Create new order with workflow context
     */
    private function createOrder(Request $request, array $workflow)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'special_instructions' => 'nullable|string|max:1000',
            'requested_time' => 'nullable|date|after:now'
        ]);

        DB::beginTransaction();
        try {
            // Check stock availability for all items
            $stockCheck = $this->validateOrderStock($validated['items'], $workflow['branch_id']);
            if (!$stockCheck['valid']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['stock' => $stockCheck['message']]);
            }

            // Create order with workflow defaults
            $order = $this->orderService->createOrder(array_merge($validated, $workflow));
            
            // Reserve stock for order items
            $this->reserveOrderStock($order, $validated['items']);
            
            DB::commit();
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $order->id)
                ->with('success', 'Order created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create order. Please try again.']);
        }
    }

    /**
     * Update existing order
     */
    private function updateOrder(Request $request, array $workflow)
    {
        $order = $workflow['order'] ?? Order::findOrFail($request->route('order'));
        
        // Validate order can be updated
        if (!$this->canUpdateOrder($order)) {
            return redirect()->back()
                ->withErrors(['status' => 'Order cannot be updated in current status.']);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            // Release previous stock reservations
            $this->releaseOrderStock($order);
            
            // Check stock for updated items
            $stockCheck = $this->validateOrderStock($validated['items'], $order->branch_id);
            if (!$stockCheck['valid']) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['stock' => $stockCheck['message']]);
            }

            // Update order
            $updatedOrder = $this->orderService->updateOrder($order, $validated);
            
            // Reserve stock for updated items
            $this->reserveOrderStock($updatedOrder, $validated['items']);
            
            DB::commit();
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $updatedOrder->id)
                ->with('success', 'Order updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update order. Please try again.']);
        }
    }

    /**
     * Submit order (transition from draft to pending)
     */
    private function submitOrder(Request $request, array $workflow)
    {
        $order = $workflow['order'] ?? Order::findOrFail($request->route('order'));
        
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->back()
                ->withErrors(['status' => 'Order has already been submitted.']);
        }

        try {
            $this->orderService->updateOrderStatus($order, Order::STATUS_SUBMITTED);
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $order->id)
                ->with('success', 'Order submitted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Order submission failed: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Failed to submit order. Please try again.']);
        }
    }

    /**
     * Confirm order (transition to confirmed status)
     */
    private function confirmOrder(Request $request, array $workflow)
    {
        $order = $workflow['order'] ?? Order::findOrFail($request->route('order'));
        
        try {
            $this->orderService->updateOrderStatus($order, Order::STATUS_CONFIRMED);
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $order->id)
                ->with('success', 'Order confirmed successfully!');
                
        } catch (\Exception $e) {
            Log::error('Order confirmation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Failed to confirm order. Please try again.']);
        }
    }

    /**
     * Cancel order and release stock
     */
    private function cancelOrder(Request $request, array $workflow)
    {
        $order = $workflow['order'] ?? Order::findOrFail($request->route('order'));
        
        if (!$this->canCancelOrder($order)) {
            return redirect()->back()
                ->withErrors(['status' => 'Order cannot be cancelled in current status.']);
        }

        DB::beginTransaction();
        try {
            // Release stock reservations
            $this->releaseOrderStock($order);
            
            // Update order status
            $this->orderService->updateOrderStatus($order, Order::STATUS_CANCELLED);
            
            DB::commit();
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $order->id)
                ->with('success', 'Order cancelled successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Failed to cancel order. Please try again.']);
        }
    }

    /**
     * Complete order
     */
    private function completeOrder(Request $request, array $workflow)
    {
        $order = $workflow['order'] ?? Order::findOrFail($request->route('order'));
        
        try {
            $this->orderService->updateOrderStatus($order, Order::STATUS_COMPLETED);
            
            return redirect()
                ->route($workflow['redirect_routes']['success'], $order->id)
                ->with('success', 'Order completed successfully!');
                
        } catch (\Exception $e) {
            Log::error('Order completion failed: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Failed to complete order. Please try again.']);
        }
    }

    /**
     * Show order form based on workflow context
     */
    private function showOrderForm(Request $request, array $workflow)
    {
        $viewData = [
            'workflow' => $workflow,
            'menuItems' => $this->getAvailableMenuItems($workflow['branch_id']),
            'branches' => Branch::active()->get()
        ];

        // Add workflow-specific data
        if ($workflow['type'] === 'reservation' && isset($workflow['reservation'])) {
            $viewData['reservation'] = $workflow['reservation'];
        }

        if ($workflow['type'] === 'admin') {
            $viewData['adminDefaults'] = $this->getAdminDefaults();
        }

        $view = match($workflow['type']) {
            'reservation' => 'orders.create-reservation',
            'takeaway' => 'orders.create-takeaway',
            'admin' => 'admin.orders.create',
            default => 'orders.create'
        };

        return view($view, $viewData);
    }

    /**
     * Validate stock availability for order items
     */
    // private function validateOrderStock(array $items, int $branchId): array
    // {
    //     foreach ($items as $item) {
    //         $menuItem = MenuItem::find($item['id']);
            
    //         if ($menuItem->type === MenuItem::TYPE_BUY_SELL) {
    //             $availableStock = $this->inventoryService->getAvailableStock($menuItem->id, $branchId);
                
    //             if ($availableStock < $item['quantity']) {
    //                 return [
    //                     'valid' => false,
    //                     'message' => "Insufficient stock for {$menuItem->name}. Available: {$availableStock}, Requested: {$item['quantity']}"
    //                 ];
    //             }
    //         }
    //     }

    //     return ['valid' => true];
    // }

    /**
     * Reserve stock for order items
     */
    // private function reserveOrderStock(Order $order, array $items): void
    // {
    //     foreach ($items as $item) {
    //         $menuItem = MenuItem::find($item['id']);
            
    //         if ($menuItem->type === MenuItem::TYPE_BUY_SELL) {
    //             $this->inventoryService->reserveStock(
    //                 $menuItem->id,
    //                 $order->branch_id,
    //                 $item['quantity'],
    //                 $order->id
    //             );
    //         }
    //     }
    // }

    /**
     * Release stock reservations for order
     */
    // private function releaseOrderStock(Order $order): void
    // {
    //     $this->inventoryService->releaseOrderReservations($order->id);
    // }

    /**
     * Get available menu items with stock information
     */
    // private function getAvailableMenuItems(int $branchId): \Illuminate\Database\Eloquent\Collection
    // {
    //     return MenuItem::where('branch_id', $branchId)
    //         ->where('is_active', true)
    //         ->with(['category'])
    //         ->get()
    //         ->map(function ($item) use ($branchId) {
    //             if ($item->type === MenuItem::TYPE_BUY_SELL) {
    //                 $item->available_stock = $this->inventoryService->getAvailableStock($item->id, $branchId);
    //             } else {
    //                 $item->available_stock = null; // KOT items don't track stock
    //             }
    //             return $item;
    //         });
    // }

    /**
     * Get admin defaults for pre-filling forms
     */
    private function getAdminDefaults(): array
    {
        $admin = Auth::guard('admin')->user();
        
        return [
            'branch_id' => $admin->branch_id,
            'organization_id' => $admin->organization_id,
            'order_type' => Order::TYPE_DINE_IN,
            'created_by' => $admin->id,
            'placed_by_admin' => true
        ];
    }

    /**
     * Check if reservation is valid for ordering
     */
    private function isReservationValid(?Reservation $reservation): bool
    {
        if (!$reservation) return false;
        
        return $reservation->status === 'confirmed' && 
               $reservation->reservation_date >= now()->startOfDay();
    }

    /**
     * Check if order can be updated
     */
    private function canUpdateOrder(Order $order): bool
    {
        return in_array($order->status, [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    private function canCancelOrder(Order $order): bool
    {
        return !in_array($order->status, [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED
        ]);
    }

    /**
     * Handle workflow errors
     */
    private function handleWorkflowError(\Exception $e, string $workflowType)
    {
        $routes = [
            'reservation' => 'reservations.create',
            'takeaway' => 'orders.takeaway.create',
            'admin' => 'admin.orders.index'
        ];

        return redirect()
            ->route($routes[$workflowType] ?? 'home')
            ->with('error', 'An error occurred while processing your order. Please try again.');
    }
}
