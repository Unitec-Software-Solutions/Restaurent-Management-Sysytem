@extends('layouts.app')
@section('content')
<h2>Orders for Reservation #{{ $reservationId }}</h2>
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

<a href="{{ route('orders.create', ['reservation_id' => $reservationId]) }}" class="btn btn-primary mb-3">
    Place Another Order
</a>

<table class="table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Type</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->customer_name }}</td>
            <td>{{ $order->order_type }}</td>
            <td>{{ $order->total }}</td>
            <td>
                <a href="{{ route('orders.edit', $order->id) }}?reservation_id={{ $reservationId }}" class="btn btn-sm btn-warning">Update</a>
                <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this order?')">Delete</button>
                </form>
            </td>
        </tr>
        <tr>
            <td>Subtotal</td>
            <td>{{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td>{{ number_format($order->tax, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td>{{ number_format($order->discount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>{{ number_format($order->total, 2) }}</strong></td>
        </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('reservations.payment', $reservationId) }}" class="btn btn-success mt-3">
    Proceed to Payment
</a>
@endsection