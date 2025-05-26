{{-- resources/views/orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">
                    @if($reservation)
                        Place Order for Reservation #{{ $reservation->id }}
                    @else
                        Create New Order
                    @endif
                </h1>
                
                @if($reservation)
                    <div class="mt-2 text-gray-600">
                        <p>
                            <span class="font-medium">Customer:</span>
                            {{ optional($reservation->customer)->name ?? 'Not Provided' }}
                        </p>
                        <p>
    <span class="font-medium">Table:</span>
    {{ optional($reservation->table)->name ?? 'Not Provided' }}
    ({{ optional($reservation->table)->capacity ?? 'N/A' }} people)
</p>
                        <p><span class="font-medium">Time:</span> {{ ($reservation->start_time) }} - {{ ($reservation->end_time) }}</p>
                    </div>
                @endif
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @unless($reservation)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                        <p>This order is not associated with any reservation.</p>
                    </div>
                @endunless

                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf
                    
                    @if($reservation)
                        <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
                        <input type="hidden" name="customer_id" value="{{ $reservation->customer_id }}">
                        <input type="hidden" name="table_id" value="{{ $reservation->table_id }}">
                    @endif

                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu Items</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($menuItems as $item)
                            <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" 
                                                   name="items[{{ $item->id }}][item_id]" 
                                                   value="{{ $item->id }}" 
                                                   id="item{{ $item->id }}" 
                                                   class="mt-1 focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded item-check"
                                                   data-item-id="{{ $item->id }}">
                                            <label for="item{{ $item->id }}" class="font-medium text-gray-700">{{ $item->name }}</label>
                                        </div>
                                        <div class="ml-6 text-sm text-gray-600">
                                            Rs. {{ number_format($item->selling_price, 2) }}
                                            @if($item->description)
                                                <p class="mt-1 text-gray-500 text-xs">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <input type="number"
                                               min="1"
                                               value="1"
                                               class="w-20 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 item-qty"
                                               data-item-id="{{ $item->id }}"
                                               disabled>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Place Order
                            </button>
                            <a href="{{ $reservation ? route('reservations.show', $reservation->id) : url()->previous() }}" 
                               class="text-gray-600 hover:text-gray-800 font-medium">
                                {{ $reservation ? 'Back to Reservation' : 'Cancel' }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (this.checked) {
                qtyInput.disabled = false;
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
            } else {
                qtyInput.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
            }
        });
    });
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        document.querySelectorAll('.item-check').forEach(function(checkbox) {
            if (!checkbox.checked) {
                checkbox.disabled = true;
            }
        });
    });
});
</script>
@endsection