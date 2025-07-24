<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
                                             <td class="text-right">{{ $reportData['srns']->sum(function($srn) { return $srn->items->count(); }) }}</td>
                                    <td class="text-right">{{ $reportData['srns']->sum(function($srn) { return $srn->items->sum('release_quantity'); }) }}</td>
                                    <td class="text-right">
                                        ${{ number_format($reportData['srns']->sum(function($srn) {
                                            return $srn->items->sum(function($item) {
                                                return $item->release_quantity * $item->unit_price;
                                            });
                                        }), 2) }}round-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }

        /* Print Toolbar */
        .print-toolbar {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .toolbar-title {
            font-size: 18px;
            font-weight: bold;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-print {
            background: #3498db;
            color: white;
        }

        .btn-print:hover {
            background: #2980b9;
        }

        .btn-close {
            background: #95a5a6;
            color: white;
        }

        .btn-close:hover {
            background: #7f8c8d;
        }

        /* Report Content */
        .report-content {
            padding: 30px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 10px;
        }

        .report-date {
            font-size: 14px;
            color: #7f8c8d;
        }

        /* Filters Section */
        .filters-section {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .filter-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #bdc3c7;
        }

        .filter-label {
            font-weight: 500;
            color: #34495e;
        }

        .filter-value {
            color: #2c3e50;
        }

        /* Summary Cards */
        .summary-section {
            margin-bottom: 30px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card.success {
            background: linear-gradient(135deg, #27ae60, #229954);
        }

        .summary-card.warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .summary-card.danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .card-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-label {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        th, td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4f8;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Status badges */
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .print-toolbar {
                display: none !important;
            }

            .container {
                box-shadow: none !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .report-content {
                padding: 15px !important;
            }

            @page {
                size: A4;
                margin: 0.5in;
            }

            table {
                page-break-inside: avoid;
                font-size: 10px;
            }

            th, td {
                padding: 4px 3px;
            }

            .summary-cards {
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }

            .summary-card {
                padding: 10px;
                margin-bottom: 10px;
            }

            .card-value {
                font-size: 18px;
            }

            .filters-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                margin: 0;
                box-shadow: none;
            }

            .report-content {
                padding: 15px;
            }

            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 10px;
            }

            th, td {
                padding: 4px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Toolbar -->
        <div class="print-toolbar">
            <div class="toolbar-title">
                <i class="fas fa-print"></i> Print Preview - {{ $reportTitle }}
            </div>
            <div class="toolbar-actions">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-close" onclick="window.close()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <!-- Report Header -->
            <div class="report-header">
                <div class="company-name">Restaurant Management System</div>
                <div class="report-title">{{ $reportTitle }}</div>
                <div class="report-date">Generated on: {{ $generated_at }}</div>
                @if($dateFrom || $dateTo)
                    <div class="report-date">
                        Period: {{ $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : 'Start' }} to {{ $dateTo ? date('d/m/Y', strtotime($dateTo)) : 'End' }}
                    </div>
                @endif
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filters-title">Report Filters</div>
                <div class="filters-grid">
                    <div class="filter-item">
                        <span class="filter-label">Status:</span>
                        <span class="filter-value">{{ $filters['status'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Branch:</span>
                        <span class="filter-value">{{ $filters['branch'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Release Type:</span>
                        <span class="filter-value">{{ $filters['release_type'] }}</span>
                    </div>
                    <div class="filter-item">
                        <span class="filter-label">Date Range:</span>
                        <span class="filter-value">{{ $filters['date_range'] }}</span>
                    </div>
                </div>
            </div>

            @if($viewType === 'summary' || $viewType === 'detailed')
                <!-- Summary Section -->
                <div class="summary-section">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-chart-bar"></i> SRN Summary
                    </h3>
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="card-value">{{ $reportData['srns']->count() }}</div>
                            <div class="card-label">Total SRNs</div>
                        </div>
                        <div class="summary-card success">
                            <div class="card-value">{{ $reportData['srns']->where('status', 'completed')->count() }}</div>
                            <div class="card-label">Completed</div>
                        </div>
                        <div class="summary-card warning">
                            <div class="card-value">{{ $reportData['srns']->where('status', 'pending')->count() }}</div>
                            <div class="card-label">Pending</div>
                        </div>
                        <div class="summary-card info">
                            <div class="card-value">{{ $reportData['srns']->where('release_type', 'sale')->count() }}</div>
                            <div class="card-label">Sales</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($viewType === 'detailed')
                <!-- Detailed SRN Report -->
                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-list-alt"></i> Detailed SRN Report
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>SRN No.</th>
                                <th>Date</th>
                                <th>Branch</th>
                                <th>Release Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Total Items</th>
                                <th class="text-right">Total Qty</th>
                                <th class="text-right">Total Value</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportData['srns'] as $srn)
                                <tr>
                                    <td>{{ $srn->srn_number ?? 'N/A' }}</td>
                                    <td>{{ $srn->release_date ? date('d/m/Y', strtotime($srn->release_date)) : 'N/A' }}</td>
                                    <td>{{ $srn->branch->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($srn->release_type ?? 'N/A') }}</td>
                                    <td class="text-center">
                                        @if($srn->status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($srn->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-info">{{ ucfirst($srn->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $srn->items->count() ?? 0 }}</td>
                                    <td class="text-right">{{ $srn->items->sum('release_quantity') ?? 0 }}</td>
                                    <td class="text-right">${{ number_format($srn->items->sum(function($item) { return $item->release_quantity * $item->unit_price; }) ?? 0, 2) }}</td>
                                    <td>{{ $srn->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                        <i class="fas fa-inbox"></i> No SRN data available for the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($reportData['srns']->isNotEmpty())
                            <tfoot>
                                <tr style="background-color: #34495e; color: white; font-weight: bold;">
                                    <td colspan="5" class="text-right">Totals:</td>
                                    <td class="text-right">{{ $reportData['srns']->sum(function($srn) { return $srn->items->count(); }) }}</td>
                                    <td class="text-right">{{ $reportData['srns']->sum(function($srn) { return $srn->items->sum('quantity'); }) }}</td>
                                    <td class="text-right">
                                        ${{ number_format($reportData['srns']->sum(function($srn) {
                                            return $srn->items->sum(function($item) {
                                                return $item->quantity * $item->unit_price;
                                            });
                                        }), 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @elseif($viewType === 'summary')
                <!-- Summary Table -->
                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: #2c3e50;">
                        <i class="fas fa-table"></i> SRN Summary by Release Type
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Release Type</th>
                                <th class="text-right">Total SRNs</th>
                                <th class="text-right">Completed</th>
                                <th class="text-right">Pending</th>
                                <th class="text-right">Total Quantity</th>
                                <th class="text-right">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $typeStats = $reportData['srns']->groupBy('release_type');
                            @endphp
                            @forelse($typeStats as $releaseType => $srns)
                                <tr>
                                    <td>{{ ucfirst($releaseType ?: 'Unknown') }}</td>
                                    <td class="text-right">{{ $srns->count() }}</td>
                                    <td class="text-right">{{ $srns->where('status', 'completed')->count() }}</td>
                                    <td class="text-right">{{ $srns->where('status', 'pending')->count() }}</td>
                                    <td class="text-right">{{ $srns->sum(function($srn) { return $srn->items->sum('release_quantity'); }) }}</td>
                                    <td class="text-right">
                                        ${{ number_format($srns->sum(function($srn) {
                                            return $srn->items->sum(function($item) {
                                                return $item->release_quantity * $item->unit_price;
                                            });
                                        }), 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 20px; color: #7f8c8d;">
                                        <i class="fas fa-inbox"></i> No SRN data available.
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
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #34495e; text-align: center; color: #7f8c8d; font-size: 12px;">
                <p>This report was automatically generated by the Restaurant Management System</p>
                <p>Generated on {{ $generated_at }}</p>
            </div>
        </div>
    </div>
</body>
</html>
