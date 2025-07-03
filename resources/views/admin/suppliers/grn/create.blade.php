@extends('layouts.admin')

@section('header-title', 'Create Goods Received Note')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Goods Received Note</h2>
                    <p class="text-sm text-gray-500">Record items received from suppliers</p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.grn.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
                </a>
            </div>

            <!-- Form Container -->
            <form id="grnForm" action="{{ route('admin.grn.store') }}" method="POST" class="p-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                        <h3 class="font-medium mb-2">Validation Errors</h3>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Organization Selection for Super Admin -->
                @if (Auth::guard('admin')->user()->is_super_admin)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-building text-blue-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-blue-900">Organization Selection</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Target Organization <span class="text-red-500">*</span>
                                </label>
                                <select name="organization_id" id="organization_id" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Organization</option>
                                    @foreach ($organizations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-sm text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    GRN will be created for the selected organization
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Display current organization for non-super admins -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-building text-gray-600 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-gray-700">Organization</h3>
                                <p class="text-gray-900 font-semibold">
                                    {{ Auth::guard('admin')->user()->organization->name }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Supplier and Branch Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <div class="relative">
                            <select name="supplier_id" id="supplier_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->supplier_id }})
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <div class="relative">
                            <select name="branch_id" id="branch_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                </div>

                <!-- Dates and Reference Numbers Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date
                            *</label>
                        <div class="relative">
                            <input type="date" name="received_date" id="received_date"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                value="{{ old('received_date', date('Y-m-d')) }}" required style="appearance: none;">
                        </div>
                    </div>

                    <div>
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note
                            No.</label>
                        <input type="text" name="delivery_note_number" id="delivery_note_number"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="{{ old('delivery_note_number') }}">
                    </div>

                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice No.</label>
                        <input type="text" name="invoice_number" id="invoice_number"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="{{ old('invoice_number') }}">
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">GRN Items</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Available categories:
                                @php
                                    $allowedCategories = ['Buy & sell', 'Ingredients']; // Keep in sync with controller
                                    echo implode(', ', $allowedCategories);
                                @endphp
                            </p>
                        </div>
                        <button type="button" id="addItemBtn"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>


                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Item *</th>
                                    {{-- <th class="px-4 py-3">Batch No</th> --}}
                                    <th class="px-4 py-3">Received Qty *</th>
                                    <th class="px-4 py-3">Free Qty</th>
                                    <th class="px-4 py-3">Price *</th>
                                    <th class="px-4 py-3">Discount (%)</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                @php
                                    // Always preserve old input if validation fails or page is refreshed
                                    $oldItems = collect(
                                        old('items', [
                                            [
                                                'item_id' => '',
                                                // 'batch_no' => '',
                                                // 'ordered_quantity' => '',
                                                'received_quantity' => '',
                                                'buying_price' => '',
                                                'discount_received' => 0,
                                                'free_received_quantity' => 0,
                                            ],
                                        ]),
                                    )
                                        ->map(function ($item) use ($items) {
                                            // If item_id is set, fetch the latest buying_price from $items (ItemMaster) only if not already filled
                                            if (
                                                !empty($item['item_id']) &&
                                                (!isset($item['buying_price']) || $item['buying_price'] === '')
                                            ) {
                                                $itemMaster = collect($items)->firstWhere('id', $item['item_id']);
                                                if ($itemMaster) {
                                                    $item['buying_price'] = $itemMaster->buying_price;
                                                }
                                            }
                                            $item['discount_received'] = $item['discount_received'] ?? 0;
                                            $item['free_received_quantity'] = $item['free_received_quantity'] ?? 0;
                                            return $item;
                                        })
                                        ->toArray();
                                @endphp

                                @if (count($oldItems) === 0)
                                    <tr class="placeholder-row">
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Please add at least one item to the GRN.
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($oldItems as $index => $item)
                                        <tr class="item-row border-b bg-white hover:bg-gray-50"
                                            data-index="{{ $index }}">
                                            <td class="px-4 py-3">
                                                <select name="items[{{ $index }}][item_id]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                                                    required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($items as $itemOption)
                                                        <option value="{{ $itemOption->id }}"
                                                            {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}
                                                            data-price="{{ $itemOption->buying_price }}"
                                                            data-category="{{ $itemOption->category->name ?? 'N/A' }}">
                                                            {{ $itemOption->item_code }} - {{ $itemOption->name }}
                                                            ({{ $itemOption->category->name ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="items[{{ $index }}][item_code]"
                                                    value="{{ $item['item_code'] ?? '' }}">
                                            </td>
                                            {{-- <td class="px-4 py-3">
                                        <input type="text" name="items[{{ $index }}][batch_no]"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent batch-no"
                                            value="{{ $item['batch_no'] }}">
                                    </td> --}}
                                            <td class="px-4 py-3">
                                                <input type="number"
                                                    name="items[{{ $index }}][received_quantity]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty"
                                                    min="0.01" step="0.01"
                                                    value="{{ $item['received_quantity'] }}" required>
                                                <input type="hidden" name="items[{{ $index }}][ordered_quantity]"
                                                    class="ordered-qty" value="{{ $item['received_quantity'] }}">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number"
                                                    name="items[{{ $index }}][free_received_quantity]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent free-qty"
                                                    min="0" step="0.01"
                                                    value="{{ $item['free_received_quantity'] ?? 0 }}">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" name="items[{{ $index }}][buying_price]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                                                    min="0" step="0.01" value="{{ $item['buying_price'] }}"
                                                    required>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number"
                                                    name="items[{{ $index }}][discount_received]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent discount-received"
                                                    min="0" max="100" step="0.01"
                                                    value="{{ $item['discount_received'] ?? 0 }}">
                                            </td>
                                            <td class="px-4 py-3 font-medium item-total">
                                                @php
                                                    $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                        ? (float) $item['received_quantity']
                                                        : 0;
                                                    $price = is_numeric($item['buying_price'] ?? 0)
                                                        ? (float) $item['buying_price']
                                                        : 0;
                                                    $discountPercent = is_numeric($item['discount_received'] ?? 0)
                                                        ? (float) $item['discount_received']
                                                        : 0;
                                                    $discountAmount = $quantity * $price * ($discountPercent / 100);
                                                    echo number_format($quantity * $price - $discountAmount, 2);
                                                @endphp
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button"
                                                    class="remove-item-btn text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold text-gray-900">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Items:</td>
                                    <td id="total-items" class="px-4 py-3 font-bold">
                                        {{ count($oldItems) }}
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Before Discount:</td>
                                    <td id="total-before-discount" class="px-4 py-3 font-bold">
                                        @php
                                            $totalBeforeDiscount = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $totalBeforeDiscount += $quantity * $price;
                                            }
                                            echo number_format($totalBeforeDiscount, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Discount (Items):</td>
                                    <td id="total-discount-items" class="px-4 py-3 font-bold">
                                        @php
                                            $totalDiscountItems = 0;
                                            foreach ($oldItems as $item) {
                                                $quantity = is_numeric($item['received_quantity'] ?? 0)
                                                    ? (float) $item['received_quantity']
                                                    : 0;
                                                $price = is_numeric($item['buying_price'] ?? 0)
                                                    ? (float) $item['buying_price']
                                                    : 0;
                                                $discountPercent = is_numeric($item['discount_received'] ?? 0)
                                                    ? (float) $item['discount_received']
                                                    : 0;
                                                $totalDiscountItems += $quantity * $price * ($discountPercent / 100);
                                            }
                                            echo number_format($totalDiscountItems, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">
                                        Grand Discount (Total Bill)
                                        <input type="number" name="grand_discount" id="grand-discount-input"
                                            class="ml-2 w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500"
                                            min="0" max="100" step="0.01"
                                            value="{{ old('grand_discount', 0) }}" placeholder="%">
                                        <span class="text-xs text-gray-500 ml-1">%</span>
                                    </td>
                                    <td id="grand-discount-amount" class="px-4 py-3 font-bold">0.00</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Grand Total:</td>
                                    <td id="grand-total" class="px-4 py-3 font-bold">
                                        @php
                                            $grandTotal = $totalBeforeDiscount - $totalDiscountItems;
                                            echo number_format($grandTotal, 2);
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-8">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        rows="3" maxlength="500" placeholder="Add any special instructions or notes for this GRN...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit" id="openConfirmModalBtn"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i> Create GRN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Modal for GRN Creation -->
    {{--
    <div id="grnConfirmModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-xl mr-3">
                    <i class="fas fa-exclamation-triangle text-green-600"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">Confirm GRN Creation</h2>
            </div>
            <p class="mb-6 text-gray-700">
                Are you sure you want to create this GRN? This action will record the goods received.
            </p>
            <div class="flex gap-3 mt-6">
                <button id="confirmCreateGrnBtn"
                    class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Yes, Create GRN
                </button>
                <button type="button" id="cancelCreateGrnBtn"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    --}}

    @push('scripts')
        <script>
            // Preload items data
            let itemsData = {
                @foreach ($items as $item)
                    "{{ $item->id }}": {
                        buying_price: {{ $item->buying_price }},
                        category: "{{ $item->category->name ?? 'N/A' }}",
                        item_code: "{{ $item->item_code }}",
                        name: "{{ $item->name }}"
                    },
                @endforeach
            };

            document.addEventListener('DOMContentLoaded', function() {
                const itemsContainer = document.getElementById('itemsContainer');
                const addItemBtn = document.getElementById('addItemBtn');
                const grandTotalEl = document.getElementById('grand-total');
                const organizationSelect = document.getElementById('organization_id');
                const supplierSelect = document.getElementById('supplier_id');
                const branchSelect = document.getElementById('branch_id');
                const isSuperAdmin = {{ Auth::guard('admin')->user()->is_super_admin ? 'true' : 'false' }};
                let itemCount = {{ count($oldItems) }};

                // Super admin organization selection handler
                if (isSuperAdmin && organizationSelect) {
                    organizationSelect.addEventListener('change', async function() {
                        const selectedOrgId = this.value;

                        // Clear dependent dropdowns
                        supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';

                        // Clear items in existing rows
                        document.querySelectorAll('.item-select').forEach(select => {
                            select.innerHTML = '<option value="">Select Item</option>';
                        });

                        if (selectedOrgId) {
                            try {
                                // Fetch suppliers
                                const suppliersResponse = await fetch(
                                    `/admin/api/grn/suppliers-by-organization?organization_id=${selectedOrgId}`
                                );
                                if (suppliersResponse.ok) {
                                    const suppliersData = await suppliersResponse.json();
                                    suppliersData.suppliers.forEach(supplier => {
                                        const option = document.createElement('option');
                                        option.value = supplier.id;
                                        option.textContent =
                                            `${supplier.name} (${supplier.supplier_id})`;
                                        supplierSelect.appendChild(option);
                                    });
                                }

                                // Fetch branches
                                const branchesResponse = await fetch(
                                    `/admin/api/grn/branches-by-organization?organization_id=${selectedOrgId}`
                                );
                                if (branchesResponse.ok) {
                                    const branchesData = await branchesResponse.json();
                                    branchesData.branches.forEach(branch => {
                                        const option = document.createElement('option');
                                        option.value = branch.id;
                                        option.textContent = branch.name;
                                        branchSelect.appendChild(option);
                                    });
                                }

                                // Fetch items and update itemsData
                                const itemsResponse = await fetch(
                                    `/admin/api/grn/items-by-organization?organization_id=${selectedOrgId}`
                                );
                                if (itemsResponse.ok) {
                                    const itemsResponseData = await itemsResponse.json();
                                    itemsData = {};
                                    itemsResponseData.items.forEach(item => {
                                        itemsData[item.id] = {
                                            buying_price: item.buying_price,
                                            category: item.category || 'N/A',
                                            item_code: item.item_code,
                                            name: item.name
                                        };
                                    });

                                    // Update all item selects
                                    document.querySelectorAll('.item-select').forEach(select => {
                                        select.innerHTML = '<option value="">Select Item</option>';
                                        itemsResponseData.items.forEach(item => {
                                            const option = document.createElement('option');
                                            option.value = item.id;
                                            option.setAttribute('data-price', item
                                                .buying_price);
                                            option.setAttribute('data-category', item
                                                .category || 'N/A');
                                            option.textContent =
                                                `${item.item_code} - ${item.name} (${item.category || 'N/A'})`;
                                            select.appendChild(option);
                                        });
                                    });
                                }
                            } catch (error) {
                                console.error('Error fetching organization data:', error);
                                alert('Error loading organization data. Please try again.');
                            }
                        }
                    });
                }

                // Function to handle item selection change
                function handleItemChange() {
                    const row = this.closest('tr');
                    const itemId = this.value;
                    const priceInput = row.querySelector('.item-price');

                    if (itemId && itemsData[itemId]) {
                        priceInput.value = itemsData[itemId].buying_price;
                        const event = new Event('input', {
                            bubbles: true
                        });
                        priceInput.dispatchEvent(event);
                    }
                }

                function addPlaceholderRow() {
                    if (!itemsContainer.querySelector('.placeholder-row')) {
                        const row = document.createElement('tr');
                        row.className = 'placeholder-row';
                        row.innerHTML = `<td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            Please add at least one item to the GRN.
                        </td>`;
                        itemsContainer.appendChild(row);
                    }
                }

                function removePlaceholderRow() {
                    const placeholder = itemsContainer.querySelector('.placeholder-row');
                    if (placeholder) placeholder.remove();
                }

                // Function to generate item select options
                function generateItemSelectOptions() {
                    let options = '<option value="">Select Item</option>';
                    Object.keys(itemsData).forEach(itemId => {
                        // Since we don't have full item data when dynamically loaded, we'll use the DOM
                        const existingOption = document.querySelector(`.item-select option[value="${itemId}"]`);
                        if (existingOption) {
                            options += existingOption.outerHTML;
                        }
                    });
                    return options;
                }

                // Attach event to existing item selects
                document.querySelectorAll('.item-select').forEach(select => {
                    select.addEventListener('change', handleItemChange);
                });

                // Add new item row
                addItemBtn.addEventListener('click', function() {
                    // For super admin, ensure organization is selected before adding items
                    if (isSuperAdmin && organizationSelect && !organizationSelect.value) {
                        alert('Please select an organization first.');
                        return;
                    }

                    removePlaceholderRow();
                    const newRow = document.createElement('tr');
                    newRow.className = 'item-row border-b bg-white hover:bg-gray-50';
                    newRow.dataset.index = itemCount;

                    // Get current item options
                    const itemOptions = document.querySelector('.item-select')?.innerHTML ||
                        '<option value="">Select Item</option>';

                    newRow.innerHTML = `
                <td class="px-4 py-3">
                    <select name="items[${itemCount}][item_id]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                            required>
                        ${itemOptions}
                    </select>
                    <input type="hidden" name="items[${itemCount}][item_code]" value="">
                </td>

                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][received_quantity]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent received-qty"
                           min="0.01" step="0.01" value="1" required>
                    <input type="hidden" name="items[${itemCount}][ordered_quantity]" class="ordered-qty" value="1">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][free_received_quantity]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent free-qty"
                           min="0" step="0.01" value="0">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][buying_price]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-price"
                           min="0" step="0.01" value="0.00" required>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][discount_received]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent discount-received"
                           min="0" max="100" step="0.01" value="0">
                </td>
                <td class="px-4 py-3 font-medium item-total">0.00</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="remove-item-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

                    itemsContainer.appendChild(newRow);

                    // Add event listeners to the new row
                    const select = newRow.querySelector('.item-select');
                    const receivedQtyInput = newRow.querySelector('.received-qty');
                    const orderedQtyInput = newRow.querySelector('.ordered-qty');
                    const priceInput = newRow.querySelector('.item-price');
                    const discountInput = newRow.querySelector('.discount-received');
                    const freeQtyInput = newRow.querySelector('.free-qty');
                    const removeBtn = newRow.querySelector('.remove-item-btn');

                    // Set price automatically when item is selected
                    select.addEventListener('change', function() {
                        handleItemChange.call(this);
                    });

                    // Set ordered quantity equal to received quantity
                    receivedQtyInput.addEventListener('input', function() {
                        orderedQtyInput.value = this.value;
                        calculateRowTotal.call(this);
                    });

                    // Prevent discount > 100%
                    discountInput.addEventListener('input', function() {
                        if (parseFloat(this.value) > 100) {
                            this.value = 100;
                            alert('Discount (%) cannot be more than 100%');
                        }
                        calculateRowTotal.call(this);
                    });

                    // Add calculation handlers
                    receivedQtyInput.addEventListener('input', calculateRowTotal);
                    priceInput.addEventListener('input', calculateRowTotal);
                    discountInput.addEventListener('input', calculateRowTotal);
                    freeQtyInput.addEventListener('input', calculateRowTotal);

                    // Add remove button handler
                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                        if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                            addPlaceholderRow();
                        }
                        updateGrandTotal();
                    });

                    itemCount++;
                });

                // Add remove button handler to dynamically added rows
                function attachRemoveHandler(row) {
                    const removeBtn = row.querySelector('.remove-item-btn');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            row.remove();
                            if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                                addPlaceholderRow();
                            }
                            updateGrandTotal();
                        });
                    }
                }

                // Attach remove handler to existing rows
                document.querySelectorAll('.item-row').forEach(row => {
                    attachRemoveHandler(row);
                });

                // When page loads, if no item-row exists, show placeholder
                if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                    addPlaceholderRow();
                }

                function updateSummaryFooter() {
                    let totalItems = 0;
                    let totalBeforeDiscount = 0;
                    let totalDiscountItems = 0;

                    document.querySelectorAll('.item-row').forEach(row => {
                        totalItems++;
                        const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
                        const price = parseFloat(row.querySelector('.item-price').value) || 0;
                        const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                        totalBeforeDiscount += quantity * price;
                        totalDiscountItems += (quantity * price) * (discountPercent / 100);
                    });

                    document.getElementById('total-items').textContent = totalItems;
                    document.getElementById('total-before-discount').textContent = totalBeforeDiscount.toFixed(2);
                    document.getElementById('total-discount-items').textContent = totalDiscountItems.toFixed(2);

                    // Grand discount
                    const grandDiscountPercent = parseFloat(document.getElementById('grand-discount-input').value) || 0;
                    const grandDiscountAmount = (totalBeforeDiscount - totalDiscountItems) * (grandDiscountPercent /
                        100);
                    document.getElementById('grand-discount-amount').textContent = grandDiscountAmount.toFixed(2);

                    // Grand total
                    const grandTotal = (totalBeforeDiscount - totalDiscountItems) - grandDiscountAmount;
                    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
                }

                // Calculate row total using discount as percentage
                function calculateRowTotal() {
                    const row = this.closest('tr');
                    const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                    const discountAmount = (quantity * price) * (discountPercent / 100);
                    const total = (quantity * price) - discountAmount;
                    row.querySelector('.item-total').textContent = total.toFixed(2);
                    updateSummaryFooter();
                }

                // Update grand total
                function updateGrandTotal() {
                    updateSummaryFooter();
                }

                // Add event listeners to existing inputs
                document.querySelectorAll('.received-qty, .item-price, .discount-received, .free-qty').forEach(
                    input => {
                        if (input.classList.contains('discount-received')) {
                            input.addEventListener('input', function() {
                                if (parseFloat(this.value) > 100) {
                                    this.value = 100;
                                    alert('Discount (%) cannot be more than 100%');
                                }
                                calculateRowTotal.call(this);
                            });
                        } else {
                            input.addEventListener('input', calculateRowTotal);
                        }
                    });

                document.getElementById('grand-discount-input').addEventListener('input', updateSummaryFooter);

                // Form validation
                document.getElementById('grnForm').addEventListener('submit', function(e) {
                    // For super admin, ensure organization is selected
                    if (isSuperAdmin && organizationSelect && !organizationSelect.value) {
                        e.preventDefault();
                        alert('Please select an organization');
                        return false;
                    }

                    const itemRows = document.querySelectorAll('.item-row');
                    if (itemRows.length === 0) {
                        e.preventDefault();
                        alert('Please add at least one item to the GRN');
                        return false;
                    }

                    // Ensure ordered_quantity is set to received_quantity for all items before submit
                    itemRows.forEach(row => {
                        const receivedQty = row.querySelector('.received-qty');
                        const orderedQty = row.querySelector('.ordered-qty');
                        if (receivedQty && orderedQty) {
                            orderedQty.value = receivedQty.value;
                        }
                    });

                    let isValid = true;
                    itemRows.forEach(row => {
                        const itemSelect = row.querySelector('.item-select');
                        const receivedQty = row.querySelector('.received-qty');
                        const price = row.querySelector('.item-price');

                        // Only check visible/required fields
                        if (!itemSelect.value || !receivedQty.value || !price.value) {
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill all required fields for all items');
                        return false;
                    }
                });

                // Initialize calculations on page load
                document.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.received-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const discountPercent = parseFloat(row.querySelector('.discount-received')?.value) || 0;
                    const discountAmount = (quantity * price) * (discountPercent / 100);
                    row.querySelector('.item-total').textContent = ((quantity * price) - discountAmount)
                        .toFixed(2);
                });
                updateSummaryFooter();
            });
        </script>
    @endpush
@endsection
