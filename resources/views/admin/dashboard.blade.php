@extends('layouts.admin')
@section('header-title', 'RMS Dashboard')
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
            
            <!-- Menu Items Overview -->
            <div class="bg-gradient-to-br from-orange-50 to-blue-50 rounded-lg shadow p-6">
                <h3 class="text-gray-700 text-sm font-medium flex items-center">
                    <i class="fas fa-utensils mr-2 text-orange-600"></i>Menu Items
                </h3>
                @php
                    try {
                        $totalMenuItems = \App\Models\MenuItem::count();
                        $kotItems = \App\Models\MenuItem::where('type', \App\Models\MenuItem::TYPE_KOT)->count();
                        $buySellItems = \App\Models\MenuItem::where('type', \App\Models\MenuItem::TYPE_BUY_SELL)->count();
                    } catch (\Exception $e) {
                        $totalMenuItems = 0;
                        $kotItems = 0;
                        $buySellItems = 0;
                    }
                @endphp
                <p class="text-2xl font-bold mt-2 text-gray-900">{{ $totalMenuItems }}</p>
                <div class="mt-2 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-orange-600">🍳 KOT Recipes</span>
                        <span class="font-medium">{{ $kotItems }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-blue-600">📦 Buy & Sell</span>
                        <span class="font-medium">{{ $buySellItems }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions for Menu Management -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-rocket mr-2 text-indigo-600"></i>Quick Actions
            </h3>
            
            <!-- Explanation Banner -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Two-Tier Menu System</p>
                        <p><strong>Step 1:</strong> Create individual menu items (food/drinks) → <strong>Step 2:</strong> Group them into dining menus (breakfast, lunch, etc.)</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Menu Items Section -->
                <div class="col-span-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-utensils text-orange-500 mr-2"></i>Step 1: Menu Items (Individual Items)
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('admin.menu-items.enhanced.index') }}" 
                           class="p-3 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-list text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-orange-900 text-sm">View Items</p>
                                    <p class="text-xs text-orange-700">All food & drinks</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('admin.menu-items.create') }}" 
                           class="p-3 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:from-red-100 hover:to-red-200 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-plus text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-red-900 text-sm">Add Item</p>
                                    <p class="text-xs text-red-700">Create single item</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Menu Builder Section -->
                <div class="col-span-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-book-open text-indigo-500 mr-2"></i>Step 2: Menu Builder (Group Items)
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('admin.menus.index') }}" 
                           class="p-3 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:from-indigo-100 hover:to-indigo-200 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-book text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-indigo-900 text-sm">View Menus</p>
                                    <p class="text-xs text-indigo-700">Dining menus</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('admin.menus.create') }}" 
                           class="p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-plus text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-green-900 text-sm">Create Menu</p>
                                    <p class="text-xs text-green-700">Group items</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
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


        </div>
    </div>
</div>
@endsection