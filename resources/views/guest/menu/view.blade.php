@extends('layouts.guest')

@section('title', 'Menu - ' . $branch->name)

@push('styles')
<style>
.quantity-btn {
    transition: all 0.2s ease-in-out;
}

.quantity-btn:not(:disabled):hover {
    transform: scale(1.05);
}

.quantity-btn:active {
    transform: scale(0.95);
}

#qty-[id] {
    transition: transform 0.15s ease-in-out;
}

.notification-toast {
    transform: translateX(100%);
}

.scale-110 {
    transform: scale(1.1);
}

.cart-button-loading {
    background: linear-gradient(90deg, #4f46e5 0%, #6366f1 50%, #4f46e5 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.item-card-hover {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.item-card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $branch->name }}</h1>
                    <p class="text-sm text-gray-600">Menu for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Branch Status -->
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $isOpen ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $isOpen ? 'Open Now' : 'Closed' }}
                    </span>
                    
                    <!-- Cart Button -->
                    <button id="cartToggle" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Cart (<span id="cartCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Menu Categories Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Categories</h2>
                    <nav class="space-y-2">
                        @foreach($menuItems as $categoryName => $items)
                            <a href="#category-{{ Str::slug($categoryName) }}" 
                               class="block px-3 py-2 rounded-lg text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                {{ $categoryName }}
                                <span class="text-xs text-gray-400 ml-2">({{ count($items) }})</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="lg:col-span-3">
                @forelse($menuItems as $categoryName => $items)
                    <div id="category-{{ Str::slug($categoryName) }}" class="mb-12">
                        <!-- Category Header -->
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $categoryName }}</h2>
                            <div class="w-16 h-1 bg-indigo-600 mt-2"></div>
                        </div>

                        <!-- Items Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($items as $item)
                                <div class="bg-white rounded-lg shadow-sm item-card-hover overflow-hidden">
                                    <!-- Item Image Placeholder -->
                                    <div class="bg-gray-100 h-48 flex items-center justify-center">
                                        <i class="fas fa-utensils text-gray-400 text-3xl"></i>
                                    </div>
                                    
                                    <div class="p-6">
                                        <!-- Item Header -->
                                        <div class="flex justify-between items-start mb-3">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                            <span class="text-lg font-bold text-indigo-600">${{ number_format($item['price'], 2) }}</span>
                                        </div>
                                        
                                        <!-- Description -->
                                        @if(!empty($item['description']))
                                            <p class="text-gray-600 text-sm mb-4">{{ $item['description'] }}</p>
                                        @endif
                                        
                                        <!-- Dietary Info -->
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @if($item['is_vegetarian'] ?? false)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-leaf mr-1"></i>Vegetarian
                                                </span>
                                            @endif
                                            @if($item['is_vegan'] ?? false)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-seedling mr-1"></i>Vegan
                                                </span>
                                            @endif
                                            @if($item['is_spicy'] ?? false)
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    <i class="fas fa-fire mr-1"></i>Spicy
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Add to Cart Section -->
                                        <div class="border-t border-gray-200 pt-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200"
                                                            onclick="changeQuantity({{ $item['id'] }}, -1)">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <span id="qty-{{ $item['id'] }}" class="font-semibold text-gray-900 min-w-[20px] text-center">1</span>
                                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200"
                                                            onclick="changeQuantity({{ $item['id'] }}, 1)">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                                
                                                <button onclick="addToCart({{ $item['id'] }})" 
                                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition-all duration-200"
                                                        {{ !$item['is_available'] ? 'disabled' : '' }}>
                                                    <i class="fas fa-plus mr-2"></i>
                                                    {{ $item['is_available'] ? 'Add to Cart' : 'Unavailable' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-5xl mb-4">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No Menu Items Available</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            We're sorry, but there are no menu items available for this date.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 z-50">
    <div class="h-full flex flex-col">
        <!-- Cart Header -->
        <div class="bg-indigo-600 text-white p-6">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">Your Cart</h2>
                <button id="closeCart" class="text-white hover:text-indigo-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Cart Items -->
        <div id="cartItems" class="flex-1 overflow-y-auto p-6">
            <!-- Cart items will be loaded here -->
        </div>
        
        <!-- Cart Footer -->
        <div id="cartFooter" class="border-t border-gray-200 p-6">
            <!-- Cart total and checkout button will be here -->
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="cartOverlay" class="fixed inset-0 bg-black/50 z-40 hidden"></div>
@endsection

@push('scripts')
<script>
let quantities = {};
let isUpdatingQuantity = false;

// Initialize quantities and button states
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing guest menu view...');
    
    // Initialize all quantities to 1 and set proper button states
    document.querySelectorAll('[id^="qty-"]').forEach(function(element) {
        const itemId = element.id.replace('qty-', '');
        quantities[itemId] = 1;
        
        // Initialize button states
        const itemContainer = element.closest('.bg-white');
        const decreaseBtn = itemContainer.querySelector('.quantity-btn:first-of-type');
        const increaseBtn = itemContainer.querySelector('.quantity-btn:last-of-type');
        
        updateButtonStates(itemId, 1, decreaseBtn, increaseBtn);
    });
    
    // Load cart count
    updateCartCount();
    
    // Add click event listeners for better error handling
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quantity-btn')) {
            if (isUpdatingQuantity) {
                e.preventDefault();
                return false;
            }
        }
    });
});

/**
 * Update button states based on quantity
 */
function updateButtonStates(itemId, quantity, decreaseBtn, increaseBtn) {
    const minQty = 1;
    const maxQty = 10;
    
    if (decreaseBtn) {
        const isDisabled = quantity <= minQty;
        decreaseBtn.disabled = isDisabled;
        decreaseBtn.classList.toggle('opacity-50', isDisabled);
        decreaseBtn.classList.toggle('cursor-not-allowed', isDisabled);
        decreaseBtn.classList.toggle('hover:bg-gray-300', !isDisabled);
    }
    
    if (increaseBtn) {
        const isDisabled = quantity >= maxQty;
        increaseBtn.disabled = isDisabled;
        increaseBtn.classList.toggle('opacity-50', isDisabled);
        increaseBtn.classList.toggle('cursor-not-allowed', isDisabled);
        increaseBtn.classList.toggle('hover:bg-gray-300', !isDisabled);
    }
}

/**
 * Change quantity with improved validation and feedback
 */
function changeQuantity(itemId, change) {
    if (isUpdatingQuantity) return;
    
    const currentQty = quantities[itemId] || 1;
    const newQty = Math.max(1, Math.min(10, currentQty + change));
    
    // No change needed
    if (newQty === currentQty) return;
    
    quantities[itemId] = newQty;
    
    // Update display
    const qtyElement = document.getElementById(`qty-${itemId}`);
    if (qtyElement) {
        qtyElement.textContent = newQty;
    }
    
    // Update button states
    const itemContainer = qtyElement?.closest('.bg-white');
    if (itemContainer) {
        const decreaseBtn = itemContainer.querySelector('.quantity-btn:first-of-type');
        const increaseBtn = itemContainer.querySelector('.quantity-btn:last-of-type');
        updateButtonStates(itemId, newQty, decreaseBtn, increaseBtn);
    }
    
    // Visual feedback
    if (qtyElement) {
        qtyElement.classList.add('scale-110');
        setTimeout(() => {
            qtyElement.classList.remove('scale-110');
        }, 150);
    }
}

/**
 * Add item to cart with improved error handling and feedback
 */
function addToCart(itemId) {
    if (isUpdatingQuantity) return;
    
    const quantity = quantities[itemId] || 1;
    const button = document.querySelector(`button[onclick="addToCart(${itemId})"]`);
    
    // Disable button during request
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
    }
    
    isUpdatingQuantity = true;
    
    fetch('{{ route("guest.cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            menu_item_id: itemId,
            quantity: quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification(`${quantity} item(s) added to cart!`, 'success');
            
            // Reset quantity to 1 after adding
            quantities[itemId] = 1;
            const qtyElement = document.getElementById(`qty-${itemId}`);
            if (qtyElement) {
                qtyElement.textContent = 1;
                
                // Update button states
                const itemContainer = qtyElement.closest('.bg-white');
                const decreaseBtn = itemContainer?.querySelector('.quantity-btn:first-of-type');
                const increaseBtn = itemContainer?.querySelector('.quantity-btn:last-of-type');
                updateButtonStates(itemId, 1, decreaseBtn, increaseBtn);
            }
        } else {
            throw new Error(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showNotification(error.message || 'Failed to add item to cart', 'error');
    })
    .finally(() => {
        // Re-enable button
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-plus mr-2"></i>Add to Cart';
        }
        isUpdatingQuantity = false;
    });
}

/**
 * Update cart count with error handling
 */
function updateCartCount() {
    fetch('{{ route("guest.session.info") }}')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count || 0;
                
                // Add animation for count changes
                cartCountElement.classList.add('scale-110');
                setTimeout(() => {
                    cartCountElement.classList.remove('scale-110');
                }, 200);
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
            // Silently fail for cart count updates
        });
}

/**
 * Show notification with improved styling and positioning
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(n => n.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    
    const icon = type === 'success' ? 'check-circle' : 
                type === 'error' ? 'exclamation-triangle' : 'info-circle';
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${icon} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page with animation
    notification.style.transform = 'translateX(100%)';
    document.body.appendChild(notification);
    
    // Animate in
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });
    
    // Auto remove after 4 seconds with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}

/**
 * Cart sidebar functionality with improved error handling
 */
document.getElementById('cartToggle').addEventListener('click', function() {
    document.getElementById('cartSidebar').classList.remove('translate-x-full');
    document.getElementById('cartOverlay').classList.remove('hidden');
    loadCartItems();
});

document.getElementById('closeCart').addEventListener('click', closeCart);
document.getElementById('cartOverlay').addEventListener('click', closeCart);

function closeCart() {
    document.getElementById('cartSidebar').classList.add('translate-x-full');
    document.getElementById('cartOverlay').classList.add('hidden');
}

/**
 * Load cart items with improved error handling and loading states
 */
function loadCartItems() {
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    
    // Show loading state
    cartItems.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-3"></i>
            <p class="text-gray-500">Loading cart...</p>
        </div>
    `;
    
    fetch('{{ route("guest.cart.view") }}', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Build cart items HTML
        let cartHTML = '';
        let cartFooterHTML = '';
        
        if (data.cart && data.cart.length > 0) {
            data.cart.forEach(item => {
                cartHTML += `
                    <div class="flex items-center space-x-4 p-4 border-b border-gray-200 hover:bg-gray-50 transition-colors">
                        <div class="bg-gray-100 rounded-lg w-12 h-12 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-utensils text-gray-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 truncate">${item.name}</h4>
                            <p class="text-sm text-gray-600">$${parseFloat(item.price).toFixed(2)} × ${item.quantity}</p>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">$${parseFloat(item.total).toFixed(2)}</div>
                        </div>
                    </div>
                `;
            });
            
            const subtotal = data.cartSummary ? parseFloat(data.cartSummary.subtotal) : 0;
            const tax = subtotal * 0.10;
            const total = subtotal + tax;
            
            cartFooterHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tax (10%)</span>
                        <span>$${tax.toFixed(2)}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between font-semibold text-gray-900">
                            <span>Total</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                    </div>
                    <a href="{{ route('guest.cart.view') }}" 
                       class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium text-center block transition-colors">
                        View Full Cart
                    </a>
                </div>
            `;
        } else {
            cartHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">Your cart is empty</p>
                    <p class="text-sm text-gray-400 mt-1">Add some delicious items!</p>
                </div>
            `;
            cartFooterHTML = `
                <button onclick="closeCart()" 
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-3 rounded-lg font-medium transition-colors">
                    Continue Shopping
                </button>
            `;
        }
        
        cartItems.innerHTML = cartHTML;
        cartFooter.innerHTML = cartFooterHTML;
    })
    .catch(error => {
        console.error('Error loading cart:', error);
        cartItems.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-3"></i>
                <p class="text-red-500 font-medium">Failed to load cart</p>
                <p class="text-sm text-gray-500 mt-1">${error.message}</p>
                <button onclick="loadCartItems()" 
                        class="mt-3 text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                    Try Again
                </button>
            </div>
        `;
        cartFooter.innerHTML = '';
    });
}

// Smooth scrolling for category navigation
document.querySelectorAll('a[href^="#category-"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
@endpush
