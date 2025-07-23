@extends('layouts.admin')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">
            <!-- Left: Order Form -->
            <div class="lg:flex-[7_7_0%]">
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl">
                    <!-- Header -->
                    <div class="px-6 py-5 bg-white border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-utensils mr-3 text-indigo-600 bg-indigo-100 p-2 rounded-lg"></i>
                                    <span>Place Order</span>
                                </h1>
                                @if(isset($reservation))
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-user mr-2 w-5"></i>
                                        <span class="font-medium">Customer:</span>
                                        <span class="ml-1">{{ $reservation->name }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-phone mr-2 w-5"></i>
                                        <span class="font-medium">Phone:</span>
                                        <span class="ml-1">{{ $reservation->phone }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="far fa-calendar-alt mr-2 w-5"></i>
                                        <span class="font-medium">Date:</span>
                                        <span class="ml-1">{{ $reservation->date }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="far fa-clock mr-2 w-5"></i>
                                        <span class="font-medium">Time:</span>
                                        <span class="ml-1">{{ $reservation->start_time }} - {{ $reservation->end_time }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="bg-indigo-100 rounded-xl p-3 text-center">
                                <div class="text-indigo-600 text-sm font-semibold">
                                    @if(isset($reservation))
                                        Reservation #
                                    @else
                                        New Order
                                    @endif
                                </div>
                                <div class="text-indigo-800 font-bold text-2xl">
                                    {{ $reservation->id ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Stock Alert Banner -->
                    <div id="stock-alerts" class="hidden">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700" id="stock-alert-message">
                                        Some items in your cart may have limited availability.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.orders.store') }}" id="order-form">
                            @csrf
                            <input type="hidden" name="reservation_id" value="{{ $reservation->id ?? '' }}">
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            <input type="hidden" name="order_type" value="{{ $orderType ?? 'dine_in' }}">

                            <!-- Customer Info (for takeaway/delivery) -->
                            @if(!isset($reservation))
                            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                                        <input type="text" name="customer_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input type="tel" name="customer_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Menu Search and Categories -->
                            <div class="mb-6">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-list-ul mr-2 text-indigo-500 bg-indigo-100 p-2 rounded-lg"></i>
                                        Menu Items
                                        <span id="availability-summary" class="ml-4 text-sm text-gray-500"></span>
                                    </h2>
                                    <div class="flex gap-3">
                                        <div class="relative flex-1 min-w-[200px]">
                                            <input type="text" id="menu-search" placeholder="Search menu items..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        </div>
                                        <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <select id="availability-filter" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="">All Items</option>
                                            <option value="available">Available Only</option>
                                            <option value="low_stock">Low Stock</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Menu Items Grid with Real-time Stock Indicators -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="menu-items-container">
                                    @foreach($menuItems as $item)
                                    <div class="menu-item-card border rounded-lg p-4 transition-all duration-300 hover:shadow-md"
                                         data-item-id="{{ $item->id }}"
                                         data-category="{{ $item->menuCategory->name ?? 'Uncategorized' }}"
                                         data-name="{{ $item->name }}"
                                         data-type="{{ $item->type_name ?? 'KOT' }}"
                                         data-availability="{{ $item->availability_info['status'] ?? 'available' }}"
                                         data-stock="{{ $item->current_stock ?? 0 }}">

                                        <!-- Item Header -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900">{{ $item->name }}</h3>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <p class="text-sm text-gray-600">{{ $item->menuCategory->name ?? 'Uncategorized' }}</p>
                                                    <!-- Item Type Badge -->
                                                    @if(($item->type ?? 2) === App\Models\MenuItem::TYPE_BUY_SELL)
                                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                            <i class="fas fa-boxes mr-1"></i>Buy & Sell
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                                                            <i class="fas fa-fire mr-1"></i>KOT
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($item->description)
                                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($item->description, 50) }}</p>
                                                @endif
                                            </div>
                                            <div class="ml-3 text-right">
                                                <div class="font-bold text-lg text-gray-900">LKR {{ number_format($item->price, 2) }}</div>
                                                <!-- Stock/Availability Indicator -->
                                                <div class="stock-indicator mt-1" data-item-id="{{ $item->id }}">
                                                    @if(($item->type ?? 2) === App\Models\MenuItem::TYPE_BUY_SELL)
                                                        @if($item->current_stock > 0)
                                                            <span class="text-xs {{ $item->current_stock < 5 ? 'text-orange-600' : 'text-green-600' }}">
                                                                <i class="fas fa-box mr-1"></i>Stock: {{ $item->current_stock }}
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-red-600">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i>Out of Stock
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-xs text-green-600">
                                                            <i class="fas fa-check-circle mr-1"></i>Available
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stock Progress Bar -->
                                        <div class="mb-3">
                                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                                <span>Stock Level</span>
                                                <span class="stock-percentage" data-item-id="{{ $item->id }}">{{ $item->stock_percentage }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="stock-bar h-2 rounded-full transition-all duration-300"
                                                     data-item-id="{{ $item->id }}"
                                                     style="width: {{ $item->stock_percentage }}%;
                                                            background-color: {{ $item->stock_percentage > 50 ? '#10B981' : ($item->stock_percentage > 20 ? '#F59E0B' : '#EF4444') }};">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add to Cart Controls -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       class="item-check mr-3"
                                                       data-item-id="{{ $item->id }}"
                                                       data-max-quantity="{{ $item->stock_percentage > 0 ? 999 : 0 }}"
                                                       id="item_{{ $item->id }}"
                                                       name="items[{{ $item->id }}][menu_item_id]"
                                                       value="{{ $item->id }}"
                                                       {{ $item->availability_status === 'out_of_stock' ? 'disabled' : '' }}>

                                                @if($item->availability_status !== 'out_of_stock')
                                                    <div class="flex items-center quantity-controls" style="display: none;">
                                                        <button type="button" class="qty-decrease w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center justify-center" data-item-id="{{ $item->id }}">
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                        <input type="number"
                                                               min="1"
                                                               value="1"
                                                               class="item-qty w-16 text-center border-x border-gray-300 text-sm py-1"
                                                               data-item-id="{{ $item->id }}"
                                                               name="items[{{ $item->id }}][quantity]">
                                                        <button type="button" class="qty-increase w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center justify-center" data-item-id="{{ $item->id }}">
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800 alternatives-btn" data-item-id="{{ $item->id }}">
                                                        View Alternatives
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Preparation Info -->
                                            @if($item->requires_preparation)
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <i class="far fa-clock mr-1"></i>
                                                    {{ $item->preparation_time }}min
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Dietary & Allergen Info -->
                                        @if($item->is_vegetarian || $item->contains_alcohol || $item->allergens)
                                        <div class="mt-3 flex flex-wrap gap-1">
                                            @if($item->is_vegetarian)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-leaf mr-1"></i>Vegetarian
                                                </span>
                                            @endif
                                            @if($item->contains_alcohol)
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-wine-glass mr-1"></i>Contains Alcohol
                                                </span>
                                            @endif
                                            @if($item->allergens)
                                                <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800"
                                                      title="Allergens: {{ implode(', ', $item->allergens) }}">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>Allergens
                                                </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Any special requests or notes for this order..."></textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between items-center pt-6 border-t">
                                <a href="{{ url()->previous() }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg flex items-center">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </a>

                                <div class="flex gap-3">
                                    <button type="button" id="validate-cart-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Validate Cart
                                    </button>
                                    <button type="submit" id="place-order-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center" disabled>
                                        <i class="fas fa-shopping-cart mr-2"></i>
                                        Place Order
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary & Cart -->
            <div class="lg:flex-[3_3_0%]">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden sticky top-8">
                    <!-- Cart Header -->
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shopping-cart mr-2 text-indigo-600"></i>
                            Order Summary
                            <span id="cart-count" class="ml-2 bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-sm">0</span>
                        </h3>
                    </div>

                    <!-- Cart Items -->
                    <div id="cart-items" class="max-h-96 overflow-y-auto">
                        <div id="empty-cart" class="p-8 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-4 text-gray-300"></i>
                            <p>Your cart is empty</p>
                            <p class="text-sm">Add items from the menu to get started</p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div id="cart-summary" class="px-6 py-4 border-t bg-gray-50" style="display: none;">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span id="cart-subtotal">LKR 0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Tax (10%):</span>
                                <span id="cart-tax">LKR 0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span id="cart-total">LKR 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Summary Widget -->
                <div class="mt-6 bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                            Stock Status
                        </h3>
                    </div>
                    <div class="p-6" id="stock-summary">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Available Items:</span>
                                <span id="available-count" class="font-semibold text-green-600">{{ $stockSummary['available_count'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Low Stock:</span>
                                <span id="low-stock-count" class="font-semibold text-yellow-600">{{ $stockSummary['low_stock_count'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Out of Stock:</span>
                                <span id="out-stock-count" class="font-semibold text-red-600">{{ $stockSummary['out_of_stock_count'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alternatives Modal -->
<div id="alternatives-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl m-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Similar Items Available</h3>
            <button id="close-alternatives" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="alternatives-content">
            <!-- Alternatives will be loaded here -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/enhanced-order.js') }}"></script>
@endpush

@push('styles')
<style>
.menu-item-card.out-of-stock {
    opacity: 0.6;
    background-color: #f9fafb;
}

.menu-item-card.low-stock {
    border-color: #f59e0b;
    background-color: #fffbeb;
}

.menu-item-card.selected {
    border-color: #6366f1;
    background-color: #eef2ff;
}

.stock-indicator {
    transition: all 0.3s ease;
}

#stock-alerts {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
