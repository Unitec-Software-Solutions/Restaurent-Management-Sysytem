# Route Conflict Resolution - Menu Items Create KOT

## Issue Description
The URL `/admin/menu-items/create-kot` was throwing a database error:
```
SQLSTATE[22P02]: Invalid text representation: 7 ERROR: 
invalid input syntax for type bigint: "create-kot" 
CONTEXT: unnamed portal parameter $1 = '...' 
(Connection: pgsql, SQL: select * from "menu_items" where "id" = create-kot and "menu_items"."deleted_at" is null limit 1)
```

## Root Cause
There was a conflicting route definition outside the main `admin/menu-items` route group:

```php
// CONFLICTING ROUTE (line 655)
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
    Route::get('/menu-items/by-branch', [AdminOrderController::class, 'getMenuItems'])->name('menu-items.by-branch');
});
```

This route was being processed BEFORE the routes inside the `Route::prefix('admin/menu-items')` group, causing Laravel to match `/admin/menu-items/create-kot` to the wrong route pattern and trying to resolve "create-kot" as a menu item ID.

## Solution
1. **Removed the conflicting route** from the incorrect location
2. **Moved the `/by-branch` route** to the proper location within the `admin/menu-items` route group
3. **Ensured proper route ordering** with specific routes before dynamic routes

## Fixed Route Structure
```php
Route::prefix('admin/menu-items')->name('admin.menu-items.')->middleware(['auth:admin'])->group(function () {
    // Standard CRUD routes
    Route::get('/', [MenuItemController::class, 'index'])->name('index');
    Route::get('/enhanced', [MenuItemController::class, 'enhancedIndex'])->name('enhanced.index');
    Route::get('/create', [MenuItemController::class, 'create'])->name('create');
    
    // Enhanced KOT specific routes (MUST be before dynamic routes)
    Route::get('/create-kot', [MenuItemController::class, 'createKotForm'])->name('create-kot');
    Route::post('/create-kot', [MenuItemController::class, 'createKotItems'])->name('store-kot');
    
    // AJAX routes
    Route::get('/api/items', [MenuItemController::class, 'getItems'])->name('api.items');
    Route::get('/by-branch', [AdminOrderController::class, 'getMenuItems'])->name('by-branch');
    Route::get('/menu-eligible-items', [MenuItemController::class, 'getMenuEligibleItems'])->name('menu-eligible-items');
    
    // Bulk operations with enhanced validation
    Route::post('/create-from-item-master', [MenuItemController::class, 'createFromItemMaster'])->name('create-from-item-master');
    
    // Dynamic routes (MUST be after specific routes)
    Route::post('/', [MenuItemController::class, 'store'])->name('store');
    Route::get('/{menuItem}', [MenuItemController::class, 'show'])->name('show');
    Route::get('/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('edit');
    Route::patch('/{menuItem}', [MenuItemController::class, 'update'])->name('update');
    Route::delete('/{menuItem}', [MenuItemController::class, 'destroy'])->name('destroy');
});
```

## Additional Improvements
- Added missing helper methods to `MenuItemController`:
  - `extractPreparationTime()`
  - `extractSpiceLevel()`
  - `extractDietaryInfo()`
  - `extractAllergenInfo()`
  - `getDefaultKitchenStation()`
  - `generateKotItemCode()`

## Verification
- Route list confirmed proper ordering: `php artisan route:list --path=admin/menu-items`
- Syntax check passed: `php -l app/Http/Controllers/Admin/MenuItemController.php`
- Route resolution working: `php artisan tinker --execute="echo route('admin.menu-items.create-kot');"`

## Key Lesson
**Always ensure specific routes are defined before dynamic routes, and avoid defining routes with similar patterns in different route groups that could conflict.**
