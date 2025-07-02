@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <form id="order-form" method="POST" action="{{ route('orders.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($menuItems as $item)
            @php
                $existing = $order->items->firstWhere('menu_item_id', $item->id);
            @endphp
            <div class="flex items-center border-b py-4">
                <input type="checkbox"
                    class="item-check mr-4"
                    data-item-id="{{ $item->id }}"
                    id="item_{{ $item->id }}"
                    name="items[{{ $item->id }}][item_id]"
                    value="{{ $item->id }}"
                    @if($existing) checked @endif>
                <label for="item_{{ $item->id }}" class="flex-1">
                    <span class="font-semibold">{{ $item->name }}</span>
                    <span class="ml-2 text-gray-500">LKR {{ number_format($item->selling_price, 2) }}</span>
                </label>
                <div class="flex items-center ml-4 border border-gray-300 rounded overflow-hidden">
                    <button type="button"
                        class="qty-decrease w-10 h-10 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 text-xl flex items-center justify-center touch-manipulation"
                        data-item-id="{{ $item->id }}"
                        @if(!$existing) disabled @endif>−</button>
                    <input type="number"
                        min="1"
                        max="99"
                        value="{{ $existing ? $existing->quantity : 1 }}"
                        class="item-qty w-14 text-center border-x border-gray-300 text-sm focus:outline-none touch-manipulation"
                        data-item-id="{{ $item->id }}"
                        @if(!$existing) disabled @endif
                        @if($existing) name="items[{{ $item->id }}][quantity]" @endif>
                    <button type="button"
                        class="qty-increase w-10 h-10 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 text-xl flex items-center justify-center touch-manipulation"
                        data-item-id="{{ $item->id }}"
                        @if(!$existing) disabled @endif>+</button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700">
                Place Order
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const qtyInput = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            const plusBtn = document.querySelector('.qty-increase[data-item-id="' + itemId + '"]');
            const minusBtn = document.querySelector('.qty-decrease[data-item-id="' + itemId + '"]');
            if (this.checked) {
                qtyInput.disabled = false;
                plusBtn.disabled = false;
                minusBtn.disabled = false;
                qtyInput.setAttribute('name', 'items[' + itemId + '][quantity]');
            } else {
                qtyInput.disabled = true;
                plusBtn.disabled = true;
                minusBtn.disabled = true;
                qtyInput.removeAttribute('name');
                qtyInput.value = 1;
            }
        });
    });

    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('input', function() {
            if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                this.value = 1;
            }
        });
    });

    document.querySelectorAll('.qty-increase').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const input = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (!input.disabled) {
                input.value = parseInt(input.value) + 1;
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    document.querySelectorAll('.qty-decrease').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const input = document.querySelector('.item-qty[data-item-id="' + itemId + '"]');
            if (!input.disabled) {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('input'));
                }
            }
        });
    });
});
</script>
@endsection