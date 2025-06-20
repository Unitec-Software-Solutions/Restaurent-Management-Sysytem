@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Welcome back to your restaurant management system</p>
    </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Live Stats Cards -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Organizations</h3>
                <p class="text-2xl font-bold mt-2">{{ \App\Models\Organization::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">After seeding</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Branches</h3>
                <p class="text-2xl font-bold mt-2">{{ \App\Models\Branch::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">Across all organizations</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Orders</h3>
                <p class="text-2xl font-bold mt-2">{{ \App\Models\Order::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">All time</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Total Customers</h3>
                <p class="text-2xl font-bold mt-2">{{ \App\Models\Customer::count() }}</p>
                <p class="text-xs text-gray-400 mt-1">Registered users</p>
            </div>
        </div>

        {{-- GRN Payment Status (if available) --}}
        @if(isset($grnPaymentStatusCounts) && !empty($grnPaymentStatusCounts))
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">GRN Payment Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($grnPaymentStatusCounts as $status => $count)
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-500">{{ ucfirst($status) }}</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $count }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h3>
                <div class="space-y-3">
                    @forelse(\App\Models\Order::with(['branch'])->latest()->take(5)->get() as $order)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Order #{{ $order->id }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $order->customer_name ?? 'Guest Customer' }} • 
                                    {{ $order->branch?->name ?? 'No Branch' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-2xl mb-2">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <p class="text-gray-500">No orders found</p>
                            <p class="text-xs text-gray-400 mt-1">Orders will appear here after creation</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reservations</h3>
                <div class="space-y-3">
                    @forelse(\App\Models\Reservation::with(['branch'])->latest()->take(5)->get() as $reservation)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $reservation->name ?? 'Unknown Customer' }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $reservation->branch?->name ?? 'No Branch' }} • 
                                    {{ $reservation->phone ?? 'No Phone' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ $reservation->date?->format('M d, Y') ?? 'No date' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($reservation->status ?? 'pending') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-2xl mb-2">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <p class="text-gray-500">No reservations found</p>
                            <p class="text-xs text-gray-400 mt-1">Reservations will appear here after creation</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Inventory Items</h3>
                <p class="text-2xl font-bold mt-2">189</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium">Registered Customers</h3>
                <p class="text-2xl font-bold mt-2">432</p>
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