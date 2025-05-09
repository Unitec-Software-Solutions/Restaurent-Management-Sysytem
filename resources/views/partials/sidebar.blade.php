<aside id="logo-sidebar"
       class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
       aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">

            <!-- Main Dashboard -->
            <li>
                <a href="{{ route('home') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('home') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 01.894.553l7 14A1 1 0 0117 18H3a1 1 0 01-.894-1.447l7-14A1 1 0 0110 2z"/>
                    </svg>
                    <span class="ml-3">Main Dashboard</span>
                </a>
            </li>

            <!-- Inventory Dashboard -->
            <li>
                <a href="{{ route('inventory.dashboard') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h18v2H3zm0 6h18v2H3zm0 6h18v2H3z"/>
                    </svg>
                    <span class="ml-3">Inventory Dashboard</span>
                </a>
            </li>

            <!-- Stock -->
            <li>
                <a href="{{ route('inventory.stock.index') }}"
                class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.stock.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4h12v2H4zm0 4h8v2H4zm0 4h6v2H4z"/>
                    </svg>
                    <span class="ml-3">Stock Management</span>
                </a>
            </li>

            <!-- Transactions -->
            <li>
                <a href="{{ route('inventory.transactions') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.transactions') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4h12v2H4zm0 4h8v2H4zm0 4h6v2H4z"/>
                    </svg>
                    <span class="ml-3">Transactions</span>
                </a>
            </li>

            <!-- Expiry Report -->
            <li>
                <a href="{{ route('inventory.expiry-report') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.expiry-report') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a8 8 0 108 8 8 8 0 00-8-8zM9 4h2v5H9zm1 9a1.5 1.5 0 11-1.5-1.5A1.5 1.5 0 0110 13z"/>
                    </svg>
                    <span class="ml-3">Expiry Report</span>
                </a>
            </li>

            <!-- Create Item                     
            <li>
                <a href="{{ route('inventory.items.create') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.items.create') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                    </svg>
                    <span class="ml-3">Add New Item</span>
                </a>
            </li>
            -->

            <!-- Logout -->
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M3 4a1 1 0 011-1h5a1 1 0 110 2H5v10h4a1 1 0 110 2H4a1 1 0 01-1-1V4zm9.293 1.293a1 1 0 011.414 0L17 8.586a2 2 0 010 2.828l-3.293 3.293a1 1 0 11-1.414-1.414L14.586 11H8a1 1 0 110-2h6.586l-1.293-1.293a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-3">Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>
</aside>
