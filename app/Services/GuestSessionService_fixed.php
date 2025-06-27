<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Phase 2: Guest Session Management Service
 * Handles cart and session management for unauthenticated users
 */
class GuestSessionService
{
    private const CART_SESSION_KEY = 'guest_cart';
    private const GUEST_ID_SESSION_KEY = 'guest_id';

    /**
     * Get or create guest session ID
     */
    public function getOrCreateGuestId(): string
    {
        $guestId = Session::get(self::GUEST_ID_SESSION_KEY);
        
        if (!$guestId) {
            $guestId = 'guest_' . Str::random(16) . '_' . time();
            Session::put(self::GUEST_ID_SESSION_KEY, $guestId);
        }

        return $guestId;
    }

    /**
     * Get current cart
     */
    public function getCart(): array
    {
        return Session::get(self::CART_SESSION_KEY, []);
    }

    /**
     * Save cart to session
     */
    private function saveCart(array $cart): void
    {
        Session::put(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Find cart item index by menu item ID
     */
    private function findCartItemIndex(array $cart, int $menuItemId): int|false
    {
        foreach ($cart as $index => $item) {
            if ($item['menu_item_id'] == $menuItemId) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Add item to cart with validation and inventory checks
     */
    public function addToCart(array $cartItem): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'cart_count' => 0,
            'cart_total' => 0
        ];
        
        try {
            // Validate menu item
            $menuItem = MenuItem::with(['menu', 'inventoryItems'])->findOrFail($cartItem['menu_item_id']);
            
            if (!$menuItem->is_available) {
                $result['message'] = 'This item is currently unavailable';
                return $result;
            }
            
            // Check stock if required
            if ($menuItem->requires_inventory_check) {
                $stockCheck = $this->checkItemStock($menuItem, $cartItem['quantity']);
                if (!$stockCheck['available']) {
                    $result['message'] = $stockCheck['message'];
                    return $result;
                }
            }
            
            // Validate menu availability
            $menuValidation = $this->validateMenuAvailability($menuItem->menu);
            if (!$menuValidation['is_available']) {
                $result['message'] = $menuValidation['message'];
                return $result;
            }
            
            $cart = $this->getCart();
            $menuItemId = $cartItem['menu_item_id'];
            
            // Prepare cart item data
            $cartItemData = [
                'menu_item_id' => $menuItemId,
                'name' => $menuItem->name,
                'description' => $menuItem->description,
                'price' => $menuItem->price,
                'quantity' => $cartItem['quantity'],
                'total' => $menuItem->price * $cartItem['quantity'],
                'special_instructions' => $cartItem['special_instructions'] ?? '',
                'allergen_notes' => $cartItem['allergen_notes'] ?? '',
                'image_url' => $menuItem->image_url,
                'category' => $menuItem->category,
                'preparation_time' => $menuItem->preparation_time ?? 15,
                'added_at' => now()->toISOString(),
                'menu_name' => $menuItem->menu->name ?? null,
                'branch_id' => $menuItem->menu->branch_id ?? null
            ];
            
            // Check if item already in cart
            $existingIndex = $this->findCartItemIndex($cart, $menuItemId);
            
            if ($existingIndex !== false) {
                // Update existing item
                $newQuantity = $cart[$existingIndex]['quantity'] + $cartItem['quantity'];
                
                // Check maximum quantity limit
                if ($this->exceedsMaxQuantity($menuItem, $newQuantity)) {
                    $result['message'] = 'Maximum quantity limit reached for this item';
                    return $result;
                }
                
                $cart[$existingIndex]['quantity'] = $newQuantity;
                $cart[$existingIndex]['total'] = $newQuantity * $cart[$existingIndex]['price'];
                $cart[$existingIndex]['updated_at'] = now()->toISOString();
                
                // Merge special instructions
                if (!empty($cartItem['special_instructions'])) {
                    $existingInstructions = $cart[$existingIndex]['special_instructions'] ?? '';
                    $cart[$existingIndex]['special_instructions'] = trim($existingInstructions . ' ' . $cartItem['special_instructions']);
                }
            } else {
                // Add new item
                $cart[] = $cartItemData;
            }
            
            $this->saveCart($cart);
            
            $result['success'] = true;
            $result['message'] = 'Item added to cart successfully';
            $result['cart_count'] = $this->getCartItemCount();
            $result['cart_total'] = $this->getCartTotal();
            
        } catch (\Exception $e) {
            $result['message'] = 'Failed to add item to cart: ' . $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Check if item has sufficient stock
     */
    private function checkItemStock(MenuItem $menuItem, int $quantity): array
    {
        $stockCheck = [
            'available' => true,
            'message' => '',
            'available_quantity' => $quantity
        ];
        
        if (!$menuItem->inventoryItems || $menuItem->inventoryItems->isEmpty()) {
            return $stockCheck;
        }
        
        foreach ($menuItem->inventoryItems as $inventoryItem) {
            $requiredQuantity = $inventoryItem->pivot->quantity_required * $quantity;
            $branchInventory = $inventoryItem->branchInventory()
                ->where('branch_id', $menuItem->menu->branch_id)
                ->first();
                
            if (!$branchInventory || $branchInventory->current_stock < $requiredQuantity) {
                $stockCheck['available'] = false;
                $stockCheck['message'] = "Insufficient stock for {$menuItem->name}";
                $stockCheck['available_quantity'] = $branchInventory 
                    ? floor($branchInventory->current_stock / $inventoryItem->pivot->quantity_required)
                    : 0;
                break;
            }
        }
        
        return $stockCheck;
    }

    /**
     * Validate menu availability for current time
     */
    private function validateMenuAvailability($menu): array
    {
        $validation = [
            'is_available' => true,
            'message' => ''
        ];
        
        if (!$menu || !$menu->is_active) {
            $validation['is_available'] = false;
            $validation['message'] = 'Menu is currently unavailable';
            return $validation;
        }
        
        $now = now();
        
        // Check date range
        if ($menu->valid_from && $now->lt(Carbon::parse($menu->valid_from))) {
            $validation['is_available'] = false;
            $validation['message'] = 'Menu is not yet available';
            return $validation;
        }
        
        if ($menu->valid_until && $now->gt(Carbon::parse($menu->valid_until))) {
            $validation['is_available'] = false;
            $validation['message'] = 'Menu is no longer available';
            return $validation;
        }
        
        // Check time window
        if ($menu->start_time && $menu->end_time) {
            $currentTime = $now->format('H:i:s');
            
            if ($currentTime < $menu->start_time || $currentTime > $menu->end_time) {
                $validation['is_available'] = false;
                $validation['message'] = 'Menu is not available at this time';
                return $validation;
            }
        }
        
        return $validation;
    }

    /**
     * Check if quantity exceeds maximum allowed
     */
    private function exceedsMaxQuantity(MenuItem $menuItem, int $quantity): bool
    {
        $maxQuantity = $menuItem->max_order_quantity ?? 10; // Default max quantity
        return $quantity > $maxQuantity;
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItemQuantity(int $menuItemId, int $quantity): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'cart_count' => 0,
            'cart_total' => 0
        ];
        
        if ($quantity <= 0) {
            return $this->removeFromCart($menuItemId);
        }
        
        $cart = $this->getCart();
        $itemIndex = $this->findCartItemIndex($cart, $menuItemId);
        
        if ($itemIndex === false) {
            $result['message'] = 'Item not found in cart';
            return $result;
        }
        
        // Validate new quantity
        $menuItem = MenuItem::find($menuItemId);
        if ($menuItem && $this->exceedsMaxQuantity($menuItem, $quantity)) {
            $result['message'] = 'Quantity exceeds maximum allowed';
            return $result;
        }
        
        // Check stock if required
        if ($menuItem && $menuItem->requires_inventory_check) {
            $stockCheck = $this->checkItemStock($menuItem, $quantity);
            if (!$stockCheck['available']) {
                $result['message'] = $stockCheck['message'];
                return $result;
            }
        }
        
        $cart[$itemIndex]['quantity'] = $quantity;
        $cart[$itemIndex]['total'] = $quantity * $cart[$itemIndex]['price'];
        $cart[$itemIndex]['updated_at'] = now()->toISOString();
        
        $this->saveCart($cart);
        
        $result['success'] = true;
        $result['message'] = 'Cart updated successfully';
        $result['cart_count'] = $this->getCartItemCount();
        $result['cart_total'] = $this->getCartTotal();
        
        return $result;
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $menuItemId): array
    {
        $cart = $this->getCart();
        $itemIndex = $this->findCartItemIndex($cart, $menuItemId);
        
        $result = [
            'success' => false,
            'message' => 'Item not found in cart',
            'cart_count' => 0,
            'cart_total' => 0
        ];
        
        if ($itemIndex !== false) {
            array_splice($cart, $itemIndex, 1);
            $this->saveCart($cart);
            
            $result['success'] = true;
            $result['message'] = 'Item removed from cart';
        }
        
        $result['cart_count'] = $this->getCartItemCount();
        $result['cart_total'] = $this->getCartTotal();
        
        return $result;
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        Session::forget(self::CART_SESSION_KEY);
    }

    /**
     * Get cart item count
     */
    public function getCartItemCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Get cart total
     */
    public function getCartTotal(): float
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'total'));
    }

    /**
     * Get cart with calculated totals
     */
    public function getCartWithTotals(): array
    {
        $cart = $this->getCart();
        
        $subtotal = array_sum(array_column($cart, 'total'));
        $taxRate = 0.10; // 10% tax rate - could be configurable
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount;
        
        return [
            'items' => $cart,
            'item_count' => count($cart),
            'total_quantity' => array_sum(array_column($cart, 'quantity')),
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'is_empty' => empty($cart),
            'estimated_preparation_time' => $this->calculateCartPreparationTime($cart)
        ];
    }

    /**
     * Calculate total preparation time for cart items
     */
    private function calculateCartPreparationTime(array $cart): int
    {
        if (empty($cart)) {
            return 0;
        }
        
        $maxTime = 0;
        foreach ($cart as $item) {
            $itemTime = ($item['preparation_time'] ?? 15) * $item['quantity'];
            $maxTime = max($maxTime, $itemTime);
        }
        
        return $maxTime;
    }

    /**
     * Validate cart before checkout
     */
    public function validateCartForCheckout(): array
    {
        $cart = $this->getCart();
        
        $validation = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        if (empty($cart)) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Cart is empty';
            return $validation;
        }
        
        foreach ($cart as $index => $item) {
            $menuItem = MenuItem::with(['menu', 'inventoryItems'])->find($item['menu_item_id']);
            
            if (!$menuItem) {
                $validation['errors'][] = "Item '{$item['name']}' is no longer available";
                continue;
            }
            
            if (!$menuItem->is_available) {
                $validation['errors'][] = "Item '{$item['name']}' is currently unavailable";
                continue;
            }
            
            // Check menu availability
            $menuValidation = $this->validateMenuAvailability($menuItem->menu);
            if (!$menuValidation['is_available']) {
                $validation['errors'][] = "Menu for '{$item['name']}' is {$menuValidation['message']}";
                continue;
            }
            
            // Check stock
            if ($menuItem->requires_inventory_check) {
                $stockCheck = $this->checkItemStock($menuItem, $item['quantity']);
                if (!$stockCheck['available']) {
                    if ($stockCheck['available_quantity'] > 0) {
                        $validation['warnings'][] = "Only {$stockCheck['available_quantity']} of '{$item['name']}' available";
                    } else {
                        $validation['errors'][] = "'{$item['name']}' is out of stock";
                    }
                }
            }
            
            // Check price changes
            if (abs($menuItem->price - $item['price']) > 0.01) {
                $validation['warnings'][] = "Price of '{$item['name']}' has changed from {$item['price']} to {$menuItem->price}";
            }
        }
        
        if (!empty($validation['errors'])) {
            $validation['is_valid'] = false;
        }
        
        return $validation;
    }

    /**
     * Apply cart validation and updates
     */
    public function refreshCartPricesAndAvailability(): array
    {
        $cart = $this->getCart();
        $updatedCart = [];
        $changes = [];
        
        foreach ($cart as $item) {
            $menuItem = MenuItem::with(['menu'])->find($item['menu_item_id']);
            
            if (!$menuItem || !$menuItem->is_available) {
                $changes[] = "Removed '{$item['name']}' - no longer available";
                continue;
            }
            
            $updatedItem = $item;
            
            // Update price if changed
            if (abs($menuItem->price - $item['price']) > 0.01) {
                $updatedItem['price'] = $menuItem->price;
                $updatedItem['total'] = $menuItem->price * $item['quantity'];
                $changes[] = "Updated price for '{$item['name']}'";
            }
            
            // Validate stock and adjust quantity if needed
            if ($menuItem->requires_inventory_check) {
                $stockCheck = $this->checkItemStock($menuItem, $item['quantity']);
                if (!$stockCheck['available'] && $stockCheck['available_quantity'] > 0) {
                    $updatedItem['quantity'] = $stockCheck['available_quantity'];
                    $updatedItem['total'] = $updatedItem['price'] * $stockCheck['available_quantity'];
                    $changes[] = "Reduced quantity for '{$item['name']}' due to stock limitations";
                } elseif (!$stockCheck['available']) {
                    $changes[] = "Removed '{$item['name']}' - out of stock";
                    continue;
                }
            }
            
            $updatedCart[] = $updatedItem;
        }
        
        if (!empty($changes)) {
            $this->saveCart($updatedCart);
        }
        
        return [
            'changes_made' => !empty($changes),
            'changes' => $changes,
            'cart' => $this->getCartWithTotals()
        ];
    }
}
