@extends('layouts.admin')

@section('header-title', 'GTN Details - Unified System')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.inventory.gtn.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
            </a>
            <div class="flex space-x-2">
                <a href="{{ route('admin.inventory.gtn.print', $gtn->gtn_id) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
                @if ($gtn->isDraft())
                    <a href="{{ route('admin.inventory.gtn.edit', $gtn->gtn_id) }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                @endif
            </div>
        </div>

        <!-- Workflow Progress Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Progress</h3>

            <!-- Workflow Steps -->
            <div class="flex items-center justify-between mb-6">
                <!-- Draft -->
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $gtn->isDraft() || $gtn->isConfirmed() || $gtn->isDelivered() ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                        <i class="fas fa-edit text-sm"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">Draft</span>
                    @if ($gtn->created_at)
                        <span class="text-xs text-gray-500">{{ $gtn->created_at->format('M d, H:i') }}</span>
                    @endif
                </div>

                <!-- Connection Line -->
                <div
                    class="flex-1 h-0.5 {{ $gtn->isConfirmed() || $gtn->isDelivered() ? 'bg-green-500' : 'bg-gray-200' }} mx-2">
                </div>

                <!-- Confirmed -->
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $gtn->isConfirmed() || $gtn->isDelivered() ? 'bg-green-500 text-white' : ($gtn->isDraft() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600') }}">
                        <i class="fas fa-check text-sm"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">Confirmed</span>
                    @if ($gtn->confirmed_at)
                        <span class="text-xs text-gray-500">{{ $gtn->confirmed_at->format('M d, H:i') }}</span>
                    @endif
                </div>

                <!-- Connection Line -->
                <div
                    class="flex-1 h-0.5 {{ $gtn->isPending() && $gtn->isConfirmed() ? 'bg-blue-500' : ($gtn->isReceived() || $gtn->isVerified() || $gtn->isAccepted() ? 'bg-green-500' : 'bg-gray-200') }} mx-2">
                </div>

                <!-- Received -->
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $gtn->isReceived() || $gtn->isVerified() || $gtn->isAccepted() ? 'bg-green-500 text-white' : ($gtn->isPending() && $gtn->isConfirmed() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600') }}">
                        <i class="fas fa-truck text-sm"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">Received</span>
                    @if ($gtn->received_at)
                        <span class="text-xs text-gray-500">{{ $gtn->received_at->format('M d, H:i') }}</span>
                    @endif
                </div>

                <!-- Connection Line -->
                <div
                    class="flex-1 h-0.5 {{ $gtn->isVerified() || $gtn->isAccepted() ? 'bg-green-500' : ($gtn->isReceived() ? 'bg-blue-500' : 'bg-gray-200') }} mx-2">
                </div>

                <!-- Verified -->
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $gtn->isVerified() || $gtn->isAccepted() ? 'bg-green-500 text-white' : ($gtn->isReceived() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600') }}">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">Verified</span>
                    @if ($gtn->verified_at)
                        <span class="text-xs text-gray-500">{{ $gtn->verified_at->format('M d, H:i') }}</span>
                    @endif
                </div>

                <!-- Connection Line -->
                <div
                    class="flex-1 h-0.5 {{ $gtn->isAccepted() || $gtn->isRejected() ? 'bg-green-500' : ($gtn->isVerified() ? 'bg-blue-500' : 'bg-gray-200') }} mx-2">
                </div>

                <!-- Accepted/Rejected -->
                <div class="flex flex-col items-center flex-1">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $gtn->isAccepted() ? 'bg-green-500 text-white' : ($gtn->isRejected() ? 'bg-red-500 text-white' : ($gtn->isVerified() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600')) }}">
                        <i
                            class="fas {{ $gtn->isAccepted() ? 'fa-thumbs-up' : ($gtn->isRejected() ? 'fa-thumbs-down' : 'fa-clipboard-check') }} text-sm"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">
                        @if ($gtn->isAccepted())
                            Accepted
                        @elseif($gtn->isRejected())
                            Rejected
                        @elseif($gtn->isPartiallyAccepted())
                            Partial
                        @else
                            Final
                        @endif
                    </span>
                    @if ($gtn->accepted_at)
                        <span class="text-xs text-gray-500">{{ $gtn->accepted_at->format('M d, H:i') }}</span>
                    @elseif($gtn->rejected_at)
                        <span class="text-xs text-gray-500">{{ $gtn->rejected_at->format('M d, H:i') }}</span>
                    @endif
                </div>
            </div>

            <!-- Current Action Required -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-900">
                            @if ($gtn->isDraft())
                                Ready to Confirm
                            @elseif($gtn->isPending() && $gtn->isConfirmed())
                                Waiting for Receipt
                            @elseif($gtn->isReceived())
                                Ready for Verification
                            @elseif($gtn->isVerified())
                                Ready for Acceptance/Rejection
                            @elseif($gtn->isAccepted() || $gtn->isRejected())
                                Transfer Complete
                            @else
                                Status Update Required
                            @endif
                        </h4>
                        <p class="text-sm text-blue-700">
                            @if ($gtn->isDraft())
                                Click "Confirm Transfer" to deduct stock from sender branch and send to receiver.
                            @elseif($gtn->isPending() && $gtn->isConfirmed())
                                Waiting for receiver branch to mark items as received.
                            @elseif($gtn->isReceived())
                                Items have been received. Verify quality and quantities before final acceptance.
                            @elseif($gtn->isVerified())
                                Items verified. Accept or reject individual items and process final inventory updates.
                            @elseif($gtn->isAccepted())
                                All items accepted. Transfer completed successfully.
                            @elseif($gtn->isRejected())
                                Transfer rejected. Items returned to sender.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2">
                @if ($gtn->isDraft())
                    <button onclick="confirmGTN()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-check mr-2"></i> Confirm Transfer
                    </button>
                @endif

                @if ($gtn->isPending() && $gtn->isConfirmed())
                    <button onclick="receiveGTN()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-truck mr-2"></i> Mark as Received
                    </button>
                @endif

                @if ($gtn->isReceived())
                    <button onclick="verifyGTN()"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-search mr-2"></i> Verify Items
                    </button>
                @endif

                @if ($gtn->isVerified())
                    <button onclick="showAcceptanceModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-clipboard-check mr-2"></i> Process Acceptance
                    </button>
                    <button onclick="showRejectionModal()"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-times mr-2"></i> Reject Transfer
                    </button>
                @endif

                <button onclick="viewAuditTrail()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-history mr-2"></i> Audit Trail
                </button>
            </div>
        </div>

        <!-- GTN Header Card -->
        <div
            class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4
            {{ $gtn->isAccepted()
                ? 'border-green-500'
                : ($gtn->isRejected()
                    ? 'border-red-500'
                    : ($gtn->isVerified()
                        ? 'border-purple-500'
                        : ($gtn->isReceived()
                            ? 'border-blue-500'
                            : ($gtn->isConfirmed()
                                ? 'border-green-500'
                                : 'border-yellow-500')))) }}">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">GTN #{{ $gtn->gtn_number }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">Origin Status:</p>
                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $gtn->origin_status === 'draft'
                                    ? 'bg-gray-100 text-gray-800'
                                    : ($gtn->origin_status === 'confirmed'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($gtn->origin_status === 'in_delivery'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-green-100 text-green-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $gtn->origin_status ?? 'draft')) }}
                            </span>
                            <p class="text-sm text-gray-500">Receiver Status:</p>
                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $gtn->receiver_status === 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : ($gtn->receiver_status === 'received'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($gtn->receiver_status === 'verified'
                                            ? 'bg-purple-100 text-purple-800'
                                            : ($gtn->receiver_status === 'accepted'
                                                ? 'bg-green-100 text-green-800'
                                                : ($gtn->receiver_status === 'rejected'
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-orange-100 text-orange-800')))) }}">
                                {{ ucfirst(str_replace('_', ' ', $gtn->receiver_status ?? 'pending')) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day mr-2"></i>
                            <span>Transfer Date: {{ \Carbon\Carbon::parse($gtn->transfer_date)->format('M d, Y') }}</span>
                        </div>
                        @if ($gtn->reference_number)
                            <div class="flex items-center">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span>Ref: {{ $gtn->reference_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-3xl font-bold text-indigo-600">Rs.
                        {{ number_format($gtn->items->sum(function ($item) {return $item->transfer_quantity * $item->transfer_price;}),2) }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">Total Value</div>
                    @if ($gtn->items->sum('quantity_rejected') > 0)
                        <div class="text-lg font-semibold text-red-600 mt-1">
                            Rejected: {{ number_format($gtn->items->sum('quantity_rejected'), 2) }} units
                        </div>
                    @endif
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
                        <p class="font-medium">{{ $gtn->fromBranch->name ?? 'N/A' }}</p>
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
                    <div>
                        <p class="text-sm text-gray-500">Manager</p>
                        <p class="font-medium">{{ $gtn->fromBranch->manager_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- To Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">To Branch</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $gtn->toBranch->name ?? 'N/A' }}</p>
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
                    <div>
                        <p class="text-sm text-gray-500">Manager</p>
                        <p class="font-medium">{{ $gtn->toBranch->manager_name ?? 'N/A' }}</p>
                    </div>
                    @if ($gtn->received_by)
                        <div class="pt-2 border-t">
                            <p class="text-sm text-gray-500">Received By</p>
                            <p class="font-medium">{{ $gtn->receivedBy->first_name ?? 'N/A' }}
                                {{ $gtn->receivedBy->last_name ?? '' }}</p>
                        </div>
                    @endif
                    @if ($gtn->received_at)
                        <div>
                            <p class="text-sm text-gray-500">Received At</p>
                            <p class="font-medium">{{ $gtn->received_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- GTN Financial Summary (moved to third column) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <h2 class="text-lg font-semibold mb-4 text-indigo-700">Transfer Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-semibold">{{ $gtn->items->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Quantity:</span>
                        <span class="font-semibold">{{ number_format($gtn->items->sum('transfer_quantity'), 2) }}</span>
                    </div>
                    @if ($gtn->items->sum('quantity_accepted') > 0)
                        <div class="flex justify-between items-center text-green-600">
                            <span>Accepted Quantity:</span>
                            <span
                                class="font-semibold">{{ number_format($gtn->items->sum('quantity_accepted'), 2) }}</span>
                        </div>
                    @endif
                    @if ($gtn->items->sum('quantity_rejected') > 0)
                        <div class="flex justify-between items-center text-red-600">
                            <span>Rejected Quantity:</span>
                            <span
                                class="font-semibold">{{ number_format($gtn->items->sum('quantity_rejected'), 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-900 font-semibold">Total Value:</span>
                            <span class="font-bold text-lg text-indigo-600">Rs.
                                {{ number_format($gtn->items->sum(function ($item) {return $item->transfer_quantity * $item->transfer_price;}),2) }}</span>
                        </div>
                    </div>
                    @if ($gtn->items->sum('quantity_accepted') > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Accepted Value:</span>
                            <span class="font-semibold text-green-600">Rs.
                                {{ number_format($gtn->items->sum(function ($item) {return ($item->quantity_accepted ?? 0) * $item->transfer_price;}),2) }}</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Created By:</span>
                            <span class="font-medium">{{ $gtn->createdBy->first_name ?? 'System' }}
                                {{ $gtn->createdBy->last_name ?? '' }}</span>
                        </div>
                        @if ($gtn->confirmed_at)
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-600">Confirmed By:</span>
                                <span class="font-medium">{{ $gtn->confirmedBy->first_name ?? 'N/A' }}
                                    {{ $gtn->confirmedBy->last_name ?? '' }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $gtn->created_at->format('M d, Y H:i') }}</span>
                        </div>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Batch</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transfer Qty</th>
                            @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected())
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Accepted</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rejected</th>
                            @endif
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Line Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expiry</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($gtn->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->batch_no ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        class="font-medium text-indigo-600">{{ number_format($item->transfer_quantity, 2) }}</span>
                                </td>
                                @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected())
                                    <td class="px-4 py-3 text-right text-sm">
                                        @if ($item->quantity_accepted > 0)
                                            <span
                                                class="font-medium text-green-600">{{ number_format($item->quantity_accepted, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">0.00</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        @if ($item->quantity_rejected > 0)
                                            <span
                                                class="text-red-600 font-medium">{{ number_format($item->quantity_rejected, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">0.00</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-right text-sm">
                                    Rs. {{ number_format($item->transfer_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="font-semibold">Rs. {{ number_format($item->line_total, 2) }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($item->expiry_date)
                                        <div class="text-gray-600">
                                            {{ $item->expiry_date->format('M d, Y') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $itemStatus = $item->item_status ?? 'pending';
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'partially_accepted' => 'bg-orange-100 text-orange-800',
                                        ];
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$itemStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $itemStatus)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($item->notes)
                                        <div class="text-sm text-gray-600">{{ $item->notes }}</div>
                                    @endif
                                    @if ($item->item_rejection_reason)
                                        <div class="text-sm text-red-600 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ $item->item_rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if ($item->quantity_rejected > 0 && $item->item_rejection_reason)
                                <tr>
                                    <td colspan="10" class="px-4 py-2 text-sm bg-red-50 border-l-4 border-red-400">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                            <span class="font-medium text-red-700">Rejection Reason:</span>
                                            <span class="text-red-600 ml-2">{{ $item->item_rejection_reason }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2">
                        <tr>
                            <td colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() ? '6' : '4' }}"
                                class="px-4 py-3 text-right font-semibold text-gray-700">Total Value:</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">Rs.
                                {{ number_format($gtn->items->sum('line_total'), 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                        @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected())
                            @if ($gtn->items->sum('quantity_accepted') > 0)
                                <tr>
                                    <td colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() ? '6' : '4' }}"
                                        class="px-4 py-2 text-right font-medium text-green-600">Accepted Value:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-green-600">Rs.
                                        {{ number_format($gtn->items->sum(function ($item) {return ($item->quantity_accepted ?? 0) * $item->transfer_price;}),2) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            @endif
                            @if ($gtn->items->sum('quantity_rejected') > 0)
                                <tr>
                                    <td colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() ? '6' : '4' }}"
                                        class="px-4 py-2 text-right font-medium text-red-600">Rejected Value:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-red-600">Rs.
                                        {{ number_format($gtn->items->sum(function ($item) {return ($item->quantity_rejected ?? 0) * $item->transfer_price;}),2) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            @endif
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($gtn->notes)
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                <p class="text-gray-600 whitespace-pre-line">{{ $gtn->notes }}</p>
            </div>
        @endif

        <!-- Inventory Transactions -->
        @if ($gtn->inventoryTransactions->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Inventory Transactions</h3>
                    <p class="text-sm text-gray-500">Stock movements related to this GTN</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($gtn->inventoryTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->item->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $typeColors = [
                                                'gtn_outgoing' => 'bg-red-100 text-red-800',
                                                'gtn_incoming' => 'bg-green-100 text-green-800',
                                                'gtn_rejection' => 'bg-orange-100 text-orange-800',
                                            ];
                                        @endphp
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $typeColors[$transaction->transaction_type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium {{ $transaction->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->quantity >= 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $transaction->notes }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Acceptance Modal -->
    <div id="acceptanceModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Process Item Acceptance</h3>
                <button onclick="closeAcceptanceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="acceptanceForm">
                <div class="space-y-4">
                    @foreach ($gtn->items as $item)
                        <div class="border rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <div>
                                    <h4 class="font-medium">{{ $item->item_name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $item->item_code }}</p>
                                    <p class="text-sm text-gray-600">Transfer Qty:
                                        {{ number_format($item->transfer_quantity, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Accepted Quantity</label>
                                    <input type="number"
                                        name="acceptance_data[{{ $item->gtn_item_id }}][quantity_accepted]"
                                        value="{{ $item->transfer_quantity }}" max="{{ $item->transfer_quantity }}"
                                        min="0" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                                    <input type="text"
                                        name="acceptance_data[{{ $item->gtn_item_id }}][rejection_reason]"
                                        placeholder="Optional"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quality Notes</label>
                                    <input type="text" name="acceptance_data[{{ $item->gtn_item_id }}][quality_notes]"
                                        placeholder="Optional"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="processAcceptance()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Process Acceptance
                    </button>
                    <button type="button" onclick="closeAcceptanceModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reject Transfer</h3>
                <button onclick="closeRejectionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectionForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea name="rejection_reason" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                        rows="3" placeholder="Please provide a reason for rejecting this transfer..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="rejectGTN()"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        Reject Transfer
                    </button>
                    <button type="button" onclick="closeRejectionModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Meta tag for CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Workflow action functions
        function confirmGTN() {
            if (confirm('Are you sure you want to confirm this GTN? This will deduct stock from the sender branch.')) {
                fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/confirm`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    });
            }
        }

        function receiveGTN() {
            const notes = prompt('Any notes about the receipt?');
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/receive`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function verifyGTN() {
            const notes = prompt('Any verification notes?');
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function showAcceptanceModal() {
            document.getElementById('acceptanceModal').classList.remove('hidden');
        }

        function closeAcceptanceModal() {
            document.getElementById('acceptanceModal').classList.add('hidden');
        }

        function processAcceptance() {
            const formData = new FormData(document.getElementById('acceptanceForm'));
            const acceptanceData = {};

            for (let [key, value] of formData.entries()) {
                const match = key.match(/acceptance_data\[(\d+)\]\[(.+)\]/);
                if (match) {
                    const itemId = match[1];
                    const field = match[2];
                    if (!acceptanceData[itemId]) acceptanceData[itemId] = {};
                    acceptanceData[itemId][field] = value;
                }
            }

            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        acceptance_data: acceptanceData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function showRejectionModal() {
            document.getElementById('rejectionModal').classList.remove('hidden');
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.add('hidden');
        }

        function rejectGTN() {
            const formData = new FormData(document.getElementById('rejectionForm'));
            const rejectionReason = formData.get('rejection_reason');

            if (!rejectionReason) {
                alert('Please provide a rejection reason.');
                return;
            }

            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        rejection_reason: rejectionReason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function viewAuditTrail() {
            fetch(`/admin/inventory/gtn/{{ $gtn->gtn_id }}/audit-trail`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display audit trail in a modal or new page
                        console.log('Audit Trail:', data.data);
                        alert('Audit trail displayed in console. See browser developer tools.');
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        // Auto-calculate rejected quantity when accepted quantity changes
        document.addEventListener('DOMContentLoaded', function() {
            const acceptedInputs = document.querySelectorAll('input[name*="quantity_accepted"]');
            acceptedInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // You can add auto-calculation logic here
                });
            });
        });
    </script>
@endsection
