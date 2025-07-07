@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.menus.list') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $menu->name }}</h1>
                    <p class="text-gray-600">{{ $menu->branch->name ?? 'All Branches' }}</p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <!-- Status Badge -->
                @if($menu->is_active)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Active
                    </span>
                @elseif($menu->valid_from && $menu->valid_from > now())
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        <i class="fas fa-clock mr-1"></i> Upcoming
                    </span>
                @elseif($menu->valid_until && $menu->valid_until < now())
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i> Expired
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                        <i class="fas fa-pause-circle mr-1"></i> Inactive
                    </span>
                @endif

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    @if($menu->is_active)
                        <button type="button" onclick="deactivateMenu({{ $menu->id }})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-pause mr-1"></i> Deactivate
                        </button>
                    @else
                        <button type="button" onclick="activateMenu({{ $menu->id }})" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-play mr-1"></i> Activate
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.menus.preview', $menu) }}" target="_blank"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-external-link-alt mr-1"></i> Preview
                    </a>
                    
                    <a href="{{ route('admin.menus.edit', $menu) }}" 
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-utensils text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Items</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $menu->menuItems->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['total_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                    <p class="text-2xl font-bold text-gray-900">LKR{{ number_format($analytics['total_revenue'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $analytics['availability_status'] ? 'bg-green-100' : 'bg-red-100' }}">
                    <i class="fas fa-{{ $analytics['availability_status'] ? 'check' : 'times' }} text-{{ $analytics['availability_status'] ? 'green' : 'red' }}-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Availability</h3>
                    <p class="text-lg font-bold text-gray-900">{{ $analytics['availability_status'] ? 'Available' : 'Unavailable' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Details and Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Information</h3>
                
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                {{ $menu->type === 'breakfast' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $menu->type === 'lunch' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $menu->type === 'dinner' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $menu->type === 'all_day' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $menu->type === 'special' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $menu->type === 'seasonal' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                            </span>
                        </dd>
                    </div>

                    @if($menu->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $menu->description }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Valid Period</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                @if($menu->valid_from)
                                    {{ \Carbon\Carbon::parse($menu->valid_from)->format('M j, Y') }}
                                    @if($menu->valid_until)
                                        - {{ \Carbon\Carbon::parse($menu->valid_until)->format('M j, Y') }}
                                    @else
                                        - Ongoing
                                    @endif
                                @else
                                    No date specified
                                @endif
                            </div>
                        </dd>
                    </div>

                    @if($menu->start_time && $menu->end_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Service Hours</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                                    {{ \Carbon\Carbon::parse($menu->start_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($menu->end_time)->format('g:i A') }}
                                </div>
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Available Days</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-1">
                                @if($menu->available_days && is_array($menu->available_days) && count($menu->available_days) > 0)
                                    @foreach($menu->available_days as $day)
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                            {{ ucfirst($day) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-sm text-gray-500">No days specified</span>
                                @endif
                            </div>
                        </dd>
                    </div>

                    @if($menu->creator)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $menu->creator->name ?? 'Unknown' }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $menu->created_at ? $menu->created_at->format('M j, Y g:i A') : 'Not available' }}</dd>
                    </div>

                    @if($menu->updated_at && $menu->created_at && $menu->updated_at->ne($menu->created_at))
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $menu->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.menus.edit', $menu) }}" 
                       class="flex items-center w-full p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-edit text-gray-400 mr-3"></i>
                        <span class="text-sm font-medium text-gray-900">Edit Menu</span>
                    </a>
                    
                    <a href="{{ route('admin.menus.preview', $menu) }}" target="_blank"
                       class="flex items-center w-full p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-external-link-alt text-gray-400 mr-3"></i>
                        <span class="text-sm font-medium text-gray-900">Preview Menu</span>
                    </a>
                    
                    <button type="button" onclick="duplicateMenu({{ $menu->id }})"
                            class="flex items-center w-full p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-copy text-gray-400 mr-3"></i>
                        <span class="text-sm font-medium text-gray-900">Duplicate Menu</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Menu Items ({{ $menu->menuItems->count() }})</h3>
                    <div class="flex gap-2">
                        <input type="text" id="items-search" placeholder="Search items..." 
                               class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <select id="category-filter" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            @foreach($menu->menuItems->groupBy('category.name') as $categoryName => $items)
                                @if($categoryName)
                                    <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($menu->menuItems->count() > 0)
                    <div class="space-y-4" id="items-list">
                        @foreach($menu->menuItems->groupBy('category.name') as $categoryName => $items)
                            <div class="category-section" data-category="{{ $categoryName }}">
                                @if($categoryName)
                                    <h4 class="text-md font-semibold text-gray-800 mb-3 border-b border-gray-200 pb-2">
                                        {{ $categoryName }}
                                    </h4>
                                @endif
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($items as $item)
                                        <div class="menu-item border border-gray-200 rounded-lg p-4 hover:bg-gray-50" 
                                             data-name="{{ strtolower($item->name) }}" data-category="{{ $categoryName }}">
                                            <div class="flex justify-between items-start mb-2">
                                                <h5 class="font-medium text-gray-900">{{ $item->name }}</h5>
                                                <span class="text-lg font-bold text-green-600">${{ number_format($item->price, 2) }}</span>
                                            </div>
                                            
                                            @if($item->description)
                                                <p class="text-sm text-gray-600 mb-3">{{ $item->description }}</p>
                                            @endif
                                            
                                            <div class="flex justify-between items-center text-sm">
                                                <div class="flex items-center gap-4">
                                                    @if($item->current_stock !== null)
                                                        <span class="flex items-center {{ $item->current_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                            <i class="fas fa-box mr-1"></i>
                                                            Stock: {{ $item->current_stock }}
                                                        </span>
                                                    @endif
                                                    
                                                    @if($item->prep_time)
                                                        <span class="flex items-center text-gray-500">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            {{ $item->prep_time }}min
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex gap-2">
                                                    @if($item->is_available)
                                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                                            Available
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                                            Unavailable
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="no-items-found" class="text-center py-8 hidden">
                        <div class="text-gray-400 text-4xl mb-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <p class="text-gray-500">No items found</p>
                        <p class="text-sm text-gray-400">Try adjusting your search or filter</p>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-5xl mb-4">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No items in this menu</h3>
                        <p class="text-gray-500 mb-6">Add items to make this menu available for orders.</p>
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i> Add Items
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function activateMenu(menuId) {
    if (confirm('Are you sure you want to activate this menu?')) {
        fetch(`/admin/menus/${menuId}/activate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to activate menu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to activate menu');
        });
    }
}

function deactivateMenu(menuId) {
    if (confirm('Are you sure you want to deactivate this menu?')) {
        fetch(`/admin/menus/${menuId}/deactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to deactivate menu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to deactivate menu');
        });
    }
}

function duplicateMenu(menuId) {
    if (confirm('Create a copy of this menu?')) {
        // This would redirect to create page with pre-filled data
        window.location.href = `/admin/menus/create?duplicate=${menuId}`;
    }
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('items-search');
    const categoryFilter = document.getElementById('category-filter');
    const itemsList = document.getElementById('items-list');
    const noItemsDiv = document.getElementById('no-items-found');

    function filterItems() {
        if (!searchInput || !categoryFilter) return;
        
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const sections = document.querySelectorAll('.category-section');
        let visibleItems = 0;

        sections.forEach(section => {
            const sectionCategory = section.dataset.category;
            const items = section.querySelectorAll('.menu-item');
            let sectionHasVisibleItems = false;

            items.forEach(item => {
                const itemName = item.dataset.name;
                const itemCategory = item.dataset.category;
                
                const matchesSearch = itemName.includes(searchTerm);
                const matchesCategory = !selectedCategory || itemCategory === selectedCategory;
                
                if (matchesSearch && matchesCategory) {
                    item.style.display = 'block';
                    sectionHasVisibleItems = true;
                    visibleItems++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide section based on whether it has visible items
            section.style.display = sectionHasVisibleItems ? 'block' : 'none';
        });

        // Show/hide no items message
        if (itemsList && noItemsDiv) {
            if (visibleItems === 0) {
                itemsList.style.display = 'none';
                noItemsDiv.style.display = 'block';
            } else {
                itemsList.style.display = 'block';
                noItemsDiv.style.display = 'none';
            }
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterItems);
    if (categoryFilter) categoryFilter.addEventListener('change', filterItems);
});
</script>
@endpush
@endsection
