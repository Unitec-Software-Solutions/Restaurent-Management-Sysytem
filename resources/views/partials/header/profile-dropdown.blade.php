<div class="flex items-center gap-4">
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
            <div class="relative h-10 w-10 overflow-hidden rounded-full">
                <img src="{{ asset('images/avatar.jpg') }}" alt="User avatar" class="object-cover h-full w-full"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth('admin')->user()->name ?? 'Admin') }}&background=D9DCFF&color=515DEF&size=64'">
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-medium dark:text-black">{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                {{-- <span class="text-xs text-gray-500 dark:text-gray-400">User - Role</span> --}}
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="h-4 w-4 text-gray-500 dark:text-gray-400">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <!-- Dropdown menu -->
        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg dark:bg-gray-700 z-50">
            <div class="py-1">
                <a href="#profile"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-user-circle mr-2"></i> Your Profile
                </a>
                <a href="#settings"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <div class="border-t border-gray-100 dark:border-gray-600"></div>


                <button onclick="toggleLogoutModal()"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                </button>

            </div>
        </div>
    </div>
</div>
