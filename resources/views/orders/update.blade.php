{{-- filepath: resources/views/orders/edit.blade.php --}}
@extends('layouts.app')
@section('content')
<h2>Edit Order #{{ $order->id }}</h2>
<form method="POST" action="{{ route('orders.update', $order->id) }}">
    @csrf
    @method('PUT')
    <div>
        <label>Menu Items:</label>
        @foreach($menuItems as $item)
            <div>
                <input type="checkbox" name="items[{{ $item->id }}][menu_item_id]" value="{{ $item->id }}"
                    @if($order->orderItems->where('menu_item_id', $item->id)->first()) checked @endif>
                {{ $item->name }} - {{ $item->selling_price }}
                <input type="number" name="items[{{ $item->id }}][quantity]" min="1"
                    value="{{ $order->orderItems->where('menu_item_id', $item->id)->first()->quantity ?? 1 }}">
            </div>
        @endforeach
    </div>
    <button type="submit">Update Order</button>
</form>
@endsection