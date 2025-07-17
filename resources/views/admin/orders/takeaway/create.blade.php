@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Create Takeaway Order (Admin)</h2>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <form method="POST" action="{{ route('admin.orders.takeaway.store') }}" class="space-y-6" id="admin-takeaway-form">
                @csrf

                <!-- Admin Context Fields -->
                @if(isset($admin))
                    <input type="hidden" name="is_admin" value="1">
                    @if(isset($defaultBranch))
                        <input type="hidden" name="branch_id" value="{{ $defaultBranch }}" id="hidden_branch_id">
                    @endif
                    @if(isset($defaultOrganization))
                        <input type="hidden" name="organization_id" value="{{ $defaultOrganization }}">
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

                    <div class="space-y-6">

                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Information</h3>


                            <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i>
                                    <span class="font-semibold text-blue-800">Takeaway Order (Admin)</span>
                                </div>
                                <p class="text-blue-700 text-sm mt-1">This order is for pickup/delivery</p>
                                <input type="hidden" name="order_type" value="takeaway_walk_in_demand">
                            </div>

                            <!-- Admin Information -->
                            @if(isset($admin))
                            <div class="mb-4 bg-green-50 p-3 rounded-lg border border-green-200">
                                <h4 class="font-semibold text-green-800 mb-2">Admin Information</h4>
                                <div class="text-sm text-green-700">
                                    @if($admin->is_super_admin)
                                        <p><strong>Super Admin:</strong> {{ $admin->name }}</p>
                                        @if(isset($organizations) && $organizations->count() > 1)
                                        <div class="mt-2">
                                            <label class="block text-sm font-medium text-green-800 mb-1">Select Organization</label>
                                            <select name="organization_id" id="organization_select" class="w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-1 px-2 border text-sm">
                                                <option value="">Choose Organization...</option>
                                                @foreach($organizations as $organization)
                                                    <option value="{{ $organization->id }}" {{ (isset($defaultOrganization) && $defaultOrganization == $organization->id) ? 'selected' : '' }}>
                                                        {{ $organization->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    @else
                                        @if(isset($admin->branch))
                                        <p><strong>Branch:</strong> {{ $admin->branch->name }}</p>
                                        <p><strong>Organization:</strong> {{ $admin->branch->organization->name }}</p>
                                        @endif
                                        <p><strong>Admin:</strong> {{ $admin->name }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Branch Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Branch</label>
                                <select name="branch_id" id="branch_select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border" required>
                                    <option value="">Choose Branch...</option>
                                    @if(isset($branches))
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                data-organization="{{ $branch->organization_id ?? $branch->organization->id ?? '' }}"
                                                data-phone="{{ $branch->phone ?? '' }}"
                                                {{ (isset($defaultBranch) && $defaultBranch == $branch->id) ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                                @if(isset($branch->organization))
                                                    ({{ $branch->organization->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                                <input type="datetime-local"
                                       name="order_time"
                                       id="order_time"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                       value="{{ old('order_time', now()->addMinutes(30)->format('Y-m-d\TH:i')) }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       required>
                            </div>

                            <!-- Special Instructions -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                <textarea name="special_instructions"
                                          id="special_instructions"
                                          rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                          placeholder="Any special instructions for the order...">{{ old('special_instructions') }}</textarea>
                            </div>
                        </div>

                        <!-- Customer Information Section -->
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Customer Information</h3>

                            <div class="grid grid-cols-1 gap-4">
                                <!-- Customer Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                                    <input type="text"
                                           name="customer_name"
                                           id="customer_name"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                           value="{{ old('customer_name', isset($defaultCustomerName) ? $defaultCustomerName : 'Customer with Order #PENDING') }}"
                                           placeholder="Enter customer name"
                                           required>
                                    <p class="text-sm text-gray-500 mt-1">Admin mode: Customer name will be auto-generated with order number</p>
                                </div>

                                <!-- Customer Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Phone</label>
                                    <input type="tel"
                                           name="customer_phone"
                                           id="customer_phone"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border"
                                           value="{{ old('customer_phone', isset($defaultPhone) ? $defaultPhone : '0000000000') }}"
                                           placeholder="Enter customer phone number"
                                           required>
                                    <p class="text-sm text-gray-500 mt-1">Admin mode: Defaults to branch phone number</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Menu Items -->
                    <div>
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Select Menu Items</h3>

                            <!-- Menu Items Loading State -->
                            <div id="menu-items-loading" class="text-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                <p class="text-gray-600 mt-2">Loading menu items...</p>
                            </div>

                            <!-- No Branch Selected -->
                            <div id="no-branch-selected" class="text-center py-8 text-gray-500">
                                <i class="fas fa-info-circle text-blue-500 text-2xl mb-2"></i>
                                <p>Please select a branch to view menu items.</p>
                            </div>

                            <!-- Menu Items Container -->
                            <div id="menu-items-container" class="space-y-4" style="display: none;">
                                <!-- Menu items will be loaded here via JavaScript -->
                            </div>

                            <!-- No Menu Items Message -->
                            <div id="no-menu-items" class="text-center py-8 text-gray-500" style="display: none;">
                                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                                <p>No active menu items available for this branch.</p>
                                <p class="text-sm">Please contact the administrator.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary and Submit -->
                <div class="border-t pt-6">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- Order Summary -->
                        <div class="lg:flex-1">
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Order Summary</h3>

                                <div id="order-summary" class="space-y-2">
                                    <div class="text-gray-500 text-center py-4">
                                        <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                                        <p>No items selected</p>
                                    </div>
                                </div>

                                <div class="border-t pt-4 mt-4">
                                    <div class="flex justify-between items-center text-sm text-gray-600 mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal-amount">LKR 0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm text-gray-600 mb-2">
                                        <span>Tax (10%):</span>
                                        <span id="tax-amount">LKR 0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-lg font-bold text-gray-900 border-t pt-2">
                                        <span>Total:</span>
                                        <span id="total-amount">LKR 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="lg:w-64">
                            <button type="submit"
                                    id="place-order-btn"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                <i class="fas fa-check mr-2"></i>
                                Place Order
                            </button>

                            <p class="text-sm text-gray-500 mt-2 text-center">
                                Please select at least one menu item
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden items input container -->
<div id="items-inputs" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchId = {{ $currentBranch->id }};
    const menuItemsContainer = document.getElementById('menu-items-container');
    const loadingDiv = document.getElementById('menu-items-loading');
    const noMenuDiv = document.getElementById('no-menu-items');
    const orderSummary = document.getElementById('order-summary');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const itemsInputsContainer = document.getElementById('items-inputs');

    let selectedItems = {};

    // Load menu items for the current branch
    function loadMenuItems() {
        loadingDiv.style.display = 'block';
        menuItemsContainer.style.display = 'none';
        noMenuDiv.style.display = 'none';

        fetch(`/api/menu-items/branch/${branchId}/active`)
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';

                if (data.success && data.items && data.items.length > 0) {
                    renderMenuItems(data.items);
                    menuItemsContainer.style.display = 'block';
                } else {
                    noMenuDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading menu items:', error);
                loadingDiv.style.display = 'none';
                noMenuDiv.style.display = 'block';
            });
    }

    // Render menu items
    function renderMenuItems(items) {
        menuItemsContainer.innerHTML = '';

        items.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors';
            itemDiv.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${item.name}</h4>
                        <p class="text-sm text-gray-600">${item.description || 'No description available'}</p>
                        <p class="text-sm text-blue-600 font-medium">LKR ${parseFloat(item.price).toFixed(2)}</p>
                        ${item.current_stock !== undefined ? `<p class="text-xs text-gray-500">Stock: ${item.current_stock}</p>` : ''}
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button type="button" class="quantity-btn minus-btn bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-2 rounded" data-item-id="${item.id}" disabled>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display font-medium px-3 py-1 bg-gray-50 rounded min-w-12 text-center" data-item-id="${item.id}">0</span>
                        <button type="button" class="quantity-btn plus-btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded" data-item-id="${item.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;
            menuItemsContainer.appendChild(itemDiv);
        });

        // Add event listeners for quantity buttons
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.itemId;
                const isPlus = this.classList.contains('plus-btn');
                updateQuantity(itemId, isPlus ? 1 : -1);
            });
        });
    }

    // Update item quantity
    function updateQuantity(itemId, change) {
        if (!selectedItems[itemId]) {
            selectedItems[itemId] = { quantity: 0 };
        }

        selectedItems[itemId].quantity = Math.max(0, selectedItems[itemId].quantity + change);

        if (selectedItems[itemId].quantity === 0) {
            delete selectedItems[itemId];
        }

        updateUI(itemId);
        updateOrderSummary();
        updateSubmitButton();
    }

    // Update UI for specific item
    function updateUI(itemId) {
        const quantityDisplay = document.querySelector(`.quantity-display[data-item-id="${itemId}"]`);
        const minusBtn = document.querySelector(`.minus-btn[data-item-id="${itemId}"]`);

        const quantity = selectedItems[itemId]?.quantity || 0;
        quantityDisplay.textContent = quantity;
        minusBtn.disabled = quantity === 0;
    }

    // Update order summary
    function updateOrderSummary() {
        const hasItems = Object.keys(selectedItems).length > 0;

        if (!hasItems) {
            orderSummary.innerHTML = `
                <div class="text-gray-500 text-center py-4">
                    <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                    <p>No items selected</p>
                </div>
            `;
            updateTotals(0, 0, 0);
            return;
        }

        // Get item prices from the rendered menu items
        let summaryHTML = '';
        let subtotal = 0;

        Object.keys(selectedItems).forEach(itemId => {
            const item = selectedItems[itemId];
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`).closest('.bg-white');
            const itemName = itemElement.querySelector('h4').textContent;
            const itemPriceText = itemElement.querySelector('.text-blue-600').textContent;
            const itemPrice = parseFloat(itemPriceText.replace('LKR ', ''));
            const itemTotal = itemPrice * item.quantity;

            subtotal += itemTotal;

            summaryHTML += `
                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                    <div>
                        <span class="font-medium">${itemName}</span>
                        <span class="text-gray-500 text-sm ml-2">×${item.quantity}</span>
                    </div>
                    <span class="font-medium">LKR ${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });

        orderSummary.innerHTML = summaryHTML;

        const tax = subtotal * 0.10;
        const total = subtotal + tax;
        updateTotals(subtotal, tax, total);

        // Update hidden form inputs
        updateFormInputs();
    }

    // Update totals display
    function updateTotals(subtotal, tax, total) {
        document.getElementById('subtotal-amount').textContent = `LKR ${subtotal.toFixed(2)}`;
        document.getElementById('tax-amount').textContent = `LKR ${tax.toFixed(2)}`;
        document.getElementById('total-amount').textContent = `LKR ${total.toFixed(2)}`;
    }

    // Update submit button state
    function updateSubmitButton() {
        const hasItems = Object.keys(selectedItems).length > 0;
        placeOrderBtn.disabled = !hasItems;

        const helpText = placeOrderBtn.nextElementSibling;
        if (hasItems) {
            helpText.textContent = 'Ready to place order';
            helpText.className = 'text-sm text-green-600 mt-2 text-center';
        } else {
            helpText.textContent = 'Please select at least one menu item';
            helpText.className = 'text-sm text-gray-500 mt-2 text-center';
        }
    }

    // Update form inputs
    function updateFormInputs() {
        itemsInputsContainer.innerHTML = '';

        Object.keys(selectedItems).forEach((itemId, index) => {
            const item = selectedItems[itemId];

            const menuItemInput = document.createElement('input');
            menuItemInput.type = 'hidden';
            menuItemInput.name = `items[${index}][menu_item_id]`;
            menuItemInput.value = itemId;

            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = `items[${index}][quantity]`;
            quantityInput.value = item.quantity;

            itemsInputsContainer.appendChild(menuItemInput);
            itemsInputsContainer.appendChild(quantityInput);
        });

        // Append to form
        document.querySelector('form').appendChild(itemsInputsContainer);
    }

    // Load menu items on page load
    loadMenuItems();
});
</script>
@endsection
