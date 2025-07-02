{{-- Enhanced Restaurant Management Admin Sidebar --}}
{{-- Mobile Overlay --}}
<div class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden sidebar-overlay hidden" 
     x-data 
     x-show="!$store.sidebar?.collapsed && window.innerWidth < 1024"
     @click="$store.sidebar?.collapse()"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"></div>

<aside id="sidebar" class="sidebar bg-gray-900 shadow-xl border-r border-gray-700 transition-all duration-300 fixed top-0 left-0 z-50" 
       x-data 
       x-init="$store.sidebar = Alpine.store('sidebar')" 
       :class="{ 
           'collapsed': $store.sidebar?.collapsed,
           'lg:translate-x-0': true,
           '-translate-x-full lg:translate-x-0': window.innerWidth < 1024 && $store.sidebar?.collapsed,
           'translate-x-0': window.innerWidth < 1024 && !$store.sidebar?.collapsed
       }">
    
    {{-- Sidebar Header --}}
    <div class="sidebar-header bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3" x-show="!collapsed" x-transition>
                <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-utensils text-indigo-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg">{{ Auth::user()?->organization?->name ?? 'Restaurant Manager' }}</h2>
                    @if(Auth::user()?->branch)
                        <p class="text-indigo-200 text-sm">{{ Auth::user()?->branch?->name }}</p>
                    @endif
                </div>
            </div>
            <button @click="collapsed = !collapsed" class="text-white hover:bg-indigo-800 p-2 rounded-lg transition-colors">
                <i class="fas" :class="collapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
            </button>
        </div>
    </div>

    {{-- Sidebar Navigation --}}
    <nav class="sidebar-nav flex-1 overflow-y-auto py-4">
        
        {{-- 1. DASHBOARD --}}
        <div class="sidebar-section">
            <ul class="space-y-1 px-3">
                <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link group">
                        <i class="fas fa-gauge sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Dashboard</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>Dashboard</div>
                    </a>
                </li>
            </ul>
        </div>

        {{-- 2. OPERATIONS --}}
        <div class="sidebar-section">
            <div class="sidebar-group-title" x-show="!collapsed" x-transition>
                <span>OPERATIONS</span>
            </div>
            <ul class="space-y-1 px-3">
                
                {{-- Reservations --}}
                @canany(['view_reservations', 'manage_reservations'])
                <li class="sidebar-item {{ request()->routeIs('admin.reservations*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.reservations*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-calendar-days sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Reservations</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Reservations</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        @can('view_reservations')
                        <li><a href="{{ route('admin.reservations.index') }}" class="submenu-link">All Reservations</a></li>
                        <li><a href="{{ route('admin.reservations.index', ['status' => 'upcoming']) }}" class="submenu-link">Upcoming</a></li>
                        @endcan
                        @can('manage_reservations')
                        <li><a href="{{ route('admin.reservations.create') }}" class="submenu-link">New Reservation</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Orders --}}
                @canany(['view_orders', 'manage_orders'])
                <li class="sidebar-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.orders*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-receipt sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Orders</span>
                        <span class="badge" x-show="!collapsed">{{ \App\Models\Order::where('status', 'pending')->count() }}</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Orders</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        @can('view_orders')
                        <li><a href="{{ route('admin.orders.index') }}" class="submenu-link">All Orders</a></li>
                        <li><a href="{{ route('admin.orders.index', ['type' => 'dine_in']) }}" class="submenu-link">Dine-in Orders</a></li>
                        <li><a href="{{ route('admin.orders.takeaway.index') }}" class="submenu-link">Takeaway Orders</a></li>
                        @endcan
                        @can('manage_orders')
                        <li><a href="{{ route('admin.orders.create') }}" class="submenu-link">New Order</a></li>
                        <li><a href="{{ route('admin.orders.takeaway.create') }}" class="submenu-link">New Takeaway</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Tables --}}
                @canany(['view_tables', 'manage_tables'])
                <li class="sidebar-item {{ request()->routeIs('admin.tables*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link group">
                        <i class="fas fa-table sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Tables</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>Tables (Coming Soon)</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </div>

        {{-- 3. MENU & INVENTORY --}}
        <div class="sidebar-section">
            <div class="sidebar-group-title" x-show="!collapsed" x-transition>
                <span>MENU & INVENTORY</span>
            </div>
            <ul class="space-y-1 px-3">
                
                {{-- Menu Builder --}}
                @canany(['view_menu', 'manage_menu'])
                <li class="sidebar-item {{ request()->routeIs('admin.menus*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.menus*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-book-open sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Menu Builder</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Menu Builder</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        @can('view_menu')
                        <li><a href="{{ route('admin.menus.index') }}" class="submenu-link">All Menus</a></li>
                        <li><a href="{{ route('admin.menus.calendar') }}" class="submenu-link">Menu Calendar</a></li>
                        @endcan
                        @can('manage_menu')
                        <li><a href="{{ route('admin.menus.create') }}" class="submenu-link">Create Menu</a></li>
                        <li><a href="{{ route('admin.menus.bulk-create') }}" class="submenu-link">Bulk Create</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Inventory --}}
                @auth('admin')
                <li class="sidebar-item {{ request()->routeIs('admin.inventory*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.inventory*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-warehouse sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Inventory</span>
                        @php
                            try {
                                $lowStockCount = \App\Models\ItemMaster::where('current_stock', '<', \Illuminate\Support\Facades\DB::raw('min_stock_level'))->count();
                            } catch (\Exception $e) {
                                $lowStockCount = 0;
                            }
                        @endphp
                        <span class="badge badge-warning" x-show="!collapsed">{{ $lowStockCount }}</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Inventory</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        <li><a href="{{ route('admin.inventory.index') }}" class="submenu-link">Stock Levels</a></li>
                        <li><a href="{{ route('admin.grn.index') }}" class="submenu-link">Purchase Orders (GRN)</a></li>
                        <li><a href="{{ route('admin.suppliers.index') }}" class="submenu-link">Suppliers</a></li>
                    </ul>
                </li>
                @endauth
            </ul>
        </div>

        {{-- 4. ADMINISTRATION --}}
        @canany(['manage_organization', 'manage_users', 'manage_branches'])
        <div class="sidebar-section">
            <div class="sidebar-group-title" x-show="!collapsed" x-transition>
                <span>ADMINISTRATION</span>
            </div>
            <ul class="space-y-1 px-3">
                
                {{-- Staff Management --}}
                @canany(['view_users', 'manage_users'])
                <li class="sidebar-item {{ request()->routeIs('admin.staff*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.staff*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-user-gear sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Staff</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Staff</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        @can('view_users')
                        <li><a href="{{ route('admin.users.index') }}" class="submenu-link">Manage Users</a></li>
                        @endcan
                        @can('manage_roles')
                        <li><a href="{{ route('admin.roles.index') }}" class="submenu-link">Roles & Permissions</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Branches --}}
                @can('manage_branches')
                <li class="sidebar-item {{ request()->routeIs('admin.branches*') ? 'active' : '' }}">
                    <a href="{{ route('admin.branches.global') }}" class="sidebar-link group">
                        <i class="fas fa-location-dot sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Branches</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>Branches</div>
                    </a>
                </li>
                @endcan

                {{-- Organization Settings --}}
                @can('manage_organization')
                <li class="sidebar-item {{ request()->routeIs('admin.organizations*') ? 'active' : '' }}">
                    <a href="{{ route('admin.organizations.index') }}" class="sidebar-link group">
                        <i class="fas fa-building sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Organization</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>Organization Settings</div>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        {{-- 6. FINANCE & REPORTS --}}
        @canany(['view_billing', 'view_reports'])
        <div class="sidebar-section">
            <div class="sidebar-group-title" x-show="!collapsed" x-transition>
                <span>FINANCE & REPORTS</span>
            </div>
            <ul class="space-y-1 px-3">
                
                {{-- Billing --}}
                @canany(['view_billing', 'manage_payments'])
                <li class="sidebar-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}" 
                    x-data="{ open: {{ request()->routeIs('admin.payments*') ? 'true' : 'false' }} }">
                    <a href="#" @click="open = !open" class="sidebar-link group has-submenu">
                        <i class="fas fa-credit-card sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Billing</span>
                        <i class="fas fa-chevron-down submenu-arrow" x-show="!collapsed" 
                           :class="{ 'rotate-180': open }" x-transition></i>
                        <div class="tooltip" x-show="collapsed" x-cloak>Billing</div>
                    </a>
                    <ul class="submenu" x-show="open && !collapsed" x-transition>
                        @can('view_billing')
                        <li><a href="{{ route('admin.payments.index') }}" class="submenu-link">Payments</a></li>
                        @endcan
                        @can('manage_payments')
                        <li><a href="{{ route('admin.payments.create') }}" class="submenu-link">New Payment</a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Reports --}}
                @can('view_reports')
                <li class="sidebar-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link group">
                        <i class="fas fa-chart-column sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Reports</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>Reports (Coming Soon)</div>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        {{-- 7. CONFIGURATION --}}
        @can('manage_settings')
        <div class="sidebar-section">
            <div class="sidebar-group-title" x-show="!collapsed" x-transition>
                <span>CONFIGURATION</span>
            </div>
            <ul class="space-y-1 px-3">
                <li class="sidebar-item {{ request()->routeIs('admin.modules*') ? 'active' : '' }}">
                    <a href="{{ route('admin.modules.index') }}" class="sidebar-link group">
                        <i class="fas fa-sliders sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>System Modules</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>System Modules</div>
                    </a>
                </li>
                
                <li class="sidebar-item {{ request()->routeIs('admin.testpage*') ? 'active' : '' }}">
                    <a href="{{ route('admin.testpage') }}" class="sidebar-link group">
                        <i class="fas fa-flask sidebar-icon"></i>
                        <span class="sidebar-text" x-show="!collapsed" x-transition>Test Page</span>
                        <div class="tooltip" x-show="collapsed" x-cloak>System Test Page</div>
                    </a>
                </li>
            </ul>
        </div>
        @endcan
    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer border-t border-gray-700 p-4 bg-gray-800">
        <div class="flex items-center gap-3" x-show="!collapsed" x-transition>
            <div class="h-8 w-8 bg-indigo-600 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-100 truncate">{{ Auth::user()?->name ?? 'Guest' }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()?->email ?? 'Not logged in' }}</p>
            </div>
        </div>
        <div class="text-center" x-show="collapsed" x-transition>
            <div class="h-8 w-8 bg-indigo-600 rounded-full flex items-center justify-center mx-auto">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
        </div>
    </div>
</aside>

{{-- Sidebar Styles --}}
<style>
.sidebar {
    width: 280px;
    transition: width 0.3s ease;
}

.sidebar.collapsed {
    width: 80px;
}

.sidebar-group-title {
    @apply text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-3;
}

.sidebar-section {
    @apply mb-6;
}

.sidebar-link {
    @apply flex items-center gap-3 px-3 py-2.5 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200 relative;
}

.sidebar-link.has-submenu {
    @apply justify-between;
}

.sidebar-item.active .sidebar-link {
    @apply bg-indigo-600 text-white;
}

.sidebar-item.active .sidebar-link:hover {
    @apply bg-indigo-700;
}

.sidebar-icon {
    @apply w-5 h-5 flex-shrink-0;
}

.submenu {
    @apply pl-6 mt-2 space-y-1;
}

.submenu-link {
    @apply block px-3 py-2 text-sm text-gray-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors duration-200;
}

.submenu-arrow {
    @apply w-4 h-4 transition-transform duration-200;
}

.badge {
    @apply bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full;
}

.badge-warning {
    @apply bg-orange-500;
}

.tooltip {
    @apply absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50 opacity-0 pointer-events-none transition-opacity duration-200;
}

.sidebar.collapsed .sidebar-link:hover .tooltip {
    @apply opacity-100;
}

/* Mobile responsive */
@media (max-width: 1024px) {
    .sidebar {
        @apply fixed top-0 left-0 h-full z-40 transform -translate-x-full transition-transform duration-300;
    }
    
    .sidebar.mobile-open {
        @apply translate-x-0;
    }
}

/* Hide text on collapsed state */
.sidebar.collapsed .sidebar-text {
    @apply hidden;
}

.sidebar.collapsed .sidebar-group-title {
    @apply hidden;
}

.sidebar.collapsed .submenu {
    @apply hidden;
}

.sidebar.collapsed .submenu-arrow {
    @apply hidden;
}

.sidebar.collapsed .badge {
    @apply hidden;
}
</style>

{{-- Alpine.js for sidebar functionality --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sidebar', () => ({
        collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        
        init() {
            this.$watch('collapsed', value => {
                localStorage.setItem('sidebar_collapsed', value);
            });
        }
    }));
});
</script>
