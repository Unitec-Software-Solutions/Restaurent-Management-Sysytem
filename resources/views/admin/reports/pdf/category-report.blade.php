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
        @if(isset($filters['category_name']) && $filters['category_name'])
            <div class="filter-item">
                <span class="filter-label">Category:</span> {{ $filters['category_name'] }}
            </div>
        @endif
        @if(isset($filters['branch_name']) && $filters['branch_name'])
            <div class="filter-item">
                <span class="filter-label">Branch:</span> {{ $filters['branch_name'] }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalCategories = $categories->count();
    $activeCategories = $categories->where('is_active', true)->count();
    $totalItems = $categories->sum('total_items');
    $totalValue = $categories->sum('total_value');
    $avgItemsPerCategory = $totalCategories > 0 ? $totalItems / $totalCategories : 0;
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalCategories) }}</div>
            <div class="summary-label">Total Categories</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($activeCategories) }}</div>
            <div class="summary-label">Active Categories</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalItems) }}</div>
            <div class="summary-label">Total Items</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">Rs. {{ number_format($totalValue, 2) }}</div>
            <div class="summary-label">Total Value</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($avgItemsPerCategory, 1) }}</div>
            <div class="summary-label">Avg Items/Category</div>
        </div>
    </div>
</div>

<!-- Category Data Table -->
<div class="avoid-break">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 20%">Category Name</th>
                <th style="width: 12%">Code</th>
                <th style="width: 10%">Total Items</th>
                <th style="width: 10%">In Stock</th>
                <th style="width: 10%">Low Stock</th>
                <th style="width: 10%">Out of Stock</th>
                <th style="width: 12%">Total Value</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $index => $category)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-semibold">{{ $category->name }}</td>
                <td class="text-sm">{{ $category->code ?? 'N/A' }}</td>
                <td class="text-center">{{ number_format($category->total_items ?? 0) }}</td>
                <td class="text-center text-success">{{ number_format($category->in_stock_items ?? 0) }}</td>
                <td class="text-center text-warning">{{ number_format($category->low_stock_items ?? 0) }}</td>
                <td class="text-center text-danger">{{ number_format($category->out_of_stock_items ?? 0) }}</td>
                <td class="text-right font-semibold">
                    Rs. {{ number_format($category->total_value ?? 0, 2) }}
                </td>
                <td class="text-center">
                    @php
                        $statusClass = $category->is_active ? 'status-active' : 'status-out-of-stock';
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Additional Analysis (if detailed view) -->
@if($viewType === 'detailed' && $categories->count() > 0)
<div class="page-break"></div>

<div class="mt-4">
    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #2563eb;">Category Performance Analysis</h3>

    <!-- Top Performing Categories -->
    @php
        $topCategories = $categories->sortByDesc('total_value')->take(10);
    @endphp

    @if($topCategories->count() > 0)
    <div class="avoid-break">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Top Categories by Value</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Category</th>
                    <th class="text-center">Items</th>
                    <th class="text-right">Total Value</th>
                    <th class="text-right">Avg Item Value</th>
                    <th class="text-center">Stock Health</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topCategories as $index => $category)
                <tr>
                    <td class="text-center font-semibold">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $category->name }}</td>
                    <td class="text-center">{{ $category->total_items ?? 0 }}</td>
                    <td class="text-right">Rs. {{ number_format($category->total_value ?? 0, 2) }}</td>
                    <td class="text-right">
                        Rs. {{ number_format(($category->total_items ?? 0) > 0 ? ($category->total_value ?? 0) / ($category->total_items ?? 1) : 0, 2) }}
                    </td>
                    <td class="text-center">
                        @php
                            $totalItems = $category->total_items ?? 0;
                            $inStock = $category->in_stock_items ?? 0;
                            $healthPercentage = $totalItems > 0 ? ($inStock / $totalItems) * 100 : 0;
                            $healthClass = $healthPercentage >= 80 ? 'text-success' : ($healthPercentage >= 50 ? 'text-warning' : 'text-danger');
                        @endphp
                        <span class="{{ $healthClass }} font-semibold">{{ number_format($healthPercentage, 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Stock Status Distribution -->
    @php
        $stockDistribution = $categories->map(function($category) {
            $total = $category->total_items ?? 0;
            return [
                'category' => $category->name,
                'total_items' => $total,
                'in_stock_percentage' => $total > 0 ? (($category->in_stock_items ?? 0) / $total) * 100 : 0,
                'low_stock_percentage' => $total > 0 ? (($category->low_stock_items ?? 0) / $total) * 100 : 0,
                'out_of_stock_percentage' => $total > 0 ? (($category->out_of_stock_items ?? 0) / $total) * 100 : 0,
                'in_stock' => $category->in_stock_items ?? 0,
                'low_stock' => $category->low_stock_items ?? 0,
                'out_of_stock' => $category->out_of_stock_items ?? 0,
            ];
        })->filter(function($item) {
            return $item['total_items'] > 0;
        })->sortByDesc('total_items');
    @endphp

    @if($stockDistribution->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Stock Status Distribution by Category</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Total Items</th>
                    <th class="text-center">In Stock</th>
                    <th class="text-center">Low Stock</th>
                    <th class="text-center">Out of Stock</th>
                    <th class="text-center">Health Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockDistribution as $dist)
                <tr>
                    <td class="font-semibold">{{ $dist['category'] }}</td>
                    <td class="text-center">{{ $dist['total_items'] }}</td>
                    <td class="text-center text-success">
                        {{ $dist['in_stock'] }}
                        <br><span class="text-xs">({{ number_format($dist['in_stock_percentage'], 1) }}%)</span>
                    </td>
                    <td class="text-center text-warning">
                        {{ $dist['low_stock'] }}
                        <br><span class="text-xs">({{ number_format($dist['low_stock_percentage'], 1) }}%)</span>
                    </td>
                    <td class="text-center text-danger">
                        {{ $dist['out_of_stock'] }}
                        <br><span class="text-xs">({{ number_format($dist['out_of_stock_percentage'], 1) }}%)</span>
                    </td>
                    <td class="text-center">
                        @php
                            $healthScore = $dist['in_stock_percentage'];
                            $scoreClass = $healthScore >= 80 ? 'text-success' : ($healthScore >= 50 ? 'text-warning' : 'text-danger');
                        @endphp
                        <span class="{{ $scoreClass }} font-semibold">{{ number_format($healthScore, 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Category Insights -->
    @php
        $criticalCategories = $categories->filter(function($category) {
            $total = $category->total_items ?? 0;
            $outOfStock = $category->out_of_stock_items ?? 0;
            return $total > 0 && ($outOfStock / $total) > 0.3; // More than 30% out of stock
        });

        $highValueCategories = $categories->filter(function($category) {
            return ($category->total_value ?? 0) > 50000; // High value threshold
        });

        $diverseCategories = $categories->filter(function($category) {
            return ($category->total_items ?? 0) > 20; // Many items
        });
    @endphp

    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px; color: #dc2626;">Category Insights & Recommendations</h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <!-- Critical Categories -->
            <div style="border: 1px solid #fecaca; border-radius: 6px; padding: 10px; background: #fef2f2;">
                <h5 style="font-size: 11px; font-weight: 600; color: #dc2626; margin-bottom: 8px;">
                    Categories Needing Attention ({{ $criticalCategories->count() }})
                </h5>
                @if($criticalCategories->count() > 0)
                    @foreach($criticalCategories->take(5) as $category)
                        <div style="font-size: 9px; margin-bottom: 4px;">
                            <strong>{{ $category->name }}</strong>
                            <span style="color: #6b7280;">
                                - {{ number_format((($category->out_of_stock_items ?? 0) / ($category->total_items ?? 1)) * 100, 1) }}% out of stock
                            </span>
                        </div>
                    @endforeach
                @else
                    <div style="font-size: 9px; color: #6b7280;">All categories are performing well</div>
                @endif
            </div>

            <!-- High Value Categories -->
            <div style="border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px; background: #eff6ff;">
                <h5 style="font-size: 11px; font-weight: 600; color: #2563eb; margin-bottom: 8px;">
                    High Value Categories ({{ $highValueCategories->count() }})
                </h5>
                @if($highValueCategories->count() > 0)
                    @foreach($highValueCategories->take(5) as $category)
                        <div style="font-size: 9px; margin-bottom: 4px;">
                            <strong>{{ $category->name }}</strong>
                            <span style="color: #6b7280;">
                                - Rs. {{ number_format($category->total_value ?? 0, 0) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div style="font-size: 9px; color: #6b7280;">No high-value categories identified</div>
                @endif
            </div>
        </div>

        <!-- Most Diverse Categories -->
        @if($diverseCategories->count() > 0)
        <div style="border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; background: #f0fdf4; margin-top: 10px;">
            <h5 style="font-size: 11px; font-weight: 600; color: #059669; margin-bottom: 8px;">
                Most Diverse Categories ({{ $diverseCategories->count() }})
            </h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 6px;">
                @foreach($diverseCategories->sortByDesc('total_items')->take(6) as $category)
                    <div style="font-size: 9px;">
                        <strong>{{ $category->name }}</strong>
                        <span style="color: #6b7280;">
                            ({{ $category->total_items ?? 0 }} items)
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif

@if($categories->count() == 0)
<div style="text-align: center; padding: 40px; color: #6b7280;">
    <div style="font-size: 14px; margin-bottom: 10px;">No category data found</div>
    <div style="font-size: 12px;">Please adjust your filters and try again.</div>
</div>
@endif
@endsection
