# Admin Sidebar Route System - Code Diffs & Corrections

## 1. routes/web.php - Corrected Admin Routes

```php
<?php
/*-------------------------------------------------------------------------
| Admin Routes - CORRECTED IMPLEMENTATION
|------------------------------------------------------------------------*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication routes (no middleware)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'adminLogout'])->name('logout.action');

    // All authenticated admin routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile.index');

        // Inventory Management - FIXED: Single source of truth
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

            // Items
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/', [ItemMasterController::class, 'index'])->name('index');
                Route::get('/create', [ItemMasterController::class, 'create'])->name('create');
                Route::post('/', [ItemMasterController::class, 'store'])->name('store');
                Route::get('/{item}', [ItemMasterController::class, 'show'])->whereNumber('item')->name('show');
                Route::get('/{item}/edit', [ItemMasterController::class, 'edit'])->whereNumber('item')->name('edit');
                Route::put('/{item}', [ItemMasterController::class, 'update'])->whereNumber('item')->name('update');
                Route::delete('/{item}', [ItemMasterController::class, 'destroy'])->whereNumber('item')->name('destroy');
            });

            // Stock Management
            Route::prefix('stock')->name('stock.')->group(function () {
                Route::get('/', [ItemTransactionController::class, 'index'])->name('index');
                Route::post('/', [ItemTransactionController::class, 'store'])->name('store');
                Route::prefix('transactions')->name('transactions.')->group(function () {
                    Route::get('/', [ItemTransactionController::class, 'transactions'])->name('index');
                });
            });

            // GTN Management
            Route::prefix('gtn')->name('gtn.')->group(function () {
                Route::get('/search-items', [GoodsTransferNoteController::class, 'searchItems'])->name('search-items');
                Route::get('/item-stock', [GoodsTransferNoteController::class, 'getItemStock'])->name('item-stock');
                Route::get('/', [GoodsTransferNoteController::class, 'index'])->name('index');
                Route::get('/create', [GoodsTransferNoteController::class, 'create'])->name('create');
                Route::post('/', [GoodsTransferNoteController::class, 'store'])->name('store');
                Route::get('/{gtn}', [GoodsTransferNoteController::class, 'show'])->whereNumber('gtn')->name('show');
            });
        });

        // Suppliers Management - FIXED: Enhanced super admin logic
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        });

        // GRN Management
        Route::prefix('grn')->name('grn.')->group(function () {
            Route::get('/', [GrnDashboardController::class, 'index'])->name('index');
            Route::get('/create', [GrnDashboardController::class, 'create'])->name('create');
            Route::post('/', [GrnDashboardController::class, 'store'])->name('store');
            Route::get('/{grn}', [GrnDashboardController::class, 'show'])->whereNumber('grn')->name('show');
            Route::get('/{grn}/edit', [GrnDashboardController::class, 'edit'])->whereNumber('grn')->name('edit');
            Route::put('/{grn}', [GrnDashboardController::class, 'update'])->whereNumber('grn')->name('update');
            Route::post('/{grn}/verify', [GrnDashboardController::class, 'verify'])->whereNumber('grn')->name('verify');
        });
    });
});
```

## 2. app/Http/Controllers/SupplierController.php - CORRECTED

```php
<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the suppliers page.');
        }

        // FIXED: Enhanced super admin check - bypass organization requirements
        $isSuperAdmin = $admin->isSuperAdmin();
        
        // FIXED: Only non-super admins need organization
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Account setup incomplete. Contact support to assign you to an organization.');
        }

        try {
            $query = Supplier::query();
            
            // FIXED: Apply organization filter only for non-super admins
            if (!$isSuperAdmin && $admin->organization_id) {
                $query->where('organization_id', $admin->organization_id);
            }

            // Apply search filters
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('supplier_id', 'ILIKE', "%{$search}%")
                      ->orWhere('contact_person', 'ILIKE', "%{$search}%")
                      ->orWhere('phone', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            $suppliers = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('admin.suppliers.index', compact('suppliers'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error loading suppliers: ' . $e->getMessage());
        }
    }

    // Additional methods remain the same...
}
```

## 3. app/Http/Controllers/Admin/InventoryController.php - CORRECTED

```php
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ItemMaster;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * FIXED: Display inventory dashboard directly - No redirect loops
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the inventory dashboard.');
        }

        // FIXED: Super admin check - bypass organization requirements
        $isSuperAdmin = $admin->isSuperAdmin();
        
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Account setup incomplete. Contact support.');
        }

        // FIXED: Get inventory summary data directly
        try {
            $orgId = $isSuperAdmin ? null : $admin->organization_id;
            
            // Get total items count
            $totalItemsQuery = ItemMaster::query();
            if (!$isSuperAdmin) {
                $totalItemsQuery->where('organization_id', $orgId);
            }
            $totalItems = $totalItemsQuery->count();

            // Get low stock items (simplified query)
            $lowStockQuery = ItemMaster::where('status', 'active');
            if (!$isSuperAdmin) {
                $lowStockQuery->where('organization_id', $orgId);
            }
            $lowStockItems = $lowStockQuery->limit(10)->get();

            // FIXED: Return view directly instead of redirecting
            return view('admin.inventory.index', compact('totalItems', 'lowStockItems'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error loading inventory dashboard: ' . $e->getMessage());
        }
    }

    /**
     * FIXED: Handle items management directly
     */
    public function items()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in.');
        }

        $isSuperAdmin = $admin->isSuperAdmin();
        
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete.');
        }

        $orgId = $isSuperAdmin ? null : $admin->organization_id;
        
        $items = $isSuperAdmin ? 
            ItemMaster::active()->paginate(15) : 
            ItemMaster::active()->where('organization_id', $orgId)->paginate(15);

        return view('admin.inventory.items.index', compact('items'));
    }

    /**
     * FIXED: Handle stock management directly
     */
    public function stock()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Please log in.');
        }

        $isSuperAdmin = $admin->isSuperAdmin();
        
        if (!$isSuperAdmin && !$admin->organization_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Account setup incomplete.');
        }

        // FIXED: Return stock management view directly
        return view('admin.inventory.stock.index');
    }
}
```

## 4. resources/views/components/admin-sidebar.blade.php - CORRECTED

```blade
{{-- CORRECTED: Admin sidebar with proper route handling --}}
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-full pt-16 bg-[#515DEF] transition-transform duration-300">
    <div class="flex flex-col h-full text-white">
        <div class="flex-1 overflow-y-auto">
            {{-- Navigation --}}
            <div class="px-4 pb-4">
                <ul class="space-y-2">
                    @foreach ($menuItems as $item)
                        <li>
                            {{-- FIXED: Check route exists before rendering --}}
                            @if(\Illuminate\Support\Facades\Route::has($item['route']) && ($item['is_route_valid'] ?? false))
                                <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                                   class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors duration-200
                                   {{ request()->routeIs($item['route'] . '*') 
                                       ? 'bg-white text-gray-700 border-white' 
                                       : 'bg-transparent text-white border-white hover:bg-white/10' }}"
                                   data-route="{{ $item['route'] }}">

                                    {{-- Icon --}}
                                    @if ($item['icon_type'] === 'svg')
                                        @if(view()->exists('partials.icons.' . $item['icon']))
                                            @include('partials.icons.' . $item['icon'])
                                        @else
                                            <i class="fas fa-{{ $item['icon'] }} w-5 text-center"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-{{ $item['icon'] }} w-5 text-center"></i>
                                    @endif
                                    
                                    <span class="font-medium">{{ $item['title'] }}</span>
                                    
                                    {{-- Badge --}}
                                    @if(isset($item['badge']) && $item['badge'] > 0)
                                        <span class="bg-{{ $item['badge_color'] ?? 'red' }}-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>

                                {{-- Sub-items --}}
                                @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                                    <ul class="ml-6 mt-2 space-y-1">
                                        @foreach($item['sub_items'] as $subItem)
                                            @if(\Illuminate\Support\Facades\Route::has($subItem['route']) && ($subItem['is_route_valid'] ?? false))
                                                <li>
                                                    <a href="{{ route($subItem['route'], $subItem['route_params'] ?? []) }}"
                                                       class="flex items-center gap-2 px-3 py-1 text-sm rounded-lg transition-colors duration-200
                                                       {{ request()->routeIs($subItem['route'] . '*') 
                                                           ? 'bg-white/20 text-white' 
                                                           : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                                                        <i class="fas fa-{{ $subItem['icon'] ?? 'circle' }} w-3 text-center"></i>
                                                        <span>{{ $subItem['title'] }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                {{-- FIXED: Show disabled item for invalid routes --}}
                                <div class="flex items-center gap-3 px-4 py-2 rounded-xl border border-red-400 bg-red-100/10 text-red-300">
                                    <i class="fas fa-exclamation-triangle w-5 text-center"></i>
                                    <span class="font-medium">{{ $item['title'] }} (Unavailable)</span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</aside>

{{-- FIXED: JavaScript for redirect loop detection --}}
@if(config('app.debug'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monitor sidebar navigation for debugging
    const sidebarLinks = document.querySelectorAll('[data-route]');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const route = this.getAttribute('data-route');
            console.log('Sidebar navigation:', {
                route: route,
                href: this.href,
                timestamp: new Date().toISOString()
            });

            // FIXED: Check for redirect loops
            if (this.href.includes('/admin/login')) {
                e.preventDefault();
                console.error('🚨 Redirect loop detected!', {
                    route: route,
                    href: this.href,
                    currentUrl: window.location.href
                });
                alert('Authentication error detected. Please refresh the page and try again.');
            }
        });
    });
});
</script>
@endif
```

## 5. routes/groups/admin.php - CORRECTED (Duplicates Removed)

```php
<?php
// FIXED: Remove duplicate route definitions
// NOTE: All admin routes are now defined in routes/web.php only
// This file should contain only supplementary admin routes that don't conflict

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DatabaseTestController;
use App\Http\Controllers\RealtimeDashboardController;

// Only keep non-conflicting admin routes here
Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Database testing (development only)
    Route::post('diagnose-table', [DatabaseTestController::class, 'diagnoseTable'])->name('diagnose-table');
    Route::post('run-migrations', [DatabaseTestController::class, 'runMigrations'])->name('run-migrations');
    Route::post('run-seeder', [DatabaseTestController::class, 'runSeeder'])->name('run-seeder');
    
    // Realtime dashboard
    Route::get('dashboard/realtime-inventory', [RealtimeDashboardController::class, 'index'])->name('dashboard.realtime-inventory');
    
    // REMOVED: All inventory, suppliers, GRN routes (now in web.php only)
    // This prevents route conflicts and ensures single source of truth
});
```

## Summary of Key Fixes

1. **Route Conflicts**: Removed duplicate definitions from `admin.php`
2. **Controller Logic**: Enhanced super admin bypass in all controllers  
3. **Redirect Loops**: Eliminated self-referencing redirects in `Admin\InventoryController`
4. **Sidebar Validation**: Added proper route existence checking
5. **Error Handling**: Improved error messages and fallback handling

These corrections ensure the admin sidebar navigation works correctly without redirect loops or authentication issues.
