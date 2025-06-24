@extends('layouts.admin')

@section('title', 'Create Production Session')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Create Production Session</h1>
                    <p class="text-gray-600 mt-1">Start a new production session for approved orders</p>
                </div>
                <a href="{{ route('admin.production.sessions.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Sessions
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200">
                    <h4 class="font-medium">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.production.sessions.store') }}" method="POST">
                @csrf

                <!-- Session Details -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Session Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="production_order_id" class="block text-sm font-medium text-gray-700 mb-2">Production
                                Order *</label>
                            <select name="production_order_id" id="production_order_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm" required>
                                <option value="">Select Production Order</option>
                                @foreach ($availableOrders as $order)
                                    <option value="{{ $order->id }}"
                                        {{ old('production_order_id') == $order->id ? 'selected' : '' }}>
                                        {{ $order->production_order_number }} - {{ $order->items->count() }} items
                                        ({{ $order->production_date->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="session_name" class="block text-sm font-medium text-gray-700 mb-2">Session Name
                                *</label>
                            <input type="text" name="session_name" id="session_name" value="{{ old('session_name') }}"
                                placeholder="e.g., Morning Batch 001" class="w-full rounded-lg border-gray-300 shadow-sm"
                                required>
                        </div>

                        <div>
                            <label for="supervisor_user_id"
                                class="block text-sm font-medium text-gray-700 mb-2">Supervisor</label>
                            <select name="supervisor_user_id" id="supervisor_user_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Select Supervisor (Optional)</option>
                                <!-- You would need to pass supervisors from controller -->
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Any special instructions or notes..."
                                class="w-full rounded-lg border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Production Order Preview -->
                <div id="orderPreview" class="hidden bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Production Order Preview</h2>
                    <div id="orderDetails">
                        <!-- Order details will be loaded here via JavaScript -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="text-red-500">*</span> Required fields
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.production.sessions.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg transition duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Create Session
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productionOrderSelect = document.getElementById('production_order_id');
            const orderPreview = document.getElementById('orderPreview');
            const orderDetails = document.getElementById('orderDetails');

            // Production order data (you'd pass this from controller)
            const orders = @json($availableOrders);

            productionOrderSelect.addEventListener('change', function() {
                const selectedOrderId = this.value;

                if (selectedOrderId) {
                    const selectedOrder = orders.find(order => order.id == selectedOrderId);

                    if (selectedOrder) {
                        showOrderPreview(selectedOrder);
                    }
                } else {
                    hideOrderPreview();
                }
            });

            function showOrderPreview(order) {
                let itemsHtml = '';

                if (order.items && order.items.length > 0) {
                    itemsHtml = `
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity to Produce</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                    `;

                    order.items.forEach(item => {
                        itemsHtml += `
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">${item.item ? item.item.name : 'Unknown Item'}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">${parseFloat(item.quantity_to_produce).toFixed(2)}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">${item.item ? item.item.unit_of_measurement : 'N/A'}</td>
                            </tr>
                        `;
                    });

                    itemsHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                orderDetails.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Order Number</label>
                            <p class="text-sm text-gray-900">${order.production_order_number}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Production Date</label>
                            <p class="text-sm text-gray-900">${new Date(order.production_date).toLocaleDateString()}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${order.status.charAt(0).toUpperCase() + order.status.slice(1).replace('_', ' ')}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Items to Produce</h3>
                        ${itemsHtml}
                    </div>
                    
                    ${order.notes ? `
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Order Notes</label>
                                <p class="text-sm text-gray-900">${order.notes}</p>
                            </div>
                        ` : ''}
                `;

                orderPreview.classList.remove('hidden');
            }

            function hideOrderPreview() {
                orderPreview.classList.add('hidden');
                orderDetails.innerHTML = '';
            }

            // Auto-generate session name based on selection
            productionOrderSelect.addEventListener('change', function() {
                const sessionNameInput = document.getElementById('session_name');
                if (this.value && !sessionNameInput.value) {
                    const selectedOrder = orders.find(order => order.id == this.value);
                    if (selectedOrder) {
                        const date = new Date();
                        const timeString = date.toLocaleTimeString('en-US', {
                            hour12: false,
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        sessionNameInput.value = `${selectedOrder.production_order_number} - ${timeString}`;
                    }
                }
            });
        });
    </script>
@endsection
