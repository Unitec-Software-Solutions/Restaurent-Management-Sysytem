<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Top Products</h2>
    <div class="space-y-4">
        @foreach([
            ['name' => 'Wireless Earbuds', 'sku' => 'WB-2023', 'revenue' => '$1,245', 'color' => 'blue'],
            ['name' => 'Smart Watch', 'sku' => 'SW-2023', 'revenue' => '$980', 'color' => 'green'],
            ['name' => 'Phone Case', 'sku' => 'PC-2023', 'revenue' => '$745', 'color' => 'purple'],
            ['name' => 'Bluetooth Speaker', 'sku' => 'BS-2023', 'revenue' => '$620', 'color' => 'yellow'],
            ['name' => 'Power Bank', 'sku' => 'PB-2023', 'revenue' => '$510', 'color' => 'red']
        ] as $product)
            <div class="flex items-center">
                <div class="w-10 h-10 bg-{{ $product['color'] }}-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-{{ $product['color'] }}-600"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-800">{{ $product['name'] }}</p>
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500">SKU: {{ $product['sku'] }}</p>
                        <p class="text-sm font-semibold text-green-600">{{ $product['revenue'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>