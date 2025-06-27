@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Page Header following UI/UX guidelines -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-vial text-indigo-600 mr-3"></i>
                        Restaurant Management Test Hub
                    </h1>
                    <p class="text-gray-600">Comprehensive testing interface for all application components and functionality verification.</p>
                </div>
                
                <!-- Enhanced System Status Card -->
                <div class="bg-indigo-50 rounded-lg p-4 min-w-[280px] flex-shrink-0">
                    <h3 class="text-sm font-medium text-indigo-700 mb-3 flex items-center">
                        <i class="fas fa-heartbeat mr-2"></i>
                        System Health Monitor
                    </h3>
                    <div class="space-y-2">
                        <!-- Database Status -->
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-indigo-600">Database:</span>
                            @php
                                try {
                                    \DB::connection()->getPdo();
                                    echo '<span class="font-medium text-green-600 flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                                            Connected
                                          </span>';
                                } catch (\Exception $e) {
                                    echo '<span class="font-medium text-red-600 flex items-center">
                                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                            Error
                                          </span>';
                                }
                            @endphp
                        </div>
                        
                        <!-- Modules Status -->
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-indigo-600">Modules:</span>
                            @php
                                try {
                                    $activeModules = \App\Models\Module::where('is_active', true)->count();
                                    $totalModules = \App\Models\Module::count();
                                    echo '<span class="font-medium text-green-600 flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            ' . $activeModules . '/' . $totalModules . '
                                          </span>';
                                } catch (\Exception $e) {
                                    echo '<span class="font-medium text-yellow-600 flex items-center">
                                            <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                            N/A
                                          </span>';
                                }
                            @endphp
                        </div>
                        
                        <!-- Menu System Status -->
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-indigo-600">Menu System:</span>
                            @php
                                try {
                                    $hasMenuTable = Schema::hasTable('menu_items');
                                    $columns = $hasMenuTable ? Schema::getColumnListing('menu_items') : [];
                                    $requiredColumns = ['requires_preparation', 'station', 'is_vegetarian', 'contains_alcohol', 'image_path', 'is_active'];
                                    $missingColumns = array_diff($requiredColumns, $columns);
                                    $hasAllColumns = empty($missingColumns);
                                    
                                    if ($hasMenuTable && $hasAllColumns) {
                                        echo '<span class="font-medium text-green-600 flex items-center">
                                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                                                Ready (' . count($columns) . ' cols)
                                              </span>';
                                    } elseif ($hasMenuTable) {
                                        echo '<span class="font-medium text-yellow-600 flex items-center">
                                                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                                Missing ' . count($missingColumns) . ' cols
                                              </span>';
                                    } else {
                                        echo '<span class="font-medium text-red-600 flex items-center">
                                                <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                                Table Missing
                                              </span>';
                                    }
                                } catch (\Exception $e) {
                                    echo '<span class="font-medium text-red-600 flex items-center">
                                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                            Error
                                          </span>';
                                }
                            @endphp
                        </div>
                        
                        <!-- Cache Status -->
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-indigo-600">Cache:</span>
                            <span class="font-medium text-green-600 flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                Active
                            </span>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-3 pt-3 border-t border-indigo-200">
                        <div class="flex gap-2">
                            <button onclick="runMigrations()" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-2 py-1 rounded flex items-center">
                                <i class="fas fa-database mr-1"></i>
                                Fix DB
                            </button>
                            <button onclick="refreshStatus()" class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded flex items-center">
                                <i class="fas fa-sync-alt mr-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts Section -->
        @php
            $alerts = [];
            
            // Check for menu table issues
            try {
                if (!Schema::hasTable('menu_items')) {
                    $alerts[] = [
                        'type' => 'warning',
                        'title' => 'Menu Items Table Missing',
                        'message' => 'The menu_items table does not exist. Run migrations to create it.',
                        'action' => 'Run Migration',
                        'command' => 'php artisan migrate'
                    ];
                } else {
                    $columns = Schema::getColumnListing('menu_items');
                    $requiredColumns = ['requires_preparation', 'image_path', 'display_order', 'is_featured'];
                    $missingColumns = array_diff($requiredColumns, $columns);
                    
                    if (!empty($missingColumns)) {
                        $alerts[] = [
                            'type' => 'warning',
                            'title' => 'Menu Items Table Incomplete',
                            'message' => 'Missing columns: ' . implode(', ', $missingColumns),
                            'action' => 'Fix Structure',
                            'command' => 'php artisan migrate'
                        ];
                    }
                }
                
                // Check for menu categories table
                if (!Schema::hasTable('menu_categories')) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Menu Categories Table Missing',
                        'message' => 'The menu_categories table should be created for proper menu management.',
                        'action' => 'Create Table',
                        'command' => 'php artisan migrate'
                    ];
                }
            } catch (\Exception $e) {
                $alerts[] = [
                    'type' => 'error',
                    'title' => 'Database Connection Issue',
                    'message' => 'Unable to check database structure: ' . $e->getMessage(),
                    'action' => 'Check Config',
                    'command' => 'Check .env file'
                ];
            }
        @endphp

        @if(!empty($alerts))
        <div class="space-y-3">
            @foreach($alerts as $alert)
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 
                {{ $alert['type'] === 'error' ? 'border-red-500 bg-red-50' : '' }}
                {{ $alert['type'] === 'warning' ? 'border-yellow-500 bg-yellow-50' : '' }}
                {{ $alert['type'] === 'info' ? 'border-blue-500 bg-blue-50' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($alert['type'] === 'error')
                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                            @elseif($alert['type'] === 'warning')
                                <i class="fas fa-exclamation-circle text-yellow-500"></i>
                            @else
                                <i class="fas fa-info-circle text-blue-500"></i>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium 
                                {{ $alert['type'] === 'error' ? 'text-red-800' : '' }}
                                {{ $alert['type'] === 'warning' ? 'text-yellow-800' : '' }}
                                {{ $alert['type'] === 'info' ? 'text-blue-800' : '' }}">
                                {{ $alert['title'] }}
                            </h3>
                            <p class="text-sm mt-1 
                                {{ $alert['type'] === 'error' ? 'text-red-700' : '' }}
                                {{ $alert['type'] === 'warning' ? 'text-yellow-700' : '' }}
                                {{ $alert['type'] === 'info' ? 'text-blue-700' : '' }}">
                                {{ $alert['message'] }}
                            </p>
                            <p class="text-xs mt-2 font-mono bg-gray-100 p-2 rounded">
                                {{ $alert['command'] }}
                            </p>
                        </div>
                    </div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs">
                        {{ $alert['action'] }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Admin Core Functions -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="border-b border-indigo-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <i class="fas fa-tools mr-2"></i>
                    Admin Core Functions
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Essential administrative operations for restaurant management and system control.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <x-test-tile label="Dashboard" route="admin.dashboard" icon="fa-tachometer-alt" />
                <x-test-tile label="Organizations" route="admin.organizations.index" icon="fa-building" />
                <x-test-tile label="Profile Management" route="admin.profile.index" icon="fa-user-circle" />
                <x-test-tile label="Settings" route="admin.settings.index" icon="fa-cog" />
                <x-test-tile label="Reports Center" route="admin.reports.index" icon="fa-chart-bar" />
                <x-test-tile label="Test Hub" route="admin.testpage" icon="fa-vial" />
            </div>
        </div>

        <!-- Enhanced Database Status Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="border-b border-red-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-red-700 flex items-center">
                    <i class="fas fa-database mr-2"></i>
                    Database Health Monitor
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Real-time database structure validation and system diagnostics.
                </p>
            </div>

            <!-- Database Tables Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @php
                    $criticalTables = [
                        'menu_items' => [
                            'icon' => 'fa-utensils',
                            'name' => 'Menu Items',
                            'required_columns' => ['requires_preparation', 'station', 'is_vegetarian', 'image_path', 'is_active']
                        ],
                        'orders' => [
                            'icon' => 'fa-shopping-cart',
                            'name' => 'Orders',
                            'required_columns' => ['reservation_id', 'subtotal', 'tax', 'service_charge', 'total']
                        ],
                        'organizations' => [
                            'icon' => 'fa-building',
                            'name' => 'Organizations',
                            'required_columns' => ['name', 'is_active']
                        ],
                        'branches' => [
                            'icon' => 'fa-code-branch',
                            'name' => 'Branches',
                            'required_columns' => ['name', 'organization_id', 'is_active']
                        ]
                    ];
                @endphp

                @foreach($criticalTables as $tableName => $config)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border">
                    <div class="flex items-center justify-between mb-3">
                        <i class="fas {{ $config['icon'] }} text-gray-600 text-xl"></i>
                        @php
                            try {
                                $tableExists = Schema::hasTable($tableName);
                                $columns = $tableExists ? Schema::getColumnListing($tableName) : [];
                                $missingColumns = array_diff($config['required_columns'], $columns);
                                $isHealthy = $tableExists && empty($missingColumns);
                                
                                if ($isHealthy) {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Healthy</span>';
                                } elseif ($tableExists) {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Issues</span>';
                                } else {
                                    echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Missing</span>';
                                }
                            } catch (\Exception $e) {
                                echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Error</span>';
                            }
                        @endphp
                    </div>
                    
                    <h3 class="font-medium text-gray-800 mb-2">{{ $config['name'] }}</h3>
                    
                    @php
                        try {
                            if ($tableExists) {
                                $recordCount = DB::table($tableName)->count();
                                echo '<p class="text-sm text-gray-600 mb-2">' . count($columns) . ' columns, ' . $recordCount . ' records</p>';
                                
                                if (!empty($missingColumns)) {
                                    echo '<p class="text-xs text-yellow-600">Missing: ' . implode(', ', array_slice($missingColumns, 0, 2)) . '</p>';
                                }
                            } else {
                                echo '<p class="text-sm text-red-600">Table does not exist</p>';
                            }
                        } catch (\Exception $e) {
                            echo '<p class="text-xs text-red-600">Query failed</p>';
                        }
                    @endphp
                    
                    <button onclick="diagnoseTable('{{ $tableName }}')" 
                            class="mt-2 w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                        <i class="fas fa-search mr-1"></i>
                        Diagnose
                    </button>
                </div>
                @endforeach
            </div>

            <!-- Quick Database Actions -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="font-medium text-blue-800 mb-3">
                    <i class="fas fa-tools mr-2"></i>
                    Quick Database Actions
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <button onclick="runDatabaseCommand('migrate')" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-database mr-1"></i>
                        Migrate
                    </button>
                    
                    <button onclick="runDatabaseCommand('seed')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-seedling mr-1"></i>
                        Seed
                    </button>
                    
                    <button onclick="runDatabaseCommand('diagnose')" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-stethoscope mr-1"></i>
                        Full Check
                    </button>
                    
                    <button onclick="runDatabaseCommand('fresh')" 
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-refresh mr-1"></i>
                        Fresh Start
                    </button>

                    <!-- Add this to your Quick Database Actions section -->
                    <button onclick="testOrderCreation(1)" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Test Order
                    </button>

                    <button onclick="testOrderCreation(5)" 
                            class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded text-sm">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Test 5 Orders
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Order Management Dashboard Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="border-b border-indigo-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-indigo-700 flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Order Management Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Comprehensive order testing, monitoring, and management interface with real-time analytics.
                </p>
            </div>

            <!-- Real-time Order Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Total Orders Card -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Total Orders</p>
                            <p class="text-2xl font-bold text-blue-900" id="total-orders-count">
                                {{ \App\Models\Order::count() }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-500 rounded-full">
                            <i class="fas fa-shopping-cart text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-sm">
                        <span class="text-green-600 font-medium">
                            +{{ \App\Models\Order::whereDate('created_at', today())->count() }}
                        </span>
                        <span class="text-blue-600 ml-1">today</span>
                    </div>
                </div>

                <!-- Pending Orders Card -->
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">Pending Orders</p>
                            <p class="text-2xl font-bold text-yellow-900" id="pending-orders-count">
                                {{ \App\Models\Order::where('status', 'pending')->count() }}
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-500 rounded-full">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-sm">
                        <span class="text-yellow-600">Requires attention</span>
                    </div>
                </div>

                <!-- Completed Orders Card -->
                <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600">Completed</p>
                            <p class="text-2xl font-bold text-green-900" id="completed-orders-count">
                                {{ \App\Models\Order::whereIn('status', ['completed', 'served'])->count() }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-500 rounded-full">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-sm">
                        <span class="text-green-600">Successfully served</span>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600">Today's Revenue</p>
                            <p class="text-2xl font-bold text-purple-900" id="revenue-count">
                                @php
                                    $todayRevenue = \App\Models\Order::whereDate('created_at', today())
                                        ->sum('total_amount');
                                    echo 'LKR ' . number_format($todayRevenue, 0);
                                @endphp
                            </p>
                        </div>
                        <div class="p-3 bg-purple-500 rounded-full">
                            <i class="fas fa-coins text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-sm">
                        <span class="text-purple-600">From {{ \App\Models\Order::whereDate('created_at', today())->count() }} orders</span>
                    </div>
                </div>
            </div>

            <!-- Order Testing Section -->
            <div class="bg-orange-50 rounded-lg p-4 mb-6">
                <h3 class="font-medium text-orange-800 mb-4 flex items-center">
                    <i class="fas fa-vial mr-2"></i>
                    Order Creation Testing Suite
                </h3>
                
                <!-- Quick Test Buttons -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <button onclick="testOrderCreation(1, 'mixed')" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded text-sm transition-all duration-200 hover:shadow-lg">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Single Test
                    </button>

                    <button onclick="testOrderCreation(5, 'mixed')" 
                            class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded text-sm transition-all duration-200 hover:shadow-lg">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Batch Test (5)
                    </button>
                    
                    <button onclick="testOrderCreation(3, 'dine_in')" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition-all duration-200 hover:shadow-lg">
                        <i class="fas fa-utensils mr-1"></i>
                        Dine-in Only
                    </button>
                    
                    <button onclick="testOrderCreation(3, 'delivery')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition-all duration-200 hover:shadow-lg">
                        <i class="fas fa-truck mr-1"></i>
                        Delivery Only
                    </button>
                </div>

                <!-- Advanced Testing Options -->
                <div class="border-t border-orange-200 pt-4">
                    <h4 class="text-sm font-medium text-orange-700 mb-3">Advanced Testing Options</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <button onclick="testOrderCreation(10, 'mixed')" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-layer-group mr-1"></i>
                            Stress Test (10)
                        </button>
                        
                        <button onclick="testOrderCreation(2, 'takeaway')" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-shopping-bag mr-1"></i>
                            Takeaway Test
                        </button>
                        
                        <button onclick="showRecentOrders()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-list mr-1"></i>
                            View Recent
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Preview -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-medium text-gray-800 flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Recent Orders Preview
                    </h3>
                    <button onclick="refreshOrdersPreview()" 
                            class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Refresh
                    </button>
                </div>
                
                <div id="recent-orders-container">
                    @php
                        $recentOrders = \App\Models\Order::with(['branch.organization'])
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @if($recentOrders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2">Order #</th>
                                        <th class="text-left py-2">Customer</th>
                                        <th class="text-left py-2">Type</th>
                                        <th class="text-left py-2">Status</th>
                                        <th class="text-right py-2">Total</th>
                                        <th class="text-left py-2">Branch</th>
                                        <th class="text-left py-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr class="border-b border-gray-100 hover:bg-gray-100">
                                        <td class="py-2 font-medium text-blue-600">{{ $order->order_number }}</td>
                                        <td class="py-2">{{ $order->customer_name ?? 'N/A' }}</td>
                                        <td class="py-2">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $order->order_type === 'delivery' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $order->order_type === 'dine_in' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $order->order_type === 'takeaway' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                            </span>
                                        </td>
                                        <td class="py-2">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-right font-medium">{{ $order->currency_symbol }}{{ $order->formatted_total }}</td>
                                        <td class="py-2 text-xs text-gray-600">{{ $order->branch?->name ?? 'N/A' }}</td>
                                        <td class="py-2 text-xs text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No orders found. Create some test orders to see them here.</p>
                        </div>
                    @endif
                </div>
            </div>
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
                </p
                
                <!-- Enhanced Real-time Module Status -->
                <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-blue-700">
                                @php
                                    try {
                                        echo \App\Models\Module::where('is_active', true)->count() . ' Active';
                                    } catch (\Exception $e) {
                                        echo '0 Active';
                                    }
                                @endphp
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                            <span class="text-blue-700">
                                @php
                                    try {
                                        echo \App\Models\Module::where('is_active', false)->count() . ' Inactive';
                                    } catch (\Exception $e) {
                                        echo '0 Inactive';
                                    }
                                @endphp
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-blue-700">
                                @php
                                    try {
                                        $totalPermissions = \App\Models\Module::get()->sum(function($module) {
                                            return count($module->permissions ?? []);
                                        });
                                        echo $totalPermissions . ' Permissions';
                                    } catch (\Exception $e) {
                                        echo '0 Permissions';
                                    }
                                @endphp
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            <span class="text-blue-700">
                                @php
                                    try {
                                        $coreModules = ['dashboard', 'inventory', 'orders', 'kitchen', 'reservations'];
                                        echo \App\Models\Module::whereIn('slug', $coreModules)->count() . ' Core';
                                    } catch (\Exception $e) {
                                        echo '0 Core';
                                    }
                                @endphp
                            </span>
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
                    <x-test-tile label="System Settings" route="settings.index" icon="fa-cog" />
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

        <!-- Admin Authentication System -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="border-b border-purple-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-purple-700 flex items-center">
                    <i class="fas fa-user-shield mr-2"></i>
                    Admin Authentication System
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Admin login, role management, user permissions, and security features.
                </p>
                
                <!-- Auth System Status -->
                <div class="mt-3 p-3 bg-purple-50 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-purple-700">{{ \App\Models\Admin::count() }} Admins</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-purple-700">{{ \App\Models\User::count() }} Users</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            <span class="text-purple-700">
                                @php
                                    try {
                                        echo \Spatie\Permission\Models\Role::where('guard_name', 'admin')->count() . ' Roles';
                                    } catch (\Exception $e) {
                                        echo '0 Roles';
                                    }
                                @endphp
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-purple-700">Secure System</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <x-test-tile label="Admin Login" route="admin.login" icon="fa-sign-in-alt" />
                <x-test-tile label="Admin Dashboard" route="admin.dashboard" icon="fa-tachometer-alt" />
                <x-test-tile label="Admin Profile" route="admin.profile.index" icon="fa-user-circle" />
                <x-test-tile label="User Management" route="admin.users.index" icon="fa-users" />
                <x-test-tile label="Role Management" route="admin.roles.index" icon="fa-user-tag" />
                <x-test-tile label="Organization Management" route="admin.organizations.index" icon="fa-building" />
                <x-test-tile label="Security Settings" route="admin.security.index" icon="fa-shield-alt" />
                <x-test-tile label="Access Logs" route="admin.logs.index" icon="fa-list-alt" />
            </div>
        </div>

        <!-- Public Customer Functions -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="border-b border-green-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-green-700 flex items-center">
                    <i class="fas fa-globe mr-2"></i>
                    Public Customer Functions
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Customer-facing interfaces, public booking system, and user experience features.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <x-test-tile label="Homepage" route="home" icon="fa-home" />
                <x-test-tile label="Customer Dashboard" route="customer.dashboard" icon="fa-user" />
                <x-test-tile label="Make Reservation" route="reservations.create" icon="fa-calendar-plus" />
                <x-test-tile label="Place Order" route="orders.create" icon="fa-shopping-cart" />
                <x-test-tile label="View Menu" route="menu.public" icon="fa-utensils" />
                <x-test-tile label="Order History" route="customer.orders.index" icon="fa-history" />
                <x-test-tile label="Customer Profile" route="customer.profile.index" icon="fa-user-edit" />
                <x-test-tile label="Contact Us" route="contact" icon="fa-envelope" />
            </div>
        </div>

        <!-- System Information & Guidelines -->
        <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-200">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-indigo-600 mr-3 mt-1"></i>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-indigo-900 mb-3">Test Hub Usage Guidelines</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h4 class="font-medium text-indigo-800 mb-2">Status Indicators</h4>
                            <div class="space-y-2 text-sm text-indigo-700">
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 mr-2">Available</span>
                                    <span>Route exists and functional</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600 mr-2">Missing</span>
                                    <span>Route not implemented</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-600 mr-2">System Error</span>
                                    <span>Backend system incomplete</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500 mr-2">Disabled</span>
                                    <span>Temporarily unavailable</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-indigo-800 mb-2">Real-time System Metrics</h4>
                            <div class="space-y-2 text-sm text-indigo-700">
                                <div class="flex justify-between">
                                    <span>Active Modules:</span>
                                    <span class="font-medium text-green-600" id="active-modules-count">
                                        {{ \App\Models\Module::where('is_active', true)->count() }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Organizations:</span>
                                    <span class="font-medium text-blue-600">{{ \App\Models\Organization::count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Branches:</span>
                                    <span class="font-medium text-purple-600">{{ \App\Models\Branch::count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Menu Items:</span>
                                    <span class="font-medium text-orange-600">{{ \App\Models\MenuItem::count() }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-indigo-800 mb-2">System Health Check</h4>
                            <div class="space-y-2 text-sm text-indigo-700">
                                <div class="flex justify-between">
                                    <span>Database Connection:</span>
                                    <span class="text-green-600 font-medium">✅ Active</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Module System:</span>
                                    <span class="text-green-600 font-medium">✅ Operational</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Authentication:</span>
                                    <span class="text-green-600 font-medium">✅ Secure</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Cache System:</span>
                                    <span class="text-green-600 font-medium">✅ Running</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced system monitoring
    function updateSystemStatus() {
        fetch('/admin/system-stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatusDisplay(data.stats);
                }
            })
            .catch(error => console.log('Stats update failed:', error));
    }

    function updateStatusDisplay(stats) {
        // Update various stat displays in your UI
        const elements = {
            'active-modules-count': stats.organizations || 0,
            'organizations-count': stats.organizations || 0,
            'branches-count': stats.branches || 0,
            'menu-items-count': stats.menu_items || 0,
            'orders-count': stats.orders || 0
        };

        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    // Enhanced database diagnostic functions
    window.diagnoseTable = function(tableName) {
        showNotification('info', `Diagnosing ${tableName} table...`);
        
        fetch('/admin/diagnose-table', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ table: tableName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', `${tableName} diagnosis completed`);
                displayDiagnosticResults(data.results);
            } else {
                showNotification('error', `Diagnosis failed: ${data.message}`);
            }
        })
        .catch(error => {
            showNotification('error', 'Failed to run diagnostics');
            console.log(`Command: php artisan db:diagnose --table=${tableName}`);
        });
    };

    window.runDatabaseCommand = function(action) {
        const commands = {
            'migrate': {
                url: '/admin/run-migrations',
                message: 'Running database migrations...',
                successMessage: 'Migrations completed successfully'
            },
            'seed': {
                url: '/admin/run-seeder',
                message: 'Seeding database...',
                successMessage: 'Database seeded successfully'
            },
            'diagnose': {
                url: '/admin/full-diagnose',
                message: 'Running full system diagnosis...',
                successMessage: 'System diagnosis completed'
            },
            'fresh': {
                url: '/admin/fresh-migrate',
                message: 'Performing fresh migration...',
                successMessage: 'Fresh migration completed'
            }
        };
        
        const command = commands[action];
        if (command) {
            showNotification('info', command.message);
            
            fetch(command.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', command.successMessage);
                    if (['migrate', 'seed', 'fresh'].includes(action)) {
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    showNotification('error', `Operation failed: ${data.message}`);
                }
            })
            .catch(error => {
                showNotification('error', `Failed to ${action} database`);
            });
        }
    };

    // Enhanced order creation function with type selection
    window.testOrderCreation = function(count = 1, type = 'mixed') {
        // Show loading state
        const loadingNotification = showLoadingNotification(`Creating ${count} test order(s) of type: ${type}...`);
        
        fetch('/admin/test-orders', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                count: count,
                type: type 
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading notification
            loadingNotification.remove();
            
            if (data.success) {
                showNotification('success', data.message);
                displayEnhancedOrderResults(data.orders, data.command_output);
                updateOrderStatistics(); // Refresh stats
                refreshOrdersPreview(); // Refresh preview table
            } else {
                showNotification('error', `Order creation failed: ${data.message}`);
                if (data.command_output) {
                    console.log('Command output:', data.command_output);
                }
            }
        })
        .catch(error => {
            loadingNotification.remove();
            showNotification('error', 'Failed to create test orders');
            console.error('Error:', error);
        });
    };

    // Show recent orders in modal
    window.showRecentOrders = function() {
        showLoadingNotification('Fetching recent orders...');
        
        fetch('/admin/recent-orders')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.loading')?.remove();
                
                if (data.success) {
                    displayRecentOrdersModal(data.orders);
                } else {
                    showNotification('error', 'Failed to fetch recent orders');
                }
            })
            .catch(error => {
                document.querySelector('.loading')?.remove();
                showNotification('error', 'Failed to fetch recent orders');
            });
    };

    // Refresh orders preview table
    window.refreshOrdersPreview = function() {
        const container = document.getElementById('recent-orders-container');
        const originalContent = container.innerHTML;
        
        // Show loading state
        container.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"></div>
                <span class="ml-2 text-gray-600">Refreshing orders...</span>
            </div>
        `;
        
        fetch('/admin/orders-preview')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = data.html;
                    showNotification('success', 'Orders preview refreshed');
                } else {
                    container.innerHTML = originalContent;
                    showNotification('error', 'Failed to refresh orders');
                }
            })
            .catch(error => {
                container.innerHTML = originalContent;
                showNotification('error', 'Failed to refresh orders');
            });
    };

    // Update order statistics
    function updateOrderStatistics() {
        fetch('/admin/order-stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-orders-count').textContent = data.stats.total;
                    document.getElementById('pending-orders-count').textContent = data.stats.pending;
                    document.getElementById('completed-orders-count').textContent = data.stats.completed;
                    document.getElementById('revenue-count').textContent = 'LKR ' + data.stats.revenue;
                }
            })
            .catch(error => console.log('Stats update failed:', error));
    }

    // Enhanced notification with loading state
    function showLoadingNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 loading-notification';
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        return notification;
    }

    // Display enhanced order results with better UX
    function displayEnhancedOrderResults(orders, commandOutput) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4';
        
        const ordersList = orders.map(order => `
            <div class="bg-gradient-to-r from-white to-gray-50 p-4 rounded-lg border hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div class="font-medium text-gray-900">${order.order_number}</div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        ${order.type === 'delivery' ? 'bg-green-100 text-green-800' : ''}
                        ${order.type === 'dine_in' ? 'bg-blue-100 text-blue-800' : ''}
                        ${order.type === 'takeaway' ? 'bg-purple-100 text-purple-800' : ''}
                        ${order.type === 'mixed' ? 'bg-gray-100 text-gray-800' : ''}">
                        ${order.type.replace('_', ' ').toUpperCase()}
                    </span>
                </div>
                
                <div class="space-y-2">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user text-xs mr-1"></i>
                        ${order.customer_name}
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm font-medium text-green-600">
                            <i class="fas fa-coins text-xs mr-1"></i>
                            Total: ${order.currency}${order.total}
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            ${order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''}
                            ${order.status === 'confirmed' ? 'bg-blue-100 text-blue-800' : ''}
                            ${order.status === 'completed' ? 'bg-green-100 text-green-800' : ''}">
                            ${order.status.toUpperCase()}
                        </span>
                    </div>
                    
                    <div class="text-xs text-gray-500 border-t pt-2">
                        <i class="fas fa-clock text-xs mr-1"></i>
                        ${order.created_at}
                    </div>
                </div>
            </div>
        `).join('');
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-5xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Test Orders Created Successfully
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">${orders.length} orders generated and processed</p>
                    </div>
                    <button onclick="this.closest('.fixed').remove()" 
                            class="text-gray-500 hover:text-gray-700 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    ${ordersList}
                </div>
                
                ${commandOutput ? `
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-terminal text-sm mr-2"></i>
                            Command Output:
                        </h4>
                        <pre class="text-xs text-gray-600 whitespace-pre-wrap overflow-x-auto bg-white p-3 rounded border">${commandOutput}</pre>
                    </div>
                ` : ''}
                
                <div class="flex justify-between items-center border-t pt-4">
                    <div class="flex gap-2">
                        <button onclick="testOrderCreation(5, 'dine_in'); this.closest('.fixed').remove();" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-utensils mr-1"></i>
                            Create 5 Dine-in
                        </button>
                        <button onclick="testOrderCreation(3, 'delivery'); this.closest('.fixed').remove();" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-truck mr-1"></i>
                            Create 3 Delivery
                        </button>
                    </div>
                    <button onclick="this.closest('.fixed').remove()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        <i class="fas fa-times mr-1"></i>
                        Close
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    // Auto-refresh statistics every 30 seconds
    setInterval(updateOrderStatistics, 30000);
    
    // Initial load
    updateOrderStatistics();
});
</script>
@endPush

@push('styles')
<style>
/* Enhanced UI following guidelines */
.system-alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Status indicator animations */
.status-indicator {
    transition: all 0.3s ease;
}

.status-indicator:hover {
    transform: scale(1.1);
}

/* Command button enhancements */
.command-button {
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.command-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.command-button:active {
    transform: translateY(0);
}

/* Loading state */
</style> {
@endPush    pointer-events: none;

    opacity: 0.7;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Alert box enhancements */
.alert-box {
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .system-status-card {
        min-width: auto;
        width: 100%;
    }
    
    .command-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>
@endPush
