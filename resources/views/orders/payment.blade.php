{{-- filepath: resources/views/orders/payment.blade.php --}}
@extends('layouts.app')
@section('content')
<h2>Payment for Order #{{ $order->id }}</h2>
<p>Total Amount: {{ $order->total }}</p>
<form method="POST" action="{{ route('payments.process', $order->id) }}">
    @csrf
    <label>Payment Method:</label>
    <select name="payment_method" required>
        <option value="cash">Cash</option>
        <option value="card">Card</option>
        <option value="mobile">Mobile Payment</option>
        <!-- Add more as needed -->
    </select>
    <button type="submit">Pay Now</button>
</form>
@endsection