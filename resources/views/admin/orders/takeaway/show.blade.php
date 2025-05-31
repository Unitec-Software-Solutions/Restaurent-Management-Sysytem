<!-- resources/views/orders/takeaway/show.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h2 class="mb-0">Order Confirmation</h2>
        </div>
        
        <div class="card-body">
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle me-2"></i> Your order has been submitted!</h4>
                <p>We'll notify you when your order is ready for pickup.</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Order ID:</strong> {{ $order->takeaway_id }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($order->status) }}</span></p>
                            <p><strong>Branch:</strong> {{ $order->branch->name }}</p>
                            <p><strong>Pickup Time:</strong> {{ $order->order_time->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Customer Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                            <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->menuItem->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>LKR {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('orders.takeaway.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Create New Order
                </a>
                <a href="#" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-print me-2"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>
</div>
@endsection