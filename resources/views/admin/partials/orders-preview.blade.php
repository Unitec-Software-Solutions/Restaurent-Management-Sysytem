{{-- filepath: resources/views/admin/partials/orders-preview.blade.php --}}

@if($recentOrders->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2">Order #</th>
                    <th class="text-left py-2">Customer</th>
                    <th class="text-left py-2">Type</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Total</th>
                    <th class="text-left py-2">Branch</th>
                    <th class="text-left py-2">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                <tr class="border-b border-gray-100 hover:bg-gray-100 transition-colors">
                    <td class="py-2 font-medium text-blue-600">{{ $order->order_number }}</td>
                    <td class="py-2">{{ $order->customer_name ?? 'N/A' }}</td>
                    <td class="py-2">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $order->order_type === 'delivery' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->order_type === 'dine_in' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->order_type === 'takeaway' ? 'bg-purple-100 text-purple-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                        </span>
                    </td>
                    <td class="py-2">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="py-2 text-right font-medium">{{ $order->currency_symbol }}{{ $order->formatted_total }}</td>
                    <td class="py-2 text-xs text-gray-600">{{ $order->branch?->name ?? 'N/A' }}</td>
                    <td class="py-2 text-xs text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-inbox text-3xl mb-2"></i>
        <p>No orders found. Create some test orders to see them here.</p>
        <button onclick="testOrderCreation(5, 'mixed')" 
                class="mt-3 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-plus mr-1"></i>
            Create Sample Orders
        </button>
    </div>
@endif