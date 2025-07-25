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

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                padding: 4px 6px !important;
                font-size: 10px !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <a href="javascript:history.back()" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        <!-- Header Section -->
        <div class="p-6 border-b border-gray-200">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900">{{ $reportTitle }}</h1>
                <p class="text-sm text-gray-600">Generated on: {{ $generated_at }}</p>
                @if($dateFrom || $dateTo)
                    <p class="text-sm text-gray-600">
                        Period: {{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : 'End' }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Filters Section -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">Report Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="font-medium text-gray-700">Item:</span>
                    <span class="text-gray-900">{{ $filters['item'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Category:</span>
                    <span class="text-gray-900">{{ $filters['category'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Branch:</span>
                    <span class="text-gray-900">{{ $filters['branch'] }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Transaction Type:</span>
                    <span class="text-gray-900">{{ $filters['transaction_type'] }}</span>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        @if($viewType === 'detailed')
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Detailed Stock Report</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Min Level</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Max Level</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reportData['stocks'] as $stock)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $stock->item->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $stock->item->category->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $stock->branch->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $stock->item->unit ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($stock->current_stock, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($stock->min_level ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($stock->max_level ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        @if($stock->current_stock <= 0)
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Out of Stock</span>
                                        @elseif($stock->current_stock <= 10)
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Low Stock</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">${{ number_format($stock->unit_price ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">${{ number_format(($stock->current_stock ?? 0) * ($stock->unit_price ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-2 text-center text-gray-500">No stock data available for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($reportData['stocks']->isNotEmpty())
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="9" class="px-4 py-2 text-right font-semibold text-gray-700">Total Value:</td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-900">
                                        ${{ number_format($reportData['stocks']->sum(function($stock) {
                                            return ($stock->current_stock ?? 0) * ($stock->unit_price ?? 0);
                                        }), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @elseif($viewType === 'summary')
            <!-- Summary Table -->
            <div class="table-container">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">
                    <i class="fas fa-table"></i> Stock Summary by Category
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Total Items</th>
                            <th class="text-right">In Stock</th>
                            <th class="text-right">Low Stock</th>
                            <th class="text-right">Out of Stock</th>
                            <th class="text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categoryStats = $reportData['stocks']->groupBy('item.category.name');
                        @endphp
                        @forelse($categoryStats as $category => $stocks)
                            <tr>
                                <td>{{ $category ?: 'Uncategorized' }}</td>
                                <td class="text-right">{{ $stocks->count() }}</td>
                                <td class="text-right">{{ $stocks->where('current_stock', '>', 0)->count() }}</td>
                                <td class="text-right">{{ $stocks->where('current_stock', '<=', 10)->where('current_stock', '>', 0)->count() }}</td>
                                <td class="text-right">{{ $stocks->where('current_stock', '<=', 0)->count() }}</td>
                                <td class="text-right">
                                    ${{ number_format($stocks->sum(function($stock) {
                                        return ($stock->current_stock ?? 0) * ($stock->unit_price ?? 0);
                                    }), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                    <i class="fas fa-inbox"></i> No stock data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($viewType === 'master_only')
            <!-- Master Items Only -->
            <div class="table-container">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">
                    <i class="fas fa-list"></i> Master Items List
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['items'] as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                <td>{{ $item->unit ?? 'N/A' }}</td>
                                <td class="text-right">${{ number_format($item->price ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                    <i class="fas fa-inbox"></i> No items available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 text-center text-sm text-gray-500">
            <p>This report was automatically generated by the Restaurant Management System</p>
            <p>Generated on {{ $generated_at }}</p>
        </div>
    </div>
</body>

</html>
