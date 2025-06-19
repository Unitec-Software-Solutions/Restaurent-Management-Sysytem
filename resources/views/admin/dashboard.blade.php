@extends('layouts.admin')

@section('content')
<div>
    <div class="p-4 rounded-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Dashboard  - Sample Page</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Stats Cards -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Orders</h3>
                <p class="text-2xl font-bold mt-2">1,234</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Active Reservations</h3>
                <p class="text-2xl font-bold mt-2">56</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Inventory Items</h3>
                <p class="text-2xl font-bold mt-2">189</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Active Orders</h3>
                <p class="text-2xl font-bold mt-2">18</p>
            </div>
        </div>
        
        <!-- test routes here  -->
        {{-- <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Test - </h2> 
            <a href="{{ route('admin.reservations.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Reservations</a>
            <a href="{{ route('admin.customers.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Customers</a>
            <a href="{{ route('admin.digital-menu.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Digital Menu</a>
            <a href="{{ route('admin.settings.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Settings</a>
            <a href="{{ route('admin.reports.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Reports</a>
            <a href="{{ route('admin.orders.index') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Orders</a> --}}

        </div>
    </div>
</div>
@endsection