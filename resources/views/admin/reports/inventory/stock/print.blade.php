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
                font-size: 9px !important;
            }

            th,
            td {
                padding: 3px 4px !important;
                font-size: 9px !important;
                border: 1px solid #ddd !important;
                line-height: 1.2 !important;
            }

            th {
                background-color: #f5f5f5 !important;
                font-weight: bold !important;
            }

            .text-2xl {
                font-size: 14px !important;
            }

            .grid {
                display: block !important;
            }

            .grid > div {
                display: inline-block !important;
                width: 18% !important;
                margin: 0 1% !important;
                text-align: center !important;
            }
        }

        @media screen {
            .print-container {
                max-width: 1200px;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-4 md:p-8">
    <!-- Back and Action Buttons -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-6">
            <a href="#" onclick="window.close();" class="flex items-center text-indigo-600 hover:text-indigo-800">
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

        <!-- Summary Statistics -->
        @php
            $stocksData = is_array($reportData) && isset($reportData['stocks']) ? $reportData['stocks'] : $reportData;
            $totalItems = count($stocksData);
            $totalValue = 0;
            $inStockCount = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;
            $totalTransactions = 0;

            foreach ($stocksData as $stock) {
                if (is_array($stock)) {
                    $currentStock = $stock['current_stock'] ?? 0;
                    $reorderLevel = $stock['reorder_level'] ?? 0;
                    $unitPrice = $stock['avg_cost_per_unit'] ?? 0;
                    $transactionCount = $stock['transaction_count'] ?? 0;
                } else {
                    $currentStock = $stock->current_stock ?? 0;
                    $reorderLevel = $stock->reorder_level ?? 0;
                    $unitPrice = $stock->unit_price ?? ($stock->item->price ?? 0);
                    $transactionCount = $stock->transaction_count ?? 0;
                }

                $totalValue += $currentStock * $unitPrice;
                $totalTransactions += $transactionCount;

                if ($currentStock <= 0) {
                    $outOfStockCount++;
                } elseif ($currentStock <= $reorderLevel) {
                    $lowStockCount++;
                } else {
                    $inStockCount++;
                }
            }
        @endphp

        @if($totalItems > 0)
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">Summary Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalItems }}</div>
                    <div class="text-sm text-gray-600">Total Items</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $inStockCount }}</div>
                    <div class="text-sm text-gray-600">In Stock</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $lowStockCount }}</div>
                    <div class="text-sm text-gray-600">Low Stock</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $outOfStockCount }}</div>
                    <div class="text-sm text-gray-600">Out of Stock</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">Rs. {{ number_format($totalValue, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Value</div>
                </div>
            </div>
        </div>
        @endif

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
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $stocksData = is_array($reportData) && isset($reportData['stocks']) ? $reportData['stocks'] : $reportData;
                                $totalValue = 0;
                            @endphp
                            @forelse($stocksData as $stock)
                                @php
                                    // Handle array-based data structure from generateStockReport
                                    if (is_array($stock)) {
                                        $itemName = $stock['item_name'] ?? 'N/A';
                                        $itemCode = $stock['item_code'] ?? 'N/A';
                                        $categoryName = $stock['category_name'] ?? 'N/A';
                                        $unit = $stock['unit'] ?? 'N/A';
                                        $branchName = $stock['branch_name'] ?? 'N/A';
                                        $currentStock = $stock['current_stock'] ?? 0;
                                        $reorderLevel = $stock['reorder_level'] ?? 0;
                                        $status = $stock['stock_status'] ?? 'unknown';
                                        $unitPrice = $stock['avg_cost_per_unit'] ?? 0;
                                    } else {
                                        // Handle object-based data structure (fallback)
                                        $item = $stock->item ?? $stock;
                                        $branch = $stock->branch ?? null;
                                        $itemName = $item->name ?? 'N/A';
                                        $itemCode = $item->item_code ?? 'N/A';
                                        $categoryName = $item->category->name ?? 'N/A';
                                        $unit = $item->unit ?? 'N/A';
                                        $branchName = $branch->name ?? 'N/A';
                                        $currentStock = $stock->current_stock ?? 0;
                                        $reorderLevel = $stock->reorder_level ?? ($item->reorder_level ?? 0);
                                        $status = $stock->status ?? 'unknown';
                                        $unitPrice = $stock->unit_price ?? ($item->price ?? 0);
                                    }
                                    $itemValue = $currentStock * $unitPrice;
                                    $totalValue += $itemValue;
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $itemName }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $categoryName }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $branchName }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $unit }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($currentStock, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($reorderLevel, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        @if($status === 'out_of_stock' || $currentStock <= 0)
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Out of Stock</span>
                                        @elseif($status === 'low_stock' || $currentStock <= $reorderLevel)
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Low Stock</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">Rs. {{ number_format($unitPrice, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">Rs. {{ number_format($itemValue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-2 text-center text-gray-500">No stock data available for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($stocksData) > 0)
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="8" class="px-4 py-2 text-right font-semibold text-gray-700">Total Value:</td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-900">Rs. {{ number_format($totalValue, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Stock Transactions Section for Detailed View -->
                @if(isset($reportData['transactions']) && count($reportData['transactions']) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Recent Stock Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['transactions'] as $transaction)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->item->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->branch->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                                    @if(in_array($transaction->transaction_type, ['grn_stock_in', 'gtn_incoming', 'production_in', 'adjustment']))
                                                        bg-green-100 text-green-800
                                                    @else
                                                        bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right text-gray-700
                                                @if($transaction->quantity > 0) text-green-600 @else text-red-600 @endif">
                                                {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->reference_id ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @elseif($viewType === 'stock_movement')
            <!-- Stock Movement Analysis -->
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Stock Movement Analysis</h3>

                <!-- Stock Summary Table -->
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Opening</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock In</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock Out</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Closing</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Current</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Transactions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $stocksData = is_array($reportData) && isset($reportData['stocks']) ? $reportData['stocks'] : $reportData;
                            @endphp
                            @forelse($stocksData as $stock)
                                @php
                                    if (is_array($stock)) {
                                        $itemName = $stock['item_name'] ?? 'N/A';
                                        $branchName = $stock['branch_name'] ?? 'N/A';
                                        $openingStock = $stock['opening_stock'] ?? 0;
                                        $stockIn = $stock['stock_in'] ?? 0;
                                        $stockOut = $stock['stock_out'] ?? 0;
                                        $closingStock = $stock['closing_stock'] ?? 0;
                                        $currentStock = $stock['current_stock'] ?? 0;
                                        $transactionCount = $stock['transaction_count'] ?? 0;
                                    } else {
                                        $itemName = $stock->item->name ?? 'N/A';
                                        $branchName = $stock->branch->name ?? 'N/A';
                                        $openingStock = $stock->opening_stock ?? 0;
                                        $stockIn = $stock->stock_in ?? 0;
                                        $stockOut = $stock->stock_out ?? 0;
                                        $closingStock = $stock->closing_stock ?? 0;
                                        $currentStock = $stock->current_stock ?? 0;
                                        $transactionCount = $stock->transaction_count ?? 0;
                                    }
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $itemName }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $branchName }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($openingStock, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-green-600">+{{ number_format($stockIn, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-red-600">-{{ number_format($stockOut, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-blue-600">{{ number_format($closingStock, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900 font-semibold">{{ number_format($currentStock, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-center text-gray-700">{{ $transactionCount }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-2 text-center text-gray-500">No stock movement data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Recent Transactions -->
                @if(isset($reportData['transactions']) && count($reportData['transactions']) > 0)
                    <div class="mt-8">
                        <h4 class="text-md font-semibold mb-4">Recent Stock Transactions</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['transactions'] as $transaction)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->item->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->branch->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                                    @if(in_array($transaction->transaction_type, ['grn_stock_in', 'gtn_incoming', 'production_in', 'adjustment']) && $transaction->quantity > 0)
                                                        bg-green-100 text-green-800
                                                    @else
                                                        bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-semibold
                                                @if($transaction->quantity > 0) text-green-600 @else text-red-600 @endif">
                                                {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $transaction->reference_id ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @elseif($viewType === 'summary')
            <!-- Summary Table -->
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-table"></i> Stock Summary by Category
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Items</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">In Stock</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Low Stock</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Out of Stock</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $stocksData = is_array($reportData) && isset($reportData['stocks']) ? $reportData['stocks'] : $reportData;
                                $categoryStats = collect($stocksData)->groupBy(function($stock) {
                                    if (is_array($stock)) {
                                        return $stock['category_name'] ?? 'Uncategorized';
                                    }
                                    return $stock->item->category->name ?? 'Uncategorized';
                                });
                            @endphp
                            @forelse($categoryStats as $category => $stocks)
                                @php
                                    $totalItems = $stocks->count();
                                    $inStock = $stocks->filter(function($stock) {
                                        $currentStock = is_array($stock) ? ($stock['current_stock'] ?? 0) : ($stock->current_stock ?? 0);
                                        return $currentStock > 0;
                                    })->count();

                                    $lowStock = $stocks->filter(function($stock) {
                                        $currentStock = is_array($stock) ? ($stock['current_stock'] ?? 0) : ($stock->current_stock ?? 0);
                                        $reorderLevel = is_array($stock) ? ($stock['reorder_level'] ?? 0) : ($stock->reorder_level ?? 0);
                                        return $currentStock <= $reorderLevel && $currentStock > 0;
                                    })->count();

                                    $outOfStock = $stocks->filter(function($stock) {
                                        $currentStock = is_array($stock) ? ($stock['current_stock'] ?? 0) : ($stock->current_stock ?? 0);
                                        return $currentStock <= 0;
                                    })->count();

                                    $totalValue = $stocks->sum(function($stock) {
                                        $currentStock = is_array($stock) ? ($stock['current_stock'] ?? 0) : ($stock->current_stock ?? 0);
                                        $unitPrice = is_array($stock) ? ($stock['avg_cost_per_unit'] ?? 0) : ($stock->unit_price ?? ($stock->item->price ?? 0));
                                        return $currentStock * $unitPrice;
                                    });
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $category }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">{{ $totalItems }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-green-600">{{ $inStock }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-yellow-600">{{ $lowStock }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-red-600">{{ $outOfStock }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">Rs. {{ number_format($totalValue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                                        <i class="fas fa-inbox"></i> No stock data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($viewType === 'master_items_only')
            <!-- Master Items Only -->
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-list"></i> Master Items List
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $itemsData = is_array($reportData) && isset($reportData['items']) ? $reportData['items'] : [];
                                if (empty($itemsData) && isset($reportData['stocks'])) {
                                    // Extract unique items from stocks data
                                    $stocksData = is_array($reportData) && isset($reportData['stocks']) ? $reportData['stocks'] : $reportData;
                                    $uniqueItems = collect();

                                    foreach ($stocksData as $stock) {
                                        if (is_array($stock)) {
                                            // Create a virtual item object from the stock data
                                            $itemObj = (object) [
                                                'id' => $stock['item_id'] ?? 0,
                                                'name' => $stock['item_name'] ?? 'N/A',
                                                'item_code' => $stock['item_code'] ?? 'N/A',
                                                'unit' => $stock['unit'] ?? 'N/A',
                                                'price' => $stock['avg_cost_per_unit'] ?? 0,
                                                'category' => (object) ['name' => $stock['category_name'] ?? 'N/A']
                                            ];

                                            if (!$uniqueItems->contains('id', $itemObj->id)) {
                                                $uniqueItems->push($itemObj);
                                            }
                                        } else {
                                            $item = $stock->item ?? $stock;
                                            if (!$uniqueItems->contains('id', $item->id)) {
                                                $uniqueItems->push($item);
                                            }
                                        }
                                    }
                                    $itemsData = $uniqueItems;
                                }
                            @endphp
                            @forelse($itemsData as $item)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $item->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $item->item_code ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $item->category->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $item->unit ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-700">Rs. {{ number_format($item->price ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                                        <i class="fas fa-inbox"></i> No items available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
