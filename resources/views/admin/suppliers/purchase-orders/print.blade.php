<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 20mm;
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

                box-shadow: none !important;
                border: none !important;
            }

            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .page-break {
                page-break-before: always;
            }



            .po-table th,
            .po-table td {
                padding: 6px !important;
            }

        }

        .status-badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }

        .status-warning {
            @apply bg-yellow-100 text-yellow-800;
        }

        .status-info {
            @apply bg-blue-100 text-blue-800;
        }

        .status-success {
            @apply bg-green-100 text-green-800;
        }

        .status-default {
            @apply bg-gray-100 text-gray-800;
        }
    </style>
</head>

<body class="bg-gray-100 p-4 md:p-8">
    <div class="no-print mb-6">
        <button onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-print mr-2"></i> Print
        </button>
        <a href="{{ route('admin.purchase-orders.show', $po->po_id) }}"
            class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to PO
        </a>
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header with logo and PO details -->
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">PURCHASE ORDER</h1>
                    <div class="text-lg font-medium">PO #{{ $po->po_number }}</div>
                </div>
            </div>
        </div>

        <!-- PO Summary -->
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">PO Date</h3>
                    <p class="font-medium">{{ $po->order_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Expected Delivery</h3>
                    <p class="font-medium">{{ $po->expected_delivery_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                    @if ($po->status === 'Pending')
                        <span class="status-badge status-warning">Pending</span>
                    @elseif($po->status === 'Approved')
                        <span class="status-badge status-success">Approved</span>
                    @else
                        <span class="status-badge status-default">{{ $po->status }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Amount</h3>
                    <p class="font-bold">Rs. {{ number_format($po->total_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Supplier and Branch Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <!-- Supplier Info -->
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Supplier Information</h3>
                <div class="space-y-3">
                    <p class="font-bold">{{ $po->supplier->name }}</p>
                    <p>{{ $po->supplier->address }}</p>
                    <p>Phone: {{ $po->supplier->phone }}</p>
                    @if ($po->supplier->email)
                        <p>Email: {{ $po->supplier->email }}</p>
                    @endif
                    @if ($po->supplier->contact_person)
                        <p>Contact: {{ $po->supplier->contact_person }}</p>
                    @endif
                </div>
            </div>

            <!-- Branch Info -->
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Branch Information</h3>
                <div class="space-y-3">
                    <p class="font-bold">{{ $po->branch->name }} - {{ $po->branch->id }}</p>
                    <p>{{ $po->branch->address }}</p>
                    <p>Phone: {{ $po->branch->phone }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="p-6 avoid-break">
            <table class="min-w-full divide-y divide-gray-200 po-table">
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
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit
                            Price</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($items as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium">{{ $item->item->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->item->item_code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->batch_no ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($item->quantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rs.
                                {{ number_format($item->buying_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rs.
                                {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th colspan="6" class="px-6 py-3 text-right text-sm font-medium">Subtotal:</th>
                        <td class="px-6 py-3 text-right text-sm font-bold">Rs.
                            {{ number_format($po->total_amount, 2) }}</td>
                    </tr>
                    @if ($po->tax_amount > 0)
                        <tr>
                            <th colspan="6" class="px-6 py-3 text-right text-sm font-medium">Tax:</th>
                            <td class="px-6 py-3 text-right text-sm font-bold">Rs.
                                {{ number_format($po->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="6" class="px-6 py-3 text-right text-sm font-medium">Total Amount:</th>
                        <td class="px-6 py-3 text-right text-sm font-bold text-lg">Rs.
                            {{ number_format($po->total_amount + $po->tax_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Terms and Notes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-t border-gray-200">
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Payment Terms</h3>
                <div class="space-y-3">
                    <p><span class="font-medium">Paid Amount:</span> Rs. {{ number_format($po->paid_amount, 2) }}</p>
                    <p><span class="font-medium">Balance:</span> Rs.
                        {{ number_format($po->total_amount - $po->paid_amount, 2) }}</p>
                    <p><span class="font-medium">Payment Due:</span> {{ $po->payment_terms ?? 'Upon receipt' }}</p>
                    <p><span class="font-medium">Payment Method:</span> {{ $po->payment_method ?? '_______________ ' }}
                    </p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Order Notes</h3>
                <div class="text-sm">
                    {!! $po->notes ? nl2br(e($po->notes)) : 'No additional notes' !!}
                </div>
            </div>
        </div>

        <!-- Approvals and Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Prepared By</p>
                        <p class="text-center mt-2">{{ $po->user->name }}</p>
                    </div>
                </div>
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Approved By</p>
                        @if ($po->status === 'Approved')
                            <p class="text-center mt-2">{{ $po->approvedBy->name ?? 'Management' }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="border-t-2 border-black pt-4">
                        <p class="text-center font-medium">Supplier Acknowledgment</p>
                    </div>
                </div>
            </div>
            <div class="mt-8 text-xs text-gray-500 text-center">
                <p>This is a computer generated purchase order and does not require a signature.</p>
                <p class="mt-1">Printed on {{ $printedDate }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads (optional)
        document.addEventListener('DOMContentLoaded', function() {
            window.print(); // Uncomment this only if needed
        });
    </script>
</body>

</html>
