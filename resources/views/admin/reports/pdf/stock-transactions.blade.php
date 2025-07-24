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
        @if(isset($filters['transaction_type']) && $filters['transaction_type'])
            <div class="filter-item">
                <span class="filter-label">Transaction Type:</span> {{ ucfirst($filters['transaction_type']) }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalTransactions = $transactions->count();
    $inTransactions = $transactions->where('transaction_type', 'in')->count();
    $outTransactions = $transactions->where('transaction_type', 'out')->count();
    $totalInQuantity = $transactions->where('transaction_type', 'in')->sum('quantity');
    $totalOutQuantity = $transactions->where('transaction_type', 'out')->sum('quantity');
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalTransactions) }}</div>
            <div class="summary-label">Total Transactions</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($inTransactions) }}</div>
            <div class="summary-label">Stock In</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($outTransactions) }}</div>
            <div class="summary-label">Stock Out</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($totalInQuantity, 2) }}</div>
            <div class="summary-label">Total In Qty</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">{{ number_format($totalOutQuantity, 2) }}</div>
            <div class="summary-label">Total Out Qty</div>
        </div>
    </div>
</div>

<!-- Transactions Data Table -->
<div class="avoid-break">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 12%">Date</th>
                <th style="width: 15%">Item Code</th>
                <th style="width: 18%">Item Name</th>
                <th style="width: 12%">Branch</th>
                <th style="width: 8%">Type</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 10%">Unit Price</th>
                <th style="width: 9%">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-sm">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M j, Y') }}</td>
                <td class="font-semibold">{{ $transaction->item->item_code ?? 'N/A' }}</td>
                <td>{{ $transaction->item->name ?? 'N/A' }}</td>
                <td class="text-sm">{{ $transaction->branch->name ?? 'N/A' }}</td>
                <td class="text-center">
                    @php
                        $typeClass = $transaction->transaction_type === 'in' ? 'text-success' : 'text-danger';
                        $typeIcon = $transaction->transaction_type === 'in' ? '↑' : '↓';
                    @endphp
                    <span class="{{ $typeClass }}">
                        {{ $typeIcon }} {{ ucfirst($transaction->transaction_type) }}
                    </span>
                </td>
                <td class="text-center font-semibold">
                    {{ number_format($transaction->quantity, 2) }}
                    @if($transaction->item && $transaction->item->unit)
                        <br><span class="text-xs">{{ $transaction->item->unit }}</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($transaction->unit_price)
                        Rs. {{ number_format($transaction->unit_price, 2) }}
                    @else
                        <span class="text-xs">N/A</span>
                    @endif
                </td>
                <td class="text-right font-semibold">
                    @if($transaction->unit_price)
                        Rs. {{ number_format($transaction->quantity * $transaction->unit_price, 2) }}
                    @else
                        <span class="text-xs">N/A</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Additional Analysis (if detailed view) -->
@if($viewType === 'detailed' && $transactions->count() > 0)
<div class="page-break"></div>

<div class="mt-4">
    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #2563eb;">Transaction Analysis</h3>

    <!-- Daily Transaction Summary -->
    @php
        $dailyTransactions = $transactions->groupBy(function($transaction) {
            return \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d');
        })->map(function($dayTransactions, $date) {
            return [
                'date' => $date,
                'total_transactions' => $dayTransactions->count(),
                'in_transactions' => $dayTransactions->where('transaction_type', 'in')->count(),
                'out_transactions' => $dayTransactions->where('transaction_type', 'out')->count(),
                'total_in_qty' => $dayTransactions->where('transaction_type', 'in')->sum('quantity'),
                'total_out_qty' => $dayTransactions->where('transaction_type', 'out')->sum('quantity'),
            ];
        })->take(15);
    @endphp

    @if($dailyTransactions->count() > 0)
    <div class="avoid-break">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Daily Transaction Summary</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Stock In</th>
                    <th class="text-center">Stock Out</th>
                    <th class="text-center">In Quantity</th>
                    <th class="text-center">Out Quantity</th>
                    <th class="text-center">Net Movement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyTransactions as $daily)
                <tr>
                    <td class="font-semibold">{{ \Carbon\Carbon::parse($daily['date'])->format('M j, Y') }}</td>
                    <td class="text-center">{{ $daily['total_transactions'] }}</td>
                    <td class="text-center text-success">{{ $daily['in_transactions'] }}</td>
                    <td class="text-center text-danger">{{ $daily['out_transactions'] }}</td>
                    <td class="text-center text-success">{{ number_format($daily['total_in_qty'], 2) }}</td>
                    <td class="text-center text-danger">{{ number_format($daily['total_out_qty'], 2) }}</td>
                    <td class="text-center font-semibold">
                        @php
                            $netMovement = $daily['total_in_qty'] - $daily['total_out_qty'];
                            $netClass = $netMovement >= 0 ? 'text-success' : 'text-danger';
                        @endphp
                        <span class="{{ $netClass }}">
                            {{ $netMovement >= 0 ? '+' : '' }}{{ number_format($netMovement, 2) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Items by Transaction Volume -->
    @php
        $topItems = $transactions->groupBy('item_id')->map(function($itemTransactions, $itemId) {
            $item = $itemTransactions->first()->item;
            return [
                'item' => $item,
                'total_transactions' => $itemTransactions->count(),
                'total_in_qty' => $itemTransactions->where('transaction_type', 'in')->sum('quantity'),
                'total_out_qty' => $itemTransactions->where('transaction_type', 'out')->sum('quantity'),
                'net_movement' => $itemTransactions->where('transaction_type', 'in')->sum('quantity') -
                                $itemTransactions->where('transaction_type', 'out')->sum('quantity'),
            ];
        })->sortByDesc('total_transactions')->take(15);
    @endphp

    @if($topItems->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Most Active Items</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Code</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-center">In Quantity</th>
                    <th class="text-center">Out Quantity</th>
                    <th class="text-center">Net Movement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topItems as $itemData)
                <tr>
                    <td class="font-semibold">{{ $itemData['item']->name ?? 'N/A' }}</td>
                    <td class="text-sm">{{ $itemData['item']->item_code ?? 'N/A' }}</td>
                    <td class="text-center">{{ $itemData['total_transactions'] }}</td>
                    <td class="text-center text-success">{{ number_format($itemData['total_in_qty'], 2) }}</td>
                    <td class="text-center text-danger">{{ number_format($itemData['total_out_qty'], 2) }}</td>
                    <td class="text-center font-semibold">
                        @php
                            $netClass = $itemData['net_movement'] >= 0 ? 'text-success' : 'text-danger';
                        @endphp
                        <span class="{{ $netClass }}">
                            {{ $itemData['net_movement'] >= 0 ? '+' : '' }}{{ number_format($itemData['net_movement'], 2) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endif

@if($transactions->count() == 0)
<div style="text-align: center; padding: 40px; color: #6b7280;">
    <div style="font-size: 14px; margin-bottom: 10px;">No transaction data found</div>
    <div style="font-size: 12px;">Please adjust your filters and try again.</div>
</div>
@endif
@endsection
