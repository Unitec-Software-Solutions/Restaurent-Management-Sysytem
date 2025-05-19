<!-- Beverages Section -->
<section class="mb-12">
    <h3 class="text-xl font-bold text-blue-800 mb-2 flex items-center">
        <i class="fas fa-glass-whiskey mr-2"></i>BEVERAGES
    </h3>
    <p class="text-sm text-gray-600 mb-4">Refreshing drinks for all occasions</p>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Cappuccino -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-48 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                     alt="Cappuccino" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Cappuccino</h4>
                <p class="text-gray-600 mt-1">Rs. 350</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Iced Tea -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-48 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1551029506-0807df4e2031?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1634&q=80" 
                     alt="Iced Tea" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Iced Tea</h4>
                <p class="text-gray-600 mt-1">Rs. 200</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Espresso -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-48 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                     alt="Espresso" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Espresso</h4>
                <p class="text-gray-600 mt-1">Rs. 300</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Green Tea -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-48 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" 
                     alt="Green Tea" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Green Tea</h4>
                <p class="text-gray-600 mt-1">Rs. 200</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Dairy Products Section -->
<section class="mb-12">
    <h3 class="text-xl font-bold text-blue-800 mb-2 flex items-center">
        <i class="fas fa-cheese mr-2"></i>DAIRY PRODUCTS
    </h3>
    <p class="text-sm text-gray-600 mb-4">Fresh dairy products daily</p>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Cesar Salad -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-40 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" 
                     alt="Cesar Salad" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Cesar Salad</h4>
                <p class="text-gray-600 mt-1">Rs. 450</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800 text-sm">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Greek Yogurt -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-40 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1550583724-b2692b85b150?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" 
                     alt="Greek Yogurt" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Greek Yogurt</h4>
                <p class="text-gray-600 mt-1">Rs. 350</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800 text-sm">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Milk -->
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-40 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1372&q=80" 
                     alt="Milk" class="w-full h-full object-cover">
            </div>
            <div class="p-4">
                <h4 class="font-semibold text-lg">Milk</h4>
                <p class="text-gray-600 mt-1">Rs. 200</p>
                @if($isAdmin)
                <div class="mt-3 flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</section> 