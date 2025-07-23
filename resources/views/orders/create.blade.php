@extends(isset($isAdmin) && $isAdmin ? 'layouts.admin' : 'layouts.app')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">
                Create Takeaway Order{{ isset($isAdmin) && $isAdmin ? ' (Admin)' : '' }}
            </h2>
            @if(isset($isAdmin) && $isAdmin)
                <p class="text-blue-100 mt-1">Admin mode: Default values pre-filled</p>
            @endif
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <form method="POST" action="{{ isset($isAdmin) && $isAdmin ? route('admin.orders.takeaway.store') : route('orders.takeaway.store') }}" class="space-y-6" id="takeaway-order-form">
                @csrf

                <!-- Admin Hidden Fields -->
                @if(isset($isAdmin) && $isAdmin)
                    <input type="hidden" name="is_admin" value="1">
                    @if(isset($defaultOrganization))
                        <input type="hidden" name="default_organization_id" value="{{ $defaultOrganization }}">
                    @endif
                    @if(isset($defaultBranch))
                        <input type="hidden" name="default_branch_id" value="{{ $defaultBranch }}">
                    @endif
                @endif

                <!-- Display Validation Errors -->
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column - Order Details -->
                    <div class="space-y-6">
                        <!-- Order Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Information</h3>

                            <!-- Show Takeaway Order Type Info (No Selection Needed) -->
                            <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i>
                                    <span class="font-semibold text-blue-800">Takeaway Order</span>
                                </div>
                                <p class="text-blue-700 text-sm mt-1">This order is for pickup/delivery</p>
                                <input type="hidden" name="order_type" value="takeaway_walk_in_demand">
                            </div>

                            <!-- Organization Selection (Admin Only) -->
                            @if(isset($isAdmin) && $isAdmin && isset($organizations) && $organizations->count() > 1)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Organization</label>
                                <select name="organization_id" id="organization_select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                                    <option value="">Choose Organization...</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}"
                                            {{ (isset($organizationId) && $organizationId == $organization->id) || (isset($defaultOrganization) && $defaultOrganization == $organization->id) ? 'selected' : '' }}>
                                            {{ $organization->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Branch Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Select {{ (isset($isAdmin) && $isAdmin) ? 'Branch' : 'Restaurant Location' }}
                                </label>
                                <select name="branch_id" id="branch_select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" required>
                                    <option value="">Choose {{ (isset($isAdmin) && $isAdmin) ? 'Branch' : 'Location' }}...</option>
                                    @if(isset($branches))
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                data-organization="{{ $branch->organization_id ?? $branch->organization->id ?? '' }}"
                                                {{ (isset($branchId) && $branchId == $branch->id) || (isset($defaultBranch) && $defaultBranch == $branch->id) ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                                @if(!(isset($isAdmin) && $isAdmin) && isset($branch->organization))
                                                    ({{ $branch->organization->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if(isset($isAdmin) && $isAdmin && isset($defaultBranch))
                                    <input type="hidden" id="admin_default_branch" value="{{ $defaultBranch }}">
                                @endif
                                @if(isset($isAdmin) && $isAdmin && isset($defaultOrganization))
                                    <input type="hidden" id="admin_default_organization" value="{{ $defaultOrganization }}">
                                @endif
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                                <input type="datetime-local" name="order_time"
                                    value="{{ isset($isAdmin) && $isAdmin ? now()->addMinutes(30)->format('Y-m-d\TH:i') : old('order_time', '') }}"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                    required>
                            </div>

                            <!-- Add Special Instructions Field -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions <span class="text-gray-500 text-xs">(Optional)</span></label>
                                <textarea name="special_instructions"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                    rows="3"
                                    placeholder="Any special requests or instructions for your order..."></textarea>
                            </div>
                        </div>

                        <!-- Customer Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Customer Information</h3>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name"
                                    value="{{ old('customer_name', isset($defaultCustomerName) ? $defaultCustomerName : '') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                    placeholder="{{ isset($isAdmin) && $isAdmin ? 'Customer name will be auto-generated' : 'Enter your full name' }}"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="customer_phone"
                                    value="{{ old('customer_phone', isset($defaultPhone) ? $defaultPhone : '') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                    placeholder="{{ isset($isAdmin) && $isAdmin ? 'Branch phone number (default)' : 'Enter your phone number' }}"
                                    required
                                    pattern="[0-9+]{10,15}"
                                    title="Please enter a valid 10-15 digit phone number">
                                <p class="mt-1 text-sm text-gray-500">
                                    @if(isset($isAdmin) && $isAdmin)
                                        Admin mode: Using branch phone as default
                                    @else
                                        We'll notify you about your order status
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Menu Items -->                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 h-fit">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Menu Items</h3>

                        <div id="menu-loading" class="hidden text-center py-8">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin h-6 w-6 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading menu items...
                            </div>
                        </div>

                        <div id="menu-error" class="hidden text-center py-8">
                            <div class="text-red-500">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Please select a branch to view menu items</p>
                            </div>
                        </div>

                        <div id="menu-items-container" class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                            @if(isset($items) && $items->count() > 0)
                                @foreach($items as $item)
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:border-blue-300 transition-colors duration-150 cursor-pointer {{ ($item->item_type === 'Buy & Sell' && $item->current_stock <= 0) ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="toggleItemSelection('{{ $item->id }}')">
                                    <div class="flex items-center">
                                        <input class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 item-check"
                                            type="checkbox"
                                            value="{{ $item->id }}"
                                            id="item_{{ $item->id }}"
                                            data-item-id="{{ $item->id }}"
                                            {{ ($item->item_type === 'Buy & Sell' && $item->current_stock <= 0) ? 'disabled' : '' }}
                                            onclick="event.stopPropagation();">

                                        <label for="item_{{ $item->id }}" class="ml-3 flex-1 cursor-pointer">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                                    @if($item->item_type === 'KOT')
                                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">KOT Available</span>
                                                    @endif
                                                </div>
                                                <span class="text-blue-600 font-semibold">LKR {{ number_format($item->price, 2) }}</span>
                                            </div>
                                            @if($item->item_type === 'KOT')
                                                <div class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded mt-1 inline-block">✓ Always Available</div>
                                            @elseif($item->item_type === 'Buy & Sell')
                                                @if($item->current_stock > 0)
                                                    <div class="text-xs text-green-600 font-medium mt-1">In Stock ({{ $item->current_stock }})</div>
                                                @else
                                                    <div class="text-xs text-red-600 font-medium mt-1 bg-red-50 px-2 py-1 rounded">❌ Out of Stock</div>
                                                @endif
                                            @endif
                                        </label>

                                        <div class="flex items-center border border-gray-300 rounded overflow-hidden touch-friendly-controls">
                                            <button type="button"
                                                class="qty-decrease w-12 h-12 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-r border-gray-300"
                                                data-item-id="{{ $item->id }}"
                                                onclick="event.stopPropagation();"
                                                disabled>−</button>
                                            <input type="number"
                                                min="1"
                                                max="{{ ($item->item_type === 'Buy & Sell') ? $item->current_stock : 99 }}"
                                                value="1"
                                                class="item-qty w-16 h-12 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 touch-manipulation"
                                                data-item-id="{{ $item->id }}"
                                                onclick="event.stopPropagation();"
                                                disabled
                                                readonly>
                                            <button type="button"
                                                class="qty-increase w-12 h-12 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-l border-gray-300"
                                                data-item-id="{{ $item->id }}"
                                                onclick="event.stopPropagation();"
                                                disabled>+</button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div id="no-menu-message" class="text-center py-8 text-gray-500">
                                    @if(isset($isAdmin) && $isAdmin)
                                        Please select an organization and branch to view menu items.
                                    @else
                                        Please select a restaurant location to view menu items.
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Summary Section -->
                <div class="mt-6 bg-gray-50 p-5 rounded-lg border border-gray-200" id="order-summary" style="display: none;">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Summary</h3>
                    <div id="selected-items" class="space-y-2"></div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center text-lg font-semibold">
                            <span>Total Items:</span>
                            <span id="total-items">0</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-bold text-black text-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all shadow-lg transform hover:scale-105 touch-manipulation">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Create Order for Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Admin-specific time handling
    @if(isset($isAdmin) && $isAdmin)
    const setDefaultTime = (minutesToAdd) => {
        const time = new Date();
        time.setMinutes(time.getMinutes() + minutesToAdd);
        const timeInput = document.querySelector('input[name="order_time"]');
        if (timeInput) {
            timeInput.value = time.toISOString().slice(0, 16);
        }
    };

    // Initial time setting (30 minutes from now for admin, empty for customers)
    @if(isset($isAdmin) && $isAdmin)
    setDefaultTime(30);
    @endif

    // Handle order type changes
    const orderTypeSelect = document.querySelector('select[name="order_type"]');
    if (orderTypeSelect) {
        orderTypeSelect.addEventListener('change', function() {
            setDefaultTime(this.value === 'takeaway_in_call_scheduled' ? 30 : 15);
        });
    }
    @endif

    // Initialize organization/branch handling
    initializeLocationHandling();

    // Initialize quantity controls first
    initializeQuantityControls();

    // Enable/disable qty and buttons on checkbox change
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            const plusBtn = document.querySelector('.qty-increase[data-item-id="' + itemId + '"]');
            const minusBtn = document.querySelector('.qty-decrease[data-item-id="' + itemId + '"]');
            const itemContainer = this.closest('.bg-white');

            if (this.checked) {
                // Enable controls
                qtyInput.disabled = false;
                qtyInput.removeAttribute('readonly');
                plusBtn.disabled = false;
                minusBtn.disabled = false;                                // Set proper form field names for Laravel validation
                                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');

                                // Create hidden input for menu_item_id to ensure it's submitted with form
                                let hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                                if (!hiddenInput) {
                                    hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'items[' + itemId + '][menu_item_id]';
                                    hiddenInput.value = itemId;
                                    hiddenInput.className = 'item-hidden-' + itemId;
                                    itemContainer.appendChild(hiddenInput);
                                }

                // Visual feedback - highlight selected item
                itemContainer.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');

                updateButtonStates(itemId, qtyInput.value);
                console.log('✅ Item selected:', itemId, 'Quantity:', qtyInput.value);
                updateOrderSummary();
            } else {
                // Disable controls
                qtyInput.disabled = true;
                qtyInput.setAttribute('readonly', 'readonly');
                plusBtn.disabled = true;
                minusBtn.disabled = true;

                // Remove form field names
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;

                // Remove hidden input
                const hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                if (hiddenInput) {
                    hiddenInput.remove();
                }

                // Remove visual feedback
                itemContainer.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');

                console.log('❌ Item deselected:', itemId);
                updateOrderSummary();
            }
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        // Check if branch is selected
        const branchSelect = document.querySelector('select[name="branch_id"]');
        if (!branchSelect || !branchSelect.value) {
            e.preventDefault();
            alert('Please select a branch');
            if (branchSelect) branchSelect.focus();
            return false;
        }

        const phoneInput = document.querySelector('input[name="customer_phone"]');
        if (phoneInput && !phoneInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            phoneInput.focus();
            return false;
        }

        // Check if at least one item is selected
        const checkedItems = document.querySelectorAll('.item-check:checked');
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item');
            return false;
        }

        console.log('Form validation passed. Submitting with:');
        console.log('- Branch ID:', branchSelect.value);
        console.log('- Selected Items:', checkedItems.length);
        console.log('- Customer Phone:', phoneInput.value);

        // Add loading state to form
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-6 w-6 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Order...
            `;
            this.classList.add('form-submitting');
        }
    });
});

/**
 * Toggle item selection when clicking on the item container
 */
function toggleItemSelection(itemId) {
    const checkbox = document.getElementById('item_' + itemId);
    if (checkbox && !checkbox.disabled) {
        checkbox.checked = !checkbox.checked;
        // Trigger the change event manually
        checkbox.dispatchEvent(new Event('change'));
    }
}

/**
 * Initialize organization and branch handling
 */
function initializeLocationHandling() {
    const organizationSelect = document.getElementById('organization_select');
    const branchSelect = document.getElementById('branch_select');
    const isAdmin = {{ isset($isAdmin) ? ($isAdmin ? 'true' : 'false') : 'false' }};

    // Handle organization change
    if (organizationSelect) {
        organizationSelect.addEventListener('change', function() {
            const organizationId = this.value;
            updateBranchOptions(organizationId);
        });
    }

    // Handle branch change
    if (branchSelect) {
        branchSelect.addEventListener('change', function() {
            const branchId = this.value;
            const organizationId = organizationSelect ? organizationSelect.value : null;
            loadMenuItems(branchId, organizationId);
        });
    }

    // Auto-load menu items if branch is pre-selected (especially for admin)
    if (branchSelect && branchSelect.value) {
        console.log('Auto-loading menu items for pre-selected branch:', branchSelect.value);
        const organizationId = organizationSelect ? organizationSelect.value : null;
        loadMenuItems(branchSelect.value, organizationId);
    } else if (isAdmin) {
        // For admin users, check if we have default branch
        const defaultBranchInput = document.getElementById('admin_default_branch');
        if (defaultBranchInput && defaultBranchInput.value) {
            console.log('Loading menu items for admin default branch:', defaultBranchInput.value);
            loadMenuItems(defaultBranchInput.value);
        } else {
            showMenuError('Please select a branch to view menu items');
        }
    } else {
        showMenuError('Please select a branch to view menu items');
    }
}

/**
 * Update branch options based on selected organization
 */
function updateBranchOptions(organizationId) {
    const branchSelect = document.getElementById('branch_select');

    if (!organizationId) {
        // Clear branches except default option
        branchSelect.innerHTML = '<option value="">Choose Branch...</option>';
        clearMenuItems();
        return;
    }

    // Show loading state
    branchSelect.innerHTML = '<option value="">Loading branches...</option>';
    branchSelect.disabled = true;

    fetch(`/api/branches/organization/${organizationId}`)
        .then(response => response.json())
        .then(data => {
            branchSelect.innerHTML = '<option value="">Choose Branch...</option>';

            data.branches.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = branch.name;
                branchSelect.appendChild(option);
            });

            branchSelect.disabled = false;
            clearMenuItems();
        })
        .catch(error => {
            console.error('Error loading branches:', error);
            branchSelect.innerHTML = '<option value="">Error loading branches</option>';
            branchSelect.disabled = false;
        });
}

/**
 * Load menu items for selected branch
 */
function loadMenuItems(branchId, organizationId = null) {
    if (!branchId) {
        clearMenuItems();
        return;
    }

    console.log('Loading menu items for branch:', branchId);
    showMenuLoading();

    // Use API endpoint that works for admin
    const url = `/admin/menu-items/${branchId}?branch_id=${branchId}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('Menu API response:', data);
            if (data.success && data.items) {
                displayMenuItems(data.items);
                console.log(`Loaded ${data.items.length} menu items for branch ${branchId}`);
            } else {
                showMenuError(data.message || 'No menu items found for this branch');
            }
        })
        .catch(error => {
            console.error('Error loading menu items:', error);
            showMenuError('Failed to load menu items. Please try again.');
        });
}

/**
 * Show loading state for menu items
 */
function showMenuLoading() {
    const container = document.getElementById('menu-items-container');
    const loading = document.getElementById('menu-loading');
    const error = document.getElementById('menu-error');

    container.style.display = 'none';
    loading.classList.remove('hidden');
    error.classList.add('hidden');
}

/**
 * Show error state for menu items
 */
function showMenuError(message = 'Failed to load menu items') {
    const container = document.getElementById('menu-items-container');
    const loading = document.getElementById('menu-loading');
    const error = document.getElementById('menu-error');

    container.style.display = 'none';
    loading.classList.add('hidden');
    error.classList.remove('hidden');

    if (message !== 'Failed to load menu items') {
        error.querySelector('p').textContent = message;
    }
}

/**
 * Display menu items
 */
function displayMenuItems(items) {
    const container = document.getElementById('menu-items-container');
    const loading = document.getElementById('menu-loading');
    const error = document.getElementById('menu-error');

    loading.classList.add('hidden');
    error.classList.add('hidden');
    container.style.display = 'block';

    if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">No menu items available for this location.</div>';
        return;
    }

    let html = '';
    items.forEach(item => {
        const isDisabled = (item.display_type === 'stock' && !item.is_available);
        const stockDisplay = item.display_type === 'kot'
            ? '<div class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded mt-1 inline-block">✓ Always Available</div>'
            : (item.is_available && item.current_stock > 0)
                ? `<div class="text-xs text-green-600 font-medium mt-1">In Stock (${item.current_stock})</div>`
                : '<div class="text-xs text-red-600 font-medium mt-1 bg-red-50 px-2 py-1 rounded">❌ Out of Stock</div>';

        const kotBadge = item.display_type === 'kot'
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">KOT Available</span>'
            : '';

        html += `
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:border-blue-300 transition-colors duration-150 ${isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}" onclick="${isDisabled ? '' : 'toggleItemSelection(\'' + item.id + '\')'}">
                <div class="flex items-center">
                    <input class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 item-check"
                        type="checkbox"
                        value="${item.id}"
                        id="item_${item.id}"
                        data-item-id="${item.id}"
                        ${isDisabled ? 'disabled' : ''}
                        onclick="event.stopPropagation();">

                    <label for="item_${item.id}" class="ml-3 flex-1 ${isDisabled ? 'cursor-not-allowed' : 'cursor-pointer'}">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-800">${item.name}</span>
                                ${kotBadge}
                            </div>
                            <span class="text-blue-600 font-semibold">LKR ${parseFloat(item.price).toFixed(2)}</span>
                        </div>
                        ${stockDisplay}
                    </label>

                    <div class="flex items-center border border-gray-300 rounded overflow-hidden touch-friendly-controls">
                        <button type="button"
                            class="qty-decrease w-12 h-12 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-r border-gray-300"
                            data-item-id="${item.id}"
                            onclick="event.stopPropagation();"
                            disabled>−</button>
                        <input type="number"
                            min="1"
                            max="${item.display_type === 'stock' ? (item.current_stock || 99) : 99}"
                            value="1"
                            class="item-qty w-16 h-12 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 touch-manipulation"
                            data-item-id="${item.id}"
                            onclick="event.stopPropagation();"
                            disabled
                            readonly>
                        <button type="button"
                            class="qty-increase w-12 h-12 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-l border-gray-300"
                            data-item-id="${item.id}"
                            onclick="event.stopPropagation();"
                            disabled>+</button>
                    </div>
                </div>
            </div>
        `;
    });
    });

    container.innerHTML = html;

    // Reinitialize event handlers for new items
    initializeQuantityControls();
    initializeItemCheckboxes();
}
                            value="1"
                            class="item-qty w-16 h-12 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 touch-manipulation"
                            data-item-id="${item.id}"
                            disabled
                            readonly>
                        <button type="button"
                            class="qty-increase w-12 h-12 bg-green-50 hover:bg-green-100 active:bg-green-200 text-green-600 text-2xl font-bold flex items-center justify-center touch-manipulation transition-all duration-150 border-l border-gray-300"
                            data-item-id="${item.id}"
                            disabled>+</button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Reinitialize event handlers for new items
    initializeQuantityControls();
    initializeItemCheckboxes();
}

/**
 * Clear menu items display
 */
function clearMenuItems() {
    const container = document.getElementById('menu-items-container');
    const loading = document.getElementById('menu-loading');
    const isAdmin = {{ isset($isAdmin) ? ($isAdmin ? 'true' : 'false') : 'false' }};

    if (loading) loading.classList.add('hidden');
    if (container) {
        container.style.display = 'block';

        const message = isAdmin
            ? 'Please select an organization and branch to view menu items.'
            : 'Please select a restaurant location to view menu items.';

        container.innerHTML = `<div class="text-center py-8 text-gray-500">${message}</div>`;
    }
}

/**
 * Show menu error state
 */
function showMenuError(message = 'Error loading menu items. Please try again.') {
    const container = document.getElementById('menu-items-container');
    const loading = document.getElementById('menu-loading');

    loading.classList.add('hidden');
    container.style.display = 'block';
    container.innerHTML = `<div class="text-center py-8 text-red-500">${message}</div>`;
}

/**
 * Initialize item checkbox event handlers
 */
function initializeItemCheckboxes() {
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            const plusBtn = document.querySelector('.qty-increase[data-item-id="' + itemId + '"]');
            const minusBtn = document.querySelector('.qty-decrease[data-item-id="' + itemId + '"]');
            const itemContainer = this.closest('.bg-white');

            if (this.checked) {
                // Enable controls
                qtyInput.disabled = false;
                qtyInput.removeAttribute('readonly');
                plusBtn.disabled = false;
                minusBtn.disabled = false;                                // Set proper form field names for Laravel validation
                                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');

                                // Create hidden input for menu_item_id to ensure it's submitted with form
                                let hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                                if (!hiddenInput) {
                                    hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'items[' + itemId + '][menu_item_id]';
                                    hiddenInput.value = itemId;
                                    hiddenInput.className = 'item-hidden-' + itemId;
                                    itemContainer.appendChild(hiddenInput);
                                }

                // Visual feedback - highlight selected item
                itemContainer.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');

                updateButtonStates(itemId, qtyInput.value);
                console.log('✅ Item selected:', itemId, 'Quantity:', qtyInput.value);
                updateOrderSummary();
            } else {
                // Disable controls
                qtyInput.disabled = true;
                qtyInput.setAttribute('readonly', 'readonly');
                plusBtn.disabled = true;
                minusBtn.disabled = true;

                // Remove form field names
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;

                // Remove hidden input
                const hiddenInput = itemContainer.querySelector('.item-hidden-' + itemId);
                if (hiddenInput) {
                    hiddenInput.remove();
                }

                // Remove visual feedback
                itemContainer.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');

                console.log('❌ Item deselected:', itemId);
                updateOrderSummary();
            }
        });
    });
}

/**
 * Initialize quantity controls for takeaway orders with enhanced touch support
 */
function initializeQuantityControls() {
    console.log('🔢 Initializing enhanced touch-friendly quantity controls...');

    // Handle quantity increase buttons with enhanced touch feedback
    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-increase')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.qty-increase');
            const itemId = button.getAttribute('data-item-id');
            const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);

            if (qtyInput && !qtyInput.disabled && !button.disabled) {
                const currentValue = parseInt(qtyInput.value) || 1;
                const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;

                if (currentValue < maxValue) {
                    qtyInput.value = currentValue + 1;
                    updateButtonStates(itemId, qtyInput.value);
                    console.log('➕ Quantity increased for item', itemId, 'to', qtyInput.value);
                    if (typeof updateCart === 'function') updateCart();
                    updateOrderSummary();

                    // Enhanced visual feedback for touch devices
                    button.style.transform = 'scale(0.9)';
                    button.style.backgroundColor = '#22c55e';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                        button.style.backgroundColor = '';
                    }, 150);

                    // Haptic feedback for mobile devices (if supported)
                    if ('vibrate' in navigator) {
                        navigator.vibrate(50);
                    }
                }
            }
        }
    });

    // Handle quantity decrease buttons with enhanced touch feedback
    document.addEventListener('click', function(e) {
        if (e.target.closest('.qty-decrease')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.closest('.qty-decrease');
            const itemId = button.getAttribute('data-item-id');
            const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);

            if (qtyInput && !qtyInput.disabled && !button.disabled) {
                const currentValue = parseInt(qtyInput.value) || 1;

                if (currentValue > 1) {
                    qtyInput.value = currentValue - 1;
                    updateButtonStates(itemId, qtyInput.value);
                    console.log('➖ Quantity decreased for item', itemId, 'to', qtyInput.value);
                    if (typeof updateCart === 'function') updateCart();
                    updateOrderSummary();

                    // Enhanced visual feedback for touch devices
                    button.style.transform = 'scale(0.9)';
                    button.style.backgroundColor = '#ef4444';
                    setTimeout(() => {
                        button.style.transform = 'scale(1)';
                        button.style.backgroundColor = '';
                    }, 150);

                    // Haptic feedback for mobile devices (if supported)
                    if ('vibrate' in navigator) {
                        navigator.vibrate(50);
                    }
                }
            }
        }
    });

    // Prevent manual input changes since we want touch-only interaction
    document.addEventListener('keydown', function(e) {
        if (e.target.classList.contains('item-qty')) {
            // Allow only Tab, Enter, and arrow keys for accessibility
            const allowedKeys = ['Tab', 'Enter', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
            if (!allowedKeys.includes(e.key)) {
                e.preventDefault();
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty')) {
            e.preventDefault(); // Prevent manual typing
        }
    });

    // Handle blur event to ensure valid values
    document.addEventListener('blur', function(e) {
        if (e.target.classList.contains('item-qty')) {
            const qtyInput = e.target;
            let value = parseInt(qtyInput.value) || 1;
            const maxValue = parseInt(qtyInput.getAttribute('max')) || 99;

            // Ensure value is within bounds
            if (value < 1) value = 1;
            if (value > maxValue) value = maxValue;

            qtyInput.value = value;
            const itemId = qtyInput.getAttribute('data-item-id');
            updateButtonStates(itemId, value);
            if (typeof updateCart === 'function') updateCart();
        }
    });
}

/**
 * Update button states based on quantity value
 */
function updateButtonStates(itemId, value) {
    const decreaseBtn = document.querySelector(`.qty-decrease[data-item-id="${itemId}"]`);
    const increaseBtn = document.querySelector(`.qty-increase[data-item-id="${itemId}"]`);
    const qtyInput = document.querySelector(`.item-qty[data-item-id="${itemId}"]`);

    const maxValue = parseInt(qtyInput?.getAttribute('max')) || 99;

    if (decreaseBtn) {
        decreaseBtn.disabled = value <= 1 || qtyInput?.disabled;
    }
    if (increaseBtn) {
        increaseBtn.disabled = value >= maxValue || qtyInput?.disabled;
    }
}

/**
 * Update the order summary display
 */
function updateOrderSummary() {
    const selectedItems = document.querySelectorAll('.item-check:checked');
    const summaryContainer = document.getElementById('order-summary');
    const selectedItemsContainer = document.getElementById('selected-items');
    const totalItemsSpan = document.getElementById('total-items');

    if (selectedItems.length === 0) {
        summaryContainer.style.display = 'none';
        return;
    }

    summaryContainer.style.display = 'block';
    selectedItemsContainer.innerHTML = '';

    let totalItems = 0;

    selectedItems.forEach(function(checkbox) {
        const itemId = checkbox.getAttribute('data-item-id');
        const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
        const label = document.querySelector('label[for="item_' + itemId + '"]');
        const itemName = label.querySelector('.font-medium').textContent;
        const quantity = parseInt(qtyInput.value) || 1;

        totalItems += quantity;

        const summaryItem = document.createElement('div');
        summaryItem.className = 'flex justify-between items-center text-sm';
        summaryItem.innerHTML = `
            <span>${itemName}</span>
            <span class="font-medium">Qty: ${quantity}</span>
        `;
        selectedItemsContainer.appendChild(summaryItem);
    });

    totalItemsSpan.textContent = totalItems;
}
</script>

<style>
/* Enhanced touch-friendly controls */
.touch-friendly-controls {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.touch-friendly-controls button {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    cursor: pointer;
    transition: all 0.15s ease;
}

.touch-friendly-controls button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #f3f4f6 !important;
    color: #9ca3af !important;
}

.touch-friendly-controls button:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.touch-friendly-controls button:not(:disabled):active {
    transform: translateY(0);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.touch-friendly-controls input[type="number"] {
    -moz-appearance: textfield;
    -webkit-appearance: none;
    appearance: none;
    background: #fff;
    border: none;
    font-weight: 600;
    color: #1f2937;
}

.touch-friendly-controls input[type="number"]::-webkit-outer-spin-button,
.touch-friendly-controls input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.touch-friendly-controls input[type="number"]:disabled {
    background-color: #f9fafb;
    color: #6b7280;
}

.touch-friendly-controls input[type="number"]:focus {
    outline: none;
    box-shadow: inset 0 0 0 2px #3b82f6;
}

/* Responsive design for mobile */
@media (max-width: 768px) {
    .touch-friendly-controls button {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }

    .touch-friendly-controls input[type="number"] {
        width: 64px;
        height: 48px;
        font-size: 1.125rem;
    }
}

/* Loading state for form submission */
.form-submitting {
    opacity: 0.6;
    pointer-events: none;
}

/* Item selection highlight */
.item-check:checked + label {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

/* Smooth animations */
.transition-all {
    transition: all 0.15s ease;
}

/* Touch feedback */
@media (hover: none) and (pointer: coarse) {
    .touch-friendly-controls button:not(:disabled):active {
        transform: scale(0.95);
    }
}
</style>
@endsection
