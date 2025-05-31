@extends('layouts.admin')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Order Management Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Reservation Orders Card -->
                <a href="{{ route('admin.orders.reservations.index') }}" class="block">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 hover:bg-blue-100 transition-colors">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-800">Reservation Orders</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Manage orders associated with table reservations</p>
                        <div class="text-blue-600 font-medium">View Reservation Orders →</div>
                    </div>
                </a>
                <!-- Takeaway Orders Card -->
                <a href="{{ route('admin.orders.takeaway.index') }}" class="block">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 hover:bg-green-100 transition-colors">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-800">Takeaway Orders</h2>
                        </div>
                        <p class="text-gray-600 mb-4">Manage orders for pickup or delivery</p>
                        <div class="text-green-600 font-medium">View Takeaway Orders →</div>
                    </div>
                </a>
            </div>
            <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <!-- Reservation order creation requires a reservation context, so provide instructions instead of a direct link -->
                    <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded cursor-not-allowed" title="Select a reservation to create an order">
                        Create Reservation Order (select a reservation first)
                    </span>
                    <a href="{{ route('admin.orders.takeaway.create') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Create Takeaway Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection