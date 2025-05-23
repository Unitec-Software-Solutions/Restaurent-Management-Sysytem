@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Order #{{ $order->id }}</h1>
        
        <form method="POST" action="{{ route('orders.update', $order->id) }}">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @foreach($menuItems as $item)
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                @php
                                    $existingItem = $order->orderItems
                                        ->where('menu_item_id', $item->id)
                                        ->first();
                                @endphp
                                <input type="checkbox" 
                                       name="items[{{ $item->id }}][item_id]" 
                                       value="{{ $item->id }}" 
                                       id="item{{ $item->id }}" 
                                       class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                       {{ $existingItem ? 'checked' : '' }}>
                                <label for="item{{ $item->id }}" class="font-medium">
                                    {{ $item->name }}
                                </label>
                            </div>
                            <div class="ml-6 text-sm text-gray-600">
                                Rs. {{ number_format($item->selling_price, 2) }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <input type="number"
                                   min="1"
                                   value="{{ $existingItem ? $existingItem->quantity : 1 }}"
                                   class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm"
                                   name="items[{{ $item->id }}][quantity]"
                                   {{ $existingItem ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route(('orders.summary'), $order->id) }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Update Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable quantity fields when checkboxes are checked
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        const quantityInput = checkbox.closest('.border').querySelector('input[type="number"]');
        
        // Initial state
        quantityInput.disabled = !checkbox.checked;
        
        // Change listener
        checkbox.addEventListener('change', () => {
            quantityInput.disabled = !checkbox.checked;
            if (!checkbox.checked) quantityInput.value = 1;
        });
    });
});
</script>
@endsection