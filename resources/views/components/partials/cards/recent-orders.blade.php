<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
        <button class="text-sm text-blue-600 hover:text-blue-800">View All</button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach([
                    ['id' => 'ORD-2023-001', 'product' => 'Wireless Earbuds', 'date' => 'Jun 12, 2023', 'status' => 'Shipped', 'statusColor' => 'green', 'revenue' => '$49.99', 'profit' => '$18.75'],
                    ['id' => 'ORD-2023-002', 'product' => 'Smart Watch', 'date' => 'Jun 11, 2023', 'status' => 'Processing', 'statusColor' => 'blue', 'revenue' => '$89.99', 'profit' => '$32.15'],
                    ['id' => 'ORD-2023-003', 'product' => 'Phone Case', 'date' => 'Jun 10, 2023', 'status' => 'Shipped', 'statusColor' => 'green', 'revenue' => '$19.99', 'profit' => '$8.25'],
                    ['id' => 'ORD-2023-004', 'product' => 'Bluetooth Speaker', 'date' => 'Jun 9, 2023', 'status' => 'Pending', 'statusColor' => 'yellow', 'revenue' => '$39.99', 'profit' => '$14.50'],
                    ['id' => 'ORD-2023-005', 'product' => 'Power Bank', 'date' => 'Jun 8, 2023', 'status' => 'Shipped', 'statusColor' => 'green', 'revenue' => '$29.99', 'profit' => '$11.25']
                ] as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $order['id'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order['product'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order['date'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order['statusColor'] }}-100 text-{{ $order['statusColor'] }}-800">{{ $order['status'] }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order['revenue'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ $order['profit'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>