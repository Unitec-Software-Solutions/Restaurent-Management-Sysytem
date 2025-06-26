<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\MenuItem;
use App\Services\GuestSessionService;
use App\Services\MenuScheduleService;
use App\Services\OrderManagementService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Phase 2: Guest Functionality Controller
 * Handles unauthenticated access to menu, orders, and reservations
 */
class GuestController extends Controller
{
    protected GuestSessionService $guestSessionService;
    protected MenuScheduleService $menuScheduleService;
    protected OrderManagementService $orderManagementService;
    protected CartService $cartService;

    public function __construct(
        GuestSessionService $guestSessionService,
        MenuScheduleService $menuScheduleService,
        OrderManagementService $orderManagementService,
        CartService $cartService
    ) {
        $this->guestSessionService = $guestSessionService;
        $this->menuScheduleService = $menuScheduleService;
        $this->orderManagementService = $orderManagementService;
        $this->cartService = $cartService;
    }

    /**
     * Display date-based menu for guests
     */
    public function viewMenu(Request $request, $branchId = null)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $requestedDate = Carbon::parse($date);

        // Get available branches
        $branches = Branch::active()
            ->select('id', 'name', 'address', 'phone', 'opening_time', 'closing_time')
            ->get();

        if (!$branchId) {
            // Show branch selection
            return view('guest.menu.branch-selection', compact('branches', 'date'));
        }

        $branch = Branch::active()->findOrFail($branchId);

        // Get active menu for the specified date and branch
        $menu = $this->getMenuForDateAndBranch($requestedDate, $branchId);

        if (!$menu) {
            return view('guest.menu.not-available', compact('branch', 'date', 'branches'));
        }

        // Get categorized menu items
        $menuItems = $this->getCategorizedMenuItems($menu);

        // Check if branch is currently open
        $isOpen = $this->isBranchOpen($branch);

        return view('guest.menu.view', compact(
            'menu', 
            'menuItems', 
            'branch', 
            'branches', 
            'date', 
            'isOpen'
        ));
    }

    /**
     * View menu by specific date
     */
    public function viewMenuByDate(Request $request, $branchId, $date)
    {
        return $this->viewMenu($request->merge(['date' => $date]), $branchId);
    }

    /**
     * View special menu
     */
    public function viewSpecialMenu(Request $request, $branchId)
    {
        $branch = Branch::active()->findOrFail($branchId);
        
        // Get special menus for today
        $specialMenus = Menu::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('is_special', true)
            ->where('active_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with(['menuItems.category'])
            ->get();

        return view('guest.menu.special', compact('specialMenus', 'branch'));
    }

    /**
     * Add item to guest cart
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1|max:10',
            'special_instructions' => 'nullable|string|max:500'
        ]);

        $menuItem = MenuItem::with(['menu', 'category'])->findOrFail($validated['menu_item_id']);

        // Validate menu is still active
        if (!$menuItem->menu->is_active || !$menuItem->menu->shouldBeActiveNow()) {
            return response()->json([
                'success' => false,
                'message' => 'This menu is no longer available.'
            ], 400);
        }

        // Check item availability
        if (!$menuItem->is_available || !$menuItem->isAvailableForOrdering()) {
            return response()->json([
                'success' => false,
                'message' => 'This item is currently unavailable.'
            ], 400);
        }

        // Add to session cart
        $cartItem = [
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'price' => $menuItem->price,
            'quantity' => $validated['quantity'],
            'special_instructions' => $validated['special_instructions'],
            'total' => $menuItem->price * $validated['quantity'],
            'menu_id' => $menuItem->menu_id,
            'branch_id' => $menuItem->menu->branch_id
        ];

        $this->guestSessionService->addToCart($cartItem);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => $this->cartService->getCartCount()
        ]);
    }

    /**
     * View guest cart
     */
    public function viewCart()
    {
        $cart = $this->guestSessionService->getCart();
        $cartSummary = $this->cartService->getCartSummary();

        return view('guest.cart.view', compact('cart', 'cartSummary'));
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:0|max:10'
        ]);

        if ($validated['quantity'] == 0) {
            $this->guestSessionService->removeFromCart($validated['menu_item_id']);
        } else {
            $this->guestSessionService->updateCartQuantity(
                $validated['menu_item_id'], 
                $validated['quantity']
            );
        }

        return response()->json([
            'success' => true,
            'cart_count' => $this->cartService->getCartCount(),
            'cart_summary' => $this->cartService->getCartSummary()
        ]);
    }

    /**
     * Update cart item
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1|max:10',
            'special_instructions' => 'nullable|string|max:500'
        ]);

        $this->cartService->updateCartItem($validated['menu_item_id'], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total' => $this->cartService->getCartTotal()
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id'
        ]);

        $this->guestSessionService->removeFromCart($validated['menu_item_id']);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total' => $this->cartService->getCartTotal()
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        $this->guestSessionService->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    }

    /**
     * Create guest order
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'order_type' => 'required|in:takeaway_on_demand,takeaway_scheduled',
            'pickup_time' => 'required_if:order_type,takeaway_scheduled|nullable|date|after:now',
            'special_instructions' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash_on_pickup,online_payment'
        ]);

        $cart = $this->guestSessionService->getCart();

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        // Validate all items are still available
        $validationResult = $this->validateCartItems($cart);
        if (!$validationResult['valid']) {
            return back()->withErrors(['cart' => $validationResult['message']]);
        }

        try {
            // Generate guest session ID
            $guestSessionId = $this->guestSessionService->getOrCreateGuestId();

            // Create order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'branch_id' => $cart[0]['branch_id'],
                'menu_id' => $cart[0]['menu_id'],
                'order_type' => $validated['order_type'],
                'pickup_time' => $validated['pickup_time'],
                'special_instructions' => $validated['special_instructions'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'guest_session_id' => $guestSessionId,
                'order_source' => 'guest_website',
                'created_at' => now()
            ]);

            // Create order items
            foreach ($cart as $item) {
                $order->orderItems()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total'],
                    'special_instructions' => $item['special_instructions']
                ]);
            }

            // Calculate totals
            $subtotal = collect($cart)->sum('total');
            $tax = $subtotal * 0.10; // 10% tax
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total
            ]);

            // Clear cart
            $this->guestSessionService->clearCart();

            // Generate order confirmation
            $orderToken = Str::random(32);
            Session::put("order_token_{$order->id}", $orderToken);

            return redirect()->route('guest.order.confirmation', [
                'order' => $order->id,
                'token' => $orderToken
            ])->with('success', 'Your order has been placed successfully!');

        } catch (\Exception $e) {
            Log::error('Guest order creation failed', [
                'error' => $e->getMessage(),
                'cart' => $cart,
                'guest_session' => $guestSessionId ?? null
            ]);

            return back()->withErrors(['order' => 'Failed to place order. Please try again.']);
        }
    }

    /**
     * Show order confirmation
     */
    public function orderConfirmation($orderId, $token)
    {
        $sessionToken = Session::get("order_token_{$orderId}");
        
        if (!$sessionToken || $sessionToken !== $token) {
            abort(404, 'Order not found');
        }

        $order = Order::with(['orderItems.menuItem', 'branch'])
            ->findOrFail($orderId);

        return view('guest.order.confirmation', compact('order'));
    }

    /**
     * Track order status
     */
    public function trackOrder(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['orderItems.menuItem', 'branch'])
            ->first();

        if (!$order) {
            return view('guest.order.not-found', compact('orderNumber'));
        }

        return view('guest.order.track', compact('order'));
    }

    /**
     * Get order details
     */
    public function orderDetails(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['orderItems.menuItem.category', 'branch'])
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'order' => $order,
            'status' => $order->status,
            'items' => $order->orderItems->map(function($item) {
                return [
                    'name' => $item->menuItem->name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->quantity * $item->unit_price,
                    'special_instructions' => $item->special_instructions
                ];
            })
        ]);
    }

    /**
     * Book table reservation (guest)
     */
    /**
     * Show reservation confirmation by ID and token (legacy route)
     */
    public function reservationConfirmationById($reservationId, $token)
    {
        $sessionToken = Session::get("reservation_token_{$reservationId}");
        
        if (!$sessionToken || $sessionToken !== $token) {
            abort(404, 'Reservation not found');
        }

        $reservation = Reservation::with('branch')->findOrFail($reservationId);

        return view('guest.reservation.confirmation', compact('reservation'));
    }

    /**
     * Show reservation booking form for guests
     */
    public function showReservationForm(Request $request, $branchId = null)
    {
        $branches = $branchId 
            ? Branch::where('id', $branchId)->where('is_active', true)->get()
            : Branch::where('is_active', true)->get();
            
        if ($branches->isEmpty()) {
            return redirect()->back()->with('error', 'No branches available for reservations');
        }
        
        $selectedBranch = $branches->first();
        
        // Get available time slots for the next 30 days
        $availableSlots = $this->getAvailableTimeSlots($selectedBranch->id);
        
        // Get guest session for pre-filling form
        $guestId = $this->guestSessionService->getOrCreateGuestId();
        $guestData = Session::get("guest_data_{$guestId}", []);
        
        return view('guest.reservations.create', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'availableSlots' => $availableSlots,
            'guestData' => $guestData,
            'minDate' => now()->format('Y-m-d'),
            'maxDate' => now()->addDays(30)->format('Y-m-d')
        ]);
    }

    /**
     * Create guest reservation
     */
    public function createReservation(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:20',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:500',
            'dietary_requirements' => 'nullable|string|max:300'
        ]);
        
        try {
            // Check branch availability
            $branch = Branch::findOrFail($validated['branch_id']);
            if (!$branch->is_active || !$branch->accepts_reservations) {
                return back()->withErrors(['branch_id' => 'This branch is not accepting reservations']);
            }
            
            // Validate time slot availability
            $reservationDateTime = Carbon::parse($validated['reservation_date'] . ' ' . $validated['reservation_time']);
            
            if (!$this->isTimeSlotAvailable($branch->id, $reservationDateTime, $validated['party_size'])) {
                return back()->withErrors(['reservation_time' => 'This time slot is not available']);
            }
            
            // Get guest session ID
            $guestId = $this->guestSessionService->getOrCreateGuestId();
            
            // Create reservation
            $reservation = Reservation::create([
                'branch_id' => $validated['branch_id'],
                'organization_id' => $branch->organization_id,
                'guest_session_id' => $guestId,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'party_size' => $validated['party_size'],
                'reservation_date' => $validated['reservation_date'],
                'reservation_time' => $validated['reservation_time'],
                'special_requests' => $validated['special_requests'],
                'dietary_requirements' => $validated['dietary_requirements'],
                'status' => 'pending',
                'confirmation_number' => $this->generateConfirmationNumber(),
                'created_at' => now()
            ]);
            
            // Store guest data for future use
            $guestData = [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'email' => $validated['customer_email']
            ];
            Session::put("guest_data_{$guestId}", $guestData);
            
            // Send confirmation (would integrate with notification service)
            Log::info('Guest reservation created', [
                'reservation_id' => $reservation->id,
                'confirmation_number' => $reservation->confirmation_number,
                'guest_id' => $guestId
            ]);
            
            return redirect()->route('guest.reservation.confirmation', $reservation->confirmation_number)
                ->with('success', 'Your reservation has been submitted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Guest reservation creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $validated
            ]);
            
            return back()->withErrors(['general' => 'Unable to create reservation. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show reservation confirmation
     */
    public function reservationConfirmation($confirmationNumber)
    {
        $reservation = Reservation::where('confirmation_number', $confirmationNumber)->firstOrFail();
        
        return view('guest.reservations.confirmation', [
            'reservation' => $reservation,
            'branch' => $reservation->branch
        ]);
    }

    /**
     * Get menu for specific date and branch
     */
    private function getMenuForDateAndBranch(Carbon $date, int $branchId): ?Menu
    {
        // Check cache first
        $cacheKey = "guest_menu_{$branchId}_{$date->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function() use ($date, $branchId) {
            return Menu::where('branch_id', $branchId)
                ->where('valid_from', '<=', $date)
                ->where('valid_until', '>=', $date)
                ->where('is_active', true)
                ->whereJsonContains('available_days', strtolower($date->format('l')))
                ->with(['menuItems.category'])
                ->first();
        });
    }

    /**
     * Get categorized menu items
     */
    private function getCategorizedMenuItems(Menu $menu): array
    {
        return $menu->menuItems()
            ->with('category')
            ->where('is_available', true)
            ->get()
            ->groupBy('category.name')
            ->toArray();
    }

    /**
     * Check if branch is currently open
     */
    private function isBranchOpen(Branch $branch): bool
    {
        $now = now();
        $currentTime = $now->format('H:i');
        
        return $currentTime >= $branch->opening_time && $currentTime <= $branch->closing_time;
    }

    /**
     * Validate cart items are still available
     */
    private function validateCartItems(array $cart): array
    {
        foreach ($cart as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            
            if (!$menuItem || !$menuItem->is_available) {
                return [
                    'valid' => false,
                    'message' => "Item '{$item['name']}' is no longer available."
                ];
            }

            if (!$menuItem->menu->is_active) {
                return [
                    'valid' => false,
                    'message' => 'The menu is no longer active. Please refresh and try again.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Check if time slot is available for reservation
     */
    private function isTimeSlotAvailable($branchInput, Carbon $dateTime, int $partySize): bool
    {
        // Handle both Branch object and branch ID
        $branch = $branchInput instanceof Branch ? $branchInput : Branch::findOrFail($branchInput);
        
        // Get existing reservations for that time slot (±30 minutes)
        $startWindow = $dateTime->copy()->subMinutes(30);
        $endWindow = $dateTime->copy()->addMinutes(30);

        $existingReservations = Reservation::where('branch_id', $branch->id)
            ->where('reservation_date', $dateTime->format('Y-m-d'))
            ->whereBetween('reservation_time', [
                $startWindow->format('H:i'),
                $endWindow->format('H:i')
            ])
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('party_size');

        $availableCapacity = $branch->total_capacity - $existingReservations;

        return $availableCapacity >= $partySize;
    }

    /**
     * Get available time slots for a branch
     */
    private function getAvailableTimeSlots(int $branchId): array
    {
        $branch = Branch::findOrFail($branchId);
        $slots = [];
        
        // Get branch operating hours (default if not set)
        $openTime = $branch->open_time ?? '09:00';
        $closeTime = $branch->close_time ?? '22:00';
        
        // Generate 30-minute slots
        $currentDate = now()->startOfDay();
        
        for ($day = 0; $day < 30; $day++) {
            $date = $currentDate->copy()->addDays($day);
            $dateString = $date->format('Y-m-d');
            
            // Skip if date is in the past
            if ($date->lt(now()->startOfDay())) {
                continue;
            }
            
            $dailySlots = [];
            $currentSlot = $date->copy()->setTimeFromTimeString($openTime);
            $endTime = $date->copy()->setTimeFromTimeString($closeTime);
            
            while ($currentSlot->lt($endTime)) {
                // Skip slots that are in the past for today
                if ($date->isToday() && $currentSlot->lt(now()->addHour())) {
                    $currentSlot->addMinutes(30);
                    continue;
                }
                
                $slotTime = $currentSlot->format('H:i');
                $isAvailable = $this->isTimeSlotAvailable($branchId, $currentSlot, 2); // Check for 2 people as default
                
                $dailySlots[] = [
                    'time' => $slotTime,
                    'display_time' => $currentSlot->format('g:i A'),
                    'is_available' => $isAvailable,
                    'capacity_remaining' => $this->getSlotCapacityRemaining($branchId, $currentSlot)
                ];
                
                $currentSlot->addMinutes(30);
            }
            
            $slots[$dateString] = [
                'date' => $dateString,
                'display_date' => $date->format('l, M j, Y'),
                'is_today' => $date->isToday(),
                'slots' => $dailySlots
            ];
        }
        
        return $slots;
    }

    /**
     * Get remaining capacity for a time slot
     */
    private function getSlotCapacityRemaining(int $branchId, Carbon $dateTime): int
    {
        $branch = Branch::findOrFail($branchId);
        $maxCapacity = $branch->max_reservation_capacity ?? 100;
        
        $existingReservations = Reservation::where('branch_id', $branchId)
            ->where('reservation_date', $dateTime->format('Y-m-d'))
            ->where('reservation_time', $dateTime->format('H:i:s'))
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('party_size');
            
        return max(0, $maxCapacity - $existingReservations);
    }

    /**
     * Generate unique confirmation number
     */
    private function generateConfirmationNumber(): string
    {
        do {
            $number = 'RSV-' . strtoupper(Str::random(6)) . '-' . now()->format('ymd');
        } while (Reservation::where('confirmation_number', $number)->exists());
        
        return $number;
    }

    /**
     * Get session info
     */
    public function sessionInfo()
    {
        return response()->json([
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total' => $this->cartService->getCartTotal(),
            'cart_items' => $this->cartService->getCartItems(),
            'session_id' => session()->getId()
        ]);
    }
}
