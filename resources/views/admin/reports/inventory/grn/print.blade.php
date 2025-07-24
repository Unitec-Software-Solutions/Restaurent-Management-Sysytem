
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
            .print-container { box-shadow: none !important; border: none !important; margin: 0; padding: 0; width: 100%; height: auto; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { padding: 4px 6px !important; font-size: 10px !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <button onclick="window.close()" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Close
            </button>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <div class="text-2xl font-bold mb-1 text-gray-900">Restaurant Management System</div>
                    <div class="text-lg font-medium text-gray-600">{{ $reportTitle }}</div>
                    <div class="text-sm text-gray-500 mt-1">Generated on: {{ $generated_at }}</div>
                    @if($dateFrom || $dateTo)
                        <div class="text-sm text-gray-500">Period: {{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : 'End' }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-base font-semibold text-gray-700">Report Filters</div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div class="text-xs text-gray-500">Status:</div>
                        <div class="text-xs text-gray-700">{{ $filters['status'] }}</div>
                        <div class="text-xs text-gray-500">Supplier:</div>
                        <div class="text-xs text-gray-700">{{ $filters['supplier'] }}</div>
                        <div class="text-xs text-gray-500">Branch:</div>
                        <div class="text-xs text-gray-700">{{ $filters['branch'] }}</div>
                        <div class="text-xs text-gray-500">Date Range:</div>
                        <div class="text-xs text-gray-700">{{ $filters['date_range'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($viewType === 'summary' || $viewType === 'detailed')
        <!-- Summary Section -->
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <div class="text-sm font-medium text-gray-500">Total GRNs</div>
                    <div class="font-bold text-lg">{{ $reportData['grns']->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Completed</div>
                    <div class="font-bold text-green-600 text-lg">{{ $reportData['grns']->where('status', 'completed')->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Pending</div>
                    <div class="font-bold text-yellow-600 text-lg">{{ $reportData['grns']->where('status', 'pending')->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Total Value</div>
                    <div class="font-bold text-purple-600 text-lg">${{ number_format($reportData['grns']->sum('total_amount'), 2) }}</div>
                </div>
            </div>
        </div>
        @endif

        @if($viewType === 'detailed')
        <!-- Detailed GRN Table -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Detailed GRN Report</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">GRN No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Supplier</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Branch</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Paid Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Balance</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Payment Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['grns'] as $grn)
                        <tr>
                            <td class="px-4 py-2 border">{{ $grn->grn_number ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $grn->received_date ? date('d/m/Y', strtotime($grn->received_date)) : 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $grn->supplier->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $grn->branch->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-center border">
                                @if($grn->status === 'completed')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Completed</span>
                                @elseif($grn->status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">{{ ucfirst($grn->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right border">${{ number_format($grn->total_amount ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-right border">${{ number_format($grn->paid_amount ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-right border">${{ number_format(($grn->total_amount ?? 0) - ($grn->paid_amount ?? 0), 2) }}</td>
                            <td class="px-4 py-2 text-center border">
                                @php $balance = ($grn->total_amount ?? 0) - ($grn->paid_amount ?? 0); @endphp
                                @if($balance <= 0)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Paid</span>
                                @elseif($grn->paid_amount > 0)
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Partial</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No GRN data available for the selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($reportData['grns']->isNotEmpty())
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="5" class="px-4 py-2 text-right font-semibold border">Totals:</th>
                            <th class="px-4 py-2 text-right font-bold border">${{ number_format($reportData['grns']->sum('total_amount'), 2) }}</th>
                            <th class="px-4 py-2 text-right font-bold border">${{ number_format($reportData['grns']->sum('paid_amount'), 2) }}</th>
                            <th class="px-4 py-2 text-right font-bold border">${{ number_format($reportData['grns']->sum('total_amount') - $reportData['grns']->sum('paid_amount'), 2) }}</th>
                            <th class="border"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @elseif($viewType === 'summary')
        <!-- Summary Table -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">GRN Summary by Supplier</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Supplier</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total GRNs</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Completed</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Pending</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Paid Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $supplierStats = $reportData['grns']->groupBy('supplier.name'); @endphp
                        @forelse($supplierStats as $supplier => $grns)
                        <tr>
                            <td class="px-4 py-2 border">{{ $supplier ?: 'Unknown Supplier' }}</td>
                            <td class="px-4 py-2 text-right border">{{ $grns->count() }}</td>
                            <td class="px-4 py-2 text-right border">{{ $grns->where('status', 'completed')->count() }}</td>
                            <td class="px-4 py-2 text-right border">{{ $grns->where('status', 'pending')->count() }}</td>
                            <td class="px-4 py-2 text-right border">${{ number_format($grns->sum('total_amount'), 2) }}</td>
                            <td class="px-4 py-2 text-right border">${{ number_format($grns->sum('paid_amount'), 2) }}</td>
                            <td class="px-4 py-2 text-right border">${{ number_format($grns->sum('total_amount') - $grns->sum('paid_amount'), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No GRN data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @elseif($viewType === 'supplier_details')
        <!-- Master Suppliers Only -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Master Suppliers List</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Supplier Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Contact Person</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Phone</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Address</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['suppliers'] as $supplier)
                        <tr>
                            <td class="px-4 py-2 border">{{ $supplier->name }}</td>
                            <td class="px-4 py-2 border">{{ $supplier->contact_person ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $supplier->phone ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $supplier->email ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $supplier->address ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-center border">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Active</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No suppliers available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50 mt-8 text-center text-xs text-gray-500">
            <p>This report was automatically generated by the Restaurant Management System</p>
            <p>Generated on {{ $generated_at }}</p>
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
