<aside class="fixed top-0 left-0 h-screen w-64 bg-blue-800 text-white p-6 overflow-y-auto">
    <h1 class="text-2xl font-bold mb-10">RM SYSTEMS</h1>
    <nav class="space-y-4">
        <a href="{{ route('dashboard') }}" 
           onclick="console.log('Dashboard clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
        </a>
        <a href="{{ route('inventory') }}" 
           onclick="console.log('Inventory clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-boxes mr-2"></i>Inventory
        </a>
        <a href="{{ route('reservations') }}" 
           onclick="console.log('Reservations clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-calendar-check mr-2"></i>Reservations
        </a>
        <a href="{{ route('orders') }}" 
           onclick="console.log('Orders clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-receipt mr-2"></i>Orders
        </a>
        <a href="{{ route('reports') }}" 
           onclick="console.log('Reports clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-chart-bar mr-2"></i>Reports
        </a>
        <a href="{{ route('customers') }}" 
           onclick="console.log('Customers clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-users mr-2"></i>Customers
        </a>
        <hr class="my-4 border-blue-600">

        <!-- Digital Menu Link -->
        @auth
            @if(auth()->user()->isAdmin)
                <a href="{{ route('admin.menu') }}" 
                   class="block py-2 px-4 rounded hover:bg-blue-700 {{ request()->routeIs('admin.menu') ? 'bg-white text-blue-800 font-semibold' : '' }}">
                    <i class="fas fa-utensils mr-2"></i>Digital Menu (Admin)
                </a>
            @else
                <a href="{{ route('customer.menu') }}" 
                   class="block py-2 px-4 rounded hover:bg-blue-700 {{ request()->routeIs('customer.menu') ? 'bg-white text-blue-800 font-semibold' : '' }}">
                    <i class="fas fa-utensils mr-2"></i>Digital Menu
                </a>
            @endif
        @endauth


        <a href="{{ route('settings') }}" 
           onclick="console.log('Settings clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-cog mr-2"></i>Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" 
                    onclick="console.log('Sign Out clicked')"
                    class="w-full text-left block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
            </button>
        </form>
    </nav>
</aside>

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
                <a href="{{ route('inventory.transactions.index') }}"
                class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.transactions.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4h12v2H4zm0 4h8v2H4zm0 4h6v2H4z"/>
                    </svg>
                    <span class="ml-3">Transactions</span>
                </a>
            </li>

            <!-- GRN -->
            <li>
                <a href="{{ route('inventory.grn.index') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.grn.') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4h12v2H4zm0 4h8v2H4zm0 4h6v2H4z"/>
                    </svg>
                    <span class="ml-3">G R N</span>
                </a>
            </li>

            <!-- Expiry Report -->
            {{-- 
            <li>
                <a href="{{ route('inventory.expiry-report') }}"
                   class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('inventory.expiry-report') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a8 8 0 108 8 8 8 0 00-8-8zM9 4h2v5H9zm1 9a1.5 1.5 0 11-1.5-1.5A1.5 1.5 0 0110 13z"/>
                    </svg>
                    <span class="ml-3">Expiry Report</span>
                </a>
            </li>
            --}}

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

