<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-full pt-16 bg-[#515DEF] border-r border-[#515DEF] dark:border-[#515DEF] transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0"
    aria-label="Sidebar">

    <div class="flex flex-col h-full text-white">

        {{-- Scrollable top section --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Logo/Header --}}
            <div class="flex items-center gap-2 px-4 ">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#515DEF]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">RM SYSTEMS</span>
            </div>

            {{-- Navigation --}}
            <div class="px-4 py-4">
                @php
                    $navItems = [

 
                        

                        ['title' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'icon_type' => 'svg'],
                        ['title' => 'Inventory Management', 'route' => 'admin.inventory.index', 'icon' => 'package', 'icon_type' => 'svg'],
                        ['title' => 'Reservation Management','route' => 'admin.reservations.index','icon' => 'calendar-clock', 'icon_type' => 'svg'],
                        ['title' => 'Order Management', 'route' => 'admin.orders.index', 'icon' => 'shopping-cart', 'icon_type' => 'svg'],
                        ['title' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'bar-chart-3', 'icon_type' => 'svg'],
                        ['title' => 'Customer Management', 'route' => 'admin.customers.index', 'icon' => 'users', 'icon_type' => 'svg'],
                        ['title' => 'Suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'fas fa-truck', 'icon_type' => 'fa'],
                        // ['title' => 'Purchase Orders', 'route' => 'admin.purchase-orders.index', 'icon' => 'fas fa-file-invoice-dollar', 'icon_type' => 'fa'],
                        // ['title' => 'GRN Management', 'route' => 'admin.grn.index', 'icon' => 'fas fa-clipboard-check', 'icon_type' => 'fa'],

                    ];
                @endphp

                <ul class="space-y-2">
                    @foreach ($navItems as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                                {{ request()->routeIs($item['route'])
                                    ? 'bg-white text-gray-700 border-white'
                                    : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                                @if($item['icon_type'] === 'svg')
                                    @include('partials.icons.' . $item['icon'])
                                @else
                                    <i class="{{ $item['icon'] }} w-5 text-center"></i>
                                @endif
                                <span class="font-medium">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Sticky bottom section --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            @php
                $bottomNavItems = [
                    ['title' => 'Digital Menu', 'route' => 'admin.digital-menu.index', 'icon' => 'menu', 'icon_type' => 'svg'],
                    ['title' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'settings', 'icon_type' => 'svg'],
                ];
            @endphp

            <ul class="space-y-2">
                @foreach ($bottomNavItems as $item)
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                            {{ request()->routeIs($item['route'])
                                ? 'bg-white text-gray-700 border-white'
                                : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                            @if($item['icon_type'] === 'svg')
                                @include('partials.icons.' . $item['icon'])
                            @else
                                <i class="{{ $item['icon'] }} w-5 text-center"></i>
                            @endif
                            <span class="font-medium">{{ $item['title'] }}</span>
                        </a>
                    </li>
                @endforeach

                <li class="py-4 pt-4">
                    <button onclick="toggleLogoutModal()"
                        class="w-full text-left flex items-center border gap-3 rounded-xl px-3 py-2 transition-colors hover:bg-[#6A71F0]">
                        @include('partials.icons.log-out')
                        <span>Sign Out</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</aside>