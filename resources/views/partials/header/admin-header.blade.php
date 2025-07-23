
<header
    class="fixed top-0 left-0 right-0 z-30 bg-white border-b border-gray-200 px-6 py-3 dark:bg-gray-800 dark:border-gray-700 transition-all duration-300"
    x-data
    x-init="$store.sidebar = Alpine.store('sidebar')"
    :class="{
        'lg:ml-64': !$store.sidebar?.collapsed,
        'lg:ml-20': $store.sidebar?.collapsed
    }"
    style="width: 100%;"
>
    <div class="flex flex-col space-y-1">
        <!-- Top row - menu button, title, and profile -->
        <div class="flex items-center justify-between w-full">

            <!-- Title and subtitle container -->
            <div class="flex-1 ml-3 lg:ml-0">
                @if(View::hasSection('header-title'))
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        @yield('header-title')
                    </h1>
                @else
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        Dashboard
                    </h1>
                @endif

                @if(View::hasSection('header-subtitle'))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @yield('header-subtitle')
                    </p>
                @endif
            </div>

            <!-- Action buttons container -->
            <div class="flex items-center space-x-3">
                <!-- Profile dropdown or other actions can go here -->
                {{-- @include('partials.header.profile-dropdown') --}}
            </div>
        </div>
    </div>
</header>
