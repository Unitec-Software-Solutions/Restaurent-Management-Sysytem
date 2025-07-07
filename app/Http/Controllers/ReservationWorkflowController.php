<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\MenuItem;
use App\Models\Employee;
use App\Enums\ReservationType;
use App\Enums\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReservationWorkflowController extends Controller
{
    /**
     * Show reservation summary and ask for order decision
     */
    public function showReservationSummary(Reservation $reservation)
    {
        $reservation->load(['branch', 'customer']);
        
        return view('reservations.summary', compact('reservation'));
    }

    /**
     * Handle reservation confirmation and order decision
     */
    public function confirmReservation(Request $request, Reservation $reservation)
    {
        $request->validate([
            'action' => 'required|in:make_order,payment_only'
        ]);

        DB::beginTransaction();
        try {
            // Confirm the reservation
            $reservation->update([
                'status' => 'confirmed',
                'confirmed_at' => now()
            ]);

            if ($request->action === 'make_order') {
                // Redirect to order creation
                DB::commit();
                return redirect()->route('orders.create-from-reservation', $reservation)
                    ->with('success', 'Reservation confirmed! Please create your order.');
            } else {
                // Redirect to payment for reservation fee
                DB::commit();
                return redirect()->route('reservations.payment', $reservation)
                    ->with('success', 'Reservation confirmed! Please proceed to payment.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to confirm reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show order creation form for confirmed reservation
     */
    public function createOrderFromReservation(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['confirmed', 'checked_in'])) {
            return redirect()->route('reservations.summary', $reservation)
                ->withErrors(['error' => 'Reservation must be confirmed before creating orders']);
        }

        // Get available menu items for the branch and organization
        $menuItems = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($reservation) {
                $q->where('branch_id', $reservation->branch_id)
                  ->orWhere(function($subQ) use ($reservation) {
                      // Include organization-wide items
                      $subQ->whereNull('branch_id')
                           ->where('organization_id', $reservation->branch->organization_id);
                  });
            })
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Add stock and availability information
        foreach ($menuItems as $item) {
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                $item->current_stock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $reservation->branch_id);
                $item->can_order = $item->current_stock > 0;
            } else {
                $item->current_stock = null;
                $item->can_order = true; // KOT items are always available
            }
        }

        // Get available stewards
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('branch_id', $reservation->branch_id)
            ->where('is_active', true)
            ->get();

        // Get dine-in order types
        $orderTypes = collect(OrderType::dineInTypes())->map(function($type) {
            return [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        });

        return view('reservations.create-order', compact(
            'reservation', 
            'menuItems', 
            'stewards', 
            'orderTypes'
        ));
    }

    /**
     * Store order created from reservation
     */
    public function storeOrderFromReservation(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::dineInTypes(), 'value')),
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            'steward_id' => 'nullable|exists:employees,id',
        ]);

        return DB::transaction(function () use ($validated, $reservation) {
            // Get customer
            $customer = $reservation->customer ?? Customer::findByPhone($reservation->phone);
            
            if (!$customer) {
                throw new \Exception('Customer not found for reservation');
            }

            // Validate stock for all items first
            $stockErrors = [];
            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem) continue;
                
                // Only check stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $reservation->branch_id);
                    if ($currentStock < $itemData['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$itemData['quantity']}";
                    }
                }
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Create order
            $order = Order::create([
                'reservation_id' => $reservation->id,
                'branch_id' => $reservation->branch_id,
                'organization_id' => $reservation->branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'customer_email' => $customer->email,
                'order_type' => OrderType::from($validated['order_type']),
                'order_number' => $this->generateOrderNumber($reservation->branch_id),
                'status' => 'pending',
                'order_date' => now(),
                'special_instructions' => $validated['special_instructions'],
                'user_id' => $validated['steward_id'],
            ]);

            // Create order items and calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem) continue;
                
                $lineTotal = $menuItem->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $order->orderItems()->create([
                    'menu_item_id' => $itemData['menu_item_id'],
                    'item_name' => $menuItem->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'subtotal' => $lineTotal,
                    'total_price' => $lineTotal,
                    'special_instructions' => $itemData['special_instructions'],
                ]);

                // Reserve stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    \App\Models\ItemTransaction::create([
                        'item_master_id' => $menuItem->item_master_id,
                        'branch_id' => $reservation->branch_id,
                        'transaction_type' => 'reservation',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Reserved for order #{$order->id}",
                    ]);
                }
            }

            // Calculate taxes and total
            $taxRate = 0.1; // 10% tax - can be configurable
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax,
                'total' => $total,
            ]);

            return redirect()->route('orders.summary', $order)
                ->with('success', 'Order created successfully!');
        });
    }

    /**
     * Show takeaway order creation form with customer phone
     */
    public function createTakeawayOrder(Request $request)
    {
        $phone = $request->input('phone');
        $organizationId = $request->input('organization_id');
        $branchId = $request->input('branch_id');
        $customer = null;
        
        if ($phone) {
            $customer = Customer::findByPhone($phone);
        }

        // Get user context (admin or regular user)
        $admin = auth('admin')->user();
        $isAdmin = !is_null($admin);
        
        // Get organizations and branches based on user type
        $organizations = collect();
        $branches = collect();
        $defaultOrganization = null;
        $defaultBranch = null;

        if ($isAdmin) {
            // Admin user - show organization selection
            if ($admin->is_super_admin) {
                // Super admin can see all organizations
                $organizations = Organization::where('is_active', true)->get();
            } elseif ($admin->organization_id) {
                // Org/Branch admin - get their organization
                $defaultOrganization = $admin->organization_id;
                $organizations = Organization::where('id', $admin->organization_id)
                    ->where('is_active', true)->get();
                
                if ($admin->branch_id) {
                    // Branch admin - set default branch
                    $defaultBranch = $admin->branch_id;
                }
            }
            
            // Get branches for selected organization
            $selectedOrgId = $organizationId ?: $defaultOrganization;
            if ($selectedOrgId) {
                $branches = Branch::where('organization_id', $selectedOrgId)
                    ->where('is_active', true)->get();
            }
        } else {
            // Regular customer - show all active branches from all organizations
            $branches = Branch::with('organization')
                ->where('is_active', true)
                ->whereHas('organization', function($q) {
                    $q->where('is_active', true);
                })
                ->get();
        }

        // Get menu items for selected branch
        $items = collect();
        $selectedBranchId = $branchId ?: $defaultBranch;
        
        if ($selectedBranchId) {
            $items = MenuItem::with(['menuCategory', 'itemMaster'])
                ->where('is_active', true)
                ->where('is_available', true)
                ->where(function($q) use ($selectedBranchId, $selectedOrgId) {
                    $q->where('branch_id', $selectedBranchId)
                      ->orWhere(function($subQ) use ($selectedOrgId) {
                          // Include organization-wide items
                          $subQ->whereNull('branch_id')
                               ->where('organization_id', $selectedOrgId);
                      });
                })
                ->orderBy('menu_category_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Add stock and availability information
            foreach ($items as $item) {
                $itemType = $item->type ?? MenuItem::TYPE_KOT;
                
                if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                    $item->current_stock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $selectedBranchId);
                    $item->can_order = $item->current_stock > 0;
                    $item->item_type = 'Buy & Sell';
                } else {
                    $item->current_stock = null;
                    $item->can_order = true; // KOT items are always available
                    $item->item_type = 'KOT';
                }
                
                // Add selling price for display
                $item->selling_price = $item->price;
            }
        }

        return view('orders.takeaway.create', compact(
            'branches', 
            'organizations', 
            'customer', 
            'phone', 
            'defaultOrganization', 
            'defaultBranch', 
            'items',
            'isAdmin',
            'organizationId',
            'branchId'
        ));
    }

    /**
     * Get branches for organization (AJAX)
     */
    public function getBranchesForOrganization(Request $request, $organizationId)
    {
        if (!$organizationId) {
            return response()->json(['branches' => []]);
        }

        // Check admin permissions
        $admin = auth('admin')->user();
        if ($admin && !$admin->is_super_admin && $admin->organization_id !== (int)$organizationId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $branches = Branch::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->select('id', 'name', 'address', 'phone')
            ->orderBy('name')
            ->get();

        return response()->json(['branches' => $branches]);
    }

    /**
     * Get menu items for branch (AJAX) - Enhanced version
     */
    public function getMenuItemsForBranch(Request $request, $branchId)
    {
        $organizationId = $request->input('organization_id');
        
        if (!$branchId) {
            return response()->json(['items' => []]);
        }

        // Get branch information to determine organization
        $branch = Branch::find($branchId);
        if (!$branch) {
            return response()->json(['items' => [], 'error' => 'Branch not found']);
        }

        $orgId = $organizationId ?: $branch->organization_id;

        $menuItems = MenuItem::with(['menuCategory', 'itemMaster'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($branchId, $orgId) {
                $q->where('branch_id', $branchId)
                  ->orWhere(function($subQ) use ($orgId) {
                      // Include organization-wide items
                      $subQ->whereNull('branch_id')
                           ->where('organization_id', $orgId);
                  });
            })
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $items = $menuItems->map(function($item) use ($branchId) {
            $itemType = $item->type ?? MenuItem::TYPE_KOT;
            $currentStock = 0;
            $canOrder = true;
            
            if ($itemType === MenuItem::TYPE_BUY_SELL && $item->item_master_id) {
                $currentStock = \App\Models\ItemTransaction::stockOnHand($item->item_master_id, $branchId);
                $canOrder = $currentStock > 0;
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'selling_price' => $item->price,
                'type' => $itemType,
                'type_name' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                'item_type' => $itemType === MenuItem::TYPE_KOT ? 'KOT' : 'Buy & Sell',
                'current_stock' => $currentStock,
                'can_order' => $canOrder,
                'category_name' => $item->menuCategory->name ?? 'Uncategorized',
                'category_id' => $item->menu_category_id,
                'preparation_time' => $item->preparation_time ?? 15,
                'is_vegetarian' => $item->is_vegetarian ?? false,
                'is_featured' => $item->is_featured ?? false,
                'display_order' => $item->display_order ?? 0,
            ];
        });

        // Group items by category for better display
        $categorizedItems = $items->groupBy('category_name')->map(function($categoryItems, $categoryName) {
            return [
                'category_name' => $categoryName,
                'items' => $categoryItems->sortBy('display_order')->values()
            ];
        })->values();

        return response()->json([
            'items' => $items,
            'categorized_items' => $categorizedItems,
            'branch_info' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'organization_id' => $branch->organization_id
            ]
        ]);
    }

    /**
     * Store takeaway order
     */
    public function storeTakeawayOrder(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_time' => 'required|date|after_or_equal:now',
            'order_type' => 'required|string|in:' . implode(',', array_column(OrderType::takeawayTypes(), 'value')),
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated) {
            // Find or create customer
            $customer = Customer::findOrCreateByPhone($validated['customer_phone'], [
                'name' => $validated['customer_name'],
            ]);

            // Validate branch
            $branch = Branch::with('organization')->findOrFail($validated['branch_id']);
            if (!$branch->is_active || !$branch->organization->is_active) {
                throw new \Exception('Branch or organization is not active');
            }

            // Validate stock for all items first
            $stockErrors = [];
            $orderItems = [];
            $subtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $menuItem = MenuItem::find($itemData['menu_item_id']);
                if (!$menuItem || !$menuItem->is_active) {
                    $stockErrors[] = "Item is not available";
                    continue;
                }
                
                // Check stock for Buy & Sell items
                if ($menuItem->type === MenuItem::TYPE_BUY_SELL && $menuItem->item_master_id) {
                    $currentStock = \App\Models\ItemTransaction::stockOnHand($menuItem->item_master_id, $validated['branch_id']);
                    if ($currentStock < $itemData['quantity']) {
                        $stockErrors[] = "Insufficient stock for {$menuItem->name}. Available: {$currentStock}, Required: {$itemData['quantity']}";
                        continue;
                    }
                }

                $lineTotal = $menuItem->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                ];
            }

            if (!empty($stockErrors)) {
                throw new \Exception('Stock validation failed: ' . implode(', ', $stockErrors));
            }

            // Create order
            $orderNumber = $this->generateOrderNumber($validated['branch_id']);
            $taxRate = 0.1; // 10% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            $order = Order::create([
                'branch_id' => $validated['branch_id'],
                'organization_id' => $branch->organization_id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_phone_fk' => $customer->phone,
                'order_type' => OrderType::from($validated['order_type']),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'order_date' => now(),
                'order_time' => $validated['order_time'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'tax' => $tax,
                'total' => $total,
                'currency' => 'LKR',
                'payment_status' => 'pending',
                'special_instructions' => $validated['special_instructions'],
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $order->orderItems()->create([
                    'menu_item_id' => $itemData['menu_item']->id,
                    'item_name' => $itemData['menu_item']->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['total_price'], // Set subtotal as required by the table
                    'total_price' => $itemData['total_price'],
                ]);

                // Deduct stock for Buy & Sell items
                if ($itemData['menu_item']->type === MenuItem::TYPE_BUY_SELL && $itemData['menu_item']->item_master_id) {
                    \App\Models\ItemTransaction::create([
                        'item_master_id' => $itemData['menu_item']->item_master_id,
                        'branch_id' => $validated['branch_id'],
                        'transaction_type' => 'sale',
                        'quantity' => -$itemData['quantity'],
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Sold via takeaway order #{$order->id}",
                    ]);
                }
            }

            return redirect()->route('orders.takeaway.summary', $order)
                ->with('success', 'Takeaway order created successfully!');
        });
    }

    /**
     * Show order summary
     */
    public function showOrderSummary(Order $order)
    {
        $order->load(['orderItems.menuItem', 'customer', 'branch', 'reservation']);
        
        // Check if order has KOT items (items that require production)
        $hasKotItems = $order->orderItems()->whereHas('menuItem', function($q) {
            $q->where('type', MenuItem::TYPE_KOT)
              ->where('requires_preparation', true);
        })->exists();
        
        // Determine if order is editable (for payment options)
        $editable = in_array($order->status, ['pending', 'confirmed']) && 
                   $order->payment_status !== 'paid';
        
        return view('orders.summary', compact('order', 'hasKotItems', 'editable'));
    }

    /**
     * Print KOT for order
     */
    public function printKOT(Order $order)
    {
        // Only generate KOT for orders with KOT items
        $kotItems = $order->orderItems()->whereHas('menuItem', function($q) {
            $q->where('type', MenuItem::TYPE_KOT)
              ->where('requires_preparation', true);
        })->get();

        if ($kotItems->isEmpty()) {
            return back()->withErrors(['error' => 'No items in this order require kitchen preparation']);
        }

        // Update order to mark KOT as generated
        $order->update([
            'kot_generated' => true,
            'kot_generated_at' => now(),
            'status' => 'preparing'
        ]);

        return view('admin.orders.print-kot', compact('order', 'kotItems'));
    }

    /**
     * Get admin defaults for phone and datetime
     */
    public function getAdminDefaults()
    {
        $admin = auth('admin')->user();
        
        $defaults = [
            'phone' => '',
            'datetime' => now()->format('Y-m-d\TH:i'),
            'branch_id' => $admin->branch_id ?? null,
        ];

        // For admins, try to get default phone from branch
        if ($admin && $admin->branch_id) {
            $branch = Branch::find($admin->branch_id);
            $defaults['phone'] = $branch->phone ?? '';
        }

        return response()->json($defaults);
    }

    /**
     * Filter today's orders for admin view
     */
    public function getTodayOrders(Request $request)
    {
        $admin = auth('admin')->user();
        $branchId = $request->input('branch_id');
        $status = $request->input('status');

        $query = Order::with(['orderItems.menuItem', 'customer', 'branch', 'reservation'])
            ->whereDate('order_date', today())
            ->orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply admin permissions
        if (!$admin->is_super_admin) {
            if ($admin->branch_id) {
                $query->where('branch_id', $admin->branch_id);
            } elseif ($admin->organization_id) {
                $query->whereHas('branch', function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                });
            }
        }

        // Apply filters
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        // Add KOT status to each order
        foreach ($orders as $order) {
            $order->has_kot_items = $order->orderItems()->whereHas('menuItem', function($q) {
                $q->where('type', MenuItem::TYPE_KOT);
            })->exists();
        }

        return view('admin.orders.today', compact('orders'));
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber($branchId)
    {
        $branch = Branch::find($branchId);
        $branchCode = $branch ? strtoupper(substr($branch->name, 0, 3)) : 'ORD';
        $date = now()->format('Ymd');
        $sequence = Order::whereDate('created_at', today())
            ->where('branch_id', $branchId)
            ->count() + 1;
        
        return "{$branchCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Admin-specific order creation with defaults
     */
    public function adminCreateOrder(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Get admin defaults
        $defaultPhone = '';
        $defaultDateTime = now()->format('Y-m-d\TH:i');
        $defaultBranch = null;

        if ($admin->branch_id) {
            $branch = Branch::find($admin->branch_id);
            $defaultPhone = $branch->phone ?? '';
            $defaultBranch = $branch;
        }

        // Get available branches based on admin permissions
        $branches = collect();
        if ($admin->is_super_admin) {
            $branches = Branch::where('is_active', true)->get();
        } elseif ($admin->organization_id) {
            $branches = Branch::where('organization_id', $admin->organization_id)
                ->where('is_active', true)->get();
        } elseif ($admin->branch_id) {
            $branches = Branch::where('id', $admin->branch_id)->get();
        }

        return view('admin.orders.create-with-defaults', compact(
            'defaultPhone', 
            'defaultDateTime', 
            'defaultBranch', 
            'branches'
        ));
    }

    /**
     * Check if order has KOT items (AJAX)
     */
    public function checkKOTItems(Order $order)
    {
        $hasKotItems = $order->orderItems()->whereHas('menuItem', function($q) {
            $q->where('type', \App\Models\MenuItem::TYPE_KOT);
        })->exists();

        return response()->json(['hasKotItems' => $hasKotItems]);
    }

    /**
     * Submit takeaway order for final processing
     */
    public function submitTakeawayOrder(Request $request, Order $order)
    {
        return DB::transaction(function () use ($request, $order) {
            try {
                // Validate that order is in pending status
                if ($order->status !== 'pending') {
                    return redirect()->back()
                        ->with('error', 'Order cannot be confirmed. Current status: ' . $order->status);
                }

                // Validate payment method if provided
                if ($request->filled('payment_method')) {
                    $request->validate([
                        'payment_method' => 'required|string|in:cash,card,online'
                    ]);
                }

                // Final stock validation before confirmation
                $stockErrors = [];
                foreach ($order->orderItems as $orderItem) {
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
                foreach ($order->orderItems as $orderItem) {
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
                            'notes' => "Stock deducted for Takeaway Order #{$order->order_number}",
                            'is_active' => true,
                        ]);
                    }
                }

                // Update order status and mark stock as deducted
                $updateData = [
                    'status' => 'submitted',
                    'stock_deducted' => true,
                    'submitted_at' => now(),
                ];

                // Add payment method if provided
                if ($request->filled('payment_method')) {
                    $updateData['payment_method'] = $request->payment_method;
                }

                $order->update($updateData);

                // Generate KOT for kitchen
                if (method_exists($order, 'generateKOT')) {
                    $order->generateKOT();
                }

                return redirect()->route('orders.takeaway.summary', $order)
                    ->with('success', 'Order confirmed successfully! Your order has been sent to the kitchen.');

            } catch (\Exception $e) {
                Log::error('Takeaway order submission failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->with('error', 'Failed to confirm order. Please try again.');
            }
        });
    }
}
