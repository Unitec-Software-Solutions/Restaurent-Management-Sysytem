@extends('layouts.app')

@section('title', 'Admin Digital Menu')

@section('content')
    <header class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-700">Admin Digital Menu</h2>
        <a href="{{ route('frontend.itemlist') }}" class="btn btn-primary">+ Add menu item</a>
    </header>

    <!-- Category Sections -->
    <div class="space-y-12">
        <!-- Beverages Section -->
        <section class="category-section" data-category="beverages">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-blue-800 flex items-center">
                    <i class="fas fa-glass-whiskey mr-2"></i>BEVERAGES
                </h3>
                <button class="toggle-category px-4 py-2 bg-blue-600 text-white rounded text-sm">
                    Show/Hide
                </button>
            </div>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.beverages-admin')
            </div>
        </section>

        <!-- Dairy Section -->
        <section class="category-section" data-category="dairy">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-blue-800 flex items-center">
                    <i class="fas fa-cheese mr-2"></i>DAIRY PRODUCTS
                </h3>
                <button class="toggle-category px-4 py-2 bg-blue-600 text-white rounded text-sm">
                    Show/Hide
                </button>
            </div>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.dairy-admin')
            </div>
        </section>

        <!-- Frozen Foods Section -->
        <section class="category-section" data-category="frozen">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-blue-800 flex items-center">
                    <i class="fas fa-s snowflake mr-2"></i>FROZEN FOODS
                </h3>
                <button class="toggle-category px-4 py-2 bg-blue-600 text-white rounded text-sm">
                    Show/Hide
                </button>
            </div>
            <div class="category-content grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @include('menu.items.frozen-admin')
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        // Example JavaScript for modal handling
        const addButton = document.querySelector('header button');
        const modal = document.getElementById('addItemModal');

        addButton.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        modal.querySelector('button[type="button"]').addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    </script>
@endsection 