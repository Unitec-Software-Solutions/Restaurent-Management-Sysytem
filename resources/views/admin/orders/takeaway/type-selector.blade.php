@extends('layouts.admin')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-green-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-green-600 to-emerald-700">
                    <div class="flex items-center">
                        <div class="bg-white/20 p-3 rounded-xl mr-4">
                            <i class="fas fa-shopping-bag text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Select Takeaway Order Type</h1>
                            <p class="text-green-100 mt-1">Choose the type of takeaway order you want to create</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Admin Information Panel -->
                    @if(auth('admin')->check())
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
                        <div class="flex items-center">
                            <i class="fas fa-user-shield text-blue-600 text-lg mr-3"></i>
                            <div>
                                <h3 class="text-blue-800 font-semibold">Admin Mode</h3>
                                <p class="text-blue-600 text-sm">
                                    You are creating an order as: {{ auth('admin')->user()->name }}
                                    @if(auth('admin')->user()->branch)
                                    | Branch: {{ auth('admin')->user()->branch->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Order Type Selection Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- Walk-in/Demand Order -->
                        <div class="group">
                            <a href="{{ route('admin.orders.takeaway.create', ['type' => 'takeaway_walk_in_demand']) }}" 
                               class="block bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200 rounded-xl p-6 hover:border-orange-400 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                                <div class="text-center">
                                    <div class="bg-orange-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-orange-600 transition-colors">
                                        <i class="fas fa-bolt text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-orange-800 mb-2">Walk-in/Immediate</h3>
                                    <p class="text-orange-600 text-sm mb-4">Customer walks in and wants order immediately</p>
                                    
                                    <div class="text-xs text-orange-500 space-y-1">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span>Immediate preparation</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-user mr-1"></i>
                                            <span>Customer present</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Phone Call Scheduled Order -->
                        <div class="group">
                            <a href="{{ route('admin.orders.takeaway.create', ['type' => 'takeaway_in_call_scheduled']) }}" 
                               class="block bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl p-6 hover:border-blue-400 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                                <div class="text-center">
                                    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-phone text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-blue-800 mb-2">Phone Order</h3>
                                    <p class="text-blue-600 text-sm mb-4">Customer called and scheduled pickup time</p>
                                    
                                    <div class="text-xs text-blue-500 space-y-1">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <span>Scheduled pickup</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-phone-volume mr-1"></i>
                                            <span>Phone call order</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Online Scheduled Order -->
                        <div class="group">
                            <a href="{{ route('admin.orders.takeaway.create', ['type' => 'takeaway_online_scheduled']) }}" 
                               class="block bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200 rounded-xl p-6 hover:border-purple-400 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                                <div class="text-center">
                                    <div class="bg-purple-500 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-600 transition-colors">
                                        <i class="fas fa-globe text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-purple-800 mb-2">Online Order</h3>
                                    <p class="text-purple-600 text-sm mb-4">Customer ordered online with scheduled pickup</p>
                                    
                                    <div class="text-xs text-purple-500 space-y-1">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-laptop mr-1"></i>
                                            <span>Online platform</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span>Pre-scheduled</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Information Panel -->
                    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                            Order Type Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <h4 class="font-semibold text-orange-700 mb-2">Walk-in/Immediate</h4>
                                <ul class="text-gray-600 space-y-1">
                                    <li>• Customer is physically present</li>
                                    <li>• Order prepared immediately</li>
                                    <li>• No advance scheduling</li>
                                    <li>• Payment on pickup</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-blue-700 mb-2">Phone Order</h4>
                                <ul class="text-gray-600 space-y-1">
                                    <li>• Customer called in advance</li>
                                    <li>• Scheduled pickup time</li>
                                    <li>• Customer details recorded</li>
                                    <li>• Payment on pickup</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-purple-700 mb-2">Online Order</h4>
                                <ul class="text-gray-600 space-y-1">
                                    <li>• Placed via online platform</li>
                                    <li>• Pre-scheduled pickup time</li>
                                    <li>• Customer account details</li>
                                    <li>• May be pre-paid</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Features Panel -->
                    @if(auth('admin')->check())
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-4 flex items-center">
                            <i class="fas fa-crown text-yellow-600 mr-2"></i>
                            Admin Features
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center text-yellow-700">
                                <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                                <span>Branch-specific stock validation</span>
                            </div>
                            <div class="flex items-center text-yellow-700">
                                <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                                <span>Default customer information pre-filling</span>
                            </div>
                            <div class="flex items-center text-yellow-700">
                                <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                                <span>Real-time menu availability</span>
                            </div>
                            <div class="flex items-center text-yellow-700">
                                <i class="fas fa-check-circle text-yellow-600 mr-2"></i>
                                <span>Order status management</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Navigation -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between items-center">
                        <a href="{{ route('admin.orders.dashboard') }}" 
                           class="text-gray-600 hover:text-gray-800 flex items-center transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Order Dashboard
                        </a>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('admin.orders.takeaway.index') }}" 
                               class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-list mr-2"></i>
                                View Existing Orders
                            </a>
                            
                            <a href="{{ route('admin.orders.index') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-th-large mr-2"></i>
                                All Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects and analytics tracking
    document.querySelectorAll('.group a').forEach(link => {
        link.addEventListener('click', function() {
            const orderType = this.href.includes('type=') ? new URL(this.href).searchParams.get('type') : 'unknown';
            console.log('Admin selecting takeaway order type:', orderType);
            
            // Optional: Add loading state
            const card = this.querySelector('.text-center');
            if (card) {
                card.style.opacity = '0.7';
                card.innerHTML += '<div class="mt-2"><i class="fas fa-spinner fa-spin text-lg"></i></div>';
            }
        });
    });
});
</script>
@endsection
