@extends('admin.reports.pdf.base')

@section('content')
<!-- Filters Applied -->
@if(!empty($filters))
<div class="filters-section">
    <div class="filters-title">Applied Filters</div>
    <div class="filters-grid">
        @if(isset($filters['date_from']) && $filters['date_from'])
            <div class="filter-item">
                <span class="filter-label">From Date:</span> {{ $filters['date_from'] }}
            </div>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
            <div class="filter-item">
                <span class="filter-label">To Date:</span> {{ $filters['date_to'] }}
            </div>
        @endif
        @if(isset($filters['branch_name']) && $filters['branch_name'])
            <div class="filter-item">
                <span class="filter-label">Branch:</span> {{ $filters['branch_name'] }}
            </div>
        @endif
        @if(isset($filters['category_name']) && $filters['category_name'])
            <div class="filter-item">
                <span class="filter-label">Category:</span> {{ $filters['category_name'] }}
            </div>
        @endif
        @if(isset($filters['item_name']) && $filters['item_name'])
            <div class="filter-item">
                <span class="filter-label">Item:</span> {{ $filters['item_name'] }}
            </div>
        @endif
        @if(isset($filters['transaction_type']) && $filters['transaction_type'])
            <div class="filter-item">
                <span class="filter-label">Transaction Type:</span> {{ ucwords(str_replace('_', ' ', $filters['transaction_type'])) }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalItems = $reportData->count();
    $inStockItems = $reportData->where('stock_status', 'In Stock')->count();
    $lowStockItems = $reportData->where('stock_status', 'Low Stock')->count();
    $outOfStockItems = $reportData->where('stock_status', 'Out of Stock')->count();
    $totalValue = $reportData->sum(function($item) {
        return $item->current_stock * ($item->selling_price ?? 0);
    });
    $totalStockIn = $reportData->sum('stock_in');
    $totalStockOut = $reportData->sum('stock_out');
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalItems) }}</div>
            <div class="summary-label">Total Items</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($inStockItems) }}</div>
            <div class="summary-label">In Stock</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($lowStockItems) }}</div>
            <div class="summary-label">Low Stock</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">{{ number_format($outOfStockItems) }}</div>
            <div class="summary-label">Out of Stock</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">Rs. {{ number_format($totalValue, 2) }}</div>
            <div class="summary-label">Total Value</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($totalStockIn, 2) }}</div>
            <div class="summary-label">Total Stock In</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($totalStockOut, 2) }}</div>
            <div class="summary-label">Total Stock Out</div>
        </div>
    </div>
</div>

@if($viewType === 'summary')
    <!-- Summary View - High Level Overview -->
    <div class="avoid-break">
        <h3 class="mb-2" style="font-size: 14px; font-weight: bold; color: #374151;">Summary Overview</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%">Category</th>
                    <th style="width: 10%">Items</th>
                    <th style="width: 15%">Current Stock</th>
                    <th style="width: 15%">Stock Value</th>
                    <th style="width: 15%">In Stock</th>
                    <th style="width: 15%">Low Stock</th>
                    <th style="width: 10%">Out of Stock</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $categoryStats = $reportData->groupBy(function($item) {
                        return $item->category->name ?? 'Uncategorized';
                    })->map(function($items, $category) {
                        $totalStock = $items->sum('current_stock');
                        $totalValue = $items->sum(function($item) {
                            return $item->current_stock * ($item->selling_price ?? 0);
                        });
                        $inStock = $items->where('stock_status', 'In Stock')->count();
                        $lowStock = $items->where('stock_status', 'Low Stock')->count();
                        $outStock = $items->where('stock_status', 'Out of Stock')->count();

                        return [
                            'category' => $category,
                            'count' => $items->count(),
                            'total_stock' => $totalStock,
                            'total_value' => $totalValue,
                            'in_stock' => $inStock,
                            'low_stock' => $lowStock,
                            'out_stock' => $outStock
                        ];
                    });
                @endphp
                @foreach($categoryStats as $stat)
                <tr>
                    <td class="font-semibold">{{ $stat['category'] }}</td>
                    <td class="text-center">{{ number_format($stat['count']) }}</td>
                    <td class="text-center">{{ number_format($stat['total_stock'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['total_value'], 2) }}</td>
                    <td class="text-center text-success">{{ $stat['in_stock'] }}</td>
                    <td class="text-center text-warning">{{ $stat['low_stock'] }}</td>
                    <td class="text-center text-danger">{{ $stat['out_stock'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($viewType === 'master_only')
    <!-- Master Only View - Basic Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 15%">Item Code</th>
                    <th style="width: 25%">Item Name</th>
                    <th style="width: 15%">Category</th>
                    <th style="width: 12%">Current Stock</th>
                    <th style="width: 15%">Selling Price</th>
                    <th style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $item->item_code ?? 'N/A' }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-sm">{{ $item->category->name ?? 'N/A' }}</td>
                    <td class="text-center font-semibold">{{ number_format($item->current_stock, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->selling_price ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if($item->stock_status === 'In Stock') status-in-stock
                            @elseif($item->stock_status === 'Low Stock') status-low-stock
                            @else status-out-of-stock
                            @endif">
                            {{ $item->stock_status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Detailed View - Complete Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 10%">Item Code</th>
                    <th style="width: 15%">Item Name</th>
                    <th style="width: 10%">Category</th>
                    <th style="width: 8%">Current Stock</th>
                    <th style="width: 8%">Reorder Level</th>
                    <th style="width: 10%">Buying Price</th>
                    <th style="width: 10%">Selling Price</th>
                    <th style="width: 8%">Stock In</th>
                    <th style="width: 8%">Stock Out</th>
                    <th style="width: 8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $item->item_code ?? 'N/A' }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-sm">{{ $item->category->name ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="font-semibold">{{ number_format($item->current_stock, 2) }}</span>
                        @if($item->unit)
                            <br><span class="text-xs">{{ $item->unit }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->reorder_level ?? 0, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->buying_price ?? 0, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($item->selling_price ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($item->stock_in ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($item->stock_out ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if($item->stock_status === 'In Stock') status-in-stock
                            @elseif($item->stock_status === 'Low Stock') status-low-stock
                            @else status-out-of-stock
                            @endif">
                            {{ $item->stock_status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($viewType === 'detailed')
    <!-- Additional Analysis for Detailed View -->
    <div class="page-break">
        <h3 class="mb-4" style="font-size: 14px; font-weight: bold; color: #374151;">Additional Analysis</h3>

        <!-- Top Items by Value -->
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #4b5563;">Top 10 Items by Stock Value</h4>
            @php
                $topItemsByValue = $reportData->sortByDesc(function($item) {
                    return $item->current_stock * ($item->selling_price ?? 0);
                })->take(10);
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topItemsByValue as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="text-center">{{ number_format($item->current_stock, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($item->selling_price ?? 0, 2) }}</td>
                        <td class="text-right font-semibold">Rs. {{ number_format($item->current_stock * ($item->selling_price ?? 0), 2) }}</td>
                        <td class="text-center">
                            <span class="status-badge
                                @if($item->stock_status === 'In Stock') status-in-stock
                                @elseif($item->stock_status === 'Low Stock') status-low-stock
                                @else status-out-of-stock
                                @endif">
                                {{ $item->stock_status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Items Requiring Attention -->
        @php
            $criticalItems = $reportData->where('stock_status', 'Out of Stock');
            $lowStockItems = $reportData->where('stock_status', 'Low Stock');
        @endphp

        @if($criticalItems->count() > 0)
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #dc2626;">Items Out of Stock ({{ $criticalItems->count() }})</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Last Stock</th>
                        <th>Reorder Level</th>
                        <th>Supplier Info</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($criticalItems->take(15) as $item)
                    <tr>
                        <td class="font-semibold">{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ number_format($item->current_stock, 2) }}</td>
                        <td class="text-center">{{ number_format($item->reorder_level ?? 0, 2) }}</td>
                        <td class="text-sm">{{ $item->supplier_name ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif

@endsection
