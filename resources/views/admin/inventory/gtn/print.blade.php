@extends('layouts.admin')

@section('header-title', 'Print Goods Transfer Note')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">PRINT - Goods Transfer Note: {{ $gtn->gtn_number }}</h2>
                    <p class="text-sm text-gray-500">Transfer details</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.gtn.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                    </a>
                    <a href=" "
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-print mr-2"></i> Print
                    </a>
                    @if ($gtn->status == 'Pending')
                        <a href="{{ route('admin.inventory.gtn.edit', $gtn->gtn_id) }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>

                        <!-- Status Change Buttons -->
                        <div class="flex gap-2">
                            <button onclick="changeStatus('Confirmed')"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-check mr-2"></i> Confirm
                            </button>
                            <button onclick="changeStatus('Approved')"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-thumbs-up mr-2"></i> Approve
                            </button>
                            <button onclick="changeStatus('Verified')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-shield-check mr-2"></i> Verify
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- GTN Info Section -->
            <div class="p-6 border-b">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">GTN Number</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->gtn_number }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Transfer Date</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->transfer_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Reference Number</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->reference_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">From Branch</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->fromBranch->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">To Branch</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->toBranch->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Status</h3>
                        <p class="mt-1 text-sm">
                            @if ($gtn->status == 'Pending')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($gtn->status == 'Confirmed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Confirmed
                                </span>
                            @elseif($gtn->status == 'Approved')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Approved
                                </span>
                            @elseif($gtn->status == 'Verified')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Verified
                                </span>
                            @elseif($gtn->status == 'Completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Completed
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Cancelled
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Transfer Items</h3>

                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Item Code</th>
                                    <th class="px-4 py-3">Item Name</th>
                                    <th class="px-4 py-3">Batch No</th>
                                    <th class="px-4 py-3">Quantity</th>
                                    <th class="px-4 py-3">Unit Price</th>
                                    <th class="px-4 py-3">Line Total</th>
                                    <th class="px-4 py-3">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gtn->items as $item)
                                    <tr class="border-b bg-white">
                                        <td class="px-4 py-3">{{ $item->item_code }}</td>
                                        <td class="px-4 py-3">{{ $item->item->name ?? $item->item_name }}</td>
                                        <td class="px-4 py-3">{{ $item->batch_no ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $item->transfer_quantity }}</td>
                                        <td class="px-4 py-3">Internal Transfer</td>
                                        <td class="px-4 py-3">-</td>
                                        <td class="px-4 py-3">
                                            {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="5" class="px-4 py-3 text-right">Total Items Transferred</td>
                                    <td class="px-4 py-3">{{ $gtn->items->sum('transfer_quantity') }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="p-6 border-t">
                <h3 class="text-sm font-medium text-gray-500">Notes</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $gtn->notes ?? 'No notes provided' }}</p>
            </div>

            <!-- Created/Updated Info -->
            <div class="p-6 border-t bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->createdBy->name ?? 'System' }}</p>
                        <p class="text-xs text-gray-500">{{ $gtn->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $gtn->updatedBy->name ?? 'System' }}</p>
                        <p class="text-xs text-gray-500">{{ $gtn->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div id="statusChangeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden"
        style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2" id="modalTitle">Confirm Status Change</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage">
                        Are you sure you want to change the status? This action cannot be undone and will process stock
                        transfers.
                    </p>
                    <div class="mt-4">
                        <label for="statusNotes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea id="statusNotes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmStatusBtn"
                        class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Confirm
                    </button>
                    <button onclick="closeModal()"
                        class="mt-3 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Form (Hidden) -->
    <form id="statusChangeForm" action="{{ route('admin.inventory.gtn.change-status', $gtn->gtn_id) }}" method="POST"
        style="display: none;">
        @csrf
        <input type="hidden" name="status" id="statusInput">
        <input type="hidden" name="notes" id="notesInput">
    </form>

    <script>
        let selectedStatus = '';

        function changeStatus(status) {
            selectedStatus = status;
            document.getElementById('modalTitle').textContent = `${status} GTN`;
            document.getElementById('modalMessage').textContent =
                `Are you sure you want to ${status.toLowerCase()} this GTN? This will process the stock transfer and cannot be undone.`;
            document.getElementById('statusChangeModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('statusChangeModal').classList.add('hidden');
            document.getElementById('statusNotes').value = '';
        }

        document.getElementById('confirmStatusBtn').addEventListener('click', function() {
            const notes = document.getElementById('statusNotes').value;
            document.getElementById('statusInput').value = selectedStatus;
            document.getElementById('notesInput').value = notes;
            document.getElementById('statusChangeForm').submit();
        });

        // Close modal when clicking outside
        document.getElementById('statusChangeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
@endsection
