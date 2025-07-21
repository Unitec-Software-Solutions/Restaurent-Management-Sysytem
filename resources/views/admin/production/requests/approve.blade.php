@extends('layouts.admin')

@section('title', 'Approve Production Request')

@section('header-title', 'Approve Production Request')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    // ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Requests" />
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-2 mt-0.5"></i>
                    <div class="text-sm text-green-800">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Please fix the following errors:</h4>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        Approve Production Request #{{ $productionRequest->id }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $productionRequest->branch->name }} - {{ $productionRequest->request_date->format('M d, Y') }}
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $productionRequest->getStatusBadgeClass() }}">
                            {{ ucfirst($productionRequest->status) }}
                        </span>
                        <span class="text-sm text-blue-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            You can approve quantities higher than requested
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.production.requests.manage') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Management
                    </a>
                </div>
            </div>

            <!-- Request Summary -->
            <div class="p-6 border-b bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Branch</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->branch->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Request Date</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->request_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Required Date</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->required_date->format('M d, Y') }}</p>
                        @if ($productionRequest->required_date->isPast())
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                Overdue
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Items</label>
                        <p class="text-sm text-gray-900">{{ $productionRequest->items->count() }} items</p>
                    </div>
                </div>

                @if ($productionRequest->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Request Notes</label>
                        <p class="text-sm text-gray-900 mt-1 bg-white p-3 rounded-lg border">
                            {{ $productionRequest->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Approval Form -->
            <form action="{{ route('admin.production.requests.processApproval', $productionRequest) }}" method="POST"
                id="approvalForm">
                @csrf

                <!-- Bulk Actions -->
                <div class="p-6 border-b bg-blue-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Items for Approval</h3>
                        <div class="flex items-center gap-3">
                            <button type="button" id="approveAllBtn"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                                <i class="fas fa-check mr-2"></i>Approve All Requested
                            </button>
                            <button type="button" id="rejectAllBtn"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm flex items-center">
                                <i class="fas fa-times mr-2"></i>Reject All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Requested Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Approved Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($productionRequest->items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $item->item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ number_format($item->quantity_requested, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="items[{{ $item->id }}][quantity_approved]"
                                            step="0.01" min="0"
                                            value="{{ old('items.' . $item->id . '.quantity_approved', $item->quantity_approved ?: $item->quantity_requested) }}"
                                            class="approved-qty w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            placeholder="Can exceed requested">
                                    </td>
                                    <td class="px-6 py-4">
                                        <select name="items[{{ $item->id }}][status]"
                                            class="item-status px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="approved" {{ $item->quantity_approved > 0 ? 'selected' : '' }}>
                                                Approved</option>
                                            <option value="enhanced"
                                                {{ $item->quantity_approved > $item->quantity_requested ? 'selected' : '' }}>
                                                Enhanced</option>
                                            <option value="partial"
                                                {{ $item->quantity_approved > 0 && $item->quantity_approved < $item->quantity_requested ? 'selected' : '' }}>
                                                Partial</option>
                                            <option value="rejected" {{ $item->quantity_approved == 0 ? 'selected' : '' }}>
                                                Rejected</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <button type="button" class="approve-item text-green-600 hover:text-green-900"
                                                data-item-id="{{ $item->id }}"
                                                data-requested="{{ $item->quantity_requested }}" title="Approve Requested">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="enhance-item text-blue-600 hover:text-blue-900"
                                                data-item-id="{{ $item->id }}"
                                                data-requested="{{ $item->quantity_requested }}" title="Approve 120%">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="reject-item text-red-600 hover:text-red-900"
                                                data-item-id="{{ $item->id }}" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Approval Notes -->
                <div class="p-6 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Approval Notes</h3>
                    <textarea name="approval_notes" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Add notes regarding this approval, especially if quantities were enhanced...">{{ old('approval_notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="p-6 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button type="submit" name="action" value="approve"
                                class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium flex items-center">
                                <i class="fas fa-check mr-2"></i>Approve Request
                            </button>
                            <button type="submit" name="action" value="reject"
                                class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-medium flex items-center"
                                onclick="return confirm('Are you sure you want to reject this entire request?')">
                                <i class="fas fa-times mr-2"></i>Reject Request
                            </button>
                        </div>
                        <a href="{{ route('admin.production.requests.show', $productionRequest) }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg flex items-center">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Approve all items with requested quantities
                document.getElementById('approveAllBtn').addEventListener('click', function() {
                    document.querySelectorAll('.approved-qty').forEach(input => {
                        const row = input.closest('tr');
                        const requestedQty = parseFloat(row.cells[1].textContent.replace(/,/g, ''));
                        input.value = requestedQty;
                        row.querySelector('.item-status').value = 'approved';
                    });
                });

                // Reject all items
                document.getElementById('rejectAllBtn').addEventListener('click', function() {
                    if (confirm('Are you sure you want to reject all items?')) {
                        document.querySelectorAll('.approved-qty').forEach(input => {
                            input.value = 0;
                            input.closest('tr').querySelector('.item-status').value = 'rejected';
                        });
                    }
                });

                // Individual item approval
                document.querySelectorAll('.approve-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const requestedQty = parseFloat(this.dataset.requested);
                        const approvedQtyInput = row.querySelector('.approved-qty');
                        const statusSelect = row.querySelector('.item-status');

                        approvedQtyInput.value = requestedQty;
                        statusSelect.value = 'approved';
                    });
                });

                // Individual item enhancement (120% of requested)
                document.querySelectorAll('.enhance-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const requestedQty = parseFloat(this.dataset.requested);
                        const enhancedQty = requestedQty * 1.2; // 120% of requested
                        const approvedQtyInput = row.querySelector('.approved-qty');
                        const statusSelect = row.querySelector('.item-status');

                        approvedQtyInput.value = enhancedQty.toFixed(2);
                        statusSelect.value = 'enhanced';
                    });
                });

                // Individual item rejection
                document.querySelectorAll('.reject-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const approvedQtyInput = row.querySelector('.approved-qty');
                        const statusSelect = row.querySelector('.item-status');

                        approvedQtyInput.value = 0;
                        statusSelect.value = 'rejected';
                    });
                });

                // Auto-update status based on approved quantity
                document.querySelectorAll('.approved-qty').forEach(input => {
                    input.addEventListener('input', function() {
                        const row = this.closest('tr');
                        const requestedQty = parseFloat(row.cells[1].textContent.replace(/,/g, ''));
                        const approvedQty = parseFloat(this.value) || 0;
                        const statusSelect = row.querySelector('.item-status');

                        if (approvedQty === 0) {
                            statusSelect.value = 'rejected';
                        } else if (approvedQty === requestedQty) {
                            statusSelect.value = 'approved';
                        } else if (approvedQty > requestedQty) {
                            statusSelect.value = 'enhanced';
                        } else {
                            statusSelect.value = 'partial';
                        }
                    });
                });

                // Validate quantities
                document.getElementById('approvalForm').addEventListener('submit', function(e) {
                    let hasValidItems = false;

                    document.querySelectorAll('.approved-qty').forEach(input => {
                        const approvedQty = parseFloat(input.value) || 0;
                        if (approvedQty > 0) {
                            hasValidItems = true;
                        }
                    });

                    if (!hasValidItems && e.submitter.value === 'approve') {
                        e.preventDefault();
                        alert('Please approve at least one item with quantity greater than 0.');
                    }
                });
            });
        </script>
    @endpush
@endsection
</script>
@endpush
@endsection
