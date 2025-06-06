<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Received Note - {{ $grn->grn_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            .p-6,
            .px-6,
            .py-3,
            .py-4 {
                padding: 4px !important;
            }

            h1,
            h3 {
                font-size: 16px !important;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                background: white;
                font-size: 11px;
                line-height: 1.3;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
            }

            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .grn-table th,
            .grn-table td {
                padding: 6px !important;
            }
        }

        .status-badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }

        .status-pending {
            @apply bg-yellow-100 text-yellow-800;
        }

        .status-verified {
            @apply bg-green-100 text-green-800;
        }

        .status-rejected {
            @apply bg-red-100 text-red-800;
        }

        .status-default {
            @apply bg-gray-100 text-gray-800;
        }
    </style>
</head>

<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GRN
            </a>
            <div class="flex space-x-2">
                <button onclick="window.print()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- Back and Action Buttons -->


    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header with logo and GRN details -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <div class="text-2xl font-bold mb-1">{{ $organization->name }}</div>
                    <div class="text-gray-600">{{ $organization->address }}</div>
                    <div class="text-gray-600">
                        {{ $organization->city }}, {{ $organization->country }}
                    </div>
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">GOODS RECEIVED NOTE</h1>
                    <div class="text-lg font-medium">GRN #{{ $grn->grn_number }}</div>
                </div>
            </div>
        </div>

        <!-- GRN Summary -->
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Received Date</h3>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($grn->received_date)->format('M d, Y') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Delivery Note</h3>
                    <p class="font-medium">{{ $grn->delivery_note_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Invoice Number</h3>
                    <p class="font-medium">{{ $grn->invoice_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                    @if ($grn->status === 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @elseif($grn->status === 'verified')
                        <span class="status-badge status-verified">Verified</span>
                    @elseif($grn->status === 'rejected')
                        <span class="status-badge status-rejected">Rejected</span>
                    @else
                        <span class="status-badge status-default">{{ $grn->status }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Amount</h3>
                    <p class="font-bold">Rs. {{ number_format($grn->total_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Supplier and Branch Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <!-- Supplier Info -->
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Supplier Information</h3>
                <div class="space-y-3">
                    <p class="font-bold">{{ $grn->supplier->name }}</p>
                    <p>{{ $grn->supplier->address }}</p>
                    <p>Phone: {{ $grn->supplier->phone }}</p>
                    @if ($grn->supplier->email)
                        <p>Email: {{ $grn->supplier->email }}</p>
                    @endif
                    @if ($grn->supplier->contact_person)
                        <p>Contact: {{ $grn->supplier->contact_person }}</p>
                    @endif
                </div>
            </div>

            <!-- Branch Info -->
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Branch Information</h3>
                <div class="space-y-3">
                    <p class="font-bold">{{ $grn->branch->name }}</p>
                    <p>{{ $grn->branch->address }}</p>
                    <p>Phone: {{ $grn->branch->phone }}</p>
                </div>
            </div>
        </div>

        <!-- Related Purchase Order -->
        @if ($grn->purchaseOrder)
            <div class="p-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold mb-2">Related Purchase Order</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">PO Number</h3>
                        <p class="font-medium">{{ $grn->purchaseOrder->po_number }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">PO Date</h3>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($grn->purchaseOrder->order_date)->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">PO Status</h3>
                        <p class="font-medium capitalize">{{ $grn->purchaseOrder->status }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Items Table -->
        <div class="p-6 avoid-break">
            <table class="min-w-full divide-y divide-gray-200 grn-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ordered</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Received</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Accepted</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rejected</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit
                            Price</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($grn->items as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium">{{ $item->item->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->item->item_code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->batch_no ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($item->ordered_quantity, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($item->received_quantity, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                <span class="font-medium">{{ number_format($item->accepted_quantity, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                @if ($item->rejected_quantity > 0)
                                    <span class="text-red-600">{{ number_format($item->rejected_quantity, 2) }}</span>
                                @else
                                    {{ number_format($item->rejected_quantity, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                Rs. {{ number_format($item->buying_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                Rs. {{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                        @if ($item->rejected_quantity > 0)
                            <tr>
                                <td colspan="10" class="px-6 py-2 text-sm text-gray-500">
                                    <div class="bg-red-50 p-2 rounded">
                                        <span class="font-medium">Rejection Reason:</span>
                                        {{ $item->rejection_reason ?? 'No reason provided' }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th colspan="9" class="px-6 py-3 text-right text-sm font-medium">Total:</th>
                        <td class="px-6 py-3 text-right text-sm font-bold">Rs.
                            {{ number_format($grn->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes and Verification -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-t border-gray-200">
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Notes</h3>
                <div class="text-sm">
                    {!! $grn->notes ? nl2br(e($grn->notes)) : 'No additional notes' !!}
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Verification</h3>
                <div class="space-y-3">
                    @if ($grn->verified_at)
                        <p><span class="font-medium">Status:</span>
                            <span class="font-bold uppercase">{{ $grn->status }}</span>
                        </p>
                        <p><span class="font-medium">Verified By:</span> {{ $grn->verifiedByUser->name ?? 'N/A' }}</p>
                        <p><span class="font-medium">Verified At:</span>
                            {{ \Carbon\Carbon::parse($grn->verified_at)->format('M d, Y h:i A') }}</p>
                    @else
                        <p class="text-yellow-600 font-medium">Pending verification</p>
                    @endif
                    <p><span class="font-medium">Received By:</span> {{ $grn->receivedByUser->name ?? 'N/A' }}</p>
                    <p><span class="font-medium">Received At:</span>
                        {{ \Carbon\Carbon::parse($grn->received_date)->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Signatures and Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Received By</p>
                        <p class="text-center mt-2">{{ $grn->receivedByUser->name ?? '____________________' }}</p>
                    </div>
                </div>
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Verified By</p>
                        @if ($grn->verifiedByUser)
                            <p class="text-center mt-2">{{ $grn->verifiedByUser->name }}</p>
                        @else
                            <p class="text-center mt-2">____________________</p>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Supplier Representative</p>
                        <p class="text-center mt-2">____________________</p>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-xs text-gray-500 text-center">
                <p>This is a computer generated goods received note and does not require a signature.</p>
                <p class="mt-1">Printed on {{ $printedDate }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.print();
        });
    </script>
</body>

</html>
