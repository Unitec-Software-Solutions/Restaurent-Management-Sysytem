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
                <h1 class="text-2xl font-bold text-gray-900">Bulk Create Menus</h1>
                <p class="text-gray-600">Create multiple menus for a date range with the same configuration</p>
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

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-blue-800">How Bulk Creation Works</h3>
                <p class="text-sm text-blue-700 mt-1">
                    This will create individual menus for each selected day within your date range. Use {date} in the name template to include the date in each menu name.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.menus.bulk.store') }}" class="space-y-6">
        @csrf

        <!-- Template Configuration -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Configuration</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name Template *</label>
                    <input type="text" name="name_template" value="{{ old('name_template', 'Daily Menu {date}') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g., Daily Menu {date} or Weekend Special {date}">
                    <p class="text-xs text-gray-500 mt-1">Use {date} to include the date in each menu name</p>
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
                              placeholder="This description will be used for all created menus...">{{ old('description') }}</textarea>
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
            </div>
        </div>

        <!-- Date Range & Schedule -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Date Range & Schedule</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">First date to create menus for</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Last date to create menus for</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Time when menus become available each day</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Time when menus become unavailable each day</p>
                </div>
            </div>

            <!-- Days Selection -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Create Menus For These Days *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    @php 
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $oldDays = old('available_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
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
                <p class="text-xs text-gray-500 mt-2">Menus will only be created for dates that fall on these selected days</p>
            </div>
        </div>

        <!-- Menu Items Selection -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Items Template</h3>
            <p class="text-sm text-gray-600 mb-4">Select the items that will be included in all created menus</p>
            
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
                    <div>
                        <button type="button" id="select-all" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                            Select All
                        </button>
                    </div>
                    <div>
                        <button type="button" id="deselect-all" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Deselect All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto" id="items-grid">
                @foreach($categories as $category)
                    @if($category->items->count() > 0)
                        <div class="col-span-full">
                            <h4 class="font-semibold text-gray-800 text-sm mb-2 border-b border-gray-200 pb-1">
                                {{ $category->name }}
                            </h4>
                        </div>
                        
                        @foreach($category->items as $item)
                            <div class="item-card border border-gray-200 rounded-lg p-3 hover:bg-gray-50" 
                                 data-category="{{ $category->id }}" data-name="{{ strtolower($item->name) }}">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="menu_items[]" value="{{ $item->id }}" 
                                           {{ in_array($item->id, old('menu_items', [])) ? 'checked' : '' }}
                                           class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <h5 class="font-medium text-gray-900 text-sm">{{ $item->name }}</h5>
                                        @if($item->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($item->description, 60) }}</p>
                                        @endif
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm font-medium text-green-600">${{ number_format($item->price, 2) }}</span>
                                            @if($item->current_stock !== null)
                                                <span class="text-xs {{ $item->current_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    Stock: {{ $item->current_stock }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    @endif
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

        <!-- Preview -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
            <div id="preview-content" class="text-sm text-gray-600">
                <p>Configure the settings above to see a preview of what will be created.</p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.menus.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                    <i class="fas fa-magic mr-2"></i> Create Menus
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
    const selectAllBtn = document.getElementById('select-all');
    const deselectAllBtn = document.getElementById('deselect-all');
    const previewContent = document.getElementById('preview-content');

    // Form inputs for preview
    const nameTemplate = document.querySelector('input[name="name_template"]');
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const availableDays = document.querySelectorAll('input[name="available_days[]"]');

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

    function selectAllVisible() {
        const visibleItems = itemsGrid.querySelectorAll('.item-card:not([style*="display: none"])');
        visibleItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = true;
            item.classList.add('bg-indigo-50', 'border-indigo-200');
        });
        updatePreview();
    }

    function deselectAllVisible() {
        const visibleItems = itemsGrid.querySelectorAll('.item-card:not([style*="display: none"])');
        visibleItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = false;
            item.classList.remove('bg-indigo-50', 'border-indigo-200');
        });
        updatePreview();
    }

    function updatePreview() {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const template = nameTemplate.value;
        const selectedDays = Array.from(availableDays).filter(cb => cb.checked).map(cb => cb.value);
        const selectedItems = document.querySelectorAll('input[name="menu_items[]"]:checked').length;

        if (!start || !end || !template || selectedDays.length === 0) {
            previewContent.innerHTML = '<p class="text-gray-500">Configure the settings above to see a preview of what will be created.</p>';
            return;
        }

        let menuCount = 0;
        const current = new Date(start);
        const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        while (current <= end) {
            const dayName = dayNames[current.getDay()];
            if (selectedDays.includes(dayName)) {
                menuCount++;
            }
            current.setDate(current.getDate() + 1);
        }

        previewContent.innerHTML = `
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Creation Summary</h4>
                <ul class="text-blue-800 space-y-1">
                    <li><strong>${menuCount}</strong> menus will be created</li>
                    <li>Each menu will have <strong>${selectedItems}</strong> items</li>
                    <li>Date range: <strong>${start.toLocaleDateString()}</strong> to <strong>${end.toLocaleDateString()}</strong></li>
                    <li>Days: <strong>${selectedDays.map(d => d.charAt(0).toUpperCase() + d.slice(1)).join(', ')}</strong></li>
                </ul>
                ${menuCount > 50 ? '<p class="text-yellow-700 mt-2 text-sm"><i class="fas fa-warning mr-1"></i> Large number of menus will be created. This may take a moment.</p>' : ''}
            </div>
        `;
    }

    // Event listeners
    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);
    selectAllBtn.addEventListener('click', selectAllVisible);
    deselectAllBtn.addEventListener('click', deselectAllVisible);

    // Preview update listeners
    nameTemplate.addEventListener('input', updatePreview);
    startDate.addEventListener('change', updatePreview);
    endDate.addEventListener('change', updatePreview);
    availableDays.forEach(cb => cb.addEventListener('change', updatePreview));

    // Add change listeners to item checkboxes
    itemsGrid.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const itemCard = this.closest('.item-card');
            if (this.checked) {
                itemCard.classList.add('bg-indigo-50', 'border-indigo-200');
            } else {
                itemCard.classList.remove('bg-indigo-50', 'border-indigo-200');
            }
            updatePreview();
        });
    });

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

    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection
