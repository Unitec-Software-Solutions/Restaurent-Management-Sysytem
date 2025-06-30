<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer Note - {{ $gtn->gtn_number }}</title>
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

            .gtn-table th,
            .gtn-table td {
                padding: 4px 6px !important;
                font-size: 10px !important;
                border: 1px solid #ddd !important;
            }

            .gtn-table {
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

        .status-draft {
            @apply bg-gray-100 text-gray-800;
        }

        .status-confirmed {
            @apply bg-blue-100 text-blue-800;
        }

        .status-pending {
            @apply bg-yellow-100 text-yellow-800;
        }

        .status-received {
            @apply bg-blue-100 text-blue-800;
        }

        .status-verified {
            @apply bg-purple-100 text-purple-800;
        }

        .status-accepted {
            @apply bg-green-100 text-green-800;
        }

        .status-rejected {
            @apply bg-red-100 text-red-800;
        }

        .status-partially-accepted {
            @apply bg-orange-100 text-orange-800;
        }
    </style>
</head>

<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to GTN
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
        <!-- Header with Organization and GTN Details -->
        <div class="header-section p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <div class="text-2xl font-bold mb-1 text-gray-900">
                        @if(Auth::guard('admin')->user()->is_super_admin)
                            All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization Name
                        @endif
                    </div>
                    @if (Auth::guard('admin')->user()->organization)
                        <div class="text-gray-600">{{ Auth::guard('admin')->user()->organization->address ?? '' }}</div>
                        <div class="text-gray-600">
                            @if (Auth::guard('admin')->user()->organization->city)
                                {{ Auth::guard('admin')->user()->organization->city }},
                            @endif
                            {{ Auth::guard('admin')->user()->organization->country ?? '' }}
                        </div>
                        @if (Auth::guard('admin')->user()->organization->phone)
                            <div class="text-gray-600">Phone: {{ Auth::guard('admin')->user()->organization->phone }}</div>
                        @endif
                        @if (Auth::guard('admin')->user()->organization->email)
                            <div class="text-gray-600">Email: {{ Auth::guard('admin')->user()->organization->email }}</div>
                        @endif
                    @endif
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">STOCK TRANSFER NOTE</h1>
                    <div class="text-lg font-medium">GTN #{{ $gtn->gtn_number }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                        Date: {{ \Carbon\Carbon::parse($gtn->transfer_date)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- GTN Status and Summary -->
        <div class="details-section p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Transfer Date</h4>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($gtn->transfer_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Origin Status</h4>
                    @php
                        $originStatus = $gtn->origin_status ?? 'draft';
                        $originStatusClass = 'status-' . str_replace('_', '-', $originStatus);
                    @endphp
                    <span class="status-badge {{ $originStatusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $originStatus)) }}
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Receiver Status</h4>
                    @php
                        $receiverStatus = $gtn->receiver_status ?? 'pending';
                        $receiverStatusClass = 'status-' . str_replace('_', '-', $receiverStatus);
                    @endphp
                    <span class="status-badge {{ $receiverStatusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $receiverStatus)) }}
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Value</h4>
                    <p class="font-bold text-lg">Rs. {{ number_format($gtn->getTotalTransferValue(), 2) }}</p>
                </div>
            </div>

            @if ($gtn->confirmed_at || $gtn->received_at || $gtn->verified_at || $gtn->accepted_at)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs text-gray-600">
                    @if ($gtn->confirmed_at)
                        <div>
                            <span class="font-medium">Confirmed:</span>
                            {{ $gtn->confirmed_at->format('d M Y H:i') }}
                        </div>
                    @endif
                    @if ($gtn->received_at)
                        <div>
                            <span class="font-medium">Received:</span>
                            {{ $gtn->received_at->format('d M Y H:i') }}
                        </div>
                    @endif
                    @if ($gtn->verified_at)
                        <div>
                            <span class="font-medium">Verified:</span>
                            {{ $gtn->verified_at->format('d M Y H:i') }}
                        </div>
                    @endif
                    @if ($gtn->accepted_at)
                        <div>
                            <span class="font-medium">Processed:</span>
                            {{ $gtn->accepted_at->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Branch Information -->
        <div class="details-section grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-200">
            <!-- From Branch -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Transfer From</h3>
                <div class="space-y-2">
                    <p class="font-bold text-gray-900">{{ $gtn->fromBranch->name ?? 'N/A' }}</p>
                    @if ($gtn->fromBranch)
                        <p class="text-gray-700">{{ $gtn->fromBranch->address ?? '' }}</p>
                        @if ($gtn->fromBranch->phone)
                            <p class="text-gray-700">Phone: {{ $gtn->fromBranch->phone }}</p>
                        @endif
                        @if ($gtn->fromBranch->manager_name)
                            <p class="text-gray-700">Manager: {{ $gtn->fromBranch->manager_name }}</p>
                        @endif
                    @endif
                    @if ($gtn->createdBy)
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Prepared by:</span>
                            {{ $gtn->createdBy->first_name ?? '' }} {{ $gtn->createdBy->last_name ?? '' }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- To Branch -->
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Transfer To</h3>
                <div class="space-y-2">
                    <p class="font-bold text-gray-900">{{ $gtn->toBranch->name ?? 'N/A' }}</p>
                    @if ($gtn->toBranch)
                        <p class="text-gray-700">{{ $gtn->toBranch->address ?? '' }}</p>
                        @if ($gtn->toBranch->phone)
                            <p class="text-gray-700">Phone: {{ $gtn->toBranch->phone }}</p>
                        @endif
                        @if ($gtn->toBranch->manager_name)
                            <p class="text-gray-700">Manager: {{ $gtn->toBranch->manager_name }}</p>
                        @endif
                    @endif
                    @if ($gtn->receivedBy)
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Received by:</span>
                            {{ $gtn->receivedBy->first_name ?? '' }} {{ $gtn->receivedBy->last_name ?? '' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="avoid-break">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Transfer Items</h3>
                <table class="min-w-full gtn-table">
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
                                Transfer Qty
                            </th>
                            @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted())
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    Accepted
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                    Rejected
                                </th>
                            @endif
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Unit Price
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Line Total
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($gtn->items as $index => $item)
                            <tr class="{{ $index % 20 == 19 ? 'page-break' : '' }}">
                                <td class="px-4 py-3 text-sm border">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium border">{{ $item->item_code }}</td>
                                <td class="px-4 py-3 text-sm border">
                                    <div class="font-medium">{{ $item->item_name }}</div>
                                    @if ($item->batch_no)
                                        <div class="text-xs text-gray-500">Batch: {{ $item->batch_no }}</div>
                                    @endif
                                    @if ($item->expiry_date)
                                        <div class="text-xs text-gray-500">Exp:
                                            {{ $item->expiry_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center border">
                                    {{ number_format($item->transfer_quantity, 2) }}
                                </td>
                                @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted())
                                    <td class="px-4 py-3 text-sm text-center border">
                                        @if ($item->quantity_accepted > 0)
                                            <span
                                                class="text-green-600 font-medium">{{ number_format($item->quantity_accepted, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center border">
                                        @if ($item->quantity_rejected > 0)
                                            <span
                                                class="text-red-600 font-medium">{{ number_format($item->quantity_rejected, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm text-right border">
                                    Rs. {{ number_format($item->transfer_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium border">
                                    Rs. {{ number_format($item->line_total, 2) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-center border">
                                    @php
                                        $itemStatus = $item->item_status ?? 'pending';
                                        $statusClass = 'status-' . str_replace('_', '-', $itemStatus);
                                    @endphp
                                    <span class="status-badge {{ $statusClass }} text-xs px-1 py-0.5">
                                        {{ ucfirst(str_replace('_', ' ', $itemStatus)) }}
                                    </span>
                                </td>
                            </tr>
                            @if ($item->item_rejection_reason)
                                <tr>
                                    <td colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted() ? '9' : '7' }}"
                                        class="px-4 py-2 text-xs bg-red-50 border text-red-700">
                                        <strong>Rejection Reason:</strong> {{ $item->item_rejection_reason }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted() ? '7' : '5' }}"
                                class="px-4 py-3 text-right font-semibold border">
                                Total Transfer Value:
                            </th>
                            <th class="px-4 py-3 text-right font-bold text-lg border">
                                Rs. {{ number_format($gtn->items->sum('line_total'), 2) }}
                            </th>
                            <th class="border"></th>
                        </tr>
                        @if ($gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted())
                            @if ($gtn->getTotalAcceptedValue() > 0)
                                <tr>
                                    <th colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted() ? '7' : '5' }}"
                                        class="px-4 py-2 text-right font-medium text-green-600 border">
                                        Accepted Value:
                                    </th>
                                    <th class="px-4 py-2 text-right font-semibold text-green-600 border">
                                        Rs. {{ number_format($gtn->getTotalAcceptedValue(), 2) }}
                                    </th>
                                    <th class="border"></th>
                                </tr>
                            @endif
                            @if ($gtn->getTotalRejectedValue() > 0)
                                <tr>
                                    <th colspan="{{ $gtn->isVerified() || $gtn->isAccepted() || $gtn->isRejected() || $gtn->isPartiallyAccepted() ? '7' : '5' }}"
                                        class="px-4 py-2 text-right font-medium text-red-600 border">
                                        Rejected Value:
                                    </th>
                                    <th class="px-4 py-2 text-right font-semibold text-red-600 border">
                                        Rs. {{ number_format($gtn->getTotalRejectedValue(), 2) }}
                                    </th>
                                    <th class="border"></th>
                                </tr>
                            @endif
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($gtn->notes)
            <div class="avoid-break p-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold mb-3">Notes</h3>
                <div class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 p-3 rounded">
                    {{ $gtn->notes }}
                </div>
            </div>
        @endif

        <!-- Signatures Section -->
        <div class="footer-section p-6 border-t border-gray-200 bg-gray-50 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Prepared By</p>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $gtn->createdBy->first_name ?? '' }} {{ $gtn->createdBy->last_name ?? '' }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $gtn->fromBranch->name ?? 'Origin Branch' }}</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Received By</p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if ($gtn->receivedBy)
                                {{ $gtn->receivedBy->first_name ?? '' }} {{ $gtn->receivedBy->last_name ?? '' }}
                            @else
                                _________________________
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $gtn->toBranch->name ?? 'Destination Branch' }}</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-black pt-4 mt-12">
                        <p class="font-medium">Verified By</p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if ($gtn->verifiedBy)
                                {{ $gtn->verifiedBy->first_name ?? '' }} {{ $gtn->verifiedBy->last_name ?? '' }}
                            @else
                                _________________________
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">Quality Control</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-xs text-gray-500 text-center border-t pt-4">
                <p>This is a computer generated stock transfer note.</p>
                <p class="mt-1">Printed on {{ now()->format('d M Y H:i') }} | GTN #{{ $gtn->gtn_number }}</p>
                @if ($gtn->isAccepted())
                    <p class="mt-1 text-green-600 font-medium">✓ Transfer completed successfully</p>
                @elseif($gtn->isRejected())
                    <p class="mt-1 text-red-600 font-medium">✗ Transfer rejected</p>
                @elseif($gtn->isPartiallyAccepted())
                    <p class="mt-1 text-orange-600 font-medium">⚠ Transfer partially accepted</p>
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
        function printGTN() {
            window.print();
        }
    </script>
</body>

</html>
