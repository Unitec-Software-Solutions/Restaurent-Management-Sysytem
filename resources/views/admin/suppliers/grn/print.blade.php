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
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                background: white;
                font-size: 11px;
                line-height: 1.4;
                color: #000;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .avoid-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0;
                padding: 0;
                width: 100%;
                height: auto;
            }

            .grn-table th,
            .grn-table td {
                padding: 4px 6px !important;
                font-size: 10px !important;
                border: 1px solid #ddd !important;
            }

            .grn-table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            h1 {
                font-size: 18px !important;
            }

            h2 {
                font-size: 16px !important;
            }

            h3 {
                font-size: 14px !important;
            }

            h4 {
                font-size: 12px !important;
            }

            .header-section {
                margin-bottom: 8px !important;
            }

            .details-section {
                margin-bottom: 6px !important;
            }

            .footer-section {
                position: fixed;
                bottom: 10mm;
                left: 0;
                right: 0;
                width: 100%;
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

        .status-approved {
            @apply bg-blue-100 text-blue-800;
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

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header with Organization and GRN Details -->
        <div class="header-section p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <div class="text-2xl font-bold mb-1 text-gray-900">
                        {{ $organization->name ?? 'Organization Name' }}</div>
                    @if ($organization)
                        <div class="text-gray-600">{{ $organization->address ?? '' }}</div>
                        <div class="text-gray-600">
                            @if ($organization->city)
                                {{ $organization->city }},
                            @endif
                            {{ $organization->country ?? '' }}
                        </div>
                        @if ($organization->phone)
                            <div class="text-gray-600">Phone: {{ $organization->phone }}</div>
                        @endif
                        @if ($organization->email)
                            <div class="text-gray-600">Email: {{ $organization->email }}</div>
                        @endif
                    @endif
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">GOODS RECEIVED NOTE</h1>
                    <div class="text-lg font-medium">GRN #{{ $grn->grn_number }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Date: {{ \Carbon\Carbon::parse($grn->received_date)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- GRN Status and Summary -->
        <div class="details-section p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Received Date</h4>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($grn->received_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Delivery Note</h4>
                    <p class="font-medium">{{ $grn->delivery_note_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Invoice Number</h4>
                    <p class="font-medium">{{ $grn->invoice_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                    @php
                        $status = $grn->status ?? 'pending';
                        $statusClass = 'status-' . str_replace('_', '-', $status);
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Amount</h4>
                    <p class="font-bold text-lg">Rs. {{ number_format($grn->total_amount, 2) }}</p>
                </div>
            </div>

            @if ($grn->verified_at)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600">
                    <div>
                        <span class="font-medium">Received:</span>
                        {{ \Carbon\Carbon::parse($grn->received_date)->format('d M Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Verified:</span>
                        {{ \Carbon\Carbon::parse($grn->verified_at)->format('d M Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Verified By:</span>
                        {{ $grn->verifiedByUser->name ?? 'N/A' }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Supplier and Branch Information -->
        <div class="details-section grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-200">
            <!-- Supplier Info -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Supplier Information</h3>
                <div class="space-y-2">
                    <p class="font-bold text-gray-900">{{ $grn->supplier->name }}</p>
                    @if ($grn->supplier->address)
                        <p class="text-gray-700">{{ $grn->supplier->address }}</p>
                    @endif
                    @if ($grn->supplier->phone)
                        <p class="text-gray-700">Phone: {{ $grn->supplier->phone }}</p>
                    @endif
                    @if ($grn->supplier->email)
                        <p class="text-gray-700">Email: {{ $grn->supplier->email }}</p>
                    @endif
                    @if ($grn->supplier->contact_person)
                        <p class="text-gray-700">Contact: {{ $grn->supplier->contact_person }}</p>
                    @endif
                </div>
            </div>

            <!-- Branch Info -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Branch Information</h3>
                <div class="space-y-2">
                    <p class="font-bold text-gray-900">{{ $grn->branch->name }}</p>
                    @if ($grn->branch->address)
                        <p class="text-gray-700">{{ $grn->branch->address }}</p>
                    @endif
                    @if ($grn->branch->phone)
                        <p class="text-gray-700">Phone: {{ $grn->branch->phone }}</p>
                    @endif
                    @if ($grn->branch->manager_name)
                        <p class="text-gray-700">Manager: {{ $grn->branch->manager_name }}</p>
                    @endif
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Received by:</span>
                        {{ $grn->receivedByUser->name ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Related Purchase Order -->
        @if ($grn->purchaseOrder)
            <div class="details-section p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-3">Related Purchase Order</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">PO Number</h4>
                        <p class="font-medium">{{ $grn->purchaseOrder->po_number }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">PO Date</h4>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($grn->purchaseOrder->order_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">PO Status</h4>
                        <p class="font-medium capitalize">{{ $grn->purchaseOrder->status }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">PO Value</h4>
                        <p class="font-medium">Rs. {{ number_format($grn->purchaseOrder->total_amount ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Items Table -->
        <div class="avoid-break">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Received Items</h3>
                <table class="min-w-full grn-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                #
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Item Code
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Item Description
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Ordered
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Received
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Accepted
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Rejected
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Unit Price
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Line Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($grn->items as $index => $item)
                            <tr class="{{ $index % 20 == 19 ? 'page-break' : '' }}">
                                <td class="px-4 py-3 text-sm border">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium border">{{ $item->item->item_code }}</td>
                                <td class="px-4 py-3 text-sm border">
                                    <div class="font-medium">{{ $item->item->name }}</div>
                                    @if ($item->batch_no)
                                        <div class="text-xs text-gray-500">Batch: {{ $item->batch_no }}</div>
                                    @endif
                                    @if ($item->expiry_date)
                                        <div class="text-xs text-gray-500">Exp:
                                            {{ \Carbon\Carbon::parse($item->expiry_date)->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center border">
                                    {{ number_format($item->ordered_quantity, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center border">
                                    {{ number_format($item->received_quantity, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center border">
                                    <span
                                        class="font-medium text-green-600">{{ number_format($item->accepted_quantity, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center border">
                                    @if ($item->rejected_quantity > 0)
                                        <span
                                            class="text-red-600 font-medium">{{ number_format($item->rejected_quantity, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right border">
                                    Rs. {{ number_format($item->buying_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium border">
                                    Rs. {{ number_format($item->line_total, 2) }}
                                </td>
                            </tr>
                            @if ($item->rejected_quantity > 0 && $item->rejection_reason)
                                <tr>
                                    <td colspan="9" class="px-4 py-2 text-xs bg-red-50 border text-red-700">
                                        <strong>Rejection Reason:</strong> {{ $item->rejection_reason }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="8" class="px-4 py-3 text-right font-semibold border">
                                Total Amount:
                            </th>
                            <th class="px-4 py-3 text-right font-bold text-lg border">
                                Rs. {{ number_format($grn->total_amount, 2) }}
                            </th>
                        </tr>
                        @php
                            $totalAccepted = $grn->items->sum(function ($item) {
                                return $item->accepted_quantity * $item->buying_price;
                            });
                            $totalRejected = $grn->items->sum(function ($item) {
                                return $item->rejected_quantity * $item->buying_price;
                            });
                        @endphp
                        @if ($totalAccepted > 0)
                            <tr>
                                <th colspan="8" class="px-4 py-2 text-right font-medium text-green-600 border">
                                    Accepted Value:
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-green-600 border">
                                    Rs. {{ number_format($totalAccepted, 2) }}
                                </th>
                            </tr>
                        @endif
                        @if ($totalRejected > 0)
                            <tr>
                                <th colspan="8" class="px-4 py-2 text-right font-medium text-red-600 border">
                                    Rejected Value:
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-red-600 border">
                                    Rs. {{ number_format($totalRejected, 2) }}
                                </th>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($grn->notes)
            <div class="avoid-break p-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold mb-3">Notes</h3>
                <div class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 p-3 rounded">
                    {{ $grn->notes }}
                </div>
            </div>
        @endif

        <!-- Signatures Section -->
        <div class="footer-section p-6 border-t border-gray-200 bg-gray-50 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Received By</p>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $grn->receivedByUser->name ?? '________________________' }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $grn->branch->name ?? 'Branch' }}</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Verified By</p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if ($grn->verifiedByUser)
                                {{ $grn->verifiedByUser->name }}
                            @else
                                _________________________
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">Quality Control</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Supplier Representative</p>
                        <p class="text-sm text-gray-600 mt-1">
                            _________________________
                        </p>
                        <p class="text-xs text-gray-500">{{ $grn->supplier->name ?? 'Supplier' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-xs text-gray-500 text-center border-t pt-4">
                <p>This is a computer generated goods received note.</p>
                <p class="mt-1">Printed on {{ now()->format('d M Y H:i') }} | GRN #{{ $grn->grn_number }}</p>
                @if ($grn->status === 'verified')
                    <p class="mt-1 text-green-600 font-medium">✓ Goods received and verified</p>
                @elseif($grn->status === 'rejected')
                    <p class="mt-1 text-red-600 font-medium">✗ Goods rejected</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        window.addEventListener('load', function() {
            // Small delay to ensure everything is rendered
            setTimeout(() => {
                window.print();
            }, 500);
        });

        // Print function for manual triggering
        function printGRN() {
            window.print();
        }
    </script>
</body>

</html>
