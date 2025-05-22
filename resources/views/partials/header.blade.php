<header class="border-b bg-white px-6 py-3 dark:bg-gray-800 dark:border-gray-700 fixed top-0 left-0 right-0 z-20 lg:left-64">
    <div class="flex items-center justify-between">
        <!-- Mobile menu button -->
        <button id="toggleSidebar" aria-expanded="false" aria-controls="sidebar"
            class="p-2 text-gray-600 rounded lg:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M3 5a1 1 0 011-1h12a1 1 0 010 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 010 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 010 2H4a1 1 0 01-1-1z"
                    clip-rule="evenodd" />
            </svg>
        </button>
        
        <!-- Page title -->
        <h1 class="text-xl font-semibold dark:text-white">@yield('header-title', 'Dashboard')</h1>
        
        <!-- Profile dropdown -->
        @include('partials.profile-dropdown')
    </div>
</header>