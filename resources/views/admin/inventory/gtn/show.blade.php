@extends('layouts.admin')

@section('header-title', 'Goods Transfer Note Details')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.inventory.gtn.index') }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
            </a>
            <div class="flex space-x-2">
                @if ($gtn->status == 'Pending')
                    <button onclick="changeStatus('Confirmed')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-check mr-2"></i> Confirm GTN
                    </button>
                    <button disabled
                        class="bg-purple-400 text-white px-4 py-2 rounded-lg flex items-center opacity-60 cursor-not-allowed">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                @endif
            </div>
        </div>

        <!-- GTN Header Card -->
        <div
            class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4
            @if ($gtn->status == 'Pending') border-yellow-500
            @elseif($gtn->status == 'Confirmed') border-green-500
            @elseif($gtn->status == 'Approved') border-blue-500
            @elseif($gtn->status == 'Verified') border-purple-500
            @elseif($gtn->status == 'Completed') border-green-500
            @else border-red-500 @endif">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">GTN #{{ $gtn->gtn_number }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">Status :</p>
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
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day mr-2"></i>
                            <span>Transfer Date:
                                {{ \Illuminate\Support\Carbon::parse($gtn->transfer_date)->format('M d, Y') }}</span>
                        </div>
                        @if ($gtn->reference_number)
                            <div class="flex items-center">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span>Reference: {{ $gtn->reference_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-2xl font-bold text-indigo-600">{{ $gtn->items->sum('transfer_quantity') }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Items Transferred</div>
                </div>
            </div>
        </div>

        <!-- GTN Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- From Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">From Branch</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $gtn->fromBranch->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $gtn->fromBranch->code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $gtn->fromBranch->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $gtn->fromBranch->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- To Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">To Branch</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $gtn->toBranch->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $gtn->toBranch->code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $gtn->toBranch->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $gtn->toBranch->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- GTN Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Transfer Summary</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-bold">{{ $gtn->items->sum('transfer_quantity') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created By:</span>
                        <span class="font-medium">{{ $gtn->createdBy->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created At:</span>
                        <span class="font-medium">{{ $gtn->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated By:</span>
                        <span class="font-medium">{{ $gtn->updatedBy->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated:</span>
                        <span class="font-medium">{{ $gtn->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GTN Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Transfer Items</h2>
                <p class="text-sm text-gray-500">Items included in this goods transfer note</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                                No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EXP
                                Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($gtn->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $item->item->name ?? $item->item_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->item_code }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->batch_no ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-indigo-600">
                                    {{ $item->transfer_quantity }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->expiry_date ? \Illuminate\Support\Carbon::parse($item->expiry_date)->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right font-medium">Total Items:</td>
                            <td class="px-6 py-3 font-bold">{{ $gtn->items->sum('transfer_quantity') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($gtn->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">GTN Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($gtn->notes)) !!}
                </div>
            </div>
        @endif
    </div>

    <!-- Status Change Modal -->
    <div id="statusChangeModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-xl mr-3">
                    <i class="fas fa-exclamation-triangle text-green-600"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800" id="modalTitle">Confirm Status Change</h2>
            </div>
            <p class="mb-6 text-gray-700" id="modalMessage">
                Are you sure you want to change the status? This action cannot be undone and will process stock transfers.
            </p>
            <div class="flex gap-3 mt-6">
                <button id="confirmStatusBtn"
                    class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Yes, Change Status
                </button>
                <button type="button" onclick="closeModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Status Change Form (Hidden) -->
    <form id="statusChangeForm" action="{{ route('admin.inventory.gtn.change-status', $gtn->gtn_id) }}" method="POST"
        style="display: none;">
        @csrf
        <input type="hidden" name="status" id="statusInput">
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
        }

        document.getElementById('confirmStatusBtn').addEventListener('click', function() {
            document.getElementById('statusInput').value = selectedStatus;
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

@push('styles')
    <style>
        .prose {
            color: #374151;
            line-height: 1.6;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        th,
        td {
            padding: 0.75rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        tfoot td {
            font-weight: 600;
            background-color: #f9fafb;
        }
    </style>
@endpush
