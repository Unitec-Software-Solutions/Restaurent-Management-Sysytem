<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Models\MenuItem;

class CartService
{
    private const CART_SESSION_KEY = 'guest_cart';

    /**
     * Get cart item count
     */
    public function getCartCount(): int
    {
        $cart = $this->getCartItems();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Get cart summary
     */
    public function getCartSummary(): array
    {
        $cart = $this->getCartItems();
        $total = 0;
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
            $total += $item['quantity'] * $item['price'];
        }

        return [
            'count' => $count,
            'total' => $total,
            'items' => count($cart)
        ];
    }

    /**
     * Get all cart items
     */
    public function getCartItems(): array
    {
        return Session::get(self::CART_SESSION_KEY, []);
    }

    /**
     * Add item to cart
     */
    public function addToCart(array $cartItem): void
    {
        $cart = $this->getCartItems();
        $menuItemId = $cartItem['menu_item_id'];

        if (isset($cart[$menuItemId])) {
            $cart[$menuItemId]['quantity'] += $cartItem['quantity'];
        } else {
            $menuItem = MenuItem::findOrFail($menuItemId);
            $cart[$menuItemId] = [
                'menu_item_id' => $menuItemId,
                'name' => $menuItem->name,
                'price' => $menuItem->price,
                'quantity' => $cartItem['quantity'],
                'special_instructions' => $cartItem['special_instructions'] ?? null
            ];
        }

        Session::put(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Update cart item
     */
    public function updateCartItem(int $menuItemId, array $updates): void
    {
        $cart = $this->getCartItems();
        
        if (isset($cart[$menuItemId])) {
            $cart[$menuItemId]['quantity'] = $updates['quantity'];
            $cart[$menuItemId]['special_instructions'] = $updates['special_instructions'] ?? null;
            Session::put(self::CART_SESSION_KEY, $cart);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $menuItemId): void
    {
        $cart = $this->getCartItems();
        unset($cart[$menuItemId]);
        Session::put(self::CART_SESSION_KEY, $cart);
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        Session::forget(self::CART_SESSION_KEY);
    }

    /**
     * Get cart total amount
     */
    public function getCartTotal(): float
    {
        $cart = $this->getCartItems();
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['quantity'] * $item['price'];
        }

        return $total;
    }
}
