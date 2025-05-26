<header class="bg-white shadow-sm">
    <div class="px-6 py-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <button class="p-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-bell"></i>
            </button>
            <button class="p-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-question-circle"></i>
            </button>
        </div>
    </div>
</header>