<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <!-- Main header row -->
        <div class="flex justify-between items-center">
            <div class="flex-1">
                <h1 class="text-xl font-semibold text-gray-900">
                    @yield('header')
                </h1>
                
                <!-- Enhanced breadcrumb for menu items -->
                @if(request()->routeIs('admin.menu-items*'))
                    <nav class="flex mt-2 text-sm text-gray-600" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-1">
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                <span class="text-indigo-600">Menu Items</span>
                            </li>
                            @if(request()->routeIs('admin.menu-items.create-kot'))
                                <li class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                    <span class="text-orange-600">Create KOT Recipe</span>
                                </li>
                            @endif
                        </ol>
                    </nav>
                @elseif(request()->routeIs('admin.inventory*'))
                    <nav class="flex mt-2 text-sm text-gray-600" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-1">
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                <span class="text-blue-600">Inventory Management</span>
                            </li>
                        </ol>
                    </nav>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    {{ Auth::user()->name }}
                </span>
                <livewire:logout-button />
            </div>
        </div>
    </div>
</header>
