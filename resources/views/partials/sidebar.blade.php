<aside id="sidebar" class="fixed top-0 left-0 z-20 flex flex-col w-64 h-full pt-16 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-transform -translate-x-full lg:translate-x-0" aria-label="Sidebar">
    <div class="flex flex-col flex-1 overflow-y-auto">
        <div class="px-4 py-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reservations.index') }}"  class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Reservation Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.inventory.index') }}"  class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Inventory Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Order Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Customer Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Digital Menu</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Settings</span>
                    </a>
                </li>
                <li class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.logout.page') }}" class="flex items-center p-2 text-base font-normal text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                        <span class="ml-3">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>