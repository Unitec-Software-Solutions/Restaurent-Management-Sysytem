@extends('layouts.guest')

@section('title', 'Order Not Found')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Error Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Not Found</h1>
            <p class="text-gray-600">We couldn't find the order you're looking for.</p>
        </div>

        <!-- Error Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Possible Reasons</h2>
            <ul class="space-y-3 text-gray-600">
                <li class="flex items-start">
                    <i class="fas fa-circle text-xs text-gray-400 mt-2 mr-3"></i>
                    <span>The order number might be incorrect or invalid</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-circle text-xs text-gray-400 mt-2 mr-3"></i>
                    <span>The order might be from a different branch</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-circle text-xs text-gray-400 mt-2 mr-3"></i>
                    <span>The order might have been cancelled or completed long ago</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-circle text-xs text-gray-400 mt-2 mr-3"></i>
                    <span>There might be a technical issue with our system</span>
                </li>
            </ul>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Search for Your Order</h3>
            <form method="GET" action="{{ route('guest.order.track') }}" class="space-y-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Order Number or Phone Number
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="order_number" 
                               name="order_number" 
                               value="{{ request('order_number') }}"
                               placeholder="Enter order number or phone number"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Try entering your order number (e.g., ORD123) or the phone number used for the order
                    </p>
                </div>
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Search Order
                </button>
            </form>
        </div>

        <!-- Help Section -->
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Need Help Finding Your Order?</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p>
                    <strong>Recent Order:</strong> Check your email or SMS for the order confirmation with the order number.
                </p>
                <p>
                    <strong>Receipt:</strong> Look for the order number on your receipt if you have one.
                </p>
                <p>
                    <strong>Contact Info:</strong> Use the same phone number you provided when placing the order.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('guest.menu.branch-selection') }}" 
               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-utensils mr-2"></i>
                Place New Order
            </a>
            <a href="{{ url('/') }}" 
               class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg flex items-center justify-center font-medium transition-colors">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>

        <!-- Contact Support -->
        <div class="text-center mt-8 p-6 bg-gray-100 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Still Need Help?</h3>
            <p class="text-sm text-gray-600 mb-4">
                Contact our support team if you continue to have issues finding your order.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="tel:+1234567890" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                    <i class="fas fa-phone mr-2"></i>
                    Call Support
                </a>
                <a href="mailto:support@restaurant.com" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                    <i class="fas fa-envelope mr-2"></i>
                    Email Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-focus on the search input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('order_number');
        if (searchInput) {
            searchInput.focus();
        }
    });

    // Format order number input
    document.getElementById('order_number').addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase();
        // Remove any non-alphanumeric characters except spaces and dashes
        value = value.replace(/[^A-Z0-9\s-]/g, '');
        e.target.value = value;
    });
</script>
@endpush
