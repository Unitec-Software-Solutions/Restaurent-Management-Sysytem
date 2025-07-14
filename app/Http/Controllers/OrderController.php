<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\InventoryService;
use App\Services\ProductCatalogService;
use App\Services\OrderService;
use App\Services\NotificationService;
use App\Services\OrderNumberService;
use App\Enums\OrderType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\Admin\KotController;

class OrderController extends Controller
{
    protected $inventoryService;
    protected $catalogService;
    protected $orderService;
    protected $notificationService;

    public function __construct(
        InventoryService $inventoryService,
        ProductCatalogService $catalogService,
        OrderService $orderService,
        NotificationService $notificationService
    ) {
        $this->inventoryService = $inventoryService;
        $this->catalogService = $catalogService;
        $this->orderService = $orderService;
        $this->notificationService = $notificationService;
    }

    // List all orders for a reservation (dine-in)
    public function index(Request $request)
    {
        $phone = $request->input('phone');
        $reservationId = $request->input('reservation_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $branchId = $request->input('branch_id');
        $export = $request->input('export');

        // Date filter setup
        if (!$startDate && !$endDate) {
            $startDate = now()->startOfDay()->toDateString();
            $endDate = now()->endOfDay()->toDateString();
        }

        // Query for orders with filters
        $ordersQuery = Order::query()
            ->with(['items.menuItem', 'steward', 'reservation', 'branch'])
            ->when($startDate, fn($q) => $q->whereDate('order_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('order_date', '<=', $endDate))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($reservationId, fn($q) => $q->where('reservation_id', $reservationId))
            ->orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Export functionality
        if ($export) {
            return $this->exportOrders($ordersQuery->get(), $export);
        }

        $orders = $ordersQuery->paginate(15);

        // Active reservations (reservation time is in the future)
        $activeReservations = Reservation::when($phone, function ($query) use ($phone) {
                return $query->where('phone', $phone);
            })
            ->whereRaw('(date > ? OR (date = ? AND end_time >= ?))', [
                now()->toDateString(),
                now()->toDateString(),
                now()->toTimeString()
            ])
            ->with(['orders' => function($query) {
                $query->where('status', '!=', 'completed')->latest();
            }])
            ->latest()
            ->get();

        // Past reservations (reservation time is in the past)
        $pastReservations = Reservation::when($phone, function ($query) use ($phone) {
                return $query->where('phone', $phone);
            })
            ->whereRaw('(date < ? OR (date = ? AND end_time < ?))', [
                now()->toDateString(),
                now()->toDateString(),
                now()->toTimeString()
            ])
            ->with(['orders' => function($query) {
                $query->where('status', 'completed')->latest();
            }])
            ->latest()
            ->get();

        // Fetch orders for a specific reservation if reservation_id is provided
        $grandTotals = ['total' => 0];
        if ($reservationId) {
            $grandTotals['total'] = $orders->sum('total');
        }

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();

        // Get branches for filter
        $branches = Branch::where('is_active', true)->get();

        return view('orders.index', [
            'activeReservations' => $activeReservations,
            'pastReservations' => $pastReservations,
            'orders' => $orders,
            'reservationId' => $reservationId,
            'grandTotals' => $grandTotals,
            'phone' => $phone,
            'stewards' => $stewards,
            'branches' => $branches,
            'filters' => compact('startDate', 'endDate', 'status', 'branchId'),
        ]);
    }

    // Show order creation form (dine-in, under reservation)
    public function create(Request $request)
    {
        $reservationId = $request->query('reservation');
        $reservation = null;
        if ($reservationId) {
            $reservation = \App\Models\Reservation::find($reservationId);
        }
        
        // Get menu items from active menus only
        $branchId = $reservation ? $reservation->branch_id : null;
        $menuItems = collect();
        
        if ($branchId) {
            $activeMenu = \App\Models\Menu::getActiveMenuForBranch($branchId);
            if ($activeMenu) {
                $menuItems = $activeMenu->menuItems()
                    ->with(['menuCategory', 'itemMaster'])
                    ->where('is_active', true)
                    ->get();
            }
        } else {
            // Fallback: get all active menu items if no specific branch
            $menuItems = \App\Models\MenuItem::with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->where(function($query) {
                    // Include KOT items (which don't need itemMaster) or Buy & Sell items with active itemMaster
                    $query->where('type', MenuItem::TYPE_KOT)
                          ->orWhereHas('itemMaster', function($subQuery) {
                              $subQuery->where('is_active', true);
                          });
                })
                ->get();
        }

        // Add proper item type and stock information for the view
        foreach ($menuItems as $item) {
            // Determine item type based on the actual MenuItem type field
            if ($item->type === MenuItem::TYPE_BUY_SELL) {
                $item->item_type = 'Buy & Sell';
                // Add current stock information for Buy & Sell items
                $item->current_stock = $item->itemMaster ? $this->getCurrentStock($item->itemMaster->id, $branchId ?? 1) : 0;
            } else {
                // KOT items (TYPE_KOT) are always available for ordering
                $item->item_type = 'KOT';
                $item->current_stock = 999; // KOT items don't have stock limitations
            }
        }

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();

        return view('orders.create', compact('reservation', 'menuItems', 'stewards'));
    }

    // Store new order (dine-in, under reservation)
    public function store(Request $request)
    {
        // Debug: Log incoming request data to help diagnose validation issues
        Log::info('Dine-in order submission attempt', [
            'request_data' => $request->all(),
            'items_structure' => $request->get('items', [])
        ]);

        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'steward_id' => 'nullable|exists:employees,id',
            'order_type' => 'nullable|string|in:' . implode(',', array_column(OrderType::cases(), 'value')),
        ], [
            'reservation_id.required' => 'Reservation is required for dine-in orders',
            'reservation_id.exists' => 'Invalid reservation selected',
            'items.required' => 'Please select at least one menu item',
            'items.min' => 'Please select at least one menu item',
            'items.*.item_id.required' => 'Menu item selection is required',
            'items.*.item_id.exists' => 'Selected menu item is not valid',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'customer_phone.required' => 'Customer phone number is required',
        ]);

        return DB::transaction(function () use ($data) {
            $reservation = Reservation::with('branch.organization', 'customer')->find($data['reservation_id']);
            
            // Enhanced: Validate reservation and branch/organization status
            if (!$reservation) {
                throw new \Exception('Reservation not found');
            }
            
            if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
                throw new \Exception('Orders can only be created for confirmed or checked-in reservations');
            }
            
            if (!$reservation->branch->is_active || !$reservation->branch->organization->is_active) {
                throw new \Exception('Cannot create orders for inactive branch or organization');
            }
            
            // Check time constraints for reservation orders
            if ($reservation->date < now()->toDateString()) {
                throw new \Exception('Cannot create orders for past reservations');
            }

            // Find or create customer by phone
            $customer = Customer::findByPhone($data['customer_phone']);
            if (!$customer) {
                $customer = Customer::createFromPhone($data['customer_phone'], [
                    'name' => $data['customer_name'] ?? $reservation->name,
                    'email' => $reservation->email,
                ]);
            }

            // Determine order type - default to dine-in demand if not specified
            $orderType = isset($data['order_type']) 
                ? OrderType::from($data['order_type']) 
                : OrderType::DINE_IN_WALK_IN_DEMAND;

            // Validate that dine-in orders have reservation
            if ($orderType->isDineIn() && !$reservation) {
                throw new \Exception('Reservation required for dine-in orders');
            }

            // Stock validation - check all items before creating order
            $stockErrors = [];
            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['item_id']);
                if (!$menuItem) continue;
                
                // Only check stock for items linked to inventory (ItemMaster)
                if ($menuItem->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $reservation->branch_id);
                    if ($currentStock < $item['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                    }
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Create order (model boot method will handle customer linking)
            $order = Order::create([
                'reservation_id' => $reservation->id,
                'branch_id' => $reservation->branch_id,
                'organization_id' => $reservation->branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'order_type' => $orderType,
                'status' => Order::STATUS_SUBMITTED,
                'steward_id' => $data['steward_id'] ?? null,
                'order_date' => now(),
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['item_id']);
                if (!$menuItem) continue;
                
                $lineTotal = $menuItem->price * $item['quantity'];
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $lineTotal,
                ]);

                
                if ($menuItem->item_master_id && $menuItem->itemMaster) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $reservation->organization_id ?? Auth::user()->organization_id,
                        'branch_id' => $reservation->branch_id,
                        'inventory_item_id' => $menuItem->item_master_id,
                        'transaction_type' => 'sales_order',
                        'quantity' => -$item['quantity'], // Negative for stock deduction
                        'cost_price' => $menuItem->itemMaster->buying_price,
                        'unit_price' => $menuItem->price,
                        'reference_id' => $order->id,
                        'reference_type' => 'Order',
                        'created_by_user_id' => Auth::id(),
                        'notes' => "Stock deducted for Order #{$order->id}",
                        'is_active' => true,
                    ]);
                }
            }

            $tax = $subtotal * 0.13; // 13% VAT
            $serviceCharge = $subtotal * 0.10; // 10% service charge
            $discount = 0;
            $total = $subtotal + $tax + $serviceCharge - $discount;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'discount' => $discount,
                'total' => $total,
                'stock_deducted' => true,
            ]);

            // Generate KOT immediately using KotController
            try {
                $kotController = new \App\Http\Controllers\Admin\KotController();
                $kotResult = $kotController->generateKot(request(), $order);
                
                if ($kotResult['success']) {
                    return redirect()->route('orders.summary', $order->id)
                        ->with('success', 'Order created successfully! Stock deducted and KOT generated.')
                        ->with('kot_generated', true)
                        ->with('kot_print_url', route('orders.print-kot', $order->id));
                } else {
                    return redirect()->route('orders.summary', $order->id)
                        ->with('success', 'Order created successfully! Stock deducted.')
                        ->with('kot_error', $kotResult['message'] ?? 'KOT generation failed');
                }
            } catch (\Exception $e) {
                Log::error('KOT generation failed for Order #' . $order->id, [
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('orders.summary', $order->id)
                    ->with('success', 'Order created successfully! Stock deducted.')
                    ->with('kot_error', 'KOT generation failed: ' . $e->getMessage());
            }
        });
    }

    // View order details
    public function show($id)
    {
        $order = Order::with(['reservation', 'orderItems.menuItem'])
               ->findOrFail($id);

        return view('orders.summary', compact('order'));
    }

    // Edit order (dine-in, under reservation)
    public function edit($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        return view('orders.edit', compact('order', 'menuItems'));
    }

    // Update order (dine-in, under reservation)
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($data, $order) {
            // First, reverse previous stock deductions if order was already processed
            if ($order->stock_deducted) {
                foreach ($order->orderItems as $orderItem) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $order->reservation->organization_id ?? Auth::user()->organization_id,
                        'branch_id' => $order->branch_id,
                        'inventory_item_id' => $orderItem->inventory_item_id,
                        'transaction_type' => 'order_adjustment',
                        'quantity' => $orderItem->quantity, // Positive to add back stock
                        'cost_price' => $orderItem->inventoryItem->buying_price ?? 0,
                        'unit_price' => $orderItem->unit_price,
                        'reference_id' => $order->id,
                        'reference_type' => 'Order',
                        'created_by_user_id' => Auth::id(),
                        'notes' => "Stock reversed for Order #{$order->id} update",
                        'is_active' => true,
                    ]);
                }
            }

            // Stock validation for new items
            $stockErrors = [];
            foreach ($data['items'] as $item) {
                $inventoryItem = ItemMaster::find($item['item_id']);
                if (!$inventoryItem) continue;
                
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item['item_id'], $order->branch_id);
                if ($currentStock < $item['quantity']) {
                    $stockErrors[] = "Insufficient stock for {$inventoryItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Delete old order items
            $order->orderItems()->delete();
            $subtotal = 0;
            
            // Create new order items and deduct stock
            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['item_id']);
                if (!$menuItem) continue;
                
                $lineTotal = $menuItem->price * $item['quantity'];
                $subtotal += $lineTotal;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $lineTotal,
                ]);

                // Deduct stock for new quantities
                \App\Models\ItemTransaction::create([
                    'organization_id' => $order->reservation->organization_id ?? Auth::user()->organization_id,
                    'branch_id' => $order->branch_id,
                    'inventory_item_id' => $item['item_id'],
                    'transaction_type' => 'sales_order',
                    'quantity' => -$item['quantity'], // Negative for stock deduction
                    'cost_price' => $inventoryItem->buying_price,
                    'unit_price' => $inventoryItem->selling_price,
                    'reference_id' => $order->id,
                    'reference_type' => 'Order',
                    'created_by_user_id' => Auth::id(),
                    'notes' => "Stock deducted for updated Order #{$order->id}",
                    'is_active' => true,
                ]);
            }

            $tax = $subtotal * 0.13; // 13% VAT
            $service = $subtotal * 0.10; // 10% service charge
            $total = $subtotal + $tax + $service;
            
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $service,
                'total' => $total,
                'stock_deducted' => true,
            ]);

            return redirect()->route('orders.index', [
                'phone' => $order->customer_phone,
                'reservation_id' => $order->reservation_id
            ])->with('success', 'Order updated successfully. Stock adjusted.');
        });
    }

    // Delete order (dine-in, under reservation)
    public function destroy($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $reservationId = $request->input('reservation_id', $order->reservation_id);
        $order->orderItems()->delete();
        $order->delete();
        return redirect()->route('orders.index', ['reservation_id' => $reservationId])
            ->with('success', 'Order deleted successfully.');
    }

    // Show payment or repeat order options
    public function paymentOrRepeat($order_id)
    {
        $order = Order::findOrFail($order_id);
        return view('orders.payment_or_repeat', compact('order'));
    }

    // Handle user choice: proceed to payment or place another order
    public function handleChoice(Request $request, $order_id)
    {
        $request->validate(['action' => 'required|in:payment,repeat']);

        if ($request->action === 'payment') {
            return redirect()->route('payments.create', ['order_id' => $order_id]);
        } else {
            $order = Order::findOrFail($order_id);
            return redirect()->route('orders.create', [
                'reservation_id' => $order->reservation_id
            ])->with('success', 'Order placed. Add another item below.');
        }
    }

    public function payment(Order $order)
    {
        return view('orders.payment', compact('order'));
    }

    // Takeaway order functions
    public function createTakeaway()
    {
        $branches = Branch::where('is_active', true)->get();
        
        // Get items from active menus only for the first active branch (or specified branch)
        $defaultBranch = $branches->first();
        $items = collect();
        
        if ($defaultBranch) {
            $activeMenu = \App\Models\Menu::getActiveMenuForBranch($defaultBranch->id);
            if ($activeMenu) {
                $items = $activeMenu->menuItems()
                    ->with(['menuCategory', 'itemMaster'])
                    ->where('is_active', true)
                    ->wherePivot('is_available', true)
                    ->get();
            } else {
                // Fallback: get all active menu items if no active menu
                $items = MenuItem::where('is_active', true)
                    ->where('branch_id', $defaultBranch->id)
                    ->with(['menuCategory', 'itemMaster'])
                    ->get();
            }
        }

        // Add missing fields for the view
        foreach ($items as $item) {
            // Determine item type based on the actual MenuItem type field
            if ($item->type === MenuItem::TYPE_BUY_SELL) {
                $item->item_type = 'Buy & Sell';
                // Add current stock information for Buy & Sell items
                $item->current_stock = $item->itemMaster ? $this->getCurrentStock($item->itemMaster->id, $branches->first()?->id ?? 1) : 0;
            } else {
                // KOT items (TYPE_KOT) are always available for ordering
                $item->item_type = 'KOT';
                $item->current_stock = 999; // KOT items don't have stock limitations
            }
        }

        return view('orders.takeaway.create', [
            'branches' => $branches,
            'items' => $items,
            'defaultBranch' => $branches->first()?->id,
            'orderType' => 'takeaway_online_scheduled'
        ]);
    }

    private function getCurrentStock($itemId, $branchId)
    {
        try {
            // Use the same method as in the existing system
            return \App\Models\ItemTransaction::stockOnHand($itemId, $branchId);
        } catch (\Exception $e) {
            return 0; // Return 0 if there's an error
        }
    }

    public function storeTakeaway(Request $request)
    {
        // Debug: Log incoming request data to help diagnose validation issues
        Log::info('Takeaway order submission attempt', [
            'request_data' => $request->all(),
            'items_structure' => $request->get('items', [])
        ]);

        // Pre-process items data to convert from associative array to sequential array
        $rawItems = $request->get('items', []);
        $processedItems = [];
        
        foreach ($rawItems as $itemId => $itemData) {
            if (isset($itemData['menu_item_id']) && isset($itemData['quantity'])) {
                $processedItems[] = [
                    'menu_item_id' => $itemData['menu_item_id'],
                    'quantity' => (int) $itemData['quantity']
                ];
            }
        }
        
        // Replace items in request
        $request->merge(['items' => $processedItems]);

        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date|after_or_equal:now',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
            'order_type' => 'nullable|string|in:takeaway_walk_in_demand,takeaway_in_call_scheduled,takeaway_online_scheduled',
            'preferred_contact' => 'nullable|string|in:email,sms',
        ], [
            'items.required' => 'Please select at least one menu item',
            'items.min' => 'Please select at least one menu item',
            'items.*.menu_item_id.required' => 'Menu item selection is required',
            'items.*.menu_item_id.exists' => 'Selected menu item is not valid',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'branch_id.required' => 'Please select a branch',
            'branch_id.exists' => 'Selected branch is not valid',
            'order_time.required' => 'Please select pickup time',
            'order_time.after_or_equal' => 'Pickup time must be in the future',
            'customer_name.required' => 'Customer name is required',
            'customer_phone.required' => 'Customer phone number is required',
        ]);

        return DB::transaction(function () use ($data) {
            // Validate branch exists and is active
            $branch = Branch::with('organization')->findOrFail($data['branch_id']);
            if (!$branch->is_active || !$branch->organization->is_active) {
                throw new \Exception('Branch or organization is not active');
            }

            // Find or create customer - FIX: Pass array instead of string
            $customer = Customer::findOrCreateByPhone($data['customer_phone'], [
                'name' => $data['customer_name']
            ]);

            // Stock validation
            $stockErrors = [];
            $orderItems = [];
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                if (!$menuItem || !$menuItem->is_active) {
                    $stockErrors[] = "Item {$item['menu_item_id']} is not available";
                    continue;
                }
                
                // Get the associated ItemMaster for stock checking if available
                $itemMaster = $menuItem->itemMaster;
                if ($itemMaster && $itemMaster->is_perishable) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($itemMaster->id, $data['branch_id']);
                    if ($currentStock < $item['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$item['quantity']}";
                    }
                }

                // Calculate item total using MenuItem's selling price
                $itemTotal = $menuItem->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $itemTotal
                ];
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Calculate totals
            $taxRate = 0.10; // 10% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            // Generate order number and takeaway ID
            $orderNumber = OrderNumberService::generate($data['branch_id']);
            $takeawayId = OrderNumberService::generateTakeawayId($data['branch_id']);

            // Create takeaway order with all required fields
            $order = Order::create([
                'branch_id' => $data['branch_id'],
                'organization_id' => $branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'order_type' => $data['order_type'],
                'order_number' => $orderNumber,
                'status' => 'pending',
                'order_date' => now(),
                'order_time' => $data['order_time'] ?? now(),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax, 
                'total' => $total, 
                'currency' => 'LKR',
                'payment_status' => 'pending',
                'special_instructions' => $data['special_instructions'] ?? null,
                'takeaway_id' => $takeawayId,
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['menu_item']->id, // MenuItem ID
                    'inventory_item_id' => $itemData['menu_item']->itemMaster ? $itemData['menu_item']->itemMaster->id : null, // ItemMaster ID if available
                    'item_name' => $itemData['menu_item']->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['total_price'],
                    'subtotal' => $itemData['total_price'], 
                ]);

                // Deduct stock for perishable items (Buy & Sell items)
                if ($itemData['menu_item']->is_perishable) {
                    \App\Models\ItemTransaction::create([
                        'organization_id' => $branch->organization_id,
                        'branch_id' => $data['branch_id'],
                        'inventory_item_id' => $itemData['menu_item']->id,
                        'transaction_type' => 'order_deduction',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Stock deduction for order #{$order->order_number}",
                        'is_active' => true,
                        'created_by_user_id' => Auth::check() ? Auth::user()->id : null,
                    ]);
                }
            }

            // Generate KOT immediately using KotController
            try {
                $kotController = new \App\Http\Controllers\Admin\KotController();
                $kotResult = $kotController->generateKot(request(), $order);
                
                if ($kotResult['success']) {
                    return redirect()->route('orders.takeaway.summary', $order)
                        ->with('success', 'Order created successfully! KOT generated.')
                        ->with('kot_generated', true)
                        ->with('kot_print_url', route('orders.print-kot', $order->id));
                } else {
                    return redirect()->route('orders.takeaway.summary', $order)
                        ->with('success', 'Order created successfully!')
                        ->with('kot_error', $kotResult['message'] ?? 'KOT generation failed');
                }
            } catch (\Exception $e) {
                Log::error('KOT generation failed for Takeaway Order #' . $order->id, [
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('orders.takeaway.summary', $order)
                    ->with('success', 'Order created successfully!')
                    ->with('kot_error', 'KOT generation failed: ' . $e->getMessage());
            }
        });
    }

    public function summary(Order $order)
    {
        $order->load('orderItems.menuItem', 'reservation', 'branch');
        
        // Determine the appropriate view based on order type
        $view = 'orders.summary';
        
        // Get order type value safely
        $orderTypeValue = $order->order_type instanceof \App\Enums\OrderType 
            ? $order->order_type->value 
            : (string) $order->order_type;
    
        if ($orderTypeValue && str_contains($orderTypeValue, 'takeaway')) {
            $view = 'orders.takeaway.summary';
        }
    
        return view($view, [
            'order' => $order,
            'editable' => $order->status === 'pending',
            'reservation' => $order->reservation,
            'orderType' => $orderTypeValue ?? 'takeaway'
        ]);
    }

    public function submit(Request $request, Order $order)
    {
        $order->update(['status' => 'submitted']);
        
        // Generate KOT for submitted orders
        try {
            $kotController = new \App\Http\Controllers\Admin\KotController();
            $kotResult = $kotController->generateKot($request, $order);
            
            if ($kotResult['success']) {
                return redirect()->route('orders.index', ['phone' => $order->customer_phone])
                    ->with('success', 'Order submitted successfully! KOT generated.')
                    ->with('kot_generated', true)
                    ->with('kot_print_url', route('orders.print-kot', $order->id));
            } else {
                return redirect()->route('orders.index', ['phone' => $order->customer_phone])
                    ->with('success', 'Order submitted successfully!')
                    ->with('kot_error', $kotResult['message'] ?? 'KOT generation failed');
            }
        } catch (\Exception $e) {
            Log::error('KOT generation failed for submitted Order #' . $order->id, [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('orders.index', ['phone' => $order->customer_phone])
                ->with('success', 'Order submitted successfully!')
                ->with('kot_error', 'KOT generation failed: ' . $e->getMessage());
        }
    }

    // Edit takeaway order
    public function editTakeaway($id)
    {
        $order = Order::with(['orderItems.menuItem', 'branch'])->findOrFail($id);
        
        // Get only active menu items for the order's branch from active menus
        $activeMenu = \App\Models\Menu::getActiveMenuForBranch($order->branch_id);
        
        if ($activeMenu) {
            // Get items from the active menu
            $items = $activeMenu->menuItems()
                ->with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->wherePivot('is_available', true)
                ->get();
        } else {
            // Fallback: get all active menu items if no active menu (shouldn't normally happen)
            $items = MenuItem::where('is_active', true)
                ->where('branch_id', $order->branch_id)
                ->with(['menuCategory', 'itemMaster'])
                ->get();
        }
            
        // Add missing fields for the view
        foreach ($items as $item) {
            // Determine item type based on associated ItemMaster
            $item->item_type = $item->itemMaster && $item->itemMaster->is_perishable ? 'Buy & Sell' : 'KOT';
            
            // Add current stock information
            $item->current_stock = $item->itemMaster ? $this->getCurrentStock($item->itemMaster->id, $order->branch_id) : 999;
        }
        
        $branches = Branch::where('is_active', true)->get();

        // Prepare cart data for pre-filling
        $cart = [
            'items' => [],
            'subtotal' => $order->subtotal,
            'tax' => $order->tax_amount ?? $order->tax,
            'total' => $order->total_amount ?? $order->total
        ];

        foreach ($order->orderItems as $item) {
            $cart['items'][] = [
                'item_id' => $item->menu_item_id,
                'quantity' => $item->quantity
            ];
        }

        return view('orders.takeaway.edit', compact('order', 'items', 'branches', 'cart'));
    }

    // Submit takeaway order
    public function submitOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'submitted']);
        return redirect()->route('orders.index', ['phone' => $order->customer_phone])
            ->with('success', 'Takeaway order submitted successfully!');
    }

    // Update takeaway order (customer)
    public function updateTakeaway(Request $request, Order $order)
    {
        Log::info('Update takeaway order attempt', [
            'order_id' => $order->id,
            'request_data' => $request->all()
        ]);

        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'order_time' => 'required|date|after_or_equal:now',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Remove old items
            $order->orderItems()->delete();
            
            // Calculate subtotal and prepare items data
            $calculation = $this->calculateSubtotal($data['items']);
            $subtotal = $calculation['subtotal'];
            $itemsData = $calculation['items_data'];
            
            // Prepare order items for database insertion
            $orderItems = $this->prepareOrderItemsForInsert($order->id, $itemsData);
            
            // Bulk insert order items
            if (!empty($orderItems)) {
                \App\Models\OrderItem::insert($orderItems);
            }
            
            // Calculate totals with tax
            $totals = $this->calculateOrderTotals($subtotal);
            
            // Update order details
            $order->update([
                'branch_id' => $data['branch_id'],
                'order_time' => Carbon::parse($data['order_time']),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax'],
                'total_amount' => $totals['total'],
                'tax' => $totals['tax'], // For compatibility
                'total' => $totals['total'], // For compatibility
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Takeaway order updated successfully', [
                'order_id' => $order->id,
                'items_count' => count($orderItems),
                'subtotal' => $totals['subtotal'],
                'total' => $totals['total']
            ]);
            
            return redirect()->route('orders.takeaway.summary', $order->id)
                ->with('success', 'Takeaway order updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update takeaway order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update order: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // Submit takeaway order (customer)
    public function submitTakeaway(Request $request, Order $order)
    {
        return DB::transaction(function () use ($order) {
            try {
                // Validate that order is in pending status
                if ($order->status !== 'pending') {
                    return redirect()->back()
                        ->with('error', 'Order cannot be confirmed. Current status: ' . $order->status);
                }

                // Final stock validation before confirmation
                $stockErrors = [];
                foreach ($order->items as $orderItem) {
                    $menuItem = $orderItem->menuItem;
                    if ($menuItem && $menuItem->item_master_id && $menuItem->itemMaster) {
                        $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $order->branch_id);
                        if ($currentStock < $orderItem->quantity) {
                            $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$orderItem->quantity}";
                        }
                    }
                }

                if (!empty($stockErrors)) {
                    return redirect()->back()
                        ->with('error', 'Cannot confirm order due to stock issues: ' . implode(', ', $stockErrors));
                }

                // Deduct stock for items linked to inventory
                foreach ($order->items as $orderItem) {
                    $menuItem = $orderItem->menuItem;
                    if ($menuItem && $menuItem->item_master_id && $menuItem->itemMaster) {
                        \App\Models\ItemTransaction::create([
                            'organization_id' => $order->branch->organization_id,
                            'branch_id' => $order->branch_id,
                            'inventory_item_id' => $menuItem->item_master_id,
                            'transaction_type' => 'takeaway_order',
                            'quantity' => -$orderItem->quantity,
                            'cost_price' => $menuItem->itemMaster->buying_price,
                            'unit_price' => $menuItem->price,
                            'reference_id' => $order->id,
                            'reference_type' => 'Order',
                            'created_by_user_id' => Auth::id(),
                            'notes' => "Stock deducted for Takeaway Order #{$order->takeaway_id}",
                            'is_active' => true,
                        ]);
                    }
                }

                // Update order status and mark stock as deducted
                $order->update([
                    'status' => 'submitted',
                    'stock_deducted' => true,
                    'submitted_at' => now(),
                ]);

                // Generate KOT for kitchen using KotController
                try {
                    $kotController = new \App\Http\Controllers\Admin\KotController();
                    $kotResult = $kotController->generateKot(request(), $order);
                    
                    if ($kotResult['success']) {
                        return redirect()->route('orders.takeaway.summary', $order)
                            ->with('success', 'Order confirmed successfully! Your order has been sent to the kitchen.')
                            ->with('kot_generated', true)
                            ->with('kot_print_url', route('orders.print-kot', $order->id));
                    } else {
                        return redirect()->route('orders.takeaway.summary', $order)
                            ->with('success', 'Order confirmed successfully!')
                            ->with('kot_error', $kotResult['message'] ?? 'KOT generation failed');
                    }
                } catch (\Exception $kotException) {
                    Log::error('KOT generation failed for submitted Order #' . $order->id, [
                        'error' => $kotException->getMessage()
                    ]);
                    
                    return redirect()->route('orders.takeaway.summary', $order)
                        ->with('success', 'Order confirmed successfully!')
                        ->with('kot_error', 'KOT generation failed: ' . $kotException->getMessage());
                }

            } catch (\Exception $e) {
                Log::error('Order confirmation failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->back()
                    ->with('error', 'Failed to confirm order. Please try again.');
            }
        });
    }

    // Show a single takeaway order (customer)
    public function showTakeaway(Order $order)
    {
        return view('orders.takeaway.show', compact('order'));
    }

    // Delete takeaway order
    public function destroyTakeaway($id)
    {
        $order = Order::findOrFail($id);
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Takeaway order deleted successfully.');
    }
}