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
use Carbon\Carbon;

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
                ->whereHas('itemMaster', function($query) {
                    $query->where('is_active', true);
                })
                ->get();
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

            // Generate KOT immediately
            $order->generateKOT();

            // // Send order confirmation notification
            // $this->notificationService->sendOrderConfirmation($order);

            return redirect()->route('orders.summary', $order->id)->with('success', 'Order created successfully! Stock deducted and KOT generated.');
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
            // Determine item type based on associated ItemMaster
            $item->item_type = $item->itemMaster && $item->itemMaster->is_perishable ? 'Buy & Sell' : 'KOT';
            
            // Add current stock information
            $item->current_stock = $item->itemMaster ? $this->getCurrentStock($item->itemMaster->id, $branches->first()?->id ?? 1) : 999;
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

            return redirect()->route('orders.takeaway.summary', $order)
                ->with('success', 'Order created successfully!');
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
        return redirect()->route('orders.index', ['phone' => $order->customer_phone]);
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

    // Show all orders with optional filters
    public function allOrders(Request $request)
    {
        $query = Order::with(['reservation', 'items', 'branch']);

        if ($request->filled('phone')) {
            $query->where('customer_phone', $request->phone);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20);
        $branches = Branch::all();
        return view('orders.all', compact('orders', 'branches'));
    }

    public function updateCart(Request $request)
    {
        $items = $request->input('items', []);
        $cart = [
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0
        ];

        foreach ($items as $item) {
            $menuItem = ItemMaster::find($item['item_id']);
            if (!$menuItem) continue;

            $quantity = (int)$item['quantity'];
            $lineTotal = $menuItem->selling_price * $quantity;

            $cart['items'][] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'price' => $menuItem->selling_price,
                'quantity' => $quantity,
                'total' => $lineTotal
            ];

            $cart['subtotal'] += $lineTotal;
        }

        // Calculate tax (10%) and total
        $cart['tax'] = $cart['subtotal'] * 0.10;
        $cart['total'] = $cart['subtotal'] + $cart['tax'];

        return response()->json($cart);
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
            
            $subtotal = 0;
            $orderItems = [];
            
            foreach ($data['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['item_id']);
                if (!$menuItem) {
                    Log::warning('Menu item not found', ['item_id' => $itemData['item_id']]);
                    continue;
                }
                
                $quantity = (int) $itemData['quantity'];
                $unitPrice = $menuItem->price;
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;
                
                $orderItems[] = [
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Bulk insert order items
            if (!empty($orderItems)) {
                \App\Models\OrderItem::insert($orderItems);
            }
            
            $tax = $subtotal * 0.10;
            $total = $subtotal + $tax;
            
            // Update order details
            $order->update([
                'branch_id' => $data['branch_id'],
                'order_time' => Carbon::parse($data['order_time']),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax, // For compatibility
                'total' => $total, // For compatibility
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Takeaway order updated successfully', [
                'order_id' => $order->id,
                'items_count' => count($orderItems),
                'subtotal' => $subtotal,
                'total' => $total
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

                // Generate KOT for kitchen
                if (method_exists($order, 'generateKOT')) {
                    $order->generateKOT();
                }

                return redirect()->route('orders.takeaway.summary', $order)
                    ->with('success', 'Order confirmed successfully! Your order has been sent to the kitchen.');

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

    /**
     * Generate and print KOT (Kitchen Order Ticket)
     */
    public function printKOT(Order $order)
    {
        // Update order to mark KOT as generated
        $order->update(['kot_generated' => true]);
        
        return view('orders.kot-print', compact('order'));
    }

    /**
     * Generate and print bill
     */
    public function printBill(Order $order)
    {
        // Mark order as completed and bill generated
        $order->update([
            'bill_generated' => true,
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
        
        return view('orders.bill-print', compact('order'));
    }

    /**
     * Mark order as preparing and generate KOT
     */
    public function markAsPreparing(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->markAsPreparing();
            
            // Generate KOT if not already generated
            if (!$order->kot_generated) {
                $order->generateKOT();
            }
            
            return redirect()->back()->with('success', 'Order marked as preparing and KOT generated.');
        });
    }

    /**
     * Mark order as ready
     */
    public function markAsReady(Order $order)
    {
        $order->markAsReady();
        return redirect()->back()->with('success', 'Order marked as ready.');
    }

    /**
     * Complete order and generate bill
     */
    public function completeOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->markAsCompleted();
            
            return redirect()->route('orders.print-bill', $order)
                ->with('success', 'Order completed. Bill generated.');
        });
    }

    /**
     * Get stock alert for order items
     */
    public function checkStock(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity');
        $branchId = $request->input('branch_id');

        $currentStock = \App\Models\ItemTransaction::stockOnHand($itemId, $branchId);
        $item = ItemMaster::find($itemId);
        
        $response = [
            'item_name' => $item->name,
            'current_stock' => $currentStock,
            'requested_quantity' => $quantity,
            'available' => $currentStock >= $quantity,
            'is_low_stock' => $currentStock <= $item->reorder_level,
            'reorder_level' => $item->reorder_level
        ];

        if ($currentStock < $quantity) {
            $response['error'] = "Insufficient stock. Available: {$currentStock}, Required: {$quantity}";
        } elseif ($currentStock <= $item->reorder_level) {
            $response['warning'] = "Low stock alert. Current: {$currentStock}, Reorder level: {$item->reorder_level}";
        }

        return response()->json($response);
    }

    /**
     * Export orders to CSV or Excel
     */
    protected function exportOrders($orders, $format)
    {
        $filename = 'orders_' . now()->format('Y-m-d_H-i-s');
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'Order ID', 'Date', 'Customer Name', 'Phone', 'Branch', 
                    'Order Type', 'Status', 'Steward', 'Items Count', 
                    'Subtotal', 'Tax', 'Service Charge', 'Total'
                ]);

                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->order_date->format('Y-m-d H:i:s'),
                        $order->customer_name,
                        $order->customer_phone,
                        $order->branch->name ?? '',
                        str_replace('_', ' ', $order->order_type),
                        ucfirst($order->status),
                        $order->steward ? $order->steward->first_name . ' ' . $order->steward->last_name : '',
                        $order->items->count(),
                        number_format($order->subtotal, 2),
                        number_format($order->tax, 2),
                        number_format($order->service_charge, 2),
                        number_format($order->total, 2),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // For Excel format (if needed in future)
        return response()->json(['error' => 'Excel export not yet implemented'], 501);
    }

    // List takeaway orders (customer)
    public function indexTakeaway(Request $request)
    {
        $phone = $request->input('phone');
        $branchId = $request->input('branch_id');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query for takeaway orders
        $ordersQuery = Order::query()
            ->where('order_type', 'takeaway_online_scheduled')
            ->with(['items.menuItem', 'branch'])
            ->when($phone, fn($q) => $q->where('customer_phone', $phone))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->paginate(15);
        $branches = Branch::where('is_active', true)->get();

        // Summary statistics
        $totalOrders = $ordersQuery->count();
        $totalAmount = $ordersQuery->sum('total');
        
        return view('orders.takeaway.index', compact(
            'orders', 
            'branches', 
            'phone', 
            'branchId', 
            'status', 
            'startDate', 
            'endDate',
            'totalOrders',
            'totalAmount'
        ));
    }

    /**
     * Add another order for the same reservation/customer
     */
    public function addAnother(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $phone = $request->input('phone');
        
        if ($reservationId) {
            return redirect()->route('orders.create', ['reservation_id' => $reservationId]);
        }
        
        if ($phone) {
            return redirect()->route('orders.takeaway.create', ['phone' => $phone]);
        }
        
        return redirect()->route('orders.create');
    }

    /**
     * Get available menu items for ordering with enhanced filtering
     */
    public function getAvailableMenuItems(Request $request)
    {
        $branchId = $request->input('branch_id', Auth::user()->branch_id);
        $categoryId = $request->input('category_id');
        $type = $request->input('type'); // 'buy_sell', 'kot', 'all'
        $search = $request->input('search');
        
        $query = MenuItem::with(['menuCategory', 'itemMaster', 'kitchenStation'])
            ->where('is_active', true)
            ->where('is_available', true);
            
        // Apply branch filter
        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id'); // Include organization-wide items
            });
        }
            
        // Apply category filter
        if ($categoryId) {
            $query->where('menu_category_id', $categoryId);
        }
        
        // Apply type filter
        if ($type && $type !== 'all') {
            if ($type === 'buy_sell') {
                $query->where('type', MenuItem::TYPE_BUY_SELL);
            } elseif ($type === 'kot') {
                $query->where('type', MenuItem::TYPE_KOT);
            }
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('item_code', 'ILIKE', "%{$search}%");
            });
        }
        
        $menuItems = $query->orderBy('display_order')
                          ->orderBy('name')
                          ->get()
                          ->map(function($item) use ($branchId) {
            
            // Determine current stock and availability
            $currentStock = 0;
            $isAvailable = true;
            $stockStatus = 'available';
            
            if ($item->type === MenuItem::TYPE_BUY_SELL && $item->item_master_id && $item->itemMaster) {
                // Get real-time stock for Buy & Sell items
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                $isAvailable = $currentStock > 0;
                
                if ($currentStock <= 0) {
                    $stockStatus = 'out_of_stock';
                } elseif ($currentStock <= ($item->itemMaster->reorder_level ?? 5)) {
                    $stockStatus = 'low_stock';
                } else {
                    $stockStatus = 'in_stock';
                }
            }
            
            // Calculate stock percentage for progress bars
            $stockPercentage = 0;
            if ($item->type === MenuItem::TYPE_BUY_SELL && $item->itemMaster) {
                $maxStock = $item->itemMaster->maximum_stock ?? 100;
                $stockPercentage = $maxStock > 0 ? min(100, ($currentStock / $maxStock) * 100) : 0;
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->current_price, // Use current price (considers promotions)
                'original_price' => $item->price,
                'currency' => $item->currency ?? 'LKR',
                'category_id' => $item->menu_category_id,
                'category' => $item->menuCategory->name ?? 'Uncategorized',
                'type' => $item->type,
                'type_name' => $item->type === MenuItem::TYPE_BUY_SELL ? 'Buy & Sell' : 'KOT',
                'item_code' => $item->item_code,
                
                // Stock and availability info
                'current_stock' => $currentStock,
                'is_available' => $isAvailable && $item->is_available,
                'stock_status' => $stockStatus,
                'stock_percentage' => $stockPercentage,
                'requires_preparation' => $item->requires_preparation,
                'preparation_time' => $item->preparation_time,
                
                // Enhanced display info
                'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                'is_featured' => $item->is_featured,
                'is_on_promotion' => $item->is_on_promotion,
                'promotion_price' => $item->promotion_price,
                
                // Dietary and allergen info
                'is_vegetarian' => $item->is_vegetarian,
                'is_vegan' => $item->is_vegan,
                'is_spicy' => $item->is_spicy,
                'spice_level' => $item->spice_level,
                'allergens' => $item->allergens ?? [],
                'allergen_info' => $item->allergen_info ?? [],
                
                // Kitchen info (for KOT items)
                'kitchen_station' => $item->kitchenStation ? [
                    'id' => $item->kitchenStation->id,
                    'name' => $item->kitchenStation->name
                ] : null,
                
                // Item master link info
                'item_master_id' => $item->item_master_id,
                'linked_to_inventory' => (bool) $item->item_master_id,
                
                // Availability info for frontend
                'availability_info' => [
                    'status' => $stockStatus,
                    'message' => $this->getAvailabilityMessage($stockStatus, $currentStock, $item->type),
                    'color' => $this->getAvailabilityColor($stockStatus)
                ]
            ];
        });
        
        // Filter out items based on availability if needed
        $availableItems = $menuItems->filter(function($item) {
            // Always show KOT items as available
            if ($item['type'] === MenuItem::TYPE_KOT) {
                return true;
            }
            
            // For Buy & Sell items, check stock
            return $item['current_stock'] > 0;
        });
        
        return response()->json([
            'success' => true,
            'data' => $availableItems->values(),
            'total' => $availableItems->count(),
            'filtered_total' => $menuItems->count(),
            'summary' => [
                'total_items' => $menuItems->count(),
                'available_items' => $availableItems->count(),
                'buy_sell_items' => $menuItems->where('type', MenuItem::TYPE_BUY_SELL)->count(),
                'kot_items' => $menuItems->where('type', MenuItem::TYPE_KOT)->count(),
                'out_of_stock' => $menuItems->where('stock_status', 'out_of_stock')->count(),
                'low_stock' => $menuItems->where('stock_status', 'low_stock')->count()
            ]
        ]);
    }
    
    /**
     * Get availability message for display
     */
    private function getAvailabilityMessage(string $status, int $stock, int $type): string
    {
        if ($type === MenuItem::TYPE_KOT) {
            return 'Available (Made to Order)';
        }
        
        return match($status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => "Low Stock ({$stock} remaining)",
            'in_stock' => "In Stock ({$stock} available)",
            default => 'Available'
        };
    }
    
    /**
     * Get availability color for display
     */
    private function getAvailabilityColor(string $status): string
    {
        return match($status) {
            'out_of_stock' => 'red',
            'low_stock' => 'orange',
            'in_stock' => 'green',
            default => 'gray'
        };
    }

    /**
     * Show reservation summary and ask if user wants to create an order
     */
    public function showReservationSummary(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        $reservation = Reservation::with(['branch', 'customer', 'orders'])->findOrFail($reservationId);
        
        return view('orders.reservation-summary', compact('reservation'));
    }

    /**
     * Create order from reservation workflow
     */
    public function createFromReservation(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        $reservation = Reservation::with(['branch.organization', 'customer'])->findOrFail($reservationId);
        
        // Validate reservation can have orders
        if (!in_array($reservation->status, ['confirmed', 'pending'])) {
            return redirect()->back()->with('error', 'Orders can only be created for confirmed reservations');
        }
        
        // Get available menu items
        $menuItems = MenuItem::whereHas('itemMaster', function($query) use ($reservation) {
                $query->where('is_active', true)
                      ->where('branch_id', $reservation->branch_id);
            })
            ->with(['menuCategory', 'itemMaster'])
            ->get()
            ->groupBy('menuCategory.name');
        
        // Get available stewards for the branch
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('branch_id', $reservation->branch_id)
            ->where('is_active', true)
            ->get();

        // Get order types for dine-in
        $orderTypes = collect(OrderType::dineInTypes())->map(function($type) {
            return [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        });

        return view('orders.create-from-reservation', compact(
            'reservation', 
            'menuItems', 
            'stewards', 
            'orderTypes'
        ));
    }

    /**
     * Store order created from reservation
     */
    public function storeFromReservation(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::dineInTypes(), 'value')),
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_masters,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:500',
            'steward_id' => 'nullable|exists:employees,id',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $reservation = Reservation::with(['branch.organization', 'customer'])->findOrFail($data['reservation_id']);
            
            // Validate reservation
            if (!in_array($reservation->status, ['confirmed', 'pending'])) {
                throw new \Exception('Orders can only be created for confirmed reservations');
            }

            // Create order from reservation
            $orderData = [
                'order_type' => OrderType::from($data['order_type']),
                'special_instructions' => $data['special_instructions'] ?? null,
                'user_id' => $data['steward_id'] ?? null,
                'placed_by_admin' => Auth::check() && Auth::user()->hasRole(['admin', 'super_admin']),
                'created_by' => Auth::id(),
            ];

            $order = Order::createFromReservation($reservation, $orderData);

            // Add order items
            $subtotal = 0;
            foreach ($data['items'] as $itemData) {
                $menuItem = ItemMaster::findOrFail($itemData['item_id']);
                
                // Check inventory availability
                if (!$this->inventoryService->checkAvailability($menuItem, $itemData['quantity'])) {
                    throw new \Exception("Insufficient inventory for {$menuItem->name}");
                }
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->selling_price,
                    'total_price' => $menuItem->selling_price * $itemData['quantity'],
                    'inventory_item_id' => $menuItem->id,
                ]);
                
                $subtotal += $orderItem->total_price;
                
                // Reserve inventory
                $this->inventoryService->reserveItem($menuItem, $itemData['quantity'], $order->id);
            }

            // Calculate totals
            $order->calculateTotals();
            $order->estimated_prep_time = $order->calculateEstimatedPrepTime();
            $order->save();

            // Send order creation notification
            $this->notificationService->sendOrderCreated($order);

            return redirect()->route('orders.payment-selection', ['order' => $order->id])
                ->with('success', 'Order created successfully! Please proceed with payment.');
        });
    }

    /**
     * Show payment selection for order
     */
    public function showPaymentSelection(Order $order)
    {
        $order->load(['reservation', 'orderItems.menuItem']);
        
        $paymentMethods = [
            Order::PAYMENT_METHOD_CASH => 'Cash',
            Order::PAYMENT_METHOD_CARD => 'Card',
            Order::PAYMENT_METHOD_DIGITAL => 'Digital Payment',
        ];

        return view('orders.payment-selection', compact('order', 'paymentMethods'));
    }

    /**
     * Process payment for order
     */
    public function processPayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_method' => 'required|string|in:' . implode(',', [
                Order::PAYMENT_METHOD_CASH,
                Order::PAYMENT_METHOD_CARD, 
                Order::PAYMENT_METHOD_DIGITAL
            ]),
            'payment_reference' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($data, $order) {
            $order->update([
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'status' => Order::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            // Update reservation status if needed
            if ($order->reservation && $order->reservation->status === 'pending') {
                $order->reservation->confirmReservation();
            }

            // Send confirmation notifications
            $this->notificationService->sendOrderConfirmed($order);

            return redirect()->route('orders.confirmation', ['order' => $order->id])
                ->with('success', 'Payment processed successfully!');
        });
    }

    /**
     * Show order confirmation
     */
    public function showConfirmation(Order $order)
    {
        $order->load(['reservation', 'orderItems.menuItem', 'customer']);
        
        return view('orders.confirmation', compact('order'));
    }
    /**
     * Get today's orders for admin dashboard with KOT filter
     */
    public function getTodaysOrdersForAdmin(Request $request)
    {
        $filters = $request->only(['status', 'order_type', 'has_kot', 'branch_id']);
        
        $branchId = $request->user()->branch_id;
        if ($request->user()->hasRole('super_admin')) {
            $branchId = $request->input('branch_id', null);
        }

        $orders = Order::getTodaysOrders($branchId, $filters)->paginate(20);
        
        return view('admin.orders.today', compact('orders', 'filters'));
    }

    /**
     * Get menu items from active menus for specific branch (API endpoint)
     */
    public function getMenuItemsFromActiveMenus(Request $request, $branchId)
    {
        try {
            // Get the currently active menu for the branch
            $activeMenu = \App\Models\Menu::getActiveMenuForBranch($branchId);
            
            if (!$activeMenu) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active menu found for this branch',
                    'items' => []
                ]);
            }

            // Get menu items from the active menu
            $menuItems = $activeMenu->menuItems()
                ->with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->wherePivot('is_available', true)
                ->get();

            $items = $menuItems->map(function($item) use ($branchId) {
                $itemType = $item->type ?? MenuItem::TYPE_KOT;
                $currentStock = 0;
                $canOrder = true;
                $stockStatus = 'available';
                
                if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                    $canOrder = $currentStock > 0;
                    
                    if ($currentStock <= 0) {
                        $stockStatus = 'out_of_stock';
                    } elseif ($currentStock <= 5) {
                        $stockStatus = 'low_stock';
                    }
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->pivot->override_price ?? $item->price,
                    'selling_price' => $item->pivot->override_price ?? $item->price,
                    'type' => $itemType,
                    'type_name' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                    'item_type' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                    'current_stock' => $currentStock,
                    'can_order' => $canOrder,
                    'stock_status' => $stockStatus,
                    'category_name' => $item->menuCategory->name ?? 'Uncategorized',
                    'category_id' => $item->menu_category_id,
                    'preparation_time' => $item->preparation_time ?? 15,
                    'is_vegetarian' => $item->is_vegetarian ?? false,
                    'is_featured' => $item->is_featured ?? false,
                    'display_order' => $item->display_order ?? 0,
                    'special_notes' => $item->pivot->special_notes,
                ];
            });

            return response()->json([
                'success' => true,
                'menu' => [
                    'id' => $activeMenu->id,
                    'name' => $activeMenu->name,
                    'type' => $activeMenu->menu_type
                ],
                'items' => $items->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading menu items from active menus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading menu items',
                'items' => []
            ], 500);
        }
    }
}