@extends('layouts.admin')

@section('title', 'Aggregate Production Requests')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Aggregate Production Requests</h1>
                    <p class="text-gray-600 mt-1">Select multiple approved requests to create a single production order
                    </p>
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

            <form action="{{ route('admin.production.orders.store') }}" method="POST" id="aggregateForm">
                @csrf

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Filter Requests</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select id="branchFilter" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date From</label>
                            <input type="date" id="dateFromFilter" class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Required Date To</label>
                            <input type="date" id="dateToFilter" class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
                            <select id="itemFilter" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">All Items</option>
                                @foreach ($productionItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Selected Requests Summary -->
                <div id="selectedSummary" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-blue-900">Selected Requests Summary</h3>
                        <button type="button" onclick="clearSelection()" class="text-blue-700 hover:text-blue-900 text-sm">
                            <i class="fas fa-times mr-1"></i>Clear Selection
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-blue-700">Selected Requests:</span>
                            <span id="selectedCount" class="font-medium text-blue-900">0</span>
                        </div>
                        <div>
                            <span class="text-blue-700">Total Items:</span>
                            <span id="totalItems" class="font-medium text-blue-900">0</span>
                        </div>
                        <div>
                            <span class="text-blue-700">Unique Items:</span>
                            <span id="uniqueItems" class="font-medium text-blue-900">0</span>
                        </div>
                        <div>
                            <span class="text-blue-700">Total Quantity:</span>
                            <span id="totalQuantity" class="font-medium text-blue-900">0</span>
                        </div>
                    </div>
                </div>

                <!-- Approved Requests -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Approved Production Requests</h2>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="selectAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                                <div class="text-sm text-gray-500">
                                    {{ $approvedRequests->count() }} approved requests available
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-12 px-6 py-3">
                                        <span class="sr-only">Select</span>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Request</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Branch</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Required Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Priority</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="requestsTableBody">
                                @forelse($approvedRequests as $request)
                                    <tr class="request-row hover:bg-gray-50" data-request-id="{{ $request->id }}"
                                        data-branch-id="{{ $request->branch_id }}"
                                        data-required-date="{{ $request->required_date->format('Y-m-d') }}"
                                        data-items="{{ $request->items->pluck('item_id')->join(',') }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="selected_requests[]" value="{{ $request->id }}"
                                                class="request-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                data-request-id="{{ $request->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Request
                                                    #{{ $request->id }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->request_date->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $request->required_date->format('M d, Y') }}</div>
                                            @if ($request->required_date->isPast())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Overdue
                                                </span>
                                            @elseif($request->required_date->isToday())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Today
                                                </span>
                                            @elseif($request->required_date->isTomorrow())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    Tomorrow
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $request->items->count() }} items</div>
                                            <div class="text-xs text-gray-500 max-w-48 truncate">
                                                {{ $request->items->pluck('item.name')->join(', ') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ number_format($request->getTotalQuantityApproved()) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($request->required_date->isPast())
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>High
                                                </span>
                                            @elseif($request->required_date->diffInDays() <= 2)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>Medium
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Normal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.production.requests.show', $request) }}"
                                                class="text-blue-600 hover:text-blue-900 mr-3" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                                            <p class="text-lg font-medium">No Approved Requests Available</p>
                                            <p class="text-sm">All production requests have been processed or there are no
                                                approved requests.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Production Order Details -->
                <div id="productionOrderSection" class="hidden bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Production Order Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Production Date *</label>
                            <input type="date" name="production_date" value="{{ now()->format('Y-m-d') }}"
                                min="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expected Completion Date</label>
                            <input type="date" name="expected_completion_date"
                                value="{{ now()->addDay()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Production Notes</label>
                        <textarea name="production_notes" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Any special instructions for production..."></textarea>
                    </div>

                    <!-- Aggregated Items Preview -->
                    <div id="aggregatedItemsPreview" class="hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Items to Produce</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total
                                            Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">From
                                            Requests</th>
                                    </tr>
                                </thead>
                                <tbody id="aggregatedItemsBody" class="divide-y divide-gray-200">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg transition duration-200">
                        Cancel
                    </a>

                    <button type="submit" id="createOrderBtn"
                        class="hidden bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Create Production Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const requestCheckboxes = document.querySelectorAll('.request-checkbox');
            const selectedSummary = document.getElementById('selectedSummary');
            const productionOrderSection = document.getElementById('productionOrderSection');
            const createOrderBtn = document.getElementById('createOrderBtn');
            const aggregatedItemsPreview = document.getElementById('aggregatedItemsPreview');

            // Handle select all
            selectAllCheckbox.addEventListener('change', function() {
                const visibleCheckboxes = document.querySelectorAll(
                    '.request-row:not([style*="display: none"]) .request-checkbox');
                visibleCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelection();
            });

            // Handle individual checkbox changes
            requestCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelection);
            });

            function updateSelection() {
                const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
                const hasSelection = checkedBoxes.length > 0;

                // Show/hide summary and form sections
                selectedSummary.classList.toggle('hidden', !hasSelection);
                productionOrderSection.classList.toggle('hidden', !hasSelection);
                createOrderBtn.classList.toggle('hidden', !hasSelection);

                if (hasSelection) {
                    // Update summary stats
                    document.getElementById('selectedCount').textContent = checkedBoxes.length;
                    document.getElementById('totalItems').textContent = checkedBoxes.length;
                    document.getElementById('uniqueItems').textContent = checkedBoxes.length;
                    document.getElementById('totalQuantity').textContent = checkedBoxes.length;

                    aggregatedItemsPreview.classList.remove('hidden');
                } else {
                    aggregatedItemsPreview.classList.add('hidden');
                }

                // Update select all checkbox state
                const visibleCheckboxes = document.querySelectorAll(
                    '.request-row:not([style*="display: none"]) .request-checkbox');
                const checkedVisibleBoxes = document.querySelectorAll(
                    '.request-row:not([style*="display: none"]) .request-checkbox:checked');

                selectAllCheckbox.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.length ===
                    checkedVisibleBoxes.length;
                selectAllCheckbox.indeterminate = checkedVisibleBoxes.length > 0 && checkedVisibleBoxes.length <
                    visibleCheckboxes.length;
            }

            function clearSelection() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectAllCheckbox.checked = false;
                updateSelection();
            }

            window.clearSelection = clearSelection;
        });
    </script>
@endsection
