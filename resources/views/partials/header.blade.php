<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">
            @yield('header')
        </h1>
        
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">
                {{ Auth::user()->name }}
            </span>
            <livewire:logout-button />
        </div>
    </div>
</header>
