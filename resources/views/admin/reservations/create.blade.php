{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')
@section('content')
<h2>Place New Order</h2>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    <input type="hidden" name="reservation_id" value="{{ $reservationId }}">
    <div>
        <label>Name:</label>
        <input type="text" name="customer_name" required>
    </div>
    <div>
        <label>Phone:</label>
        <input type="text" name="customer_phone" required>
    </div>
    <div>
        <label>Order Type:</label>
        <select name="order_type" required>
            <option value="dine_in_online_scheduled">Dine-in Online Scheduled</option>
            <option value="dine_in_walk_in_scheduled">Dine-in Walk-in Scheduled</option>
            <!-- Add other types as needed -->
        </select>
    </div>
    <div>
        <label>Menu Items:</label>
        @foreach($menuItems as $item)
            <div>
                <input type="checkbox" name="items[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                {{ $item->name }} - Rs. {{ number_format($item->selling_price, 2) }}
                <input type="number" name="items[{{ $item->id }}][quantity]" min="1" value="1">
            </div>
        @endforeach
    </div>
    <button type="submit">Place Order</button>
</form>
@endsection