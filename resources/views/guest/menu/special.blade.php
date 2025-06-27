@extends('layouts.guest')

@section('title', 'Special Menu - ' . $branch->name)

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
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-8 mb-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold mb-2">{{ $menu->name }}</h2>
                            @if($menu->description)
                                <p class="text-indigo-100 text-lg">{{ $menu->description }}</p>
                            @endif
                        </div>
                        
                        <div class="text-right">
                            <div class="bg-white/20 rounded-lg p-4">
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
                                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border-2 border-yellow-200">
                                    <!-- Special Badge -->
                                    <div class="relative">
                                        <div class="bg-yellow-100 h-48 flex items-center justify-center">
                                            <i class="fas fa-star text-yellow-500 text-4xl"></i>
                                        </div>
                                        <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">
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
                                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                                            onclick="changeQuantity({{ $item->id }}, -1)">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <span id="qty-{{ $item->id }}" class="font-semibold text-gray-900 min-w-[20px] text-center">1</span>
                                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 w-8 h-8 rounded-full flex items-center justify-center"
                                                            onclick="changeQuantity({{ $item->id }}, 1)">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                                
                                                <button onclick="addToCart({{ $item->id }})" 
                                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center font-medium"
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

// Initialize quantities
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="qty-"]').forEach(function(element) {
        const itemId = element.id.replace('qty-', '');
        quantities[itemId] = 1;
    });
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
            showNotification('Special item added to cart!', 'success');
        } else {
            showNotification(data.message || 'Failed to add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add item to cart', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
