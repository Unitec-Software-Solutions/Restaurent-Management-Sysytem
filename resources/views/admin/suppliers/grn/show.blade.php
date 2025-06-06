@extends('layouts.admin')

@section('header-title', 'GRN Details')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.grn.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
            </a>
            <div class="flex space-x-2">
                @if($grn->status === 'Pending')
                    <a href="{{ route('admin.grn.edit', $grn->grn_id) }}" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit GRN
                    </a>
                @endif
                <a href="{{ route('admin.grn.print', $grn->grn_id) }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <!-- GRN Header -->
        {{-- <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">GRN #{{ $grn->grn_number }}</h1>
                    <div class="flex items-center mt-2 space-x-2">
                        <span class="text-sm font-medium">Status:</span>
                        @if($grn->status === 'Pending')
                            <x-partials.badges.status-badge status="warning" text="Pending" />
                        @elseif($grn->status === 'Verified')
                            <x-partials.badges.status-badge status="success" text="Verified" />
                        @elseif($grn->status === 'Rejected')
                            <x-partials.badges.status-badge status="danger" text="Rejected" />
                        @else
                            <x-partials.badges.status-badge status="default" text="{{ $grn->status }}" />
                        @endif
                        
                        <span class="text-sm font-medium ml-4">Payment:</span>
                        @if ($grn->isPaymentPaid())
                            <x-partials.badges.status-badge status="success" text="Fully Paid" />
                        @elseif($grn->isPaymentPartial())
                            <x-partials.badges.status-badge status="info" text="Partially Paid" />
                        @else
                            <x-partials.badges.status-badge status="warning" text="Pending Payment" />
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-gray-500 text-sm">Received Date</div>
                    <div class="text-lg font-semibold">{{ $grn->received_date->format('M d, Y') }}</div>
                    <div class="text-gray-500 text-sm mt-1">Invoice No</div>
                    <div class="text-lg font-semibold">{{ $grn->invoice_number ?? 'N/A' }}</div>
                </div>
            </div>
        </div> --}}

         <!-- GRN Header Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 {{ $grn->status === 'Pending' ? 'border-yellow-500' : ($grn->status === 'Verified' ? 'border-green-500' : 'border-red-500') }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">GRN #{{ $grn->grn_number }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">GRN Status :</p>
                                @if($grn->status === 'Pending')
                                    <x-partials.badges.status-badge status="warning" text="Pending" />
                                @elseif($grn->status === 'Verified')
                                    <x-partials.badges.status-badge status="success" text="Verified" />
                                @elseif($grn->status === 'Rejected')
                                    <x-partials.badges.status-badge status="danger" text="Rejected" />
                                @else
                                    <x-partials.badges.status-badge status="default" text="{{ $grn->status }}" />
                                @endif
                            <p class="text-sm text-gray-500">GRN Payemnt Status :</p>
                            @if ($grn->isPaymentPaid())
                                <x-partials.badges.status-badge status="success" text="Fully Paid" />
                            @elseif($grn->isPaymentPartial())
                                <x-partials.badges.status-badge status="info" text="Partially Paid" />
                            @else
                                <x-partials.badges.status-badge status="warning" text="Pending Payment" />
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day mr-2"></i>
                            <span>Received: {{ $grn->received_date->format('M d, Y') }}</span>
                        </div>
                        @if($grn->invoice_number)
                        <div class="flex items-center">
                            <i class="fas fa-file-invoice mr-2"></i>
                            <span>Invoice: {{ $grn->invoice_number }}</span>
                        </div>
                        @endif
                        @if($grn->delivery_note_number)
                        <div class="flex items-center">
                            <i class="fas fa-truck mr-2"></i>
                            <span>DN: {{ $grn->delivery_note_number }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-2xl font-bold text-indigo-600">Rs. {{ number_format($grn->total_amount, 2) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Amount</div>
                </div>
            </div>
        </div>

        <!-- GRN Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Supplier Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Supplier Name</p>
                        <p class="font-medium">{{ $grn->supplier->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact Person</p>
                        <p class="font-medium">{{ $grn->supplier->contact_person ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $grn->supplier->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $grn->supplier->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Branch Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $grn->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $grn->branch->code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $grn->branch->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $grn->branch->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- GRN Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">GRN Summary</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($grn->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Paid Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($grn->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Balance:</span>
                        <span class="font-bold">Rs. {{ number_format($grn->total_amount - $grn->paid_amount, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Received By:</span>
                            <span class="font-medium">
                                {{ $grn->receivedByUser->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-gray-600">Verified By:</span>
                            <span class="font-medium">
                                {{ $grn->verifiedByUser->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-gray-600">Created At:</span>
                            <span class="font-medium">
                                {{ $grn->created_at->format('M d, Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRN Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Received Items</h2>
                <p class="text-sm text-gray-500">Items included in this goods received note</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accepted Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EXP Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($grn->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $item->item->name ?? $item->item_code }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->batch_no ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{ number_format($item->ordered_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{ number_format($item->received_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-green-600">
                                    {{ number_format($item->accepted_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-red-600">
                                    {{ number_format($item->rejected_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    Rs. {{ number_format($item->buying_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    Rs. {{ number_format($item->line_total, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ optional($item->expiry_date)->format('M d, Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="7" class="px-6 py-3 text-right font-medium">Total:</td>
                            <td class="px-6 py-3 font-bold">Rs. {{ number_format($grn->total_amount, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Related Purchase Order -->
        @if($grn->purchaseOrder)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Related Purchase Order</h2>
                    <p class="text-sm text-gray-500">Purchase order associated with this GRN</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.purchase-orders.show', $grn->purchaseOrder->po_id) }}" 
                                       class="text-indigo-600 hover:text-indigo-800">
                                        {{ $grn->purchaseOrder->po_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $grn->purchaseOrder->order_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $grn->purchaseOrder->expected_delivery_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $grn->purchaseOrder->user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($grn->purchaseOrder->status === 'Pending')
                                        <x-partials.badges.status-badge status="warning" text="Pending" />
                                    @elseif($grn->purchaseOrder->status === 'Approved')
                                        <x-partials.badges.status-badge status="info" text="Approved" />
                                    @elseif($grn->purchaseOrder->status === 'Received')
                                        <x-partials.badges.status-badge status="success" text="Received" />
                                    @else
                                        <x-partials.badges.status-badge status="default" text="{{ $grn->purchaseOrder->status }}" />
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    Rs. {{ number_format($grn->purchaseOrder->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.purchase-orders.show', $grn->purchaseOrder->po_id) }}"
                                       class="text-indigo-600 hover:text-indigo-800" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Notes Section -->
        @if($grn->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">GRN Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($grn->notes)) !!}
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
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        th, td {
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