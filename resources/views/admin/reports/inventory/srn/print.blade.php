<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            html, body {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                background: white;
                font-size: 11px;
                line-height: 1.4;
                color: #000;
            }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; page-break-inside: avoid; break-inside: avoid; }
            .avoid-break { page-break-inside: avoid; break-inside: avoid; }
            .print-container { box-shadow: none !important; border: none !important; margin: 0; padding: 0; width: 100%; height: auto; }
            .srn-table th, .srn-table td { padding: 4px 6px !important; font-size: 10px !important; border: 1px solid #ddd !important; }
            .srn-table { border-collapse: collapse !important; width: 100% !important; }
            h1 { font-size: 18px !important; }
            h2 { font-size: 16px !important; }
            h3 { font-size: 14px !important; }
            h4 { font-size: 12px !important; }
            .header-section { margin-bottom: 8px !important; }
            .details-section { margin-bottom: 6px !important; }
            .footer-section { position: fixed; bottom: 10mm; left: 0; right: 0; width: 100%; }
        }
        .status-badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-completed { @apply bg-green-100 text-green-800; }
        .status-other { @apply bg-gray-100 text-gray-800; }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <a href="#" onclick="window.close()" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Close
            </a>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header with Organization and SRN Details -->
        <div class="header-section p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <div class="text-2xl font-bold mb-1 text-gray-900">Restaurant Management System</div>
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">STOCK RELEASE NOTE</h1>
                    <div class="text-lg font-medium">{{ $reportTitle }}</div>
                    <div class="text-sm text-gray-600 mt-1">Generated on: {{ $generated_at }}</div>
                </div>
            </div>
        </div>

        <!-- SRN Status and Summary -->
        <div class="details-section p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total SRNs</h4>
                    <p class="font-medium">{{ $reportData['srns']->count() }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Completed</h4>
                    <p class="font-medium">{{ $reportData['srns']->where('status', 'completed')->count() }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Pending</h4>
                    <p class="font-medium">{{ $reportData['srns']->where('status', 'pending')->count() }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Sales</h4>
                    <p class="font-medium">{{ $reportData['srns']->where('release_type', 'sale')->count() }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Total Value</h4>
                    <p class="font-bold text-lg">${{ number_format($reportData['srns']->sum(function($srn) { return $srn->items->sum(function($item) { return $item->release_quantity * $item->unit_price; }); }), 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="details-section grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Report Filters</h3>
                <div class="space-y-2">
                    <p><span class="font-bold text-gray-900">Status:</span> <span class="text-gray-700">{{ $filters['status'] }}</span></p>
                    <p><span class="font-bold text-gray-900">Branch:</span> <span class="text-gray-700">{{ $filters['branch'] }}</span></p>
                    <p><span class="font-bold text-gray-900">Release Type:</span> <span class="text-gray-700">{{ $filters['release_type'] }}</span></p>
                    <p><span class="font-bold text-gray-900">Date Range:</span> <span class="text-gray-700">{{ $filters['date_range'] }}</span></p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Report Period</h3>
                <div class="space-y-2">
                    <p class="text-gray-700">{{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : 'End' }}</p>
                </div>
            </div>
        </div>

        <!-- SRN Table -->
        <div class="avoid-break">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Released Items</h3>
                <table class="min-w-full srn-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">SRN No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Branch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Release Type</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">Total Items</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">Total Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border">Total Value</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['srns'] as $srn)
                            <tr>
                                <td class="px-4 py-3 text-sm border">{{ $srn->srn_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm border">{{ $srn->release_date ? date('d/m/Y', strtotime($srn->release_date)) : 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm border">{{ $srn->branch->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm border">{{ ucfirst($srn->release_type ?? 'N/A') }}</td>
                                <td class="px-4 py-3 text-center border">
                                    @if($srn->status === 'completed')
                                        <span class="status-badge status-completed">Completed</span>
                                    @elseif($srn->status === 'pending')
                                        <span class="status-badge status-pending">Pending</span>
                                    @else
                                        <span class="status-badge status-other">{{ ucfirst($srn->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right border">{{ $srn->items->count() ?? 0 }}</td>
                                <td class="px-4 py-3 text-right border">{{ $srn->items->sum('release_quantity') ?? 0 }}</td>
                                <td class="px-4 py-3 text-right border">${{ number_format($srn->items->sum(function($item) { return $item->release_quantity * $item->unit_price; }) ?? 0, 2) }}</td>
                                <td class="px-4 py-3 text-sm border">{{ $srn->notes ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center px-4 py-6 text-gray-500 bg-gray-50">
                                    <i class="fas fa-inbox"></i> No SRN data available for the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($reportData['srns']->isNotEmpty())
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th colspan="5" class="px-4 py-3 text-right font-semibold border">Totals:</th>
                                <th class="px-4 py-3 text-right border">{{ $reportData['srns']->sum(function($srn) { return $srn->items->count(); }) }}</th>
                                <th class="px-4 py-3 text-right border">{{ $reportData['srns']->sum(function($srn) { return $srn->items->sum('release_quantity'); }) }}</th>
                                <th class="px-4 py-3 text-right border">${{ number_format($reportData['srns']->sum(function($srn) { return $srn->items->sum(function($item) { return $item->release_quantity * $item->unit_price; }); }), 2) }}</th>
                                <th class="border"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section p-6 border-t border-gray-200 bg-gray-50 mt-8">
            <div class="mt-8 text-xs text-gray-500 text-center border-t pt-4">
                <p>This report was automatically generated by the Restaurant Management System</p>
                <p class="mt-1">Printed on {{ now()->format('d M Y H:i') }} | {{ $reportTitle }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        window.addEventListener('load', function() {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
