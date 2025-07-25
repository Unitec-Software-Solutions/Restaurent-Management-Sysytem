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
                        <div class="text-xs text-gray-500">From Branch:</div>
                        <div class="text-xs text-gray-700">{{ $filters['from_branch'] }}</div>
                        <div class="text-xs text-gray-500">To Branch:</div>
                        <div class="text-xs text-gray-700">{{ $filters['to_branch'] }}</div>
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
                    <div class="text-sm font-medium text-gray-500">Total GTNs</div>
                    <div class="font-bold text-lg">{{ $reportData['gtns']->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Sent</div>
                    <div class="font-bold text-green-600 text-lg">{{ $reportData['gtns']->where('origin_status', 'sent')->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Pending</div>
                    <div class="font-bold text-yellow-600 text-lg">{{ $reportData['gtns']->where('origin_status', 'pending')->count() }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Received</div>
                    <div class="font-bold text-purple-600 text-lg">{{ $reportData['gtns']->where('receiver_status', 'received')->count() }}</div>
                </div>
            </div>
        </div>
        @endif

        @if($viewType === 'detailed')
        <!-- Detailed GTN Table -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Detailed GTN Report</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">GTN No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">From Branch</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">To Branch</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Origin Status</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Receiver Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total Items</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['gtns'] as $gtn)
                        <tr>
                            <td class="px-4 py-2 border">{{ $gtn->gtn_number ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $gtn->transfer_date ? date('d/m/Y', strtotime($gtn->transfer_date)) : 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $gtn->fromBranch->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $gtn->toBranch->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-center border">
                                @if($gtn->origin_status === 'sent')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Sent</span>
                                @elseif($gtn->origin_status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">{{ ucfirst($gtn->origin_status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center border">
                                @if($gtn->receiver_status === 'received')
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-semibold">Received</span>
                                @elseif($gtn->receiver_status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">{{ ucfirst($gtn->receiver_status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right border">{{ $gtn->items->count() ?? 0 }}</td>
                            <td class="px-4 py-2 text-right border">{{ $gtn->items->sum('transfer_quantity') ?? 0 }}</td>
                            <td class="px-4 py-2 border">{{ $gtn->notes ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No GTN data available for the selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($reportData['gtns']->isNotEmpty())
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="6" class="px-4 py-2 text-right font-semibold border">Totals:</th>
                            <th class="px-4 py-2 text-right font-bold border">{{ $reportData['gtns']->sum(function($gtn) { return $gtn->items->count(); }) }}</th>
                            <th class="px-4 py-2 text-right font-bold border">{{ $reportData['gtns']->sum(function($gtn) { return $gtn->items->sum('transfer_quantity'); }) }}</th>
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
            <h3 class="text-lg font-semibold mb-4">GTN Summary by Branch</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">From Branch</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">To Branch</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total GTNs</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Sent</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Pending</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Received</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase border">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $branchStats = $reportData['gtns']->groupBy(function($gtn) {
                                return ($gtn->fromBranch->name ?? 'Unknown') . ' → ' . ($gtn->toBranch->name ?? 'Unknown');
                            });
                        @endphp
                        @forelse($branchStats as $branches => $gtns)
                            @php
                                list($fromBranch, $toBranch) = explode(' → ', $branches, 2);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 border">{{ $fromBranch }}</td>
                                <td class="px-4 py-2 border">{{ $toBranch }}</td>
                                <td class="px-4 py-2 text-right border">{{ $gtns->count() }}</td>
                                <td class="px-4 py-2 text-right border">{{ $gtns->where('origin_status', 'sent')->count() }}</td>
                                <td class="px-4 py-2 text-right border">{{ $gtns->where('origin_status', 'pending')->count() }}</td>
                                <td class="px-4 py-2 text-right border">{{ $gtns->where('receiver_status', 'received')->count() }}</td>
                                <td class="px-4 py-2 text-right border">{{ $gtns->sum(function($gtn) { return $gtn->items->sum('transfer_quantity'); }) }}</td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No GTN data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @elseif($viewType === 'Branches_only')
        <!-- Master Branches Only -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Master Branches List</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Branch Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Manager</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Phone</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Address</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase border">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['branches'] as $branch)
                        <tr>
                            <td class="px-4 py-2 border">{{ $branch->name }}</td>
                            <td class="px-4 py-2 border">{{ $branch->manager_name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $branch->phone ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $branch->email ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $branch->address ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-center border">
                                @if($branch->is_active)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Active</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center px-4 py-6 text-gray-400"><i class="fas fa-inbox"></i> No branches available.</td>
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

