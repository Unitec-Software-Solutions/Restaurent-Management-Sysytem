@extends('layouts.admin')
@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Order Details</h1>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">Admin Panel</span>
                <i class="fas fa-user-shield text-blue-500"></i>
            </div>
        </div>

        <!-- Main Order Card -->
        <div class="bg-white order-card rounded-xl p-6 mb-8">
            <!-- Order Summary -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Order Summary</h2>
                    <span class="status-badge {{ $order->isSubmitted() ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        <i class="fas {{ $order->isSubmitted() ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                        {{ $order->isSubmitted() ? 'Submitted' : 'Active' }}
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-hashtag text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Order ID:</span> #{{ $order->id }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-user text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Customer:</span> {{ $order->customer_name }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Phone:</span> {{ $order->customer_phone }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Date:</span> {{ $order->created_at->format('F j, Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-boxes text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Total Items:</span> {{ $order->items->sum('quantity') }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Order Time:</span> {{ $order->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-tag text-blue-500 mr-2 w-5"></i>
                            <p><span class="font-semibold">Type:</span> {{ request()->routeIs('orders.takeaway.*') ? 'Takeaway' : 'Dine-in' }}</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-money-bill-wave text-blue-500 mr-2 w-5"></i>
                            <p class="text-xl font-bold">Total: LKR {{ number_format($order->total, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                    Order Items
                </h3>
                
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex justify-between items-center p-3 item-card rounded-lg border">
                        <div class="flex items-center">
                            @if($item->menuItem->image)
                                <img src="{{ asset('storage/'.$item->menuItem->image) }}" alt="{{ $item->menuItem->name }}" class="w-12 h-12 rounded-md object-cover mr-3">
                            @else
                                <img src="https://via.placeholder.com/50" alt="Item" class="w-12 h-12 rounded-md object-cover mr-3">
                            @endif
                            <div>
                                <p class="font-medium">{{ $item->menuItem->name }}</p>
                                @if($item->special_requests)
                                    <p class="text-sm text-gray-500">{{ $item->special_requests }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">LKR {{ number_format($item->total_price, 2) }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($order->special_instructions)
                <!-- Order Notes -->
                <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                        Special Instructions
                    </h4>
                    <p class="text-yellow-700">{{ $order->special_instructions }}</p>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row md:justify-between items-center gap-4">
                @if($editable)
                <div class="space-x-4 flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <!-- Update Order -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.edit' : 'orders.edit', $order) }}" class="btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-1"></i> Update Order
                    </a>
                    
                    <!-- Submit Order -->
                    <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.submit' : 'orders.submit', $order) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-paper-plane mr-1"></i> Submit Order
                        </button>
                    </form>
                    
                    <!-- Add Another Order -->
                    <a href="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.takeaway.create' : 'orders.create') }}" class="btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus-circle mr-1"></i> Add Another
                    </a>
                </div>
                @else
                <p class="text-green-500 font-semibold">Order Submitted</p>
                @endif
                
                <!-- Delete Button -->
                <form action="{{ route(request()->routeIs('orders.takeaway.*') ? 'orders.destroy' : 'orders.destroy', $order) }}" method="POST" class="w-full md:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg w-full" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                        <i class="fas fa-trash-alt mr-1"></i> Delete Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 mt-6">
            @if(isset($reservation) && $reservation)
            <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-1"></i> Back to Reservation
            </a>
            @endif
            
            <a href="{{ route('admin.reservations.index') }}" class="btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-list mr-1"></i> Reservation List
            </a>
            
            <a href="{{ route('admin.orders.index') }}" class="btn bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-utensils mr-1"></i> All Orders
            </a>
        </div>
    </div>
</div>

<style>
    .order-card {
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    .item-card {
        transition: all 0.2s ease;
    }
    .item-card:hover {
        background-color: #f8fafc;
    }
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .btn {
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .btn:hover {
        transform: translateY(-1px);
    }
</style>
@endsection