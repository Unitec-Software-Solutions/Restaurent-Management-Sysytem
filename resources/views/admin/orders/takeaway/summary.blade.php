<!-- resources/views/orders/takeaway/summary.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Order Summary</h2>
        </div>
        
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4>Order Details</h4>
                    <p><strong>Order ID:</strong> {{ $order->takeaway_id }}</p>
                    <p><strong>Branch:</strong> {{ $order->branch->name }}</p>
                    <p><strong>Pickup Time:</strong> 
                        @if($order->order_time instanceof \Carbon\Carbon)
                            {{ $order->order_time->format('M j, Y H:i') }}
                        @elseif(!empty($order->order_time))
                            {{ \Carbon\Carbon::parse($order->order_time)->format('M j, Y H:i') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h4>Customer Details</h4>
                    <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                </div>
            </div>

            <h4 class="mb-3">Order Items</h4>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->menuItem->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>LKR {{ number_format($item->unit_price, 2) }}</td>
                        <td>LKR {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Subtotal</th>
                        <td>LKR {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="3">Tax (10%)</th>
                        <td>LKR {{ number_format($order->tax, 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="3">Total</th>
                        <td>LKR {{ number_format($order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <!-- Update Order -->
                <a href="{{ route('orders.takeaway.edit', $order->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i> Update Order
                </a>
                <!-- Add Another Order -->
                <a href="{{ route('orders.takeaway.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add Another Order
                </a>
                <!-- Delete Order -->
                <form action="{{ route('orders.takeaway.destroy', $order->id) }}" method="POST" class="d-inline ms-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i> Delete Order
                    </button>
                </form>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.orders.takeaway.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Takeaway Orders
                </a>
            </div>
        </div>
    </div>
</div>
@endsection