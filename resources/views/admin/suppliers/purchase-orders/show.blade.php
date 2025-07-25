@extends('layouts.admin')

@section('header-title', 'Purchase Order Details')
@section('content')


{{-- Debug Info Card for PO Show --}}
{{-- @if(config('app.debug'))
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-medium text-purple-800">🔍 Purchase Order Debug Info</h3>
            <a href="{{ route('admin.purchase-orders.show', $po->po_id ?? 0) }}?debug=1"
               class="text-xs text-purple-600 hover:text-purple-800">
                Full Debug (@dd)
            </a>
        </div>
        <div class="text-xs text-purple-700 mt-2 grid grid-cols-4 gap-4">
            <div>
                <p><strong>PO Variable:</strong> {{ isset($po) ? 'Set' : 'NOT SET' }}</p>
                <p><strong>PO Number:</strong> {{ $po->po_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Items Variable:</strong> {{ isset($items) ? 'Set (' . count($items) . ')' : 'NOT SET' }}</p>
                <p><strong>PO Items:</strong> {{ isset($po) ? $po->items->count() : 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Supplier:</strong> {{ $po->supplier->name ?? 'N/A' }}</p>
                <p><strong>Total Amount:</strong> Rs. {{ number_format($po->total_amount ?? 0, 2) }}</p>
            </div>
            <div>
                <p><strong>Status:</strong> {{ $po->status ?? 'N/A' }}</p>
                <p><strong>GRNs:</strong> {{ isset($po) && $po->grns ? $po->grns->count() : 0 }}</p>
            </div>
        </div>
    </div>
@endif --}}

    <div class="mx-auto px-4 py-8">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.purchase-orders.index') }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to POs
            </a>
            <div class="flex space-x-2">
                {{-- @if ($po->status === 'Pending')
                    <form action="{{ route('admin.purchase-orders.approve', $po->po_id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-check mr-2"></i> Approve
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.purchase-orders.edit', $po->po_id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a> --}}
                <a href="{{ route('admin.purchase-orders.print', $po->po_id) }}"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <!-- PO Header -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Order #{{ $po->po_number }}</h1>
                    <div class="flex items-center mt-2">
                        <span class="text-sm font-medium mr-2">Status:</span>
                        @if ($po->status === 'Pending')
                            <x-partials.badges.status-badge status="warning" text="Pending" />
                        @elseif($po->status === 'Approved')
                            <x-partials.badges.status-badge status="info" text="Approved" />
                        @elseif($po->status === 'Received')
                            <x-partials.badges.status-badge status="success" text="Received" />
                        @else
                            <x-partials.badges.status-badge status="default" text="{{ $po->status }}" />
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-gray-500 text-sm">Order Date</div>
                    <div class="text-lg font-semibold">{{ $po->order_date->format('M d, Y') }}</div>
                    <div class="text-gray-500 text-sm mt-1">Expected Delivery</div>
                    <div class="text-lg font-semibold">{{ $po->expected_delivery_date->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- PO Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Supplier Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Supplier Name</p>
                        <p class="font-medium">{{ $po->supplier->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact Person</p>
                        <p class="font-medium">{{ $po->supplier->contact_person ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $po->supplier->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $po->supplier->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Branch Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $po->branch->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $po->branch->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $po->branch->address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $po->branch->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- PO Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">PO Summary</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($po->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Paid Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($po->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Balance:</span>
                        <span class="font-bold">Rs. {{ number_format($po->total_amount - $po->paid_amount, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created By:</span>
                            <span class="font-medium">
                                {{ $po->user->name }}
                            </span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-gray-600">Created At:</span>
                            <span class="font-medium">
                                {{ $po->created_at->format('M d, Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PO Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Order Items</h2>
                <p class="text-sm text-gray-500">Items included in this purchase order</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                            </th>
                            {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th> --}}
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $item->item->name ?? $item->item_code }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                </td>
                                {{-- <td class="px-6 py-4">
                                    {{ $item->batch_no ?? 'N/A' }}
                                </td> --}}
                                <td class="px-6 py-4">
                                    {{ number_format($item->quantity, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    Rs. {{ number_format($item->buying_price, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    Rs. {{ number_format($item->line_total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right font-medium">Total:</td>
                            <td class="px-6 py-3 font-bold">Rs. {{ number_format($po->total_amount, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Related GRNs -->
        @if ($po->grns->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Related GRNs</h2>
                    <p class="text-sm text-gray-500">Goods received notes for this purchase order</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    GRN Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Received By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($po->grns as $grn)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800">
                                            {{ $grn->grn_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->received_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->receivedByUser->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->grnItems->count() }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($grn->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($grn->status === 'Pending')
                                            <x-partials.badges.status-badge status="warning" text="Pending" />
                                        @elseif($grn->status === 'Verified')
                                            <x-partials.badges.status-badge status="success" text="Verified" />
                                        @else
                                            <x-partials.badges.status-badge status="default"
                                                text="{{ $grn->status }}" />
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Notes Section -->
        @if ($po->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">PO Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($po->notes)) !!}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .prose {
            color: #374151;
            line-height: 1.6;
        }

        .prose a {
            color: #4f46e5;
            text-decoration: underline;
        }
    </style>
@endpush
