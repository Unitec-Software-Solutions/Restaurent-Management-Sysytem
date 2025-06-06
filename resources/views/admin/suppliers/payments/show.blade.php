@extends('layouts.admin')

@section('header-title', 'Create Purchase Order')
@section('content')
    <div class="p-4 rounded-lg">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.purchase-orders.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to POs
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Purchase Order</h1>

            <form method="POST" action="{{ route('admin.purchase-orders.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Branch Selection -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <select id="branch_id" name="branch_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supplier Selection -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select id="supplier_id" name="supplier_id" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order Date -->
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                        <input type="date" id="order_date" name="order_date" required
                            value="{{ old('order_date', date('Y-m-d')) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('order_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expected Delivery Date -->
                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery *</label>
                        <input type="date" id="expected_delivery_date" name="expected_delivery_date" required
                            value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('expected_delivery_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- PO Items -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-semibold">Order Items</h2>
                        <button type="button" id="add-item-btn"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>

                    <div id="items-container">
                        <!-- Items will be added here dynamically -->
                        <div class="grid grid-cols-12 gap-4 mb-4 items-center border-b pb-4">
                            <div class="col-span-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item *</label>
                                <select name="items[0][item_code]" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 item-select">
                                    <option value="">Select Item</option>
                                    <!-- Items will be loaded via AJAX based on supplier -->
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Batch No</label>
                                <input type="text" name="items[0][batch_no]"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                                <input type="number" step="0.01" min="0.01" name="items[0][quantity]" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 quantity">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                                <input type="number" step="0.01" min="0" name="items[0][buying_price]" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 price">
                            </div>
                            <div class="col-span-1 flex items-end">
                                <button type="button" class="remove-item-btn text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-save mr-2"></i> Create Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemCounter = 1;

        // Add new item row
        $('#add-item-btn').click(function() {
            const newItem = `
                <div class="grid grid-cols-12 gap-4 mb-4 items-center border-b pb-4">
                    <div class="col-span-5">
                        <select name="items[${itemCounter}][item_code]" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 item-select">
                            <option value="">Select Item</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="items[${itemCounter}][batch_no]"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="0.01" min="0.01" name="items[${itemCounter}][quantity]" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 quantity">
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="0.01" min="0" name="items[${itemCounter}][buying_price]" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 price">
                    </div>
                    <div class="col-span-1 flex items-end">
                        <button type="button" class="remove-item-btn text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#items-container').append(newItem);
            itemCounter++;
        });

        // Remove item row
        $(document).on('click', '.remove-item-btn', function() {
            if($('#items-container').children().length > 1) {
                $(this).closest('.grid').remove();
            } else {
                alert('At least one item is required');
            }
        });

        // Load items when supplier changes
        $('#supplier_id').change(function() {
            const supplierId = $(this).val();
            if(supplierId) {
                // AJAX call to get supplier items
                $.get(`/admin/suppliers/${supplierId}/items`, function(data) {
                    $('.item-select').empty().append('<option value="">Select Item</option>');
                    data.forEach(item => {
                        $('.item-select').append(`<option value="${item.item_code}">${item.name}</option>`);
                    });
                });
            }
        });
    });
</script>
@endpush