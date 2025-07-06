{{-- Enhanced Dynamic Sidebar Component with Permission-Aware Navigation --}}
<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-full pt-16 bg-[#515DEF] border-r border-[#515DEF] dark:border-[#515DEF] transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0"
    aria-label="Sidebar">

    <div class="flex flex-col h-full text-white">

        {{-- Scrollable top section --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Logo/Header with responsive design --}}
            <div class="flex items-center gap-2 px-4 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#515DEF]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">RM SYSTEMS</span>
            </div>

            {{-- Organization/Branch Info with subscription status --}}
            @if(isset($organization) && $organization)
                <div class="px-4 py-2 border-b border-[#6A71F0]">
                    <div class="text-sm font-medium">{{ $organization->name ?? 'Organization' }}</div>
                    @if(isset($branch) && $branch)
                        <div class="text-xs text-indigo-200">{{ $branch->name ?? 'Branch' }}</div>
                    @endif
                    {{-- Subscription status indicator --}}
                    @if(isset($subscription) && $subscription)
                        <div class="text-xs text-indigo-300 mt-1 flex items-center gap-1">
                            <i class="fas fa-circle text-green-400" style="font-size: 6px;"></i>
                            {{ $subscription->plan->name ?? 'Basic' }} Plan
                            @if(method_exists($subscription, 'isExpired') && $subscription->isExpired())
                                <span class="bg-red-500 text-white px-1 py-0.5 rounded text-xs ml-1">Expired</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- Enhanced Navigation Sections --}}
            <div class="px-4 py-4">
                {{-- Main Navigation --}}
                @if(isset($menuItems['main']) && is_array($menuItems['main']) && count($menuItems['main']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Main</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['main'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Subscription Management Section (For Org Admins) --}}
                @if(isset($menuItems['subscription']) && is_array($menuItems['subscription']) && count($menuItems['subscription']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Subscription</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['subscription'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif                {{-- Operations Section (For Branch Admins) --}}
                @if(isset($menuItems['operations']) && is_array($menuItems['operations']) && count($menuItems['operations']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Operations</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['operations'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Kitchen Section with Real-time Status --}}
                @if(isset($menuItems['kitchen']) && is_array($menuItems['kitchen']) && count($menuItems['kitchen']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2 flex items-center justify-between">
                            Kitchen
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" title="Live Status"></div>
                            </div>
                        </h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['kitchen'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Service Section --}}
                @if(isset($menuItems['service']) && is_array($menuItems['service']) && count($menuItems['service']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Service</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['service'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Inventory Section with Stock Alerts --}}
                @if(isset($menuItems['inventory']) && is_array($menuItems['inventory']) && count($menuItems['inventory']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2 flex items-center justify-between">
                            Inventory
                            @php
                                $lowStockCount = 0;
                                try {
                                    $lowStockCount = collect($menuItems['inventory'])->where('name', 'Inventory Items')->first()['badge'] ?? 0;
                                } catch (\Exception $e) {
                                    $lowStockCount = 0;
                                }
                            @endphp
                            @if($lowStockCount > 0)
                                <span class="bg-yellow-500 text-yellow-900 text-xs rounded-full px-2 py-1 font-bold">
                                    {{ $lowStockCount }} Low
                                </span>
                            @endif
                        </h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['inventory'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Production Section (New Feature) --}}
                @if(isset($menuItems['production']) && is_array($menuItems['production']) && count($menuItems['production']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Production</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['production'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Modules Section with Tier Indicators --}}
                @if(isset($menuItems['modules']) && is_array($menuItems['modules']) && count($menuItems['modules']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">
                            Modules
                            @if(isset($subscription) && $subscription)
                                <span class="text-xs normal-case text-indigo-400">({{ $subscription->plan->name ?? 'Basic' }})</span>
                            @endif
                        </h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['modules'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Administration Section --}}
                @if(isset($menuItems['admin']) && is_array($menuItems['admin']) && count($menuItems['admin']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Administration</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['admin'] as $item)
                                @if(is_array($item))
                                    @include('components.sidebar.menu-item', ['item' => $item])
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
                        <ul class="space-y-2">
                            @foreach($menuItems['service'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Inventory Section with Stock Alerts --}}
                @if(isset($menuItems['inventory']) && count($menuItems['inventory']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2 flex items-center justify-between">
                            Inventory
                            @php
                                $lowStockCount = collect($menuItems['inventory'])->where('name', 'Inventory Items')->first()['badge'] ?? 0;
                            @endphp
                            @if($lowStockCount > 0)
                                <span class="bg-yellow-500 text-yellow-900 text-xs rounded-full px-2 py-1 font-bold">
                                    {{ $lowStockCount }} Low
                                </span>
                            @endif
                        </h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['inventory'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Production Section (New Feature) --}}
                @if(isset($menuItems['production']) && count($menuItems['production']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Production</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['production'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Modules Section with Tier Indicators --}}
                @if(isset($menuItems['modules']) && count($menuItems['modules']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">
                            Modules
                            @if($subscription)
                                <span class="text-xs normal-case text-indigo-400">({{ $subscription->plan->name ?? 'Basic' }})</span>
                            @endif
                        </h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['modules'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Administration Section --}}
                @if(isset($menuItems['admin']) && count($menuItems['admin']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Administration</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['admin'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>        {{-- Sticky bottom section with enhanced features --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            <ul class="space-y-2">
                {{-- Digital Menu --}}
                @if(isset($currentUser) && $currentUser && method_exists($currentUser, 'can'))
                    @try
                        @if($currentUser->can('view_digital_menu'))
                            <li>
                                <a href="{{ route('admin.digital-menu.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                                    {{ request()->routeIs('admin.digital-menu*')
                                        ? 'bg-white text-gray-700 border-white'
                                        : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                                    @include('partials.icons.menu')
                                    <span class="font-medium">Digital Menu</span>
                                </a>
                            </li>
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail for permissions --}}
                    @endtry
                @endif

                {{-- Settings --}}
                @if(isset($currentUser) && $currentUser && method_exists($currentUser, 'can'))
                    @try
                        @if($currentUser->can('view_settings'))
                            <li>
                                <a href="{{ route('admin.settings.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                                    {{ request()->routeIs('admin.settings*')
                                        ? 'bg-white text-gray-700 border-white'
                                        : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                                    @include('partials.icons.settings')
                                    <span class="font-medium">Settings</span>
                                </a>
                            </li>
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail for permissions --}}
                    @endtry
                @endif

                {{-- Quick Actions for Staff --}}
                @if(isset($currentUser) && $currentUser)
                    @try
                        @if((method_exists($currentUser, 'hasRole') && ($currentUser->hasRole('server') || $currentUser->hasRole('cashier'))))
                            <li>
                                <button onclick="quickOrderModal()"
                                    class="w-full text-left flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors bg-transparent text-white border-white hover:bg-white/10">
                                    <i class="fas fa-plus-circle"></i>
                                    <span class="font-medium">Quick Order</span>
                                </button>
                            </li>
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail for role check --}}
                    @endtry
                @endif

                {{-- Emergency KOT Reset (Admins only) --}}
                @if(isset($currentUser) && $currentUser)
                    @try
                        @if(method_exists($currentUser, 'hasRole') && ($currentUser->hasRole('Organization Administrator') || $currentUser->hasRole('Branch Administrator')))
                            <li>
                                <button onclick="emergencyKOTReset()"
                                    class="w-full text-left flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors bg-red-600/20 text-red-200 border-red-400 hover:bg-red-600/30">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="font-medium">Emergency Reset</span>
                                </button>
                            </li>
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail for role check --}}
                    @endtry
                @endif

                {{-- Logout --}}
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
                            @foreach($menuItems['modules'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Kitchen Section --}}
                @if(isset($menuItems['kitchen']) && count($menuItems['kitchen']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Kitchen</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['kitchen'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Service Section --}}
                @if(isset($menuItems['service']) && count($menuItems['service']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Service</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['service'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Inventory Section --}}
                @if(isset($menuItems['inventory']) && count($menuItems['inventory']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Inventory</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['inventory'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Admin Section --}}
                @if(isset($menuItems['admin']) && count($menuItems['admin']) > 0)
                    <div class="mb-6">
                        <h3 class="text-xs uppercase font-semibold text-indigo-300 mb-3 px-2">Administration</h3>
                        <ul class="space-y-2">
                            @foreach($menuItems['admin'] as $item)
                                @include('components.sidebar.menu-item', ['item' => $item])
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sticky bottom section --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            <ul class="space-y-2">
                {{-- Digital Menu --}}
                <li>
                    <a href="{{ route('admin.digital-menu.index') }}"
                        class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                        {{ request()->routeIs('admin.digital-menu*')
                            ? 'bg-white text-gray-700 border-white'
                            : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                        @include('partials.icons.menu')
                        <span class="font-medium">Digital Menu</span>
                    </a>
                </li>

                {{-- Settings --}}
                <li>
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
                        {{ request()->routeIs('admin.settings*')
                            ? 'bg-white text-gray-700 border-white'
                            : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                        @include('partials.icons.settings')
                        <span class="font-medium">Settings</span>
                    </a>
                </li>

                {{-- Logout --}}
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

{{-- Real-time status indicators and enhanced sidebar functionality --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize real-time updates via WebSocket/Broadcasting if available
    @if(config('broadcasting.default') !== 'log' && auth()->id())
    if (typeof Echo !== 'undefined') {
        const userId = {{ auth()->id() ?? 0 }};
        const branchId = {{ auth()->user()?->branch_id ?? 0 }};
        
        // Low stock alerts for inventory managers
        @if(auth()->user() && auth()->user()->can('view_inventory'))
        Echo.private(`inventory-alerts.${userId}`)
            .listen('LowStockAlert', (e) => {
                updateBadge('inventory-items-badge', e.count);
                showToast('warning', `${e.count} items are running low on stock`, 'inventory-alert');
            })
            .listen('OutOfStockAlert', (e) => {
                updateBadge('stock-alerts-badge', e.count);
                showToast('error', `${e.count} items are out of stock!`, 'stock-alert');
            });
        @endif

        // Kitchen order alerts for kitchen staff
        @if(auth()->user() && auth()->user()->can('view-kitchen-orders'))
        Echo.private(`kitchen-updates.${branchId}`)
            .listen('NewKOTAlert', (e) => {
                updateBadge('kitchen-orders-badge', e.count);
                playNotificationSound();
                showToast('info', `New order #${e.order_number} received`, 'kot-alert');
            })
            .listen('OrderReadyAlert', (e) => {
                showToast('success', `Order #${e.order_number} is ready for pickup`, 'order-ready');
            });
        @endif

        // Reservation alerts for hosts/servers
        @if(auth()->user() && auth()->user()->can('manage-reservations'))
        Echo.private(`reservation-updates.${branchId}`)
            .listen('ReservationAlert', (e) => {
                updateBadge('reservations-badge', e.count);
                if (e.type === 'upcoming') {
                    showToast('info', `Upcoming reservation: ${e.customer_name} in ${e.minutes} minutes`, 'reservation-reminder');
                }
            });
        @endif

        // Payment alerts for cashiers
        @if(auth()->user() && auth()->user()->can('process-payments'))
        Echo.private(`payment-updates.${branchId}`)
            .listen('PendingPaymentAlert', (e) => {
                updateBadge('payments-badge', e.count);
                showToast('warning', `Table ${e.table_number} is ready for payment`, 'payment-alert');
            });
        @endif

        // Production alerts for production managers
        @if(auth()->user() && auth()->user()->can('view_production'))
        Echo.private(`production-updates.${branchId}`)
            .listen('ProductionRequestAlert', (e) => {
                updateBadge('production-requests-badge', e.count);
                showToast('info', `New production request: ${e.item_name}`, 'production-alert');
            });
        @endif
    }
    @endif

    // Collapsible submenus with enhanced animations
    document.querySelectorAll('.menu-item-with-submenu').forEach(item => {
        const toggle = item.querySelector('.submenu-toggle');
        const submenu = item.querySelector('.submenu');
        
        if (toggle && submenu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                const isHidden = submenu.classList.contains('hidden');
                
                // Close all other submenus
                document.querySelectorAll('.submenu').forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.add('hidden');
                        const otherIcon = menu.parentElement.querySelector('.submenu-icon');
                        if (otherIcon) otherIcon.classList.remove('rotate-180');
                    }
                });
                
                // Toggle current submenu
                submenu.classList.toggle('hidden');
                
                const icon = toggle.querySelector('.submenu-icon');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
                
                // Add smooth animation
                if (!isHidden) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    setTimeout(() => {
                        submenu.style.maxHeight = '0px';
                    }, 10);
                } else {
                    submenu.style.maxHeight = '0px';
                    setTimeout(() => {
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }, 10);
                }
            });
        }
    });

    // Auto-refresh badge counts every 30 seconds
    setInterval(function() {
        refreshBadgeCounts();
    }, 30000);

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('translate-x-0');
        });
    }

    // Auto-hide mobile sidebar on route change
    window.addEventListener('beforeunload', function() {
        if (window.innerWidth < 1024 && sidebar) {
            sidebar.classList.add('-translate-x-full');
        }
    });
});

// Helper functions
function updateBadge(badgeId, count) {
    const badge = document.getElementById(badgeId);
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
        
        // Add pulse animation for new alerts
        if (count > 0) {
            badge.classList.add('animate-pulse');
            setTimeout(() => badge.classList.remove('animate-pulse'), 2000);
        }
    }
}

function showToast(type, message, id = null) {
    // Remove existing toast with same ID
    if (id) {
        const existing = document.getElementById(`toast-${id}`);
        if (existing) existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = id ? `toast-${id}` : `toast-${Date.now()}`;
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-transform duration-300 translate-x-full ${getToastClasses(type)}`;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas ${getToastIcon(type)}"></i>
            <span class="font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

function getToastClasses(type) {
    const classes = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'warning': 'bg-yellow-500 text-yellow-900',
        'info': 'bg-blue-500 text-white'
    };
    return classes[type] || classes['info'];
}

function getToastIcon(type) {
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    return icons[type] || icons['info'];
}

function playNotificationSound() {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Fallback to system beep
            console.beep?.();
        });
    } catch (e) {
        // Silent fail
    }
}

function refreshBadgeCounts() {
    fetch('/api/sidebar/badge-counts')
        .then(response => response.json())
        .then(data => {
            Object.entries(data).forEach(([badgeId, count]) => {
                updateBadge(badgeId, count);
            });
        })
        .catch(() => {
            // Silent fail
        });
}

// Quick action functions
function quickOrderModal() {
    // Implementation for quick order modal
    console.log('Quick order modal would open here');
}

function emergencyKOTReset() {
    if (confirm('Are you sure you want to reset all pending KOTs? This action cannot be undone.')) {
        fetch('/api/kitchen/emergency-reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'KOT system has been reset successfully');
                location.reload();
            } else {
                showToast('error', 'Failed to reset KOT system');
            }
        })
        .catch(() => {
            showToast('error', 'Network error occurred');
        });
    }
}

function toggleLogoutModal() {
    // Existing logout modal functionality
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.classList.toggle('hidden');
    }
}

// Keyboard shortcuts for staff efficiency
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'q': // Quick order
                e.preventDefault();
                if (typeof quickOrderModal === 'function') {
                    quickOrderModal();
                }
                break;
            case 'k': // Kitchen view
                @if(isset($currentUser) && $currentUser)
                    @try
                        @if(method_exists($currentUser, 'can') && $currentUser->can('view-kitchen-orders'))
                            e.preventDefault();
                            @if(\Illuminate\Support\Facades\Route::has('kitchen.orders.index'))
                                window.location.href = "{{ route('kitchen.orders.index') }}";
                            @endif
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail --}}
                    @endtry
                @endif
                break;
            case 'r': // Reservations
                @if(isset($currentUser) && $currentUser)
                    @try
                        @if(method_exists($currentUser, 'can') && $currentUser->can('manage-reservations'))
                            e.preventDefault();
                            @if(\Illuminate\Support\Facades\Route::has('reservations.index'))
                                window.location.href = "{{ route('reservations.index') }}";
                            @endif
                        @endif
                    @catch(\Exception $e)
                        {{-- Silent fail --}}
                    @endtry
                @endif
                break;
        }
    }
});

// Service worker registration for push notifications
if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/sw.js')
        .then(registration => {
            console.log('Service Worker registered');
        })
        .catch(error => {
            console.log('Service Worker registration failed');
        });
}
</script>
@endpush
