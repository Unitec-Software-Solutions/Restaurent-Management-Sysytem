<header class="fixed top-0 right-0 z-30 bg-white border-b border-gray-200 px-6 py-3 dark:bg-gray-800 dark:border-gray-700 transition-all duration-300 lg:left-72"
        x-data 
        x-init="$store.sidebar = Alpine.store('sidebar')"
        :class="{ 'lg:left-72': !$store.sidebar?.collapsed, 'lg:left-20': $store.sidebar?.collapsed }">
    <div class="flex flex-col space-y-1">
        <!-- Top row - menu button, title, and profile -->
        <div class="flex items-center justify-between w-full">
            <!-- Mobile menu button & Desktop sidebar toggle -->
            <button @click="$store.sidebar?.toggle()" 
                    data-sidebar-toggle
                    aria-expanded="false" 
                    aria-controls="sidebar"
                    class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Toggle sidebar</span>
            </button>
            
            <!-- Title and subtitle container -->
            <div class="flex-1 ml-3 lg:ml-0">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    @yield('header-title', 'Dashboard')
                </h1>
                @hasSection('header-subtitle')
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @yield('header-subtitle')
                    </p>
                @endif
            </div>
            <div class="flex items-center space-x-4">
    <!-- 🔘 Test Page Button -->
    <a href="{{ route('admin.testpage') }}"
       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        🧪 Test Page
    </a>

    <!-- Profile dropdown -->
    @include('partials.header.profile-dropdown')
</div>
    </div>
</header>