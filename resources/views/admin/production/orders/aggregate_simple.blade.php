@extends('layouts.admin')

@section('title', 'Aggregate Production Requests')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Aggregate Production Requests</h1>
                    <p class="text-gray-600 mt-1">Select and combine production requests to create production orders</p>
                </div>
                <a href="{{ route('admin.production.requests.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Show aggregated data from server -->
            @if (!empty($aggregatedItems['items']))
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Items Summary</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">From
                                        Requests</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($aggregatedItems['items'] as $itemId => $itemData)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                            {{ $itemData['item']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ number_format($itemData['total_quantity'], 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600">
                                            @foreach ($itemData['requests'] as $request)
                                                {{ $request['branch'] }}: {{ $request['quantity'] }}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (!empty($aggregatedItems['ingredients']))
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Required Ingredients Summary</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ingredient
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Required</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Used For
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($aggregatedItems['ingredients'] as $ingredientId => $ingredientData)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                            {{ $ingredientData['item']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ number_format($ingredientData['total_required'], 3) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600">{{ $ingredientData['unit'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600">
                                            @foreach ($ingredientData['from_items'] as $fromItem)
                                                {{ $fromItem['production_item'] }}:
                                                {{ number_format($fromItem['quantity_needed'], 3) }}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.production.orders.store') }}" method="POST" id="aggregateForm">
                @csrf

                <!-- Available Requests -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Available Production Requests</h3>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="selectAll"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                                <span class="text-sm text-gray-500">{{ $requests->count() }} requests available</span>
                            </div>
                        </div>
                    </div>

                    @if ($requests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-6 py-3"></th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($requests as $request)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="selected_requests[]"
                                                    value="{{ $request->id }}"
                                                    class="request-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">Request #{{ $request->id }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $request->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->branch->name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-xs text-gray-500 max-w-48 truncate">
                                                    {{ $request->items->pluck('item.name')->join(', ') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $request->required_date->format('M d, Y') }}
                                                @if ($request->required_date->isPast())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                                        Overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($request->getTotalQuantityApproved()) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No approved production requests found</p>
                            <p class="text-sm">Try adjusting your filters or check back later</p>
                        </div>
                    @endif
                </div>

                <!-- Production Order Creation Section -->
                <div id="productionOrderSection" class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Production Order</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="production_date" class="block text-sm font-medium text-gray-700 mb-2">Production
                                Date <span class="text-red-500">*</span></label>
                            <input type="date" name="production_date" id="production_date" required
                                value="{{ date('Y-m-d') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="expected_completion_date"
                                class="block text-sm font-medium text-gray-700 mb-2">Expected Completion Date</label>
                            <input type="date" name="expected_completion_date" id="expected_completion_date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="production_notes" class="block text-sm font-medium text-gray-700 mb-2">Production
                            Notes</label>
                        <textarea name="production_notes" id="production_notes" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter any special instructions or notes for this production order..."></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Select requests above to create a production order
                        </div>
                        <button type="submit" id="createOrderBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Create Production Order
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const requestCheckboxes = document.querySelectorAll('.request-checkbox');
            const createOrderBtn = document.getElementById('createOrderBtn');

            // Handle select all
            selectAllCheckbox.addEventListener('change', function() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateButtonState();
            });

            // Handle individual checkbox changes
            requestCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateButtonState();

                    // Update select all checkbox
                    const checkedCount = document.querySelectorAll('.request-checkbox:checked')
                        .length;
                    selectAllCheckbox.checked = checkedCount === requestCheckboxes.length;
                    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount <
                        requestCheckboxes.length;
                });
            });

            function updateButtonState() {
                const selectedCount = document.querySelectorAll('.request-checkbox:checked').length;
                createOrderBtn.disabled = selectedCount === 0;
                createOrderBtn.classList.toggle('opacity-50', selectedCount === 0);
                createOrderBtn.classList.toggle('cursor-not-allowed', selectedCount === 0);
            }

            // Initial state
            updateButtonState();
        });
    </script>
@endsection
