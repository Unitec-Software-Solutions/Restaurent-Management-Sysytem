<?php
@extends('layouts.app')

@section('title', 'Payment or Repeat Order')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id ?? 'N/A' }}</h1>
                    <p class="text-gray-600 mt-1">Choose to proceed with payment or repeat this order</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        {{ ($order->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ ($order->status ?? '') === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ ($order->status ?? '') === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($order->status ?? 'Unknown') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
            
            @if(isset($order->orderItems) && $order->orderItems->count() > 0)
                <div class="space-y-3">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">
                                    {{ $item->menuItem->name ?? 'Unknown Item' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Quantity: {{ $item->quantity ?? 0 }} × 
                                    ${{ number_format($item->price ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900">
                                    ${{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-xl font-bold text-gray-900">
                            ${{ number_format($order->total_amount ?? 0, 2) }}
                        </span>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No items in this order</h3>
                    <p class="text-gray-500">This order appears to be empty.</p>
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">What would you like to do?</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Proceed to Payment -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
                    <div class="text-center">
                        <div class="text-indigo-600 text-3xl mb-3">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Proceed to Payment</h3>
                        <p class="text-gray-600 mb-4">Complete this order by proceeding to payment</p>
                        
                        <form action="{{ route('orders.payment', $order->id ?? 0) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i class="fas fa-arrow-right mr-2"></i>
                                Proceed to Payment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Repeat Order -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-green-300 transition-colors">
                    <div class="text-center">
                        <div class="text-green-600 text-3xl mb-3">
                            <i class="fas fa-redo"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Repeat Order</h3>
                        <p class="text-gray-600 mb-4">Create a new order with the same items</p>
                        
                        <form action="{{ route('orders.repeat', $order->id ?? 0) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Repeat Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6 text-center">
                <a href="{{ route('orders.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Error Display -->
        @if(isset($stockErrors) && !empty($stockErrors))
            <div class="bg-red-50 text-red-700 p-6 rounded-lg mt-6">
                <h3 class="font-medium mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Stock Issues Detected
                </h3>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($stockErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-6 rounded-lg mt-6">
                <h3 class="font-medium mb-2">Validation Errors</h3>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 text-green-700 p-6 rounded-lg mt-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Add any necessary JavaScript for enhanced interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states to buttons
    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        });
    });
});
</script>
@endsection