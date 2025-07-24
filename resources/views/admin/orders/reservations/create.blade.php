@extends('layouts.admin')

@section('title', 'Create Reservation Order')
@section('header-title', 'Create Reservation Order')

@section('content')
<div class="mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <!-- Reservation Info -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Reservation Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <p class="mt-1">{{ $reservation->customer_name }}</p>
                    @if($reservation->customer_phone)
                        <p class="text-sm text-gray-500">{{ $reservation->customer_phone }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date & Time</label>
                    <p class="mt-1">{{ $reservation->reservation_date->format('M d, Y') }}</p>
                    <p class="text-sm text-gray-500">{{ $reservation->reservation_time->format('h:i A') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Branch</label>
                    <p class="mt-1">{{ $reservation->branch->name }}</p>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <form id="orderForm" action="{{ route('admin.orders.reservations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
            <input type="hidden" name="branch_id" value="{{ $reservation->branch_id }}">

            <!-- Menu Items Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Items</h3>
                
                <!-- Menu Categories Tabs -->
                <div class="mb-4">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                            @foreach($menuCategories as $category)
                            <button type="button"
                                    onclick="showCategory('{{ $category->id }}')"
                                    class="category-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    data-category="{{ $category->id }}">
                                {{ $category->name }}
                            </button>
                            @endforeach
                        </nav>
                    </div>
                </div>

                <!-- Menu Items Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($menuCategories as $category)
                    <div class="menu-category hidden" id="category-{{ $category->id }}">
                        @foreach($category->menuItems as $item)
                        <div class="border rounded-lg p-4 menu-item" data-item-id="{{ $item->id }}" data-price="{{ $item->price }}">
                            <h4 class="font-semibold">{{ $item->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $item->description }}</p>
                            <p class="text-lg font-bold mt-2">LKR {{ number_format($item->price, 2) }}</p>
                            <div class="mt-3 flex items-center gap-2">
                                <button type="button" onclick="decrementQuantity('{{ $item->id }}')" class="bg-gray-200 px-3 py-1 rounded">-</button>
                                <input type="number" id="quantity-{{ $item->id }}" name="items[{{ $item->id }}][quantity]" 
                                       value="0" min="0" class="w-16 text-center border rounded px-2 py-1">
                                <button type="button" onclick="incrementQuantity('{{ $item->id }}')" class="bg-gray-200 px-3 py-1 rounded">+</button>
                            </div>
                            <div class="mt-2">
                                <input type="text" name="items[{{ $item->id }}][special_instructions]" 
                                       placeholder="Special instructions" class="w-full border rounded px-2 py-1">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                <div id="selectedItems" class="mb-4">
                    <!-- Selected items will be displayed here -->
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span id="subtotal">LKR 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax (10%):</span>
                        <span id="tax">LKR 0.00</span>
                        <input type="hidden" name="tax_amount" id="taxAmount" value="0">
                    </div>
                    <div class="flex justify-between">
                        <span>Discount:</span>
                        <div class="flex items-center gap-2">
                            <span>LKR</span>
                            <input type="number" name="discount_amount" id="discountAmount" value="0" 
                                   min="0" step="0.01" class="w-24 border rounded px-2 py-1"
                                   onchange="updateTotal()">
                        </div>
                    </div>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total:</span>
                        <span id="total">LKR 0.00</span>
                        <input type="hidden" name="total_amount" id="totalAmount" value="0">
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" class="w-full border rounded px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" class="w-full border rounded px-3 py-2" rows="3"
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
                    Create Order
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let selectedItems = {};
let menuItems = @json($menuItems);
let menuItemsMap = menuItems.reduce((acc, item) => {
    acc[item.id] = item;
    return acc;
}, {});

function showCategory(categoryId) {
    // Hide all categories
    document.querySelectorAll('.menu-category').forEach(el => el.classList.add('hidden'));
    // Show selected category
    document.getElementById(`category-${categoryId}`).classList.remove('hidden');
    // Update tab styles
    document.querySelectorAll('.category-tab').forEach(el => {
        if (el.dataset.category === categoryId) {
            el.classList.add('border-primary-500', 'text-primary-600');
            el.classList.remove('border-transparent', 'text-gray-500');
        } else {
            el.classList.remove('border-primary-500', 'text-primary-600');
            el.classList.add('border-transparent', 'text-gray-500');
        }
    });
}

function incrementQuantity(itemId) {
    const input = document.getElementById(`quantity-${itemId}`);
    input.value = parseInt(input.value) + 1;
    updateSelectedItems();
}

function decrementQuantity(itemId) {
    const input = document.getElementById(`quantity-${itemId}`);
    if (parseInt(input.value) > 0) {
        input.value = parseInt(input.value) - 1;
        updateSelectedItems();
    }
}

function updateSelectedItems() {
    let subtotal = 0;
    const selectedItemsDiv = document.getElementById('selectedItems');
    selectedItemsDiv.innerHTML = '';

    // Collect all selected items
    document.querySelectorAll('[id^="quantity-"]').forEach(input => {
        const itemId = input.id.replace('quantity-', '');
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const menuItem = menuItemsMap[itemId];
            const itemTotal = quantity * menuItem.price;
            subtotal += itemTotal;

            // Add item to display
            selectedItemsDiv.innerHTML += `
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <span class="font-medium">${menuItem.name}</span>
                        <span class="text-sm text-gray-500">× ${quantity}</span>
                    </div>
                    <span>LKR ${itemTotal.toFixed(2)}</span>
                </div>
            `;

            // Update hidden inputs for form submission
            const formGroup = document.querySelector(`input[name="items[${itemId}][quantity]"]`);
            if (formGroup) {
                formGroup.value = quantity;
            }
        }
    });

    updateTotal(subtotal);
}

function updateTotal(subtotal = null) {
    if (subtotal === null) {
        subtotal = calculateSubtotal();
    }

    const tax = subtotal * 0.10; // 10% tax
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = subtotal + tax - discount;

    document.getElementById('subtotal').textContent = `LKR ${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `LKR ${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `LKR ${total.toFixed(2)}`;
    
    // Update hidden inputs
    document.getElementById('taxAmount').value = tax.toFixed(2);
    document.getElementById('totalAmount').value = total.toFixed(2);
}

function calculateSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('[id^="quantity-"]').forEach(input => {
        const itemId = input.id.replace('quantity-', '');
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const menuItem = menuItemsMap[itemId];
            subtotal += quantity * menuItem.price;
        }
    });
    return subtotal;
}

// Show first category by default
const firstCategoryTab = document.querySelector('.category-tab');
if (firstCategoryTab) {
    showCategory(firstCategoryTab.dataset.category);
}

// Initialize form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Check if at least one item is selected
    const hasItems = document.querySelectorAll('[id^="quantity-"]').some(input => parseInt(input.value) > 0);
    if (!hasItems) {
        alert('Please select at least one menu item.');
        return;
    }

    // Submit the form if validation passes
    this.submit();
});
</script>
@endpush
@endsection
