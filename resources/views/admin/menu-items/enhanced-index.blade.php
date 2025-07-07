@extends('layouts.admin')

@section('title', 'Menu Items Management')

@section('content')
<div class="p-6">
    <!-- Breadcrumb Navigation -->
    <x-breadcrumb 
        :items="[['name' => 'Menu Items', 'url' => route('admin.menu-items.enhanced.index')]]"
        current="All Menu Items"
        type="menu-items" />

    <!-- Header with Enhanced Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Menu Items Management</h1>
                <p class="text-gray-600 mb-3">Manage all your menu items: Buy & Sell items (from inventory) + KOT recipes (dishes)</p>
                
                <!-- Quick Stats -->
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">{{ $menuItems->where('type', App\Models\MenuItem::TYPE_BUY_SELL)->count() }} Buy & Sell Items</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">{{ $menuItems->where('type', App\Models\MenuItem::TYPE_KOT)->count() }} KOT Recipes</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">{{ $menuItems->where('is_available', true)->count() }} Available</span>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-items.create') }}" 
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add Menu Item
                    <span class="ml-2 text-xs bg-indigo-500 px-2 py-0.5 rounded-full">Single</span>
                </a>
                <a href="{{ route('admin.menu-items.create-kot') }}" 
                   class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center">
                    <i class="fas fa-warehouse mr-2"></i>Bulk Add from Inventory
                    <span class="ml-2 text-xs bg-orange-500 px-2 py-0.5 rounded-full">Bulk</span>
                </a>
                <a href="{{ route('admin.menu-categories.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
                    <i class="fas fa-tags mr-2"></i>Categories
                </a>
            </div>
        </div>
    </div>

    <!-- System Explanation -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-base font-semibold text-blue-900 mb-3">What are Menu Items?</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-blue-800">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3 mt-0.5">
                            <i class="fas fa-utensils text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium mb-1">Individual Food & Drink Items</h4>
                            <p class="text-blue-700">Menu Items are the individual products customers can order - each dish, drink, or product that appears on your menu.</p>
                            <p class="text-blue-600 text-xs mt-1 italic">Examples: Chicken Curry, Caesar Salad, Coca Cola, Coffee</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3 mt-0.5">
                            <i class="fas fa-book-open text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium mb-1">Different from "Menus"</h4>
                            <p class="text-blue-700">Menus are collections of menu items for specific times/contexts (like "Lunch Menu 12PM-3PM").</p>
                            <p class="text-orange-600 text-xs mt-1 italic">Create items here, then group them into menus in Menu Builder</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                    <p class="text-blue-800 text-sm"><strong>Two Types:</strong> 🍳 <strong>KOT Items</strong> (kitchen-prepared dishes) and 📦 <strong>Buy & Sell Items</strong> (sold directly from inventory). Both can be ordered by customers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters with Type Classification -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.menu-items.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, description..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Enhanced Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Types</option>
                        <option value="{{ App\Models\MenuItem::TYPE_BUY_SELL }}" {{ request('type') == App\Models\MenuItem::TYPE_BUY_SELL ? 'selected' : '' }}>
                            📦 Buy & Sell (Inventory)
                        </option>
                        <option value="{{ App\Models\MenuItem::TYPE_KOT }}" {{ request('type') == App\Models\MenuItem::TYPE_KOT ? 'selected' : '' }}>
                            🍳 KOT Recipes (Dishes)
                        </option>
                    </select>
                </div>
                            <i class="fas fa-fire mr-1"></i>KOT (Kitchen Order)
                        </option>
                    </select>
                </div>

                <!-- Availability Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                    <select name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Items</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available Only</option>
                        <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        <option value="requires_preparation" {{ request('availability') == 'requires_preparation' ? 'selected' : '' }}>Requires Preparation</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <a href="{{ route('admin.menu-items.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-utensils text-indigo-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Menu Items</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $menuItems->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Buy & Sell Items</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $menuItems->where('type', App\Models\MenuItem::TYPE_BUY_SELL)->count() }}
                    </p>
                    <p class="text-xs text-blue-600">From Item Master (inventory)</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-utensils text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">KOT Recipes</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $menuItems->where('type', App\Models\MenuItem::TYPE_KOT)->count() }}
                    </p>
                    <p class="text-xs text-orange-600">Dishes made from ingredients</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Available Items</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $menuItems->where('is_available', true)->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Menu Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($menuItems as $item)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                <!-- Item Image/Header -->
                <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                             alt="{{ $item->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-utensils text-4xl text-gray-400"></i>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    <div class="absolute top-3 left-3">
                        @if($item->is_available)
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Available
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Unavailable
                            </span>
                        @endif
                    </div>

                    <!-- Type Badge -->
                    <div class="absolute top-3 right-3">
                        @if($item->type == App\Models\MenuItem::TYPE_BUY_SELL)
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                📦 Inventory
                            </span>
                        @else
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full font-medium">
                                🍳 Recipe
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Item Details -->
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-900 mb-1">{{ $item->name }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">
                                    {{ $item->menuCategory->name ?? 'Uncategorized' }}
                                </span>
                                @if($item->requires_preparation)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                        <i class="fas fa-clock mr-1"></i>{{ $item->preparation_time ?? 15 }}min
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Source Information -->
                    <div class="mb-3 p-2 bg-gray-50 rounded text-xs">
                        @if($item->type == App\Models\MenuItem::TYPE_BUY_SELL)
                            <div class="flex items-center text-blue-600">
                                <i class="fas fa-boxes mr-2"></i>
                                <span><strong>Source:</strong> Item Master (Inventory) 
                                @if($item->itemMaster)
                                    - Code: {{ $item->itemMaster->item_code }}
                                @endif
                                </span>
                            </div>
                        @else
                            <div class="flex items-center text-orange-600">
                                <i class="fas fa-utensils mr-2"></i>
                                <span><strong>Source:</strong> KOT Recipe (Made from ingredients)</span>
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($item->description)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->description }}</p>
                    @endif

                    <!-- Price and Stock Info -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            @if($item->is_on_promotion)
                                <span class="text-lg font-bold text-green-600">LKR {{ number_format($item->current_price, 2) }}</span>
                                <span class="text-sm text-gray-500 line-through ml-1">LKR {{ number_format($item->price, 2) }}</span>
                            @else
                                <span class="text-lg font-bold text-gray-900">LKR {{ number_format($item->price, 2) }}</span>
                            @endif
                        </div>
                        
                        <!-- Stock/Preparation Info -->
                        <div class="text-right text-sm">
                            @if($item->type == App\Models\MenuItem::TYPE_BUY_SELL && $item->itemMaster)
                                @php
                                    $currentStock = $item->itemMaster->current_stock ?? 0;
                                @endphp
                                @if($currentStock > 0)
                                    <span class="text-green-600">
                                        <i class="fas fa-box mr-1"></i>{{ $currentStock }} in stock
                                    </span>
                                @else
                                    <span class="text-red-600">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Out of stock
                                    </span>
                                @endif
                            @else
                                <span class="text-blue-600">
                                    <i class="fas fa-chef-hat mr-1"></i>Made to order
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Enhanced Dietary Icons -->
                    @if($item->is_vegetarian || $item->is_vegan || $item->is_spicy)
                        <div class="flex items-center gap-2 mb-4">
                            @if($item->is_vegan)
                                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-bold" title="Vegan">V</span>
                            @elseif($item->is_vegetarian)
                                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-bold" title="Vegetarian">VG</span>
                            @endif
                            @if($item->is_spicy)
                                <span class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xs" title="Spicy">
                                    <i class="fas fa-pepper-hot"></i>
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="{{ route('admin.menu-items.show', $item) }}" 
                           class="flex-1 px-3 py-2 bg-indigo-600 text-white text-center rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="{{ route('admin.menu-items.edit', $item) }}" 
                           class="flex-1 px-3 py-2 bg-gray-600 text-white text-center rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Menu Items Found</h3>
                <p class="text-gray-500 mb-4">Get started by creating your first menu item.</p>
                <a href="{{ route('admin.menu-items.create') }}" 
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Menu Item
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($menuItems->hasPages())
        <div class="mt-6">
            {{ $menuItems->links() }}
        </div>
    @endif
</div>

<!-- Create from Item Master Modal (Enhanced) -->
<div id="createFromItemMasterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-screen overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Create Menu Items from Item Master</h3>
                <button onclick="closeCreateFromItemMasterModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createFromItemMasterForm" method="POST" action="{{ route('admin.menu-items.create-from-item-master') }}">
                @csrf
                <div class="p-6 max-h-96 overflow-y-auto">
                    <!-- Category Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Menu Category *</label>
                        <select name="menu_category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Item Master Selection -->
                    <div id="itemMasterList">
                        <p class="text-gray-500 text-center py-8">Loading available items...</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
                    <button type="button" onclick="closeCreateFromItemMasterModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Menu Items
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreateFromItemMasterModal() {
    document.getElementById('createFromItemMasterModal').classList.remove('hidden');
    loadItemMasterData();
}

function closeCreateFromItemMasterModal() {
    document.getElementById('createFromItemMasterModal').classList.add('hidden');
}

function loadItemMasterData() {
    fetch('/admin/menu-items/menu-eligible-items')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('itemMasterList');
            
            if (data.success && data.items.length > 0) {
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                
                data.items.forEach(item => {
                    const typeClass = item.menu_item_type === 'Buy & Sell' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800';
                    const typeIcon = item.menu_item_type === 'Buy & Sell' ? 'fas fa-boxes' : 'fas fa-fire';
                    
                    html += `
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="item_master_ids[]" value="${item.id}" 
                                       class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <h5 class="font-medium text-gray-900">${item.name}</h5>
                                        <span class="px-2 py-1 text-xs rounded-full ${typeClass}">
                                            <i class="${typeIcon} mr-1"></i>${item.menu_item_type}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Code: ${item.item_code || 'N/A'} | 
                                        Selling Price: LKR ${parseFloat(item.selling_price).toFixed(2)}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        Category: ${item.category?.name || 'Uncategorized'}
                                        ${item.requires_preparation ? ' | <span class="text-orange-600">Requires Preparation</span>' : ''}
                                        ${item.current_stock !== null ? ` | Stock: ${item.current_stock}` : ''}
                                    </div>
                                    ${item.description ? `<p class="text-xs text-gray-400 mt-1">${item.description}</p>` : ''}
                                </div>
                            </label>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No eligible items found in Item Master.</p>
                        <p class="text-sm text-gray-400">Items must have "is_menu_item" enabled and valid selling prices.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('itemMasterList').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                    <p class="text-red-500">Error loading items. Please try again.</p>
                </div>
            `;
        });
}
</script>
@endpush
@endsection
