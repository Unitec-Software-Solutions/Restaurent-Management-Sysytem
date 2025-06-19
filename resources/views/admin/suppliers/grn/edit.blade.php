@extends('layouts.admin')

@section('header-title', 'Edit Goods Received Note')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit GRN: {{ $grn->grn_number }}</h2>
                    <p class="text-sm text-gray-500">Update goods received details</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.grn.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
                    </a>
                    <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-eye mr-2"></i> View GRN
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.grn.update', $grn->grn_id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

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

                <!-- GRN Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GRN Number</label>
                        <input type="text" value="{{ $grn->grn_number }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <div class="relative">
                            <select id="branch_id" name="branch_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ $grn->branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <div class="relative">
                            <select id="supplier_id" name="supplier_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ $grn->supplier_id == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date
                            *</label>
                        <div class="relative">
                            <input type="date" id="received_date" name="received_date"
                                value="{{ $grn->received_date->format('Y-m-d') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                required>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note
                            Number</label>
                        <input type="text" id="delivery_note_number" name="delivery_note_number"
                            value="{{ $grn->delivery_note_number }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice
                            Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" value="{{ $grn->invoice_number }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Received Items</h3>
                        <button type="button" id="addItemBtn"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <p class="text-sm font-medium text-gray-700 text-m px-4 py-3 ">Items of
                                            {{ $grn->grn_number }}</p>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    @foreach ($grn->items as $item)
                                        <tr class="item-row border-b bg-white ">
                                            <td class="px-4 py-3" colspan="9">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">Item
                                                            *</label>
                                                        <div class="relative">
                                                            <select name="items[{{ $loop->index }}][item_id]"
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select"
                                                                required>
                                                                <option value="">Select Item</option>
                                                                @foreach ($items as $itemOption)
                                                                    <option value="{{ $itemOption->id }}"
                                                                        data-code="{{ $itemOption->item_code }}"
                                                                        data-price="{{ $itemOption->buying_price }}"
                                                                        {{ $item->item_id == $itemOption->id ? 'selected' : '' }}>
                                                                        {{ $itemOption->item_code }} -
                                                                        {{ $itemOption->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden"
                                                                name="items[{{ $loop->index }}][item_code]"
                                                                value="{{ $item->item_code }}">
                                                            <input type="hidden"
                                                                name="items[{{ $loop->index }}][po_detail_id]"
                                                                value="{{ $item->po_detail_id }}">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">Batch
                                                            Number</label>
                                                        <input type="text" name="items[{{ $loop->index }}][batch_no]"
                                                            value="{{ $item->batch_no }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">Ordered
                                                            Qty</label>
                                                        <input type="number" step="0.01"
                                                            name="items[{{ $loop->index }}][ordered_quantity]"
                                                            value="{{ $item->ordered_quantity }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg ordered-qty"
                                                            required>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-500 mb-1">Received
                                                            Qty *</label>
                                                        <input type="number" step="0.01"
                                                            name="items[{{ $loop->index }}][received_quantity]"
                                                            value="{{ $item->received_quantity }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg received-qty"
                                                            required>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-500 mb-1">Accepted
                                                            Qty *</label>
                                                        <input type="number" step="0.01"
                                                            name="items[{{ $loop->index }}][accepted_quantity]"
                                                            value="{{ $item->accepted_quantity }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg accepted-qty"
                                                            required>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-500 mb-1">Rejected
                                                            Qty *</label>
                                                        <input type="number" step="0.01"
                                                            name="items[{{ $loop->index }}][rejected_quantity]"
                                                            value="{{ $item->rejected_quantity }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg rejected-qty"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">Buying
                                                            Price *</label>
                                                        <input type="number" step="0.01"
                                                            name="items[{{ $loop->index }}][buying_price]"
                                                            value="{{ $item->buying_price }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg price"
                                                            required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">Line
                                                            Total</label>
                                                        <div class="w-full px-3 py-2 font-medium line-total">
                                                            ${{ number_format($item->line_total, 2) }}</div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">MFG
                                                            Date</label>
                                                        <input type="date"
                                                            name="items[{{ $loop->index }}][manufacturing_date]"
                                                            value="{{ $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : '' }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-500 mb-1">EXP
                                                            Date</label>
                                                        <input type="date"
                                                            name="items[{{ $loop->index }}][expiry_date]"
                                                            value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Rejection
                                                        Reason</label>
                                                    <input type="text"
                                                        name="items[{{ $loop->index }}][rejection_reason]"
                                                        value="{{ $item->rejection_reason }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                <button type="button"
                                                    class="remove-item text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ $grn->notes }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                    <button type="reset"
                        class="px-6 py-3 bg-gray-200  text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Update GRN
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCounter = {{ count($grn->items) }};

            // Add new item row
            document.getElementById('addItemBtn').addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row border-b bg-white ';
                newRow.innerHTML = `
                    <td class="px-4 py-3" colspan="9">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Item *</label>
                                <div class="relative">
                                    <select name="items[${itemCounter}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach ($items as $itemOption)
                                            <option value="{{ $itemOption->id }}"
                                                data-code="{{ $itemOption->item_code }}"
                                                data-price="{{ $itemOption->buying_price }}"
                                                {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}>
                                                {{ $itemOption->item_code }} - {{ $itemOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[${itemCounter}][item_code]" value="">
                                    <input type="hidden" name="items[${itemCounter}][po_detail_id]" value="">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Batch Number</label>
                                <input type="text" name="items[${itemCounter}][batch_no]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Ordered Qty</label>
                                <input type="number" step="0.01" name="items[${itemCounter}][ordered_quantity]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg ordered-qty" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Received Qty *</label>
                                <input type="number" step="0.01" name="items[${itemCounter}][received_quantity]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg received-qty" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Accepted Qty *</label>
                                <input type="number" step="0.01" name="items[${itemCounter}][accepted_quantity]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg accepted-qty" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Rejected Qty *</label>
                                <input type="number" step="0.01" name="items[${itemCounter}][rejected_quantity]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg rejected-qty" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Buying Price *</label>
                                <input type="number" step="0.01" name="items[${itemCounter}][buying_price]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg price" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Line Total</label>
                                <div class="w-full px-3 py-2 font-medium line-total">$0.00</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">MFG Date</label>
                                <input type="date" name="items[${itemCounter}][manufacturing_date]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">EXP Date</label>
                                <input type="date" name="items[${itemCounter}][expiry_date]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Rejection Reason</label>
                            <input type="text" name="items[${itemCounter}][rejection_reason]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <button type="button" class="remove-item text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                document.getElementById('itemsContainer').appendChild(newRow);

                // Add event listeners to the new row
                const select = newRow.querySelector('.item-select');
                const priceInput = newRow.querySelector('.price');
                const acceptedQtyInput = newRow.querySelector('.accepted-qty');
                const removeBtn = newRow.querySelector('.remove-item');

                select.addEventListener('change', handleItemChange);
                priceInput.addEventListener('input', function() {
                    calculateLineTotal(this.closest('.item-row'));
                });
                acceptedQtyInput.addEventListener('input', function() {
                    calculateLineTotal(this.closest('.item-row'));
                });
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                });

                itemCounter++;
            });

            // Remove item row
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item')) {
                    e.target.closest('.item-row').remove();
                }
            });

            // Handle item selection change
            function handleItemChange() {
                const selectedOption = this.options[this.selectedIndex];
                const row = this.closest('.item-row');
                if (selectedOption && selectedOption.value) {
                    row.querySelector('input[name$="[item_code]"]').value = selectedOption.dataset.code || '';
                    const priceInput = row.querySelector('.price');
                    priceInput.value = selectedOption.dataset.price || '0';
                    calculateLineTotal(row);
                }
            }

            // Calculate line total
            function calculateLineTotal(row) {
                const qtyInput = row.querySelector('.accepted-qty');
                const priceInput = row.querySelector('.price');
                const lineTotalCell = row.querySelector('.line-total');
                if (qtyInput && priceInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const total = qty * price;
                    lineTotalCell.textContent = '$' + total.toFixed(2);
                }
            }

            // Attach event listeners to existing elements
            document.querySelectorAll('.item-select').forEach(select => {
                select.addEventListener('change', handleItemChange);
            });

            document.querySelectorAll('.accepted-qty, .price').forEach(input => {
                input.addEventListener('input', function() {
                    calculateLineTotal(this.closest('.item-row'));
                });
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.item-row').remove();
                });
            });
        });
    </script>
@endpush
