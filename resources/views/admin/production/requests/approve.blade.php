@extends('layouts.admin')

@section('title', 'Approve Production Request')

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
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productionRequest->getStatusBadgeClass() }}">
                        {{ ucfirst($productionRequest->status) }}
                    </span>
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Requests
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

            <!-- Request Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Summary</h2>

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
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $productionRequest->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Approval Form -->
            <form action="{{ route('admin.production.requests.process-approval', $productionRequest) }}" method="POST"
                id="approvalForm">
                @csrf

                <!-- Items for Approval -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Items for Approval</h2>
                            <div class="flex items-center gap-3">
                                <button type="button" id="approveAllBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-check mr-1"></i>Approve All
                                </button>
                                <button type="button" id="rejectAllBtn"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-times mr-1"></i>Reject All
                                </button>
                            </div>
                        </div>
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
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionRequest->items as $index => $item)
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
                                            {{ number_format($item->quantity_requested, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[{{ $item->id }}][quantity_approved]"
                                                step="0.01" min="0" max="{{ $item->quantity_requested }}"
                                                value="{{ old('items.' . $item->id . '.quantity_approved', $item->quantity_approved ?: $item->quantity_requested) }}"
                                                class="approved-qty w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select name="items[{{ $item->id }}][status]"
                                                class="item-status w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                <option value="approved"
                                                    {{ $item->quantity_approved > 0 ? 'selected' : '' }}>Approved
                                                </option>
                                                <option value="partial"
                                                    {{ $item->quantity_approved > 0 && $item->quantity_approved < $item->quantity_requested ? 'selected' : '' }}>
                                                    Partial</option>
                                                <option value="rejected"
                                                    {{ $item->quantity_approved == 0 ? 'selected' : '' }}>Rejected
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="approve-item text-green-600 hover:text-green-900"
                                                    data-item-id="{{ $item->id }}" title="Approve Full Quantity">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="reject-item text-red-600 hover:text-red-900"
                                                    data-item-id="{{ $item->id }}" title="Reject Item">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
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
                    <textarea name="approval_notes" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add any notes regarding this approval...">{{ old('approval_notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="approve"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-check mr-2"></i>Approve Request
                        </button>
                        <button type="submit" name="action" value="reject"
                            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200"
                            onclick="return confirm('Are you sure you want to reject this entire request?')">
                            <i class="fas fa-times mr-2"></i>Reject Request
                        </button>
                    </div>
                    <a href="{{ route('admin.production.requests.show', $productionRequest) }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition duration-200">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Approve all items
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
                        const itemId = this.dataset.itemId;
                        const row = this.closest('tr');
                        const requestedQty = parseFloat(row.cells[1].textContent.replace(/,/g, ''));
                        const approvedQtyInput = row.querySelector('.approved-qty');
                        const statusSelect = row.querySelector('.item-status');

                        approvedQtyInput.value = requestedQty;
                        statusSelect.value = 'approved';
                    });
                });

                // Individual item rejection
                document.querySelectorAll('.reject-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.dataset.itemId;
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
