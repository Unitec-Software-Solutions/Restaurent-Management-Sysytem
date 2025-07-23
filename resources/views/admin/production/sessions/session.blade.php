@extends('layouts.admin')

@section('title', 'Kitchen Production Session')
@section('header-title', 'Kitchen Production Session')
@section('content')
    <div class="mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Kitchen Production Session
                    </h1>
                    <p class="text-gray-600 mt-1">Production Order #{{ $productionOrder->production_order_number }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productionOrder->getStatusBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $productionOrder->status)) }}
                    </span>
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sessions
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200">
                    <h4 class="font-medium mb-2">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Production Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Production Order Summary</h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Number</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->production_order_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Production Date</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->production_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Items</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->items->count() }} items</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created By</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->createdBy->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Items to Produce -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Items to Produce</h2>
                </div>

                <form action="{{ route('admin.production.sessions.complete-production', $productionOrder) }}"
                    method="POST" id="productionForm">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Required Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produced Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Extra Produced</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total to Add</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionOrder->items as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $item->item->unit_of_measurement }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->quantity_to_produce, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[{{ $item->id }}][quantity_produced]"
                                                step="0.01" min="0"
                                                value="{{ old('items.' . $item->id . '.quantity_produced', $item->quantity_to_produce) }}"
                                                class="produced-qty w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                data-required="{{ $item->quantity_to_produce }}"
                                                data-item-id="{{ $item->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[{{ $item->id }}][extra_produced]"
                                                step="0.01" min="0"
                                                value="{{ old('items.' . $item->id . '.extra_produced', 0) }}"
                                                class="extra-qty w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                data-item-id="{{ $item->id }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="total-qty text-sm font-medium text-gray-900"
                                                data-item-id="{{ $item->id }}">
                                                {{ number_format($item->quantity_to_produce, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <textarea name="items[{{ $item->id }}][production_notes]" rows="2"
                                                class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                placeholder="Notes...">{{ old('items.' . $item->id . '.production_notes') }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Production Session Notes -->
                    <div class="p-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-4">
                            <!-- Branch Selection -->
                            <div>
                                <label for="destination_branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-store mr-1"></i>Destination Branch <span class="text-red-500">*</span>
                                </label>
                                <select id="destination_branch_id" name="destination_branch_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select branch to send produced items</option>
                                    @php
                                        $user = Auth::user();
                                        $availableBranches = collect([]);

                                        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                                            $availableBranches = \App\Models\Branch::with('organization')->get();
                                        } elseif ($user->organization_id) {
                                            if ($user->branch_id === null) {
                                                // Organization admin
                                                $availableBranches = \App\Models\Branch::where('organization_id', $user->organization_id)->get();
                                            } else {
                                                // Branch admin - can only send to their own branch
                                                $availableBranches = \App\Models\Branch::where('id', $user->branch_id)->get();
                                            }
                                        }

                                        // Find HQ branch as default
                                        $hqBranch = $availableBranches->where('is_head_office', true)->first();
                                        $defaultBranchId = old('destination_branch_id', $hqBranch?->id ?? $user->branch_id);
                                    @endphp

                                    @foreach ($availableBranches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $defaultBranchId == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                            @if ($branch->is_head_office)
                                                (Head Office)
                                            @endif
                                            @if (isset($branch->organization))
                                                - {{ $branch->organization->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Produced items will be added to the selected branch's inventory
                                </p>
                            </div>

                            <!-- Session Notes -->
                            <div>
                                <label for="session_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Production Session Notes
                                </label>
                                <textarea id="session_notes" name="session_notes" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Add notes about this production session, batch information, quality notes, etc...">{{ old('session_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Production Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if ($productionOrder->status === 'approved')
                                    <button type="submit" name="action" value="start"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                                        <i class="fas fa-play mr-2"></i>Start Production
                                    </button>
                                @elseif ($productionOrder->status === 'in_progress')
                                    <button type="submit" name="action" value="complete"
                                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                                        <i class="fas fa-check mr-2"></i>Complete Production & Add to Inventory
                                    </button>
                                @endif

                                <button type="button" id="previewTransactionsBtn"
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                    <i class="fas fa-eye mr-2"></i>Preview Inventory Transactions
                                </button>
                            </div>

                            <div class="text-sm text-gray-500">
                                Last updated: {{ $productionOrder->updated_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ingredients Used (if available) -->
            @if ($productionOrder->ingredients->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Ingredients Status</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ingredient</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Planned Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issued Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionOrder->ingredients as $ingredient)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $ingredient->ingredient->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $ingredient->unit_of_measurement }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ingredient->planned_quantity, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($ingredient->issued_quantity, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($ingredient->issued_quantity >= $ingredient->planned_quantity)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Complete
                                                </span>
                                            @elseif ($ingredient->issued_quantity > 0)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>Partial
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-minus mr-1"></i>Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Transaction Preview Modal -->
    <div id="transactionPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-96 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Inventory Transaction Preview</h3>
                        <button type="button" id="closeTransactionModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6 overflow-y-auto max-h-80">
                    <div id="transactionPreviewContent">
                        <!-- Transaction preview will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Calculate total quantities
                function updateTotalQuantity(itemId) {
                    const producedInput = document.querySelector(`.produced-qty[data-item-id="${itemId}"]`);
                    const extraInput = document.querySelector(`.extra-qty[data-item-id="${itemId}"]`);
                    const totalSpan = document.querySelector(`.total-qty[data-item-id="${itemId}"]`);

                    const produced = parseFloat(producedInput.value) || 0;
                    const extra = parseFloat(extraInput.value) || 0;
                    const total = produced + extra;

                    totalSpan.textContent = total.toFixed(2);
                }

                // Add event listeners to quantity inputs
                document.querySelectorAll('.produced-qty, .extra-qty').forEach(input => {
                    input.addEventListener('input', function() {
                        const itemId = this.dataset.itemId;
                        updateTotalQuantity(itemId);
                    });
                });

                // Preview transactions
                document.getElementById('previewTransactionsBtn')?.addEventListener('click', function() {
                    const modal = document.getElementById('transactionPreviewModal');
                    const content = document.getElementById('transactionPreviewContent');

                    modal.classList.remove('hidden');
                    content.innerHTML =
                        '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i><p class="text-gray-500 mt-2">Calculating transactions...</p></div>';

                    // Collect production data
                    const productionData = [];
                    document.querySelectorAll('.produced-qty').forEach(input => {
                        const itemId = input.dataset.itemId;
                        const produced = parseFloat(input.value) || 0;
                        const extra = parseFloat(document.querySelector(
                            `.extra-qty[data-item-id="${itemId}"]`).value) || 0;
                        const total = produced + extra;

                        if (total > 0) {
                            const itemName = input.closest('tr').querySelector('.text-sm.font-medium')
                                .textContent;
                            productionData.push({
                                itemId: itemId,
                                itemName: itemName,
                                produced: produced,
                                extra: extra,
                                total: total
                            });
                        }
                    });

                    // Generate preview
                    setTimeout(() => {
                        let html = '<div class="space-y-4">';
                        html +=
                            '<h4 class="font-medium text-gray-900">Inventory Transactions to be Created:</h4>';

                        if (productionData.length > 0) {
                            html +=
                                '<div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50"><tr>';
                            html += '<th class="px-3 py-2 text-left">Item</th>';
                            html += '<th class="px-3 py-2 text-left">Quantity</th>';
                            html += '<th class="px-3 py-2 text-left">Type</th>';
                            html += '<th class="px-3 py-2 text-left">Notes</th>';
                            html += '</tr></thead><tbody>';

                            productionData.forEach(item => {
                                html += `<tr class="border-t">`;
                                html += `<td class="px-3 py-2">${item.itemName}</td>`;
                                html += `<td class="px-3 py-2">+${item.total.toFixed(2)}</td>`;
                                html +=
                                    `<td class="px-3 py-2"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Production In</span></td>`;
                                html +=
                                    `<td class="px-3 py-2">Production Order #{{ $productionOrder->production_order_number }}</td>`;
                                html += `</tr>`;
                            });

                            html += '</tbody></table></div>';
                        } else {
                            html +=
                                '<p class="text-gray-500">No items to add to inventory. Please enter production quantities.</p>';
                        }

                        html += '</div>';
                        content.innerHTML = html;
                    }, 1000);
                });

                // Close modal
                document.getElementById('closeTransactionModal')?.addEventListener('click', function() {
                    document.getElementById('transactionPreviewModal').classList.add('hidden');
                });

                // Form validation
                document.getElementById('productionForm')?.addEventListener('submit', function(e) {
                    const action = e.submitter.value;

                    if (action === 'complete') {
                        // Check if branch is selected
                        const branchSelect = document.getElementById('destination_branch_id');
                        if (!branchSelect.value) {
                            e.preventDefault();
                            alert('Please select a destination branch for the produced items.');
                            branchSelect.focus();
                            return;
                        }

                        // Check if any production quantities are entered
                        let hasProduction = false;
                        document.querySelectorAll('.produced-qty').forEach(input => {
                            if (parseFloat(input.value) > 0) {
                                hasProduction = true;
                            }
                        });

                        if (!hasProduction) {
                            e.preventDefault();
                            alert('Please enter at least one item with produced quantity greater than 0.');
                            return;
                        }

                        if (!confirm(
                                'This will complete the production and add items to the selected branch inventory. Are you sure?'
                                )) {
                            e.preventDefault();
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
