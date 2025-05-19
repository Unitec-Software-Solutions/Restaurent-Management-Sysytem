@extends('layouts.app')

@section('title', 'Digital Menu')

@section('content')
    <header class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-700">
            @if(request()->is('frontend')) Admin @endif Digital Menu
        </h2>
        @if(request()->is('frontend'))
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
                <i class="fas fa-plus mr-2"></i>Add menu item
            </button>
        @endif
    </header>

    <!-- Filter Buttons -->
    <div class="flex gap-3 mb-6 overflow-x-auto py-2">
        <button onclick="filterMenu('all')" 
                class="filter-btn px-4 py-2 rounded-full whitespace-nowrap transition-all
                {{ request()->input('filter', 'all') === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            All Menu
        </button>
        <button onclick="filterMenu('beverages')" 
                class="filter-btn px-4 py-2 rounded-full whitespace-nowrap transition-all
                {{ request()->input('filter') === 'beverages' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Beverages
        </button>
        <button onclick="filterMenu('dairy')" 
                class="filter-btn px-4 py-2 rounded-full whitespace-nowrap transition-all
                {{ request()->input('filter') === 'dairy' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Dairy Products
        </button>
        <button onclick="filterMenu('frozen')" 
                class="filter-btn px-4 py-2 rounded-full whitespace-nowrap transition-all
                {{ request()->input('filter') === 'frozen' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Frozen Foods
        </button>
    </div>

    <!-- Menu Content -->
    <div id="menu-content">
        <!-- Beverages Section -->
        <section id="beverages-section" class="mb-10 {{ request()->input('filter', 'all') !== 'all' && request()->input('filter') !== 'beverages' ? 'hidden' : '' }}">
            <div class="flex items-center mb-4">
                <i class="fas fa-glass-whiskey text-blue-800 mr-2 text-xl"></i>
                <h3 class="text-xl font-bold text-blue-800">BEVERAGES</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">Refreshing drinks for all occasions</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Cappuccino -->
                <div class="menu-item bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-all hover:-translate-y-1">
                    <div class="h-40 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                             alt="Cappuccino" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-lg">Cappuccino</h4>
                        <p class="text-gray-600 mt-1">Rs. 350</p>
                        @if(request()->is('frontend'))
                        <div class="mt-3 flex gap-3">
                            <button class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="text-red-600 hover:text-red-800 text-sm flex items-center">
                                <i class="fas fa-trash mr-1"></i> Remove
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Iced Tea -->
                <div class="menu-item bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-all hover:-translate-y-1">
                    <div class="h-40 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1551029506-0807df4e2031?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1634&q=80" 
                             alt="Iced Tea" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-lg">Iced Tea</h4>
                        <p class="text-gray-600 mt-1">Rs. 200</p>
                        @if(request()->is('frontend'))
                        <div class="mt-3 flex gap-3">
                            <button class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="text-red-600 hover:text-red-800 text-sm flex items-center">
                                <i class="fas fa-trash mr-1"></i> Remove
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Add more beverage items here -->
            </div>
        </section>

        <!-- Dairy Products Section -->
        <section id="dairy-section" class="mb-10 {{ request()->input('filter', 'all') !== 'all' && request()->input('filter') !== 'dairy' ? 'hidden' : '' }}">
            <div class="flex items-center mb-4">
                <i class="fas fa-cheese text-blue-800 mr-2 text-xl"></i>
                <h3 class="text-xl font-bold text-blue-800">DAIRY PRODUCTS</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">Fresh dairy products daily</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Add dairy items here following same structure -->
            </div>
        </section>

        <!-- Frozen Foods Section -->
        <section id="frozen-section" class="mb-10 {{ request()->input('filter', 'all') !== 'all' && request()->input('filter') !== 'frozen' ? 'hidden' : '' }}">
            <div class="flex items-center mb-4">
                <i class="fas fa-snowflake text-blue-800 mr-2 text-xl"></i>
                <h3 class="text-xl font-bold text-blue-800">FROZEN FOODS</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">Chilled and ready to enjoy</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Add frozen food items here following same structure -->
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
function filterMenu(category) {
    // Update URL without reload
    const url = new URL(window.location.href);
    if (category === 'all') {
        url.searchParams.delete('filter');
    } else {
        url.searchParams.set('filter', category);
    }
    history.pushState({}, '', url.toString());

    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const btnCategory = btn.onclick.toString().match(/filterMenu\('(.*)'\)/)[1];
        if (btnCategory === category) {
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            btn.classList.add('bg-blue-600', 'text-white');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
        }
    });

    // Show/hide sections
    const sections = {
        'all': ['beverages-section', 'dairy-section', 'frozen-section'],
        'beverages': ['beverages-section'],
        'dairy': ['dairy-section'],
        'frozen': ['frozen-section']
    };

    // Hide all sections first
    document.querySelectorAll('[id$="-section"]').forEach(section => {
        section.classList.add('hidden');
    });

    // Show selected sections
    sections[category].forEach(sectionId => {
        document.getElementById(sectionId).classList.remove('hidden');
    });
}

// Handle browser back/forward
window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('filter') || 'all';
    filterMenu(category);
});
</script>
@endpush