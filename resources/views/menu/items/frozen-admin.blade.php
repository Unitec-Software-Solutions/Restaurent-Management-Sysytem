@foreach([
    [
        'name' => 'Ice Cream', 
        'price' => 'Rs. 300', 
        'image' => 'https://images.unsplash.com/photo-1560008581-09826d1de69e'
    ],
    [
        'name' => 'Frozen Pizza', 
        'price' => 'Rs. 800', 
        'image' => 'https://images.unsplash.com/photo-1595854341625-f33ee10dbf94'
    ],
    [
        'name' => 'Frozen Vegetables', 
        'price' => 'Rs. 400', 
        'image' => 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716'
    ],
    [
        'name' => 'Frozen Berries', 
        'price' => 'Rs. 500', 
        'image' => 'https://images.unsplash.com/photo-1425934398893-310a009a77f9'
    ]
] as $item)
<div class="menu-item bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
    <div class="h-40 overflow-hidden">
        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
    </div>
    <div class="p-4">
        <h4 class="font-semibold text-lg">{{ $item['name'] }}</h4>
        <p class="text-gray-600 mt-1">{{ $item['price'] }}</p>
        <div class="mt-3 flex gap-2">
            <button class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    </div>
</div>
@endforeach 