@foreach([
    [
        'name' => 'Cesar Salad', 
        'price' => 'Rs. 450', 
        'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1'
    ],
    [
        'name' => 'Greek Yogurt', 
        'price' => 'Rs. 350', 
        'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150'
    ],
    [
        'name' => 'Milk', 
        'price' => 'Rs. 200', 
        'image' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b'
    ],
    [
        'name' => 'Cheese Plate', 
        'price' => 'Rs. 550', 
        'image' => 'https://unsplash.com/photos/white-and-brown-bread-on-white-ceramic-plate-4HWhg7KZ8wc'
    ]
] as $item)
<div class="menu-item bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
    <div class="h-40 overflow-hidden">
        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
    </div>
    <div class="p-4">
        <h4 class="font-semibold text-lg">{{ $item['name'] }}</h4>
        <p class="text-gray-600 mt-1">{{ $item['price'] }}</p>
    </div>
</div>
@endforeach 