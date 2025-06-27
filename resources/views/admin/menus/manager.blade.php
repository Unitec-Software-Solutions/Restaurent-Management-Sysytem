@extends('layouts.admin')

@section('title', 'Menu Manager')

@section('content')
<div class="p-4 rounded-lg">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Menu Manager</h1>
                <p class="text-gray-600">Manage which items appear in your restaurant menus</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.inventory.items.create') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Item
                </a>
                <a href="{{ route('admin.menus.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Menus
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalItems }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-utensils text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Menu Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $menuItems }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <i class="fas fa-eye-slash text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Not in Menu</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $nonMenuItems }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Chef's Specials</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $chefsSpecials }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text" id="search-items" placeholder="Search items..." 
                           class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>

                <!-- Category Filter -->
                <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <!-- Menu Status Filter -->
                <select id="menu-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Items</option>
                    <option value="menu">Menu Items Only</option>
                    <option value="non-menu">Non-Menu Items Only</option>
                    <option value="specials">Chef's Specials</option>
                    <option value="popular">Popular Items</option>
                </select>
            </div>

            <!-- Bulk Actions -->
            <div class="flex gap-2">
                <button id="bulk-add-menu" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-plus mr-2"></i> Add to Menu
                </button>
                <button id="bulk-remove-menu" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-minus mr-2"></i> Remove from Menu
                </button>
                <button id="select-all" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-check-square mr-2"></i> Select All
                </button>
                <button id="deselect-all" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-square mr-2"></i> Deselect All
                </button>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="items-grid">
                @foreach($items as $item)
                <div class="item-card border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-300 {{ $item['is_menu_item'] ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}"
                     data-item-id="{{ $item['id'] }}"
                     data-name="{{ strtolower($item['name']) }}"
                     data-category="{{ strtolower($item['category']) }}"
                     data-is-menu="{{ $item['is_menu_item'] ? 'true' : 'false' }}"
                     data-is-special="{{ $item['is_chefs_special'] ? 'true' : 'false' }}"
                     data-is-popular="{{ $item['is_popular'] ? 'true' : 'false' }}">
                    
                    <!-- Selection Checkbox -->
                    <div class="flex items-start justify-between mb-3">
                        <input type="checkbox" class="item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex gap-1">
                            @if($item['is_chefs_special'])
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-star mr-1"></i> Chef's
                                </span>
                            @endif
                            @if($item['is_popular'])
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-fire mr-1"></i> Popular
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Item Info -->
                    <div class="mb-3">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $item['name'] }}</h3>
                        <p class="text-sm text-gray-600">{{ $item['category'] }}</p>
                        <p class="text-lg font-bold text-indigo-600 mt-1">LKR {{ number_format($item['selling_price'], 2) }}</p>
                    </div>

                    <!-- Menu Attributes (if applicable) -->
                    @if($item['is_menu_item'] && ($item['cuisine_type'] || $item['spice_level'] || $item['prep_time_minutes']))
                    <div class="mb-3 space-y-1">
                        @if($item['cuisine_type'])
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-globe mr-2 w-3"></i>
                                <span>{{ ucfirst(str_replace('_', ' ', $item['cuisine_type'])) }}</span>
                            </div>
                        @endif
                        @if($item['spice_level'])
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-pepper-hot mr-2 w-3"></i>
                                <span>{{ ucfirst($item['spice_level']) }}</span>
                            </div>
                        @endif
                        @if($item['prep_time_minutes'])
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-clock mr-2 w-3"></i>
                                <span>{{ $item['prep_time_minutes'] }} mins</span>
                            </div>
                        @endif
                        @if($item['availability'])
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-calendar mr-2 w-3"></i>
                                <span>{{ ucfirst(str_replace('_', ' ', $item['availability'])) }}</span>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Menu Toggle -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="menu-toggle sr-only" 
                                       data-item-id="{{ $item['id'] }}"
                                       {{ $item['is_menu_item'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                {{ $item['is_menu_item'] ? 'In Menu' : 'Not in Menu' }}
                            </span>
                        </div>
                        <a href="{{ route('admin.inventory.items.edit', $item['id']) }}" 
                           class="text-indigo-600 hover:text-indigo-800 text-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- No Items Found -->
            <div id="no-items-found" class="text-center py-12 hidden">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No items found</h3>
                <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-items');
    const categoryFilter = document.getElementById('category-filter');
    const menuFilter = document.getElementById('menu-filter');
    const itemsGrid = document.getElementById('items-grid');
    const noItemsDiv = document.getElementById('no-items-found');
    const bulkAddBtn = document.getElementById('bulk-add-menu');
    const bulkRemoveBtn = document.getElementById('bulk-remove-menu');
    const selectAllBtn = document.getElementById('select-all');
    const deselectAllBtn = document.getElementById('deselect-all');

    // Filter items
    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedMenuFilter = menuFilter.value;
        const items = itemsGrid.querySelectorAll('.item-card');
        let visibleCount = 0;

        items.forEach(item => {
            const itemName = item.dataset.name;
            const itemCategory = item.dataset.category;
            const isMenu = item.dataset.isMenu === 'true';
            const isSpecial = item.dataset.isSpecial === 'true';
            const isPopular = item.dataset.isPopular === 'true';
            
            const matchesSearch = itemName.includes(searchTerm);
            const matchesCategory = !selectedCategory || itemCategory === selectedCategory;
            
            let matchesMenuFilter = true;
            switch (selectedMenuFilter) {
                case 'menu':
                    matchesMenuFilter = isMenu;
                    break;
                case 'non-menu':
                    matchesMenuFilter = !isMenu;
                    break;
                case 'specials':
                    matchesMenuFilter = isSpecial;
                    break;
                case 'popular':
                    matchesMenuFilter = isPopular;
                    break;
            }
            
            if (matchesSearch && matchesCategory && matchesMenuFilter) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no items message
        if (visibleCount === 0) {
            itemsGrid.style.display = 'none';
            noItemsDiv.classList.remove('hidden');
        } else {
            itemsGrid.style.display = 'grid';
            noItemsDiv.classList.add('hidden');
        }
    }

    // Handle individual menu toggle
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('menu-toggle')) {
            const itemId = e.target.dataset.itemId;
            const isMenu = e.target.checked;
            
            // Update UI immediately
            const itemCard = e.target.closest('.item-card');
            const statusText = itemCard.querySelector('.text-sm.font-medium.text-gray-700');
            
            if (isMenu) {
                itemCard.classList.remove('bg-gray-50');
                itemCard.classList.add('bg-green-50', 'border-green-200');
                statusText.textContent = 'In Menu';
            } else {
                itemCard.classList.remove('bg-green-50', 'border-green-200');
                itemCard.classList.add('bg-gray-50');
                statusText.textContent = 'Not in Menu';
            }
            
            // Update data attribute
            itemCard.dataset.isMenu = isMenu.toString();
            
            // Send AJAX request
            fetch('{{ route("admin.menus.toggle-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    item_id: itemId,
                    is_menu: isMenu
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert UI changes if request failed
                    e.target.checked = !isMenu;
                    if (!isMenu) {
                        itemCard.classList.remove('bg-gray-50');
                        itemCard.classList.add('bg-green-50', 'border-green-200');
                        statusText.textContent = 'In Menu';
                    } else {
                        itemCard.classList.remove('bg-green-50', 'border-green-200');
                        itemCard.classList.add('bg-gray-50');
                        statusText.textContent = 'Not in Menu';
                    }
                    itemCard.dataset.isMenu = (!isMenu).toString();
                    alert('Failed to update menu status. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert changes on error
                e.target.checked = !isMenu;
                alert('Network error. Please try again.');
            });
        }
    });

    // Handle item selection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            updateBulkButtons();
        }
    });

    // Update bulk action buttons
    function updateBulkButtons() {
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        const hasSelection = selectedItems.length > 0;
        
        bulkAddBtn.disabled = !hasSelection;
        bulkRemoveBtn.disabled = !hasSelection;
    }

    // Select all visible items
    selectAllBtn.addEventListener('click', function() {
        const visibleItems = itemsGrid.querySelectorAll('.item-card:not([style*="display: none"]) .item-checkbox');
        visibleItems.forEach(checkbox => checkbox.checked = true);
        updateBulkButtons();
    });

    // Deselect all items
    deselectAllBtn.addEventListener('click', function() {
        const allCheckboxes = document.querySelectorAll('.item-checkbox');
        allCheckboxes.forEach(checkbox => checkbox.checked = false);
        updateBulkButtons();
    });

    // Bulk add to menu
    bulkAddBtn.addEventListener('click', function() {
        const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(checkbox => checkbox.closest('.item-card').dataset.itemId);
        
        if (selectedItems.length === 0) return;
        
        bulkUpdateMenuStatus(selectedItems, true);
    });

    // Bulk remove from menu
    bulkRemoveBtn.addEventListener('click', function() {
        const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(checkbox => checkbox.closest('.item-card').dataset.itemId);
        
        if (selectedItems.length === 0) return;
        
        bulkUpdateMenuStatus(selectedItems, false);
    });

    // Bulk update menu status
    function bulkUpdateMenuStatus(itemIds, isMenu) {
        fetch('{{ route("admin.menus.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                item_ids: itemIds,
                is_menu: isMenu
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI for all selected items
                itemIds.forEach(itemId => {
                    const itemCard = document.querySelector(`[data-item-id="${itemId}"]`);
                    const toggle = itemCard.querySelector('.menu-toggle');
                    const statusText = itemCard.querySelector('.text-sm.font-medium.text-gray-700');
                    
                    toggle.checked = isMenu;
                    
                    if (isMenu) {
                        itemCard.classList.remove('bg-gray-50');
                        itemCard.classList.add('bg-green-50', 'border-green-200');
                        statusText.textContent = 'In Menu';
                    } else {
                        itemCard.classList.remove('bg-green-50', 'border-green-200');
                        itemCard.classList.add('bg-gray-50');
                        statusText.textContent = 'Not in Menu';
                    }
                    
                    itemCard.dataset.isMenu = isMenu.toString();
                });
                
                // Clear selections
                document.querySelectorAll('.item-checkbox').forEach(checkbox => checkbox.checked = false);
                updateBulkButtons();
                
                alert(data.message);
            } else {
                alert('Failed to update menu status. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        });
    }

    // Event listeners for filters
    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);
    menuFilter.addEventListener('change', filterItems);
});
</script>
@endpush
@endsection
