@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-12 text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Our Menu</h1>
        <div class="w-32 h-1 bg-gradient-to-r from-blue-400 to-purple-500 mx-auto rounded-full"></div>
    </div>

    <!-- Categories with Item Cards -->
    @forelse($categories as $category)
        <div class="mb-16">
            <!-- Category Header -->
            <div class="mb-10 text-center">
                <h2 class="text-3xl font-bold text-gray-800 inline-block px-6 py-2 rounded-full 
                @if($loop->iteration % 3 == 1) bg-gradient-to-r from-yellow-100 to-yellow-300 text-yellow-800
                @elseif($loop->iteration % 3 == 2) bg-gradient-to-r from-green-100 to-green-300 text-green-800
                @else bg-gradient-to-r from-red-100 to-red-300 text-red-800 @endif">
                    {{ $category->name }}
                </h2>
            </div>
            
            <!-- Item Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @forelse($category->items as $item)
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:-translate-y-2 transition-all duration-300 border-l-4 
                    @if($loop->parent->iteration % 3 == 1) border-yellow-400
                    @elseif($loop->parent->iteration % 3 == 2) border-green-400
                    @else border-red-400 @endif">
                        <!-- Item Image Placeholder with colorful overlay -->
                        <div class="h-48 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br 
                            @if($loop->parent->iteration % 3 == 1) from-yellow-100 to-yellow-300
                            @elseif($loop->parent->iteration % 3 == 2) from-green-100 to-green-300
                            @else from-red-100 to-red-300 @endif opacity-50">
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-gray-600 font-medium">Food Image</span>
                            </div>
                        </div>
                        
                        <!-- Item Details -->
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-gray-800">{{ $item->name }}</h3>
                                <span class="text-xl font-bold 
                                @if($loop->parent->iteration % 3 == 1) text-yellow-600
                                @elseif($loop->parent->iteration % 3 == 2) text-green-600
                                @else text-red-600 @endif">
                                    ${{ number_format($item->selling_price, 2) }}
                                </span>
                            </div>
                            <!-- Optional: Add description if available -->
                            <p class="text-gray-500 mt-2 italic">Delicious {{ $item->name }} with fresh ingredients</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-gray-500">
                        No items in this category
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="py-12 text-center text-gray-500">
            No categories found
        </div>
    @endforelse
</div>

<style>
    .menu-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection