@extends('layouts.admin')

@section('header-title', 'Supplier Payment Details')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.payments.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Payments
            </a>
            <div class="flex space-x-2">
                <a href="{{ route('admin.payments.edit', $payment->id) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('admin.payments.print', $payment->id) }}"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <!-- Payment Header -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payment #{{ $payment->payment_number }}</h1>
                    <div class="flex items-center mt-2">
                        <span class="text-sm font-medium mr-2">Status:</span>
                        @if ($payment->payment_status === 'draft')
                            <x-partials.badges.status-badge status="default" text="Draft" />
                        @elseif($payment->payment_status === 'pending')
                            <x-partials.badges.status-badge status="warning" text="Pending" />
                        @elseif($payment->payment_status === 'partial')
                            <x-partials.badges.status-badge status="info" text="Partial" />
                        @elseif($payment->payment_status === 'paid')
                            <x-partials.badges.status-badge status="success" text="Paid" />
                        @else
                            <x-partials.badges.status-badge status="default" text="{{ $payment->payment_status }}" />
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-gray-500 text-sm">Payment Date</div>
                    <div class="text-lg font-semibold">{{ $payment->payment_date->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Supplier Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Supplier Name</p>
                        <p class="font-medium">{{ $payment->supplier->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact Person</p>
                        <p class="font-medium">{{ $payment->supplier->contact_person ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $payment->supplier->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $payment->supplier->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Branch Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $payment->branch->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $payment->branch->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $payment->branch->address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $payment->branch->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Payment Summary</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($payment->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Allocated Amount:</span>
                        <span class="font-bold">Rs. {{ number_format($payment->allocated_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Balance:</span>
                        <span class="font-bold">Rs.
                            {{ number_format($payment->total_amount - $payment->allocated_amount, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Processed By:</span>
                            <span class="font-medium">{{ $payment->processedBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-gray-600">Created At:</span>
                            <span class="font-medium">{{ $payment->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Details Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Payment Details</h2>
                <p class="text-sm text-gray-500">Details of the payment transaction</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Method Type</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $payment->paymentDetails->method_type)) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Amount</p>
                        <p class="font-medium">Rs. {{ number_format($payment->paymentDetails->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Reference Number</p>
                        <p class="font-medium">{{ $payment->paymentDetails->reference_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Value Date</p>
                        <p class="font-medium">
                            {{ $payment->paymentDetails->value_date ? \Carbon\Carbon::parse($payment->paymentDetails->value_date)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    @if ($payment->paymentDetails->cheque_number)
                        <div>
                            <p class="text-sm text-gray-500">Cheque Number</p>
                            <p class="font-medium">{{ $payment->paymentDetails->cheque_number }}</p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->bank_name)
                        <div>
                            <p class="text-sm text-gray-500">Bank Name</p>
                            <p class="font-medium">{{ $payment->paymentDetails->bank_name }}</p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->cheque_date)
                        <div>
                            <p class="text-sm text-gray-500">Cheque Date</p>
                            <p class="font-medium">
                                {{ \Carbon\Carbon::parse($payment->paymentDetails->cheque_date)->format('M d, Y') }}
                            </p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->transaction_id)
                        <div>
                            <p class="text-sm text-gray-500">Transaction ID</p>
                            <p class="font-medium">{{ $payment->paymentDetails->transaction_id }}</p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->bank_reference)
                        <div>
                            <p class="text-sm text-gray-500">Bank Reference</p>
                            <p class="font-medium">{{ $payment->paymentDetails->bank_reference }}</p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->installment_number)
                        <div>
                            <p class="text-sm text-gray-500">Installment Number</p>
                            <p class="font-medium">{{ $payment->paymentDetails->installment_number }}</p>
                        </div>
                    @endif
                    @if ($payment->paymentDetails->due_date)
                        <div>
                            <p class="text-sm text-gray-500">Due Date</p>
                            <p class="font-medium">
                                {{ \Carbon\Carbon::parse($payment->paymentDetails->due_date)->format('M d, Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related GRNs -->
        @if ($payment->grns->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Related GRNs</h2>
                    <p class="text-sm text-gray-500">Goods Received Notes paid by this payment</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    GRN Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Received Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paid Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($payment->grns as $grn)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800">
                                            {{ $grn->grn_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->purchaseOrder ? $grn->purchaseOrder->po_number : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->received_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($grn->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($grn->paid_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $grn->due_date ? $grn->due_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($grn->payment_status === 'pending')
                                            <x-partials.badges.status-badge status="warning" text="Pending" />
                                        @elseif ($grn->payment_status === 'partial')
                                            <x-partials.badges.status-badge status="info" text="Partial" />
                                        @elseif ($grn->payment_status === 'paid')
                                            <x-partials.badges.status-badge status="success" text="Paid" />
                                        @else
                                            <x-partials.badges.status-badge status="default"
                                                text="{{ $grn->payment_status ?? 'N/A' }}" />
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

        <!-- Related POs -->
        @if ($payment->purchaseOrders->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Related Purchase Orders</h2>
                    <p class="text-sm text-gray-500">Purchase Orders paid by this payment</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    PO Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paid Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($payment->purchaseOrders as $po)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.purchase-orders.show', $po->po_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800">
                                            {{ $po->po_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $po->order_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($po->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($po->paid_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($po->payment_status === 'Pending')
                                            <x-partials.badges.status-badge status="warning" text="Pending" />
                                        @elseif ($po->payment_status === 'Partial')
                                            <x-partials.badges.status-badge status="info" text="Partial" />
                                        @elseif ($po->payment_status === 'Paid')
                                            <x-partials.badges.status-badge status="success" text="Paid" />
                                        @else
                                            <x-partials.badges.status-badge status="default"
                                                text="{{ $po->payment_status ?? 'N/A' }}" />
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.purchase-orders.show', $po->po_id) }}"
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

        <!-- Allocations -->
        @if ($payment->allocations->count() > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Payment Allocations</h2>
                    <p class="text-sm text-gray-500">Documents this payment is allocated to</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Document Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Document Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount Allocated</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Allocated At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Allocated By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($payment->allocations as $allocation)
                                <tr>
                                    <td class="px-6 py-4">
                                        {{ $allocation->grn_id ? 'GRN' : 'PO' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($allocation->grn_id)
                                            <a href="{{ route('admin.grn.show', $allocation->grn->grn_id) }}"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                {{ $allocation->grn->grn_number }}
                                            </a>
                                        @else
                                            <a href="{{ route('admin.purchase-orders.show', $allocation->po->po_id) }}"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                {{ $allocation->po->po_number }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        Rs. {{ number_format($allocation->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $allocation->allocated_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $allocation->allocatedBy->name ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Notes Section -->
        @if ($payment->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">Payment Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($payment->notes)) !!}
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
