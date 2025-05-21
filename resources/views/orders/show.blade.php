@extends('layouts.app')
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    <div class="alert alert-info mt-3">
        <strong>What would you like to do next?</strong>
        <div class="mt-2 d-flex gap-2">
            <a href="{{ route('orders.create', ['reservation_id' => $reservationId]) }}" class="btn btn-primary">
                Place Another Order
            </a>
            <a href="{{ route('reservations.payment', $reservationId) }}" class="btn btn-success">
                Proceed to Payment
            </a>
        </div>
    </div>
@endif
<h2>Order #{{ $order->id }}</h2>
<p>Name: {{ $order->customer_name }}</p>
<p>Phone: {{ $order->customer_phone }}</p>
<p>Order Type: {{ $order->order_type }}</p>
<table>
    <thead>
        <tr>
            <th>Menu Item</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->orderItems as $item)
        <tr>
            <td>{{ $item->menuItem->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit_price }}</td>
            <td>{{ $item->total_price }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<p>Subtotal: {{ $order->subtotal }}</p>
<p>Tax: {{ $order->tax }}</p>
<p>Service Charge: {{ $order->service_charge }}</p>
<p>Discount: {{ $order->discount }}</p>
<p><strong>Total: {{ $order->total }}</strong></p>
<a href="{{ route('orders.edit', $order->id) }}">Edit Order</a>
<a href="{{ route('orders.cancel', $order->id) }}">Cancel Order</a>
<a href="{{ route('orders.proceedToPayment', $order->id) }}">Proceed to Payment</a>
@endsection