@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.menus.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Menu</h1>
                <p class="text-gray-600">Set up a new menu for your restaurant</p>
            </div>
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
            <h3 class="font-medium mb-2">Please fix the following errors:</h3>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g., Weekend Brunch Menu">
                    <p class="text-xs text-gray-500 mt-1">A descriptive name for your menu</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menu Type *</label>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select menu type...</option>
                        <option value="breakfast" {{ old('type') === 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                        <option value="lunch" {{ old('type') === 'lunch' ? 'selected' : '' }}>Lunch</option>
                        <option value="dinner" {{ old('type') === 'dinner' ? 'selected' : '' }}>Dinner</option>
                        <option value="all_day" {{ old('type') === 'all_day' ? 'selected' : '' }}>All Day</option>
                        <option value="special" {{ old('type') === 'special' ? 'selected' : '' }}>Special</option>
                        <option value="seasonal" {{ old('type') === 'seasonal' ? 'selected' : '' }}>Seasonal</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Describe this menu...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                    <select name="branch_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activate immediately</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Date & Time Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Date & Time Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid From *</label>
                    <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">When this menu becomes available</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for no end date</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Time when menu becomes available each day</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Time when menu becomes unavailable each day</p>
                </div>
            </div>

            <!-- Available Days -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Available Days *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    @php 
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $oldDays = old('available_days', []);
                    @endphp
                    @foreach($days as $day)
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="available_days[]" value="{{ $day }}" 
                                   {{ in_array($day, $oldDays) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ ucfirst($day) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Menu Items Selection -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Items</h3>
            
            <!-- Search and Filter -->
            <div class="mb-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" id="item-search" placeholder="Search menu items..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="items-grid">
                @foreach($menuItems as $item)
                    <div class="item-card border border-gray-200 rounded-lg p-4 hover:bg-gray-50" 
                         data-category="{{ $item->category_id }}" data-name="{{ strtolower($item->name) }}">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="menu_items[]" value="{{ $item->id }}" 
                                   {{ in_array($item->id, old('menu_items', [])) ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 flex-1">
                                <h4 class="font-medium text-gray-900">{{ $item->name }}</h4>
                                @if($item->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($item->description, 80) }}</p>
                                @endif
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-medium text-green-600">${{ number_format($item->price, 2) }}</span>
                                    @if($item->menuCategory)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ $item->menuCategory->name }}</span>
                                    @elseif($item->category && is_string($item->category))
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ $item->category }}</span>
                                    @endif
                                </div>
                                @if($item->current_stock !== null)
                                    <div class="mt-1">
                                        <span class="text-xs {{ $item->current_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Stock: {{ $item->current_stock }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            <div id="no-items" class="text-center py-8 hidden">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-search"></i>
                </div>
                <p class="text-gray-500">No items found</p>
                <p class="text-sm text-gray-400">Try adjusting your search or filter</p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.menus.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-save mr-2"></i> Create Menu
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('item-search');
    const categoryFilter = document.getElementById('category-filter');
    const itemsGrid = document.getElementById('items-grid');
    const noItemsDiv = document.getElementById('no-items');

    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const items = itemsGrid.querySelectorAll('.item-card');
        let visibleCount = 0;

        items.forEach(item => {
            const itemName = item.dataset.name;
            const itemCategory = item.dataset.category;
            
            const matchesSearch = itemName.includes(searchTerm);
            const matchesCategory = !selectedCategory || itemCategory === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no items message
        if (visibleCount === 0) {
            itemsGrid.style.display = 'none';
            noItemsDiv.style.display = 'block';
        } else {
            itemsGrid.style.display = 'grid';
            noItemsDiv.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);

    // Auto-suggest time based on menu type
    const typeSelect = document.querySelector('select[name="type"]');
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        let startTime = '';
        let endTime = '';

        switch(type) {
            case 'breakfast':
                startTime = '06:00';
                endTime = '11:00';
                break;
            case 'lunch':
                startTime = '11:00';
                endTime = '16:00';
                break;
            case 'dinner':
                startTime = '17:00';
                endTime = '23:00';
                break;
        }

        if (startTime && !startTimeInput.value) {
            startTimeInput.value = startTime;
        }
        if (endTime && !endTimeInput.value) {
            endTimeInput.value = endTime;
        }
    });
});
</script>
@endpush
@endsection
