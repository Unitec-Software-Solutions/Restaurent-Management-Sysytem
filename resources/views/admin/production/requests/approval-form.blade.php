@extends('layouts.admin')

@section('title', 'Approve Production Request')
@section('header-title', 'Approve Production Request - ' . $productionRequest->id)
@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Approve Production Request #{{ $productionRequest->id }}
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $productionRequest->branch->name }} -
                        {{ $productionRequest->request_date->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Manage
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200 shadow">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Request Summary and Enhanced Approval -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Summary</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Flexible Approval:</strong> You can approve quantities higher than requested if operationally beneficial.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productionRequest->getStatusBadgeClass() }}">
                            {{ ucfirst($productionRequest->status) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Required Date</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->required_date->format('M d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Items</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->items->count() }} items</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Quantity</label>
                        <p class="text-sm text-gray-900">
                            {{ number_format($productionRequest->getTotalQuantityRequested()) }}</p>
                    </div>
                </div>

                @if ($productionRequest->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Request Notes</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $productionRequest->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Approval Form -->
            <form method="POST" action="{{ route('admin.production.requests.approve', $productionRequest) }}">
                @csrf

                <!-- Items to Approve -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Items to Approve</h2>
                        <p class="text-sm text-gray-600 mt-1">Set the approved quantities for each item. You can approve higher quantities than requested if needed.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Requested Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Approved Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionRequest->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->quantity_requested, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[{{ $item->item_id }}][quantity_approved]"
                                                value="{{ old('items.' . $item->item_id . '.quantity_approved', $item->quantity_requested) }}"
                                                step="0.01" min="0"
                                                class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Can exceed requested" required>

                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="text" name="items[{{ $item->item_id }}][notes]"
                                                value="{{ old('items.' . $item->item_id . '.notes') }}"
                                                placeholder="Optional notes"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button"
                                                onclick="approveAll({{ $item->item_id }}, {{ $item->quantity_requested }})"
                                                class="text-green-600 hover:text-green-900 mr-2"
                                                title="Approve Requested Quantity">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button"
                                                onclick="approveEnhanced({{ $item->item_id }}, {{ $item->quantity_requested }})"
                                                class="text-blue-600 hover:text-blue-900 mr-2"
                                                title="Approve 120% of Requested">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" onclick="rejectItem({{ $item->item_id }})"
                                                class="text-red-600 hover:text-red-900" title="Reject Item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Approval Notes -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Approval Notes</h3>
                    <textarea name="approval_notes" rows="3" placeholder="Optional notes about this approval, especially if quantities were enhanced..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('approval_notes') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-check mr-2"></i>Approve Request
                        </button>
                    </div>

                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition duration-200">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveAll(itemId, requestedQty) {
            document.querySelector(`input[name="items[${itemId}][quantity_approved]"]`).value = requestedQty;
        }

        function approveEnhanced(itemId, requestedQty) {
            const enhancedQty = requestedQty * 1.2; // 120% of requested
            document.querySelector(`input[name="items[${itemId}][quantity_approved]"]`).value = enhancedQty.toFixed(2);
        }

        function rejectItem(itemId) {
            document.querySelector(`input[name="items[${itemId}][quantity_approved]"]`).value = 0;
        }

        function approveAllItems() {
            const inputs = document.querySelectorAll('input[name*="[quantity_approved]"]');
            inputs.forEach(input => {
                const max = parseFloat(input.getAttribute('max'));
                input.value = max;
            });
        }
    </script>
@endsection
