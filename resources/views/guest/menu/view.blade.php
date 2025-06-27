@extends('layouts.guest')

@section('title', 'Menu - ' . $branch->name)

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
                                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
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
                                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                                            onclick="changeQuantity({{ $item['id'] }}, -1)">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <span id="qty-{{ $item['id'] }}" class="font-semibold text-gray-900 min-w-[20px] text-center">1</span>
                                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                                            onclick="changeQuantity({{ $item['id'] }}, 1)">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                                
                                                <button onclick="addToCart({{ $item['id'] }})" 
                                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center"
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

// Initialize quantities
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all quantities to 1
    document.querySelectorAll('[id^="qty-"]').forEach(function(element) {
        const itemId = element.id.replace('qty-', '');
        quantities[itemId] = 1;
    });
    
    // Load cart count
    updateCartCount();
});

function changeQuantity(itemId, change) {
    const currentQty = quantities[itemId] || 1;
    const newQty = Math.max(1, Math.min(10, currentQty + change));
    quantities[itemId] = newQty;
    document.getElementById(`qty-${itemId}`).textContent = newQty;
}

function addToCart(itemId) {
    const quantity = quantities[itemId] || 1;
    
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Item added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add item to cart', 'error');
    });
}

function updateCartCount() {
    fetch('{{ route("guest.session.info") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cartCount').textContent = data.cart_count || 0;
        })
        .catch(error => console.error('Error updating cart count:', error));
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Cart sidebar functionality
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

function loadCartItems() {
    fetch('{{ route("guest.cart.view") }}')
        .then(response => response.text())
        .then(html => {
            // Parse and display cart items
            document.getElementById('cartItems').innerHTML = '<p class="text-gray-500">Loading...</p>';
        })
        .catch(error => {
            document.getElementById('cartItems').innerHTML = '<p class="text-red-500">Failed to load cart items</p>';
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
