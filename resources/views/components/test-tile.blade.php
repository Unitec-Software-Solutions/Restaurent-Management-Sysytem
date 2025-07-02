@props(['label', 'route', 'icon' => 'fa-link', 'disabled' => false])

<div class="relative group test-tile-container">
    @if($isClickable())
        <a href="{{ $url }}" 
           class="block w-full p-4 {{ $cardClass }} rounded-lg shadow-sm border transition-all duration-200">
    @else
        <div class="block w-full p-4 {{ $cardClass }} rounded-lg shadow-sm border">
    @endif
        
        <!-- Card Header with Icon and Status following UI/UX patterns -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center flex-1 min-w-0">
                <!-- Icon Container with consistent styling -->
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg transition-colors duration-200">
                    <i class="fas {{ $icon }} text-lg {{ $iconClass }} transition-colors duration-200"></i>
                </div>
                
                <!-- Label Section with proper truncation -->
                <div class="ml-3 flex-1 min-w-0">
                    <h3 class="text-sm font-medium {{ $textClass }} leading-tight truncate transition-colors duration-200">
                        {{ $label }}
                    </h3>
                </div>
            </div>
            
            <!-- Status Badge following UI/UX color palette -->
            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $this->getStatusClass() }} flex-shrink-0 transition-all duration-200">
                {{ $statusBadge }}
            </span>
        </div>
        
        <!-- Route Information with monospace font -->
        <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 font-mono truncate" title="{{ $route }}">
                {{ $route }}
            </p>
        </div>
        
        <!-- System Status Indicator for Complex Routes -->
        @if(str_contains($route, 'admin') || str_contains($route, 'inventory') || str_contains($route, 'order') || str_contains($route, 'menu'))
            <div class="mt-2 pt-2 border-t border-gray-50">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Backend:</span>
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 rounded-full {{ $hasSystemIntegrity ? 'bg-green-500' : 'bg-red-500' }} transition-colors duration-200"></div>
                        <i class="fas fa-database text-xs {{ $hasSystemIntegrity ? 'text-green-500' : 'text-red-500' }} transition-colors duration-200"></i>
                        <span class="font-medium {{ $hasSystemIntegrity ? 'text-green-600' : 'text-red-600' }} transition-colors duration-200">
                            {{ $hasSystemIntegrity ? 'Ready' : 'Error' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Interactive Elements for Available Routes -->
        @if($isClickable())
            <!-- Hover Border Effect following animation guidelines -->
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-indigo-300 rounded-lg transition-all duration-200 pointer-events-none"></div>
            
            <!-- Navigation Indicator -->
            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <i class="fas fa-external-link-alt text-indigo-500 text-xs"></i>
            </div>
            
            <!-- Success Glow Effect -->
            <div class="absolute inset-0 rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-200 pointer-events-none bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        @endif
        
        <!-- Disabled State Overlay -->
        @if($disabled || !$available || !$hasSystemIntegrity)
            <div class="absolute inset-0 rounded-lg bg-gray-50 bg-opacity-50 pointer-events-none"></div>
        @endif
    
    @if($isClickable())
        </a>
    @else
        </div>
    @endif
</div>

<!-- System Modules Management -->
<div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
    <div class="border-b border-blue-200 pb-4 mb-6">
        <h2 class="text-xl font-semibold text-blue-700 flex items-center">
            <i class="fas fa-puzzle-piece mr-2"></i>
            System Modules Management
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            Modular system architecture with role-based permissions and subscription integration.
        </p>
        
        <!-- Real-time Module Status -->
        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-blue-700">{{ App\Models\Module::where('is_active', true)->count() }} Active</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                    <span class="text-blue-700">{{ App\Models\Module::where('is_active', false)->count() }} Inactive</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="text-blue-700">{{ App\Models\Module::sum(DB::raw('json_array_length(permissions)')) }} Permissions</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                    <span class="text-blue-700">{{ App\Models\Module::getCoreModules()->count() }} Core Modules</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Modules Grid -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Core System Modules</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <x-test-tile label="Dashboard" route="dashboard" icon="fa-tachometer-alt" />
            <x-test-tile label="Inventory" route="inventory.index" icon="fa-boxes" />
            <x-test-tile label="Orders" route="orders.index" icon="fa-shopping-cart" />
            <x-test-tile label="Kitchen" route="kitchen.index" icon="fa-fire" />
            <x-test-tile label="Reservations" route="reservations.index" icon="fa-calendar-check" />
            <x-test-tile label="Reports" route="reports.index" icon="fa-chart-bar" />
        </div>
    </div>

    <!-- Extended Modules Grid -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Extended Modules</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <x-test-tile label="Menu Management" route="menu.index" icon="fa-utensils" />
            <x-test-tile label="Staff Management" route="staff.index" icon="fa-user-tie" />
            <x-test-tile label="Customer Management" route="customers.index" icon="fa-users" />
            <x-test-tile label="Supplier Management" route="suppliers.index" icon="fa-truck" />
            <x-test-tile label="Table Management" route="tables.index" icon="fa-chair" />
            <x-test-tile label="POS System" route="pos.index" icon="fa-cash-register" />
            <x-test-tile label="Financial Management" route="finance.index" icon="fa-dollar-sign" />
            
        </div>
    </div>

    <!-- Administration Modules -->
    <div>
        <h3 class="text-lg font-medium text-gray-800 mb-4">System Administration</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            <x-test-tile label="Module Management" route="admin.modules.index" icon="fa-puzzle-piece" />
            <x-test-tile label="Roles & Permissions" route="admin.roles.index" icon="fa-user-shield" />
            <x-test-tile label="User Management" route="admin.users.index" icon="fa-user-cog" />
            <x-test-tile label="Organizations" route="admin.organizations.index" icon="fa-building" />
            <x-test-tile label="Branches" route="admin.branches.index" icon="fa-code-branch" />
            <x-test-tile label="Subscriptions" route="admin.subscriptions.index" icon="fa-credit-card" />
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
/* Enhanced hover effects following UI/UX guidelines */
.test-tile-container {
    transition: transform 0.2s ease;
}

.test-tile-container:hover {
    transform: translateY(-1px);
}

/* Card transitions with subtle spring effect */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Status badge animations */
.bg-green-100 {
    position: relative;
    overflow: hidden;
}

.bg-green-100::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    50% { left: 100%; }
    100% { left: 100%; }
}

/* Icon hover effects */
.group:hover .fas {
    transform: scale(1.1);
}

/* Responsive design adjustments */
@media (max-width: 768px) {
    .test-tile-container {
        margin-bottom: 0.5rem;
    }
    
    .test-tile-container h3 {
        font-size: 0.875rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .bg-white {
        background-color: #1f2937;
        border-color: #374151;
    }
    
    .text-gray-900 {
        color: #f9fafb;
    }
    
    .text-gray-500 {
        color: #9ca3af;
    }
}

/* Focus states for accessibility */
.test-tile-container a:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}

/* Loading state animation */
.test-tile-container.loading {
    pointer-events: none;
}

.test-tile-container.loading .fas {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endPushOnce
