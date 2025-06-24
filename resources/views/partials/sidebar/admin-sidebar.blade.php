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
                    $admin = auth()->user();
                    $navItems = [
                        [
                            'title' => 'Dashboard',
                            'route' => 'admin.dashboard',
                            'icon' => 'layout-dashboard',
                            'icon_type' => 'svg',
                        ],
                        [
                            'title' => 'Inventory Management',

                            'title' => 'Inventory',

                            'route' => 'admin.inventory.index',
                            'icon' => 'package',
                            'icon_type' => 'svg',
                        ],
                        [
                            'title' => 'Reservation Management',

                            'title' => 'Reservation',

                            'route' => 'admin.reservations.index',
                            'icon' => 'calendar-clock',
                            'icon_type' => 'svg',
                        ],
                        [
                            'title' => 'Order Management',

                            'title' => 'Orders',

                            'route' => 'admin.orders.index',
                            'icon' => 'shopping-cart',
                            'icon_type' => 'svg',
                        ],
                        [
                            'title' => 'Reports',
                            'route' => 'admin.reports.index',
                            'icon' => 'bar-chart-3',
                            'icon_type' => 'svg',
                        ],
                        [
                            'title' => 'Customer Management',
                            'route' => 'admin.customers.index',
                            'icon' => 'users',
                            'icon_type' => 'svg',

                            'title' => 'Customers',
                            'route' => 'admin.customers.index',
                            'icon' => 'fa-solid fa-user',
                            'icon_type' => 'fa',
                        ],
                        [
                            'title' => 'Suppliers',
                            'route' => 'admin.suppliers.index',
                            'icon' => 'fas fa-truck',
                            'icon_type' => 'fa',
                        ],
                        [
                            'title' => 'Production',
                            'route' => 'admin.production.index',
                            'icon' => 'fas fa-clipboard-list',
                            'icon_type' => 'fa',
                        ],

                        ['title' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'icon_type' => 'svg'],

                        [
                            'title' => 'Users',
                            'route' => 'admin.users.index',
                            'icon' => 'fa-solid fa-users',
                            'icon_type' => 'fa',
                        ],
                    ];

                    // Organizations nav with sub-item for activation
                    $organizationsNav = [
                        'title' => 'Organizations',
                        'route' => 'admin.organizations.index',
                        'icon' => 'fas fa-building',
                        'icon_type' => 'fa',
                        'sub_items' => [],
                    ];
                    if ($admin->is_super_admin) {
                        $organizationsNav['sub_items'][] = [
                            'title' => 'Activate Organization',
                            'route' => 'admin.organizations.activate.form',
                            'icon' => 'fas fa-key',
                            'icon_type' => 'fa',
                        ];
                    }

                    // Branches nav with correct route and sub-item for activation
                    $branchesNav = [
                        'title' => 'Branches',
                        'icon' => 'fas fa-store',
                        'icon_type' => 'fa',
                        'sub_items' => [
                            [
                                'title' => 'Activate Branch',
                                'route' => 'admin.branches.activate.form',
                                'icon' => 'fas fa-key',
                                'icon_type' => 'fa',
                            ],
                        ],
                    ];
                    if ($admin->is_super_admin) {
                        $branchesNav['route'] = 'admin.branches.global';
                        $branchesNav['route_params'] = [];
                    } elseif ($admin->organization_id) {
                        $branchesNav['route'] = 'admin.branches.index';
                        $branchesNav['route_params'] = ['organization' => $admin->organization_id];
                    } else {
                        $branchesNav['route'] = '#';
                        $branchesNav['route_params'] = [];
                    }

                    // Add to nav
                    $navItems[] = $organizationsNav;
                    $navItems[] = $branchesNav;
                    if ($admin->is_super_admin) {
                        $navItems[] = [
                            'title' => 'Subscription Plans',
                            'route' => 'admin.subscription-plans.index',
                            'icon' => 'fa-solid fa-credit-card',
                            'icon_type' => 'fa',
                        ];
                        $navItems[] = [
                            'title' => 'Roles & Permissions',
                            'route' => 'admin.roles.index',
                            'icon' => 'fa-solid fa-lock',
                            'icon_type' => 'fa',
                        ];
                        $navItems[] = [
                            'title' => 'Modules Management',
                            'route' => 'admin.modules.index',
                            'icon' => 'fa-solid fa-cogs',
                            'icon_type' => 'fa',
                        ];
                    }
                @endphp

                <ul class="space-y-2">
                    @foreach ($navItems as $item)
                        <li>
                            <a href="{{ $item['route'] !== '#' ? route($item['route'], $item['route_params'] ?? []) : '#' }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                                {{ request()->routeIs($item['route'])
                                    ? 'bg-white text-gray-700 border-white'
                                    : 'bg-transparent text-white border-white hover:bg-white/10' }}">

                                @if ($item['icon_type'] === 'svg')
                                    @include('partials.icons.' . $item['icon'])
                                @else
                                    <i class="{{ $item['icon'] }} w-5 text-center"></i>
                                @endif
                                <span class="font-medium">{{ $item['title'] }}</span>
                            </a>
                            @if (isset($item['sub_items']) && count($item['sub_items']))
                                <ul class="ml-8 mt-1 space-y-1">
                                    @foreach ($item['sub_items'] as $sub)
                                        <li>
                                            <a href="{{ route($sub['route']) }}"
                                                class="flex items-center gap-2 px-3 py-1 rounded border transition-colors text-sm
                                                {{ request()->routeIs($sub['route'])
                                                    ? 'bg-white text-gray-700 border-white'
                                                    : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                                                @if ($sub['icon_type'] === 'svg')
                                                    @includeIf('partials.icons.' . $sub['icon'])
                                                @else
                                                    <i class="{{ $sub['icon'] }} w-4 text-center"></i>
                                                @endif
                                                <span>{{ $sub['title'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>

                {{-- resources/views/partials/sidebar.blade.php --}}
                <nav class="mt-6">
                    @if (auth()->user()->role && auth()->user()->role->modules)
                        @foreach (auth()->user()->role->modules as $module)
                            <a href="{{ route($module->name . '.index') }}"
                                class="block px-4 py-2 text-gray-600 hover:bg-gray-100 {{ request()->routeIs($module->name . '.*') ? 'bg-gray-100' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $module->name)) }}
                            </a>
                        @endforeach
                    @endif
                </nav>
            </div>
        </div>

        {{-- Sticky bottom section --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            @php
                $bottomNavItems = [
                    [
                        'title' => 'Digital Menu',
                        'route' => 'admin.digital-menu.index',
                        'icon' => 'menu',
                        'icon_type' => 'svg',
                    ],
                    [
                        'title' => 'Settings',
                        'route' => 'admin.settings.index',
                        'icon' => 'settings',
                        'icon_type' => 'svg',
                    ],
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
                            @if ($item['icon_type'] === 'svg')
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

@can('create', App\Models\Role::class)
    <a href="{{ route('admin.roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        + Add Role
    </a>
@endcan
