@extends('layouts.admin')

@section('header-title', 'View GTN')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Goods Transfer Note: {{ $gtn->gtn_number }}</h2>
                    <p class="text-sm text-gray-500">Transfer details</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.gtn.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to GTNs
                    </a>
                    <a href="{{ route('admin.gtn.print', $gtn->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-print mr-2"></i> Print
                    </a>
                    @if($gtn->status == 'Pending')
                        <a href="{{ route('admin.gtn.edit', $gtn->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
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
                                        <td class="px-4 py-3">{{ $item->item->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $item->batch_no ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $item->transfer_quantity }}</td>
                                        <td class="px-4 py-3">${{ number_format($item->transfer_price, 2) }}</td>
                                        <td class="px-4 py-3">${{ number_format($item->transfer_quantity * $item->transfer_price, 2) }}</td>
                                        <td class="px-4 py-3">{{ $item->expiry_date ? $item->expiry_date->format('d M Y') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td colspan="5" class="px-4 py-3 text-right">Total</td>
                                    <td class="px-4 py-3">${{ number_format($gtn->items->sum(function($item) { return $item->transfer_quantity * $item->transfer_price; }), 2) }}</td>
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
@endsection
