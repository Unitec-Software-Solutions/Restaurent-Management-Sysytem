@extends('layouts.admin')

@section('header-title', 'Goods Received Note Details')
@section('content')

    {{-- Debug Info Card for GRN Show --}}
    @if (config('app.debug'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-medium text-emerald-800">🔍 GRN Debug Info</h3>
                <a href="{{ route('admin.grn.show', $grn->grn_id ?? 0) }}?debug=1"
                    class="text-xs text-emerald-600 hover:text-emerald-800">
                    Full Debug (debug=1)
                </a>
            </div>
            <div class="text-xs text-emerald-700 mt-2 grid grid-cols-4 gap-4">
                <div>
                    <p><strong>GRN Variable:</strong> {{ isset($grn) ? 'Set' : 'NOT SET' }}</p>
                    <p><strong>GRN Number:</strong> {{ $grn->grn_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p><strong>Items Count:</strong> {{ isset($grn) ? $grn->grnItems->count() : 'N/A' }}</p>
                    <p><strong>Total Amount:</strong> Rs. {{ number_format($grn->total_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p><strong>Supplier:</strong> {{ $grn->supplier->name ?? 'N/A' }}</p>
                    <p><strong>PO Number:</strong> {{ $grn->purchaseOrder->po_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p><strong>Status:</strong> {{ $grn->status ?? 'N/A' }}</p>
                    <p><strong>Received By:</strong> {{ $grn->receivedByUser->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="p-4 rounded-lg">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.grn.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GRNs
            </a>
            <div class="flex space-x-2">
                @if ($grn->status === 'Pending')
                    <!-- Verification form, but button triggers modal -->
                    <form id="verifyGrnForm" action="{{ route('admin.grn.verify', $grn->grn_id) }}" method="POST"
                        class="inline">
                        @csrf
                        <input type="hidden" name="status" value="Verified">
                        <button type="button" onclick="openVerifyModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-check mr-2"></i> Verify GRN
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.grn.print', $grn->grn_id) }}"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <!-- GRN Header Card -->
        <div
            class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 {{ $grn->status === 'Pending' ? 'border-yellow-500' : ($grn->status === 'Verified' ? 'border-green-500' : 'border-red-500') }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">GRN #{{ $grn->grn_number }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">GRN Status:</p>
                            @if ($grn->status === 'Pending')
                                <x-partials.badges.status-badge status="warning" text="Pending" />
                            @elseif($grn->status === 'Verified')
                                <x-partials.badges.status-badge status="success" text="Verified" />
                            @elseif($grn->status === 'Rejected')
                                <x-partials.badges.status-badge status="danger" text="Rejected" />
                            @else
                                <x-partials.badges.status-badge status="default" text="{{ $grn->status }}" />
                            @endif
                            <p class="text-sm text-gray-500">Payment Status:</p>
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
                        @if ($grn->invoice_number)
                            <div class="flex items-center">
                                <i class="fas fa-file-invoice mr-2"></i>
                                <span>Invoice: {{ $grn->invoice_number }}</span>
                            </div>
                        @endif
                        @if ($grn->delivery_note_number)
                            <div class="flex items-center">
                                <i class="fas fa-truck mr-2"></i>
                                <span>DN: {{ $grn->delivery_note_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-3xl font-bold text-indigo-600">Rs. {{ number_format($grn->final_total, 2) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Final Amount</div>
                    @if ($grn->balance_amount > 0)
                        <div class="text-lg font-semibold text-red-600 mt-1">
                            Balance: Rs. {{ number_format($grn->balance_amount, 2) }}
                        </div>
                    @endif
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

            <!-- GRN Financial Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <h2 class="text-lg font-semibold mb-4 text-indigo-700">Financial Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold">Rs. {{ number_format($grn->sub_total, 2) }}</span>
                    </div>
                    @if ($grn->item_discount_total > 0)
                        <div class="flex justify-between items-center text-orange-600">
                            <span>Item Discounts:</span>
                            <span class="font-semibold">- Rs. {{ number_format($grn->item_discount_total, 2) }}</span>
                        </div>
                    @endif
                    @if (($grn->grand_discount ?? 0) > 0)
                        <div class="flex justify-between items-center text-orange-600">
                            <span>Grand Discount ({{ $grn->grand_discount }}%):</span>
                            <span class="font-semibold">- Rs. {{ number_format($grn->grand_discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-900 font-semibold">Final Total:</span>
                            <span class="font-bold text-lg text-indigo-600">Rs.
                                {{ number_format($grn->final_total, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Paid Amount:</span>
                        <span class="font-semibold text-green-600">Rs.
                            {{ number_format($grn->paid_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900 font-semibold">Balance:</span>
                        <span
                            class="font-bold text-lg {{ $grn->balance_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rs. {{ number_format($grn->balance_amount, 2) }}
                        </span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Received By:</span>
                            <span class="font-medium">{{ $grn->receivedByUser->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Verified By:</span>
                            <span class="font-medium">{{ $grn->verifiedByUser->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $grn->created_at->format('M d, Y H:i') }}</span>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Batch</th>
                            {{-- <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ordered</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Received</th> --}}
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Accepted</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Free</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">To
                                Stock</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rejected</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Discount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Line Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expiry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($grn->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->item->name ?? $item->item_code }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->batch_no ?? 'N/A' }}
                                </td>
                                {{-- <td class="px-4 py-3 text-right text-sm">
                                    {{ number_format($item->ordered_quantity, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number_format($item->received_quantity, 2) }}
                                </td> --}}
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        class="font-medium text-green-600">{{ number_format($item->accepted_quantity, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        class="text-blue-600">{{ number_format($item->free_received_quantity, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        class="font-semibold text-indigo-600">{{ number_format($item->total_to_stock, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if ($item->rejected_quantity > 0)
                                        <span
                                            class="text-red-600 font-medium">{{ number_format($item->rejected_quantity, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">0.00</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    Rs. {{ number_format($item->buying_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if (($item->discount_received ?? 0) > 0)
                                        <span
                                            class="text-orange-600">{{ number_format($item->discount_received, 2) }}%</span>
                                        <div class="text-xs text-orange-500">
                                            Rs. {{ number_format($item->line_discount_amount, 2) }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">0%</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @php
                                        $lineTotalBeforeDiscount = $item->accepted_quantity * $item->buying_price;
                                        $discountAmount = ($item->discount_received / 100) * $lineTotalBeforeDiscount;
                                        $finalLineTotal = $lineTotalBeforeDiscount - $discountAmount;
                                    @endphp
                                    <div class="font-semibold">Rs. {{ number_format($finalLineTotal, 2) }}</div>
                                    @if (($item->discount_received ?? 0) > 0)
                                        <div class="text-xs text-gray-500 line-through">
                                            Rs. {{ number_format($lineTotalBeforeDiscount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($item->expiry_date)
                                        <div
                                            class="{{ $item->expiry_status === 'Expired' ? 'text-red-600' : ($item->expiry_status === 'Expiring Soon' ? 'text-orange-600' : 'text-gray-600') }}">
                                            {{ $item->expiry_date->format('M d, Y') }}
                                        </div>
                                        <div
                                            class="text-xs {{ $item->expiry_status === 'Expired' ? 'text-red-500' : ($item->expiry_status === 'Expiring Soon' ? 'text-orange-500' : 'text-gray-500') }}">
                                            {{ $item->expiry_status }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($item->rejected_quantity > 0 && $item->rejection_reason)
                                <tr>
                                    <td colspan="12" class="px-4 py-2 text-sm bg-red-50 border-l-4 border-red-400">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                            <span class="font-medium text-red-700">Rejection Reason:</span>
                                            <span class="text-red-600 ml-2">{{ $item->rejection_reason }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2">
                        <tr>
                            <td colspan="8" class="px-4 py-3 text-right font-semibold text-gray-700">Subtotal:</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">Rs. {{ number_format($grn->sub_total, 2) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        @if ($grn->item_discount_total > 0)
                            <tr>
                                <td colspan="8" class="px-4 py-2 text-right font-medium text-orange-600">Item
                                    Discounts:</td>
                                <td class="px-4 py-2 text-right font-semibold text-orange-600">- Rs.
                                    {{ number_format($grn->item_discount_total, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        @endif
                        @if (($grn->grand_discount ?? 0) > 0)
                            <tr>
                                <td colspan="8" class="px-4 py-2 text-right font-medium text-orange-600">Grand Discount
                                    ({{ $grn->grand_discount }}%):</td>
                                <td class="px-4 py-2 text-right font-semibold text-orange-600">- Rs.
                                    {{ number_format($grn->grand_discount_amount, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        @endif
                        <tr class="border-t-2">
                            <td colspan="8" class="px-4 py-3 text-right font-bold text-gray-900 text-lg">Final Total:
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-xl text-indigo-600">Rs.
                                {{ number_format($grn->final_total, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Related Purchase Order -->
        @if ($grn->purchaseOrder)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Related Purchase Order</h2>
                    <p class="text-sm text-gray-500">Purchase order associated with this GRN</p>
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
                                    Expected Delivery</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
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
                                    @if ($grn->purchaseOrder->status === 'Pending')
                                        <x-partials.badges.status-badge status="warning" text="Pending" />
                                    @elseif($grn->purchaseOrder->status === 'Approved')
                                        <x-partials.badges.status-badge status="info" text="Approved" />
                                    @elseif($grn->purchaseOrder->status === 'Received')
                                        <x-partials.badges.status-badge status="success" text="Received" />
                                    @else
                                        <x-partials.badges.status-badge status="default"
                                            text="{{ $grn->purchaseOrder->status }}" />
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
        @if ($grn->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">GRN Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($grn->notes)) !!}
                </div>
            </div>
        @endif

        <!-- Confirm Verification Modal -->
        <div id="verifyModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-xl mr-3">
                        <i class="fas fa-exclamation-triangle text-green-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">Confirm GRN Verification</h2>
                </div>
                <p class="mb-6 text-gray-700">
                    Are you sure you want to verify this GRN? This action cannot be undone.
                </p>
                <div class="flex gap-3 mt-6">
                    <button id="confirmVerifyBtn"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Yes, Verify GRN
                    </button>
                    <button type="button" onclick="closeVerifyModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
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

        .table-financial {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-financial th,
        .table-financial td {
            border-bottom: 1px solid #e5e7eb;
        }

        .table-financial thead th {
            background-color: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .hover-row:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .discount-highlight {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
        }

        .total-highlight {
            background: linear-gradient(90deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function openVerifyModal() {
            document.getElementById('verifyModal').classList.remove('hidden');
        }

        function closeVerifyModal() {
            document.getElementById('verifyModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var confirmBtn = document.getElementById('confirmVerifyBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    document.getElementById('verifyGrnForm').submit();
                });
            }

            // Add smooth hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.classList.add('hover-row');
            });
        });
    </script>
@endpush
