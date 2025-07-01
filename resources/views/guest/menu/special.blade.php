@extends('layouts.guest')

@section('title', 'Special Menu - ' . $branch->name)

@push('styles')
<style>
.special-item-card {
    transition: all 0.3s ease-in-out;
    position: relative;
}

.special-item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.special-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.quantity-btn {
    transition: all 0.2s ease-in-out;
}

.quantity-btn:not(:disabled):hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.special-add-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    transition: all 0.3s ease-in-out;
}

.special-add-btn:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.special-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}

.notification-toast {
    transform: translateX(100%);
}

.scale-110 {
    transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Special Menu</h1>
                        <p class="text-gray-600 mt-1">{{ $branch->name }} - Limited time offers</p>
                    </div>
                    
                    <a href="{{ route('guest.menu.view', ['branchId' => $branch->id]) }}" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Regular Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Menu Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @forelse($specialMenus as $menu)
            <div class="mb-12">
                <!-- Menu Header -->
                <div class="special-header rounded-lg shadow-lg p-8 mb-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold mb-2">{{ $menu->name }}</h2>
                            @if($menu->description)
                                <p class="text-indigo-100 text-lg">{{ $menu->description }}</p>
                            @endif
                        </div>
                        
                        <div class="text-right">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                                <div class="text-sm text-indigo-100">Available until</div>
                                <div class="text-lg font-semibold">
                                    {{ \Carbon\Carbon::parse($menu->end_date)->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                @php
                    $categorizedItems = $menu->menuItems->groupBy('category.name');
                @endphp

                @foreach($categorizedItems as $categoryName => $items)
                    <div class="mb-10">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-sm font-medium mr-3">
                                {{ $categoryName }}
                            </span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($items as $item)
                                <div class="special-item-card bg-white rounded-lg shadow-sm overflow-hidden border-2 border-yellow-200">
                                    <!-- Special Badge -->
                                    <div class="relative">
                                        <div class="bg-yellow-100 h-48 flex items-center justify-center">
                                            <i class="fas fa-star text-yellow-500 text-4xl"></i>
                                        </div>
                                        <div class="special-badge absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                            SPECIAL
                                        </div>
                                    </div>
                                    
                                    <div class="p-6">
                                        <!-- Item Header -->
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $item->name }}</h4>
                                            <div class="text-right">
                                                @if($item->original_price && $item->original_price > $item->price)
                                                    <div class="text-sm text-gray-500 line-through">
                                                        ${{ number_format($item->original_price, 2) }}
                                                    </div>
                                                @endif
                                                <span class="text-lg font-bold text-red-600">${{ number_format($item->price, 2) }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        @if($item->description)
                                            <p class="text-gray-600 text-sm mb-4">{{ $item->description }}</p>
                                        @endif
                                        
                                        <!-- Special Offer Info -->
                                        @if($item->special_offer_text)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                                <div class="flex items-center">
                                                    <i class="fas fa-tags text-yellow-600 mr-2"></i>
                                                    <span class="text-sm font-medium text-yellow-800">{{ $item->special_offer_text }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Dietary Info -->
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @if($item->is_vegetarian)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-leaf mr-1"></i>Vegetarian
                                                </span>
                                            @endif
                                            @if($item->is_vegan)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-seedling mr-1"></i>Vegan
                                                </span>
                                            @endif
                                            @if($item->is_spicy)
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
                                                            onclick="changeQuantity({{ $item->id }}, -1)">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <span id="qty-{{ $item->id }}" class="font-semibold text-gray-900 min-w-[20px] text-center">1</span>
                                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                                            onclick="changeQuantity({{ $item->id }}, 1)">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                                
                                                <button onclick="addToCart({{ $item->id }})" 
                                                        class="special-add-btn text-white px-4 py-2 rounded-lg flex items-center font-medium"
                                                        {{ !$item->is_available ? 'disabled' : '' }}>
                                                    <i class="fas fa-star mr-2"></i>
                                                    {{ $item->is_available ? 'Add Special' : 'Unavailable' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="text-gray-400 text-6xl mb-6">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Special Menu Available</h3>
                <p class="text-gray-600 text-lg max-w-md mx-auto mb-8">
                    We don't have any special offers available at {{ $branch->name }} right now. Check back soon for exciting new dishes!
                </p>
                
                <div class="space-y-4">
                    <a href="{{ route('guest.menu.view', ['branch' => $branch->id]) }}" 
                       class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-utensils mr-2"></i>
                        View Regular Menu
                    </a>
                    
                    <div class="text-sm text-gray-500">
                        or call us at <a href="tel:{{ $branch->phone }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ $branch->phone }}</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
let quantities = {};
let isUpdatingQuantity = false;

// Initialize quantities and button states
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌟 Initializing special menu view...');
    
    document.querySelectorAll('[id^="qty-"]').forEach(function(element) {
        const itemId = element.id.replace('qty-', '');
        quantities[itemId] = 1;
        
        // Initialize button states for each item
        const itemContainer = element.closest('.bg-white');
        const decreaseBtn = itemContainer?.querySelector('button[onclick*="changeQuantity(' + itemId + ', -1)"]');
        const increaseBtn = itemContainer?.querySelector('button[onclick*="changeQuantity(' + itemId + ', 1)"]');
        
        updateButtonStates(itemId, 1, decreaseBtn, increaseBtn);
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
    }
    
    if (increaseBtn) {
        const isDisabled = quantity >= maxQty;
        increaseBtn.disabled = isDisabled;
        increaseBtn.classList.toggle('opacity-50', isDisabled);
        increaseBtn.classList.toggle('cursor-not-allowed', isDisabled);
    }
}

/**
 * Change quantity with improved validation and visual feedback
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
        
        // Add visual feedback
        qtyElement.classList.add('scale-110');
        setTimeout(() => {
            qtyElement.classList.remove('scale-110');
        }, 150);
    }
    
    // Update button states
    const itemContainer = qtyElement?.closest('.bg-white');
    if (itemContainer) {
        const decreaseBtn = itemContainer.querySelector('button[onclick*="changeQuantity(' + itemId + ', -1)"]');
        const increaseBtn = itemContainer.querySelector('button[onclick*="changeQuantity(' + itemId + ', 1)"]');
        updateButtonStates(itemId, newQty, decreaseBtn, increaseBtn);
    }
}

/**
 * Add special item to cart with enhanced feedback
 */
function addToCart(itemId) {
    if (isUpdatingQuantity) return;
    
    const quantity = quantities[itemId] || 1;
    const button = document.querySelector(`button[onclick="addToCart(${itemId})"]`);
    
    // Disable button during request
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Special...';
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
            showNotification(`${quantity} special item(s) added to cart!`, 'success');
            
            // Reset quantity to 1 after adding
            quantities[itemId] = 1;
            const qtyElement = document.getElementById(`qty-${itemId}`);
            if (qtyElement) {
                qtyElement.textContent = 1;
                
                // Update button states
                const itemContainer = qtyElement.closest('.bg-white');
                const decreaseBtn = itemContainer?.querySelector('button[onclick*="changeQuantity(' + itemId + ', -1)"]');
                const increaseBtn = itemContainer?.querySelector('button[onclick*="changeQuantity(' + itemId + ', 1)"]');
                updateButtonStates(itemId, 1, decreaseBtn, increaseBtn);
            }
        } else {
            throw new Error(data.message || 'Failed to add special item to cart');
        }
    })
    .catch(error => {
        console.error('Error adding special item to cart:', error);
        showNotification(error.message || 'Failed to add special item to cart', 'error');
    })
    .finally(() => {
        // Re-enable button
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-star mr-2"></i>Add Special';
        }
        isUpdatingQuantity = false;
    });
}

/**
 * Show enhanced notification with special styling
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
        type === 'success' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white' : 
        type === 'error' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white' : 
        'bg-gradient-to-r from-blue-500 to-blue-600 text-white'
    }`;
    
    const icon = type === 'success' ? 'check-circle' : 
                type === 'error' ? 'exclamation-triangle' : 'info-circle';
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${icon} mr-2"></i>
            <span>${message}</span>
            ${type === 'success' ? '<i class="fas fa-star ml-2 text-yellow-200"></i>' : ''}
        </div>
    `;
    
    // Add with animation
    notification.style.transform = 'translateX(100%)';
    document.body.appendChild(notification);
    
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });
    
    // Auto remove with animation
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Prevent double-clicks during updates
document.addEventListener('click', function(e) {
    if (isUpdatingQuantity && e.target.closest('button')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endpush
