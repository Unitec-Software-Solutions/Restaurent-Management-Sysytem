@extends('layouts.app')

@section('title', 'Our Digital Menu')

@section('content')
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-700 text-center">Our Menu</h2>
    </header>

    <!-- Category Sections -->
    <div class="space-y-12">
        <!-- Beverages Section -->
        <section class="category-section" data-category="beverages">
            <h3 class="text-xl font-bold text-blue-800 mb-4 flex items-center justify-center">
                <i class="fas fa-glass-whiskey mr-2"></i>BEVERAGES
            </h3>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.beverages-customer')
            </div>
        </section>

        <!-- Dairy Section -->
        <section class="category-section" data-category="dairy">
            <h3 class="text-xl font-bold text-blue-800 mb-4 flex items-center justify-center">
                <i class="fas fa-cheese mr-2"></i>DAIRY PRODUCTS
            </h3>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.dairy-customer')
            </div>
        </section>

        <!-- Frozen Foods Section -->
        <section class="category-section" data-category="frozen">
            <h3 class="text-xl font-bold text-blue-800 mb-4 flex items-center justify-center">
                <i class="fas fa-snowflake mr-2"></i>FROZEN FOODS
            </h3>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.frozen-customer')
            </div>
        </section>
    </div>
@endsection 