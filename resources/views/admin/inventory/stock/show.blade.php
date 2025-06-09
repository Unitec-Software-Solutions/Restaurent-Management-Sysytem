@extends('layouts.admin')

@section('title', 'Transaction Details')

@section('content')
<div class="p-4 rounded-lg">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-6">
        <!-- Header -->
        <div class="mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Transaction Details</h2>
            <div class="flex items-center space-x-4">
                {{-- <x-partials.badges.status-badge :status="$transaction->is_active ? 'success' : 'danger'"
                    :text="$transaction->is_active ? 'Active' : 'Inactive'"
                /> --}}
                <span class="text-sm text-gray-500">
                    {{ $transaction->created_at->format('M d, Y H:i') }}
                </span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Transaction Overview -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>
                    Transaction Overview
                </h3>

                <x-partials.detail-item label="Item">
                    {{ $transaction->item->name }} ({{ $transaction->item->item_code }})
                </x-partials.detail-item>

                <x-partials.detail-item label="Transaction Type">
                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                </x-partials.detail-item>

                <x-partials.detail-item label="Branch">
                    {{ optional($transaction->branch)->name ?? 'N/A' }}
                    @if($transaction->branch)
                        <div class="text-sm text-gray-500 mt-1">
                            {{ $transaction->branch->address }}<br>
                            {{ $transaction->branch->phone }}
                        </div>
                    @endif
                </x-partials.detail-item>
            </div>

            <!-- Transfer Details -->
            @if($transaction->incoming_branch_id)
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-truck-moving mr-2 text-indigo-600"></i>
                    Transfer Details
                </h3>

                <x-partials.detail-item label="To Branch">
                    {{ optional($transaction->transferToBranch)->name ?? 'N/A' }}
                    @if($transaction->transferToBranch)
                        <div class="text-sm text-gray-500 mt-1">
                            {{ $transaction->transferToBranch->address }}<br>
                            {{ $transaction->transferToBranch->phone }}
                        </div>
                    @endif
                </x-partials.detail-item>

                <x-partials.detail-item label="Received By">
                    @if($transaction->receiver)
                        {{ $transaction->receiver->name }}
                        <div class="text-sm text-gray-500">{{ $transaction->receiver->email }}</div>
                    @else
                        N/A
                    @endif
                </x-partials.detail-item>
            </div>
            @endif

            <!-- Pricing Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-coins mr-2 text-indigo-600"></i>
                    Pricing Information
                </h3>

                <x-partials.detail-item label="Cost Price">
                    Rs. {{ number_format($transaction->cost_price, 4) }}
                </x-partials.detail-item>

                <x-partials.detail-item label="Unit Price">
                    Rs. {{ number_format($transaction->unit_price, 4) }}
                </x-partials.detail-item>
            </div>

            <!-- Quantity Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-balance-scale mr-2 text-indigo-600"></i>
                    Quantities
                </h3>

                <x-partials.detail-item label="Initial Quantity">
                    {{ number_format($transaction->quantity, 2) }} {{ $transaction->item->unit_of_measurement }}
                </x-partials.detail-item>

                <x-partials.detail-item label="Received Quantity">
                    {{ number_format($transaction->received_quantity, 2) }} {{ $transaction->item->unit_of_measurement }}
                </x-partials.detail-item>

                <x-partials.detail-item label="Damaged Quantity">
                    {{ number_format($transaction->damaged_quantity, 2) }} {{ $transaction->item->unit_of_measurement }}
                </x-partials.detail-item>
            </div>

            <!-- Audit Trail -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i>
                    Audit Trail
                </h3>

                <x-partials.detail-item label="Created By">
                    @if($transaction->creator)
                        {{ $transaction->creator->name }}
                        <div class="text-sm text-gray-500">{{ $transaction->creator->email }}</div>
                    @else
                        System Generated
                    @endif
                </x-partials.detail-item>

                <x-partials.detail-item label="Source">
                    {{ $transaction->source_type ? class_basename($transaction->source_type) : 'Manual Entry' }}
                    @if($transaction->source_id)
                        <div class="text-sm text-gray-500">ID: {{ $transaction->source_id }}</div>
                    @endif
                </x-partials.detail-item>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                <i class="fas fa-sticky-note mr-2 text-indigo-600"></i>
                Additional Notes
            </h3>
            <p class="text-gray-600 whitespace-pre-wrap">{{ $transaction->notes ?? 'No notes provided' }}</p>
        </div>

        <!-- Actions -->
        <div class="mt-6 pt-4 border-t flex justify-end space-x-3">
            <a href="{{ route('admin.inventory.stock.transactions.index') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Transactions
            </a>
        </div>
    </div>
</div>
@endsection
