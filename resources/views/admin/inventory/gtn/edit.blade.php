@extends('layouts.admin')

@section('header-title', 'Edit GTN')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit GTN: {{ $gtn->gtn_number }}</h2>
                    <p class="text-sm text-gray-500">Update goods transfer details</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.gtn.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                    </a>
                    <a href="{{ route('admin.inventory.gtn.show', $gtn->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-eye mr-2"></i> View GTN
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="{{ route('admin.inventory.gtn.update', $gtn->id) }}" method="POST" class="p-6">
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

                <!-- GTN Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GTN Number</label>
                        <input type="text" value="{{ $gtn->gtn_number }}" disabled class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-1">From Branch *</label>
                        <div class="relative">
                            <select id="from_branch_id" name="from_branch_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $gtn->from_branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-1">To Branch *</label>
                        <div class="relative">
                            <select id="to_branch_id" name="to_branch_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $gtn->to_branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Info Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1">Transfer Date *</label>
                        <div class="relative">
                            <input type="date" id="transfer_date" name="transfer_date" value="{{ $gtn->transfer_date->format('Y-m-d') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" value="{{ $gtn->reference_number }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="relative">
                            <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="Pending" {{ $gtn->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Completed" {{ $gtn->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Cancelled" {{ $gtn->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Transfer Items</h3>
                        @if($gtn->status == 'Pending')
                            <button type="button" id="addItemBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        @endif
                    </div>

                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Batch No</th>
                                        <th class="px-4 py-3">Quantity</th>
                                        <th class="px-4 py-3">Unit Price</th>
                                        <th class="px-4 py-3">Line Total</th>
                                        <th class="px-4 py-3">Expiry Date</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    @foreach ($gtn->items as $index => $item)
                                        <tr class="item-row border-b bg-white">
                                            <td class="px-4 py-3">
                                                <select name="items[{{ $index }}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" {{ $gtn->status != 'Pending' ? 'disabled' : 'required' }}>
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
                                                <input type="hidden" name="items[{{ $index }}][item_code]" value="{{ $item->item_code }}">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="items[{{ $index }}][batch_no]" value="{{ $item->batch_no }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" {{ $gtn->status != 'Pending' ? 'disabled' : '' }}>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" name="items[{{ $index }}][transfer_quantity]" value="{{ $item->transfer_quantity }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity" {{ $gtn->status != 'Pending' ? 'disabled' : 'required' }}>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" name="items[{{ $index }}][transfer_price]" value="{{ $item->transfer_price }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg price" {{ $gtn->status != 'Pending' ? 'disabled' : 'required' }}>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="w-full px-3 py-2 font-medium line-total">${{ number_format($item->transfer_quantity * $item->transfer_price, 2) }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="date" name="items[{{ $index }}][expiry_date]" value="{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" {{ $gtn->status != 'Pending' ? 'disabled' : '' }}>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($gtn->status == 'Pending')
                                                    <button type="button" class="remove-item text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
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
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" {{ $gtn->status != 'Pending' ? 'disabled' : '' }}>{{ $gtn->notes }}</textarea>
                </div>

                <!-- Form Actions -->
                @if($gtn->status == 'Pending')
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t">
                        <button type="reset" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center">
                            <i class="fas fa-redo mr-2"></i> Reset Form
                        </button>
                        <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Update GTN
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCounter = {{ count($gtn->items) }};

            // Add new item row
            document.getElementById('addItemBtn')?.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row border-b bg-white';
                newRow.innerHTML = `
                    <td class="px-4 py-3">
                        <select name="items[${itemCounter}][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent item-select" required>
                            <option value="">Select Item</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}"
                                    data-code="{{ $item->item_code }}"
                                    data-price="{{ $item->buying_price }}">
                                    {{ $item->name }} ({{ $item->item_code }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="items[${itemCounter}][item_code]" value="">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="items[${itemCounter}][batch_no]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" step="0.01" name="items[${itemCounter}][transfer_quantity]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity" required>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" step="0.01" name="items[${itemCounter}][transfer_price]" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg price" required>
                    </td>
                    <td class="px-4 py-3">
                        <div class="w-full px-3 py-2 font-medium line-total">$0.00</div>
                    </td>
                    <td class="px-4 py-3">
                        <input type="date" name="items[${itemCounter}][expiry_date]" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
                const qtyInput = newRow.querySelector('.quantity');
                const removeBtn = newRow.querySelector('.remove-item');

                select.addEventListener('change', handleItemChange);
                priceInput.addEventListener('input', function() { calculateLineTotal(this.closest('.item-row')); });
                qtyInput.addEventListener('input', function() { calculateLineTotal(this.closest('.item-row')); });
                removeBtn.addEventListener('click', function() { newRow.remove(); });

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
                const qtyInput = row.querySelector('.quantity');
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

            document.querySelectorAll('.quantity, .price').forEach(input => {
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
