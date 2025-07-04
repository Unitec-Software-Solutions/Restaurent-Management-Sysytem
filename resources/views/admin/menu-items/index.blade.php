@extends('layouts.admin')

@section('title', 'Menu Items Management')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Menu Items</h1>
            <p class="text-gray-600">Manage your restaurant menu items</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.menu-items.create') }}" 
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Menu Item
            </a>
            <a href="{{ route('admin.menu-items.create-kot') }}" 
               class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                <i class="fas fa-fire mr-2"></i>Create KOT Items
            </a>
            <button onclick="openCreateFromItemMasterModal()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-link mr-2"></i>From Item Master
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.menu-items.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Types</option>
                        @foreach(App\Models\MenuItem::getTypes() as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Availability Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_available" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Items</option>
                        <option value="1" {{ request('is_available') === '1' ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ request('is_available') === '0' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('admin.menu-items.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Menu Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($menuItems as $item)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <!-- Image -->
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    @if($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                             alt="{{ $item->name }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="flex items-center justify-center h-48 bg-gray-100">
                            <i class="fas fa-utensils text-4xl text-gray-400"></i>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="p-4">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-900 line-clamp-1">{{ $item->name }}</h3>
                        <div class="flex gap-1">
                            @if($item->is_featured)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                    <i class="fas fa-star"></i>
                                </span>
                            @endif
                            @if(!$item->is_available)
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                    Unavailable
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Category & Type -->
                    <div class="flex gap-2 mb-2">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                            {{ $item->menuCategory->name ?? 'Uncategorized' }}
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $item->type == App\Models\MenuItem::TYPE_KOT ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $item->type == App\Models\MenuItem::TYPE_BUY_SELL ? 'Direct' : 'KOT' }}
                        </span>
                    </div>

                    <!-- Description -->
                    @if($item->description)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->description }}</p>
                    @endif

                    <!-- Price -->
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            @if($item->is_on_promotion)
                                <span class="text-lg font-bold text-green-600">LKR {{ number_format($item->current_price, 2) }}</span>
                                <span class="text-sm text-gray-500 line-through ml-2">LKR {{ number_format($item->price, 2) }}</span>
                            @else
                                <span class="text-lg font-bold text-gray-900">LKR {{ number_format($item->price, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Dietary Info -->
                    <div class="flex gap-1 mb-3">
                        @if($item->is_vegetarian)
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                <i class="fas fa-leaf"></i> Veg
                            </span>
                        @endif
                        @if($item->is_vegan)
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                <i class="fas fa-seedling"></i> Vegan
                            </span>
                        @endif
                        @if($item->is_spicy)
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                <i class="fas fa-pepper-hot"></i> {{ ucfirst($item->spice_level) }}
                            </span>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('admin.menu-items.show', $item) }}" 
                           class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 text-center rounded-lg hover:bg-gray-200 transition-colors text-sm">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="{{ route('admin.menu-items.edit', $item) }}" 
                           class="flex-1 px-3 py-2 bg-indigo-100 text-indigo-700 text-center rounded-lg hover:bg-indigo-200 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Menu Items Found</h3>
                <p class="text-gray-500 mb-4">Start by adding some menu items to your restaurant.</p>
                <a href="{{ route('admin.menu-items.create') }}" 
                   class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Your First Menu Item
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($menuItems->hasPages())
        <div class="mt-8">
            {{ $menuItems->links() }}
        </div>
    @endif
</div>

<!-- Create From Item Master Modal -->
<div id="createFromItemMasterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-hidden">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Menu Category</label>
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
    fetch('/admin/inventory/menu-eligible')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('itemMasterList');
            
            if (data.success && data.items.length > 0) {
                let html = '<div class="space-y-3">';
                
                data.items.forEach(item => {
                    // Determine badge style based on menu item type
                    const typeClass = item.menu_item_type === 'KOT' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800';
                    
                    html += `
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="item_master_ids[]" value="${item.id}" 
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="font-medium text-gray-900">${item.name}</div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${typeClass}">
                                        ${item.menu_item_type}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500">${item.description || 'No description'}</div>
                                <div class="text-sm text-gray-600">
                                    Selling Price: LKR ${parseFloat(item.selling_price).toFixed(2)} | 
                                    Category: ${item.category?.name || 'Uncategorized'}
                                    ${item.menu_item_type === 'KOT' ? ' | <span class="text-orange-600">Requires Preparation</span>' : ''}
                                </div>
                            </div>
                        </label>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No eligible items found in Item Master.</p>
                        <p class="text-sm text-gray-400">Items must have "is_menu_item" enabled.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('itemMasterList').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                    <p class="text-red-500">Failed to load items. Please try again.</p>
                </div>
            `;
        });
}

// Close modal when clicking outside
document.getElementById('createFromItemMasterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateFromItemMasterModal();
    }
});
</script>
@endpush
@endsection
