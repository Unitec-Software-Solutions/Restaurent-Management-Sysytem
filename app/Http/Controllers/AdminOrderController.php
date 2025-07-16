<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\ItemMaster;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Kot;
use App\Models\ItemCategory;
use App\Models\ItemTransaction;
use App\Models\Organization;
use App\Models\StockReservation;
use App\Traits\Exportable;
use App\Enums\OrderType;
use App\Services\NotificationService;
use App\Services\OrderNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\MenuSafetyService;
use App\Services\EnhancedOrderService;
use App\Services\EnhancedMenuSchedulingService;

class AdminOrderController extends Controller
{
    use Exportable;

    protected $menuSafetyService;
    protected $enhancedOrderService;
    protected $menuSchedulingService;
    protected $notificationService;

    public function __construct(
        MenuSafetyService $menuSafetyService,
        EnhancedOrderService $enhancedOrderService,
        EnhancedMenuSchedulingService $menuSchedulingService,
        NotificationService $notificationService
    ) {
        $this->menuSafetyService = $menuSafetyService;
        $this->enhancedOrderService = $enhancedOrderService;
        $this->menuSchedulingService = $menuSchedulingService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        // Implementation here
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->can('create', Order::class)) {
            abort(403, 'You do not have permission to create orders.');
        }

        $data = $request->validate([
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
            'customer_email' => 'nullable|email|max:255',
            'reservation_id' => 'nullable|exists:reservations,id',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'preferred_contact' => 'nullable|string|in:email,sms',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'order_type' => $data['order_type'],
                'order_time' => $data['order_time'],
                'special_instructions' => $data['special_instructions'] ?? null,
                'status' => 'pending',
                'created_by' => $admin->id,
                'order_date' => now(),
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                $lineTotal = ($menuItem?->price ?? 0) * $item['quantity'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem?->price ?? 0,
                    'subtotal' => $lineTotal,
                    'total_price' => $lineTotal,
                ]);

                if ($menuItem && $menuItem->itemMaster) {
                    $itemMaster = $menuItem->itemMaster;
                    $currentStock = $itemMaster->current_stock ?? 0;
                    $newStock = $currentStock - $item['quantity'];
                    $itemMaster->current_stock = $newStock;
                    $itemMaster->save();
                }
            }

            $tax = $subtotal * 0.10;
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ]);

            DB::commit();
            return redirect()->route('admin.orders.show', $order)
                ->with('success', "Order created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Failed to create order: {$e->getMessage()}");
        }
    }

    public function update(Request $request, Order $order)
    {
        $orderTimeRule = $order->id ? 'required|date|after_or_equal:now' : 'required|date|after_or_equal:now';

        $validated = $request->validate([
            'status' => 'required|in:submitted,preparing,ready,completed,cancelled',
            'order_type' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'order_time' => $orderTimeRule,
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|min:10|max:15',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $order->update([
                'status' => $validated['status'],
                'order_type' => $validated['order_type'],
                'branch_id' => $validated['branch_id'],
                'order_time' => $validated['order_time'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone']
            ]);

            if ($request->has('items') && is_array($request->items)) {
                $selectedItems = [];
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['item_id'])) {
                        $selectedItems[$itemId] = [
                            'item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'] ?? 1
                        ];
                    }
                }

                Log::debug('Selected items for order #' . $order->id, [
                    'items' => $selectedItems,
                    'raw' => $request->items
                ]);

                if (!empty($selectedItems)) {
                    $order->orderItems()->delete();

                    $subtotal = 0;
                    foreach ($selectedItems as $itemData) {
                        $menuItem = ItemMaster::find($itemData['item_id']);
                        if (!$menuItem) continue;

                        $lineTotal = $menuItem->selling_price * $itemData['quantity'];
                        $subtotal += $lineTotal;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_item_id' => $itemData['item_id'],
                            'inventory_item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $menuItem->selling_price,
                            'subtotal' => $lineTotal,
                            'total_price' => $lineTotal
                        ]);
                    }

                    $tax = $subtotal * 0.10;
                    $order->update([
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $subtotal + $tax
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function edit(Order $order)
    {
        $admin = auth('admin')->user();

        if (!$admin->is_super_admin) {
            if ($admin->branch_id && $order->branch_id !== $admin->branch_id) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Access denied to this order');
            } elseif ($admin->organization_id && $order->branch && $order->branch->organization_id !== $admin->organization_id) {
                return redirect()->route('admin.orders.index')
                    ->with('error', 'Access denied to this order');
            }
        }

        $order->load(['orderItems.menuItem', 'branch']);
        $branches = $this->getAdminAccessibleBranches($admin);
        if (!$branches || $branches->isEmpty()) {
            $branches = collect([]);
        }

        $activeMenu = null;
        $menuItems = collect([]);
        if ($order->branch_id) {
            $activeMenu = Menu::getActiveMenuForBranch($order->branch_id);
            if ($activeMenu) {
                $menuItems = $activeMenu->availableMenuItems()->with('itemMaster')->get();
            }
        }

        if ($menuItems->isEmpty()) {
            $menuItems = ItemMaster::select('id', 'name', 'selling_price as price', 'description', 'attributes')
                ->where('is_menu_item', true)
                ->where('is_active', true)
                ->when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })
                ->get();
        }

        foreach ($menuItems as $item) {
            $item->current_stock = ItemTransaction::stockOnHand($item->id, $order->branch_id);
            $item->is_low_stock = $item->current_stock <= ($item->reorder_level ?? 10);
        }

        $categories = ItemCategory::when(!$admin->is_super_admin && $admin->organization_id, function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })
            ->active()
            ->get();

        $statusOptions = [
            'submitted' => 'Submitted',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];

        return view('admin.orders.edit', compact('order', 'statusOptions', 'branches', 'menuItems', 'categories', 'activeMenu'));
    }

    protected function getSearchableColumns(): array
    {
        return ['customer_name', 'customer_phone', 'id'];
    }

    private function checkMenuItemStock($menuItem, $quantity, $branchId)
    {
        return [
            'available' => true,
            'available_quantity' => 999,
            'message' => 'Item available'
        ];
    }

    public function getBranches(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            $organizationId = $request->get('organization_id');

            if (!$admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if (!$organizationId) {
                return response()->json(['error' => 'Organization ID required'], 400);
            }

            $query = Branch::where('organization_id', $organizationId)
                ->where('is_active', true);

            if (!$admin->is_super_admin) {
                if ($admin->organization_id && $admin->organization_id != $organizationId) {
                    return response()->json(['error' => 'Access denied'], 403);
                }

                if ($admin->branch_id) {
                    $query->where('id', $admin->branch_id);
                }
            }

            $branches = $query->select(['id', 'name', 'address', 'phone', 'is_head_office'])
                ->orderBy('is_head_office', 'desc')
                ->orderBy('name')
                ->get();

            Log::info('Admin order branches fetched for organization', [
                'admin_id' => $admin->id,
                'organization_id' => $organizationId,
                'branches_count' => $branches->count()
            ]);

            return response()->json([
                'success' => true,
                'branches' => $branches
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching admin order branches', [
                'organization_id' => $organizationId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch branches'
            ], 500);
        }
    }


}
