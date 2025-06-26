@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $menu->name }}</h1>
            @if($menu->description)
                <p class="text-lg text-gray-600 mb-4">{{ $menu->description }}</p>
            @endif
            
            <div class="flex justify-center items-center gap-4 text-sm text-gray-500">
                @if($menu->branch)
                    <span class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $menu->branch->name }}
                    </span>
                @endif
                
                <span class="flex items-center">
                    <i class="fas fa-tag mr-1"></i>
                    {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                </span>
                
                @if($menu->start_time && $menu->end_time)
                    <span class="flex items-center">
                        <i class="fas fa-clock mr-1"></i>
                        {{ \Carbon\Carbon::parse($menu->start_time)->format('g:i A') }} - 
                        {{ \Carbon\Carbon::parse($menu->end_time)->format('g:i A') }}
                    </span>
                @endif
            </div>
            
            <!-- Availability indicator -->
            <div class="mt-4">
                @if($menu->shouldBeActiveNow())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Available Now
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-2"></i>
                        Currently Unavailable
                    </span>
                @endif
            </div>
        </div>

        <!-- Menu Items by Category -->
        @if($menu->menuItems->count() > 0)
            @foreach($menu->menuItems->groupBy('menuCategory.name') as $categoryName => $items)
                <div class="mb-8">
                    @if($categoryName)
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b-2 border-indigo-200">
                            {{ $categoryName }}
                        </h2>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($items as $item)
                            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $item->name }}</h3>
                                    <span class="text-xl font-bold text-green-600">${{ number_format($item->price, 2) }}</span>
                                </div>
                                
                                @if($item->description)
                                    <p class="text-gray-600 mb-4">{{ $item->description }}</p>
                                @endif
                                
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-4">
                                        @if($item->prep_time)
                                            <span class="flex items-center text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $item->prep_time }} min
                                            </span>
                                        @endif
                                        
                                        @if($item->spice_level)
                                            <span class="flex items-center text-orange-500">
                                                @for($i = 1; $i <= $item->spice_level; $i++)
                                                    <i class="fas fa-pepper-hot"></i>
                                                @endfor
                                            </span>
                                        @endif
                                        
                                        @if($item->is_vegetarian)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                Vegetarian
                                            </span>
                                        @endif
                                        
                                        @if($item->is_vegan)
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-medium">
                                                Vegan
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center">
                                        @if($item->current_stock !== null)
                                            @if($item->current_stock > 0)
                                                <span class="flex items-center text-green-600">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Available
                                                </span>
                                            @else
                                                <span class="flex items-center text-red-600">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Out of Stock
                                                </span>
                                            @endif
                                        @elseif($item->is_available)
                                            <span class="flex items-center text-green-600">
                                                <i class="fas fa-check mr-1"></i>
                                                Available
                                            </span>
                                        @else
                                            <span class="flex items-center text-red-600">
                                                <i class="fas fa-times mr-1"></i>
                                                Unavailable
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @php
                                    $allergens = null;
                                    if ($item->allergen_info && is_array($item->allergen_info)) {
                                        $allergens = $item->allergen_info;
                                    } elseif ($item->allergens) {
                                        $allergens = is_array($item->allergens) ? $item->allergens : explode(',', $item->allergens);
                                    }
                                @endphp
                                
                                @if($allergens && count($allergens) > 0)
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-exclamation-triangle mr-1 text-yellow-500"></i>
                                            Contains: {{ implode(', ', array_filter($allergens)) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Menu Coming Soon</h3>
                <p class="text-gray-500">We're still preparing this menu for you.</p>
            </div>
        @endif
        
        <!-- Footer Information -->
        <div class="mt-12 bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Available Days</h4>
                        <p class="text-gray-600">
                            @if($menu->available_days && is_array($menu->available_days) && count($menu->available_days) > 0)
                                {{ implode(', ', array_map('ucfirst', $menu->available_days)) }}
                            @else
                                No days specified
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Service Period</h4>
                        <p class="text-gray-600">
                            @if($menu->valid_from)
                                {{ \Carbon\Carbon::parse($menu->valid_from)->format('M j, Y') }}
                                @if($menu->valid_until)
                                    - {{ \Carbon\Carbon::parse($menu->valid_until)->format('M j, Y') }}
                                @else
                                    - Ongoing
                                @endif
                            @else
                                No dates specified
                            @endif
                        </p>
                    </div>
                    
                    @if($menu->start_time && $menu->end_time)
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Service Hours</h4>
                            <p class="text-gray-600">
                                {{ \Carbon\Carbon::parse($menu->start_time)->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($menu->end_time)->format('g:i A') }}
                            </p>
                        </div>
                    @endif
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        Prices and availability subject to change. Please inform your server of any allergies or dietary restrictions.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .fas.fa-pepper-hot {
        font-size: 12px;
    }
</style>
@endpush
@endsection
