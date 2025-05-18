@extends('layouts.admin')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <!-- Header -->
        <h2>Inventory Dashboard</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- KPI Widgets -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-box-open fa-2x"></i>
                        <h5>Total Items</h5>
                    </div>
                    <h2 class="mb-0">{{ $totalItems }}</h2>
                    <small class="text-white-50">+{{ $newItemsToday }} from today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-dollar-sign fa-2x"></i>
                        <h5>Total Stock Value</h5>
                    </div>
                    <h2 class="mb-0">Rs. {{ number_format($totalStockValue, 2) }}</h2>
                    <small class="text-dark-50">+{{ $stockValueChange }} from yesterday</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-shopping-cart fa-2x"></i>
                        <h5>Purchase Orders</h5>
                    </div>
                    <h2 class="mb-0">${{ number_format($purchaseOrdersTotal, 2) }}</h2>
                    <small class="text-white-50">+{{ $purchaseOrdersCount }} from yesterday</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-chart-line fa-2x"></i>
                        <h5>Sales Orders</h5>
                    </div>
                    <h2 class="mb-0">${{ number_format($salesOrdersTotal, 2) }}</h2>
                    <small class="text-white-50">+{{ $salesOrdersCount }} from yesterday</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Table -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Product Details</h5>
                    <button class="btn btn-primary btn-sm">View All</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockItems as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ ItemTransaction::stockOnHand($item->id) }}</td>
                                    <td>
                                        @if (ItemTransaction::stockOnHand($item->id) <= $item->reorder_level)
                                            <span class="badge bg-warning">Warning</span>
                                        @else
                                            <span class="badge bg-success">OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Selling Items -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Top Selling Items</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Quantity Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topSellingItems as $item)
                                <tr>
                                    <td>{{ $item->category->name ?? '-' }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->quantity_sold }}</td>
                                    <td>${{ number_format($item->revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Order and Sales Order Cards -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Purchase Order</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="mb-0">{{ $purchaseOrderQuantity }}</h2>
                            <small>Quantity Ordered</small>
                        </div>
                        <div class="col-md-6">
                            <h2 class="mb-0">Rs. {{ number_format($purchaseOrderTotalCost, 2) }}</h2>
                            <small>Total Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Sales Order</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Stock Info</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesOrders as $order)
                                <tr>
                                    <td>{{ $order->category->name ?? '-' }}</td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>
                                        @if ($order->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif ($order->status === 'shipped')
                                            <span class="badge bg-info">Shipped</span>
                                        @elseif ($order->status === 'delivered')
                                            <span class="badge bg-success">Delivered</span>
                                        @else
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection