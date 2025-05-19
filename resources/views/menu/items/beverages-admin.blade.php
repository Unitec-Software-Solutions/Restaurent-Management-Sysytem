@foreach([
    [
        'name' => 'Cappuccino', 
        'price' => 'Rs. 350', 
        'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5'
    ],
    [
        'name' => 'Iced Tea', 
        'price' => 'Rs. 200', 
        'image' => 'https://images.unsplash.com/photo-1551029506-0807df4e2031'
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