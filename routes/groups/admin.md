# Admin Routes

Generated on: 2025-06-25 17:00:20
Total routes: 206

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|-----------|
| GET, HEAD | admin/auth/debug | admin.auth.check | App\Http\Controllers\AdminAuthTestController@checkAuth | web |
| GET, HEAD | admin/login | admin.login | App\Http\Controllers\AdminAuthController@showLoginForm | web |
| POST | admin/login | admin.login.submit | App\Http\Controllers\AdminAuthController@login | web |
| POST | admin/logout | admin.logout.action | App\Http\Controllers\AdminAuthController@adminLogout | web |
| GET, HEAD | admin/dashboard | admin.dashboard | App\Http\Controllers\AdminController@dashboard | web, auth:admin |
| GET, HEAD | admin/profile | admin.profile.index | App\Http\Controllers\AdminController@profile | web, auth:admin |
| GET, HEAD | admin/testpage | admin.testpage | App\Http\Controllers\AdminTestPageController@index | web, auth:admin |
| GET, HEAD | admin/reservations | admin.reservations.index | App\Http\Controllers\AdminReservationController@index | web, auth:admin |
| GET, HEAD | admin/reservations/create | admin.reservations.create | App\Http\Controllers\AdminReservationController@create | web, auth:admin |
| POST | admin/reservations | admin.reservations.store | App\Http\Controllers\AdminReservationController@store | web, auth:admin |
| GET, HEAD | admin/reservations/{reservation} | admin.reservations.show | App\Http\Controllers\AdminReservationController@show | web, auth:admin |
| GET, HEAD | admin/reservations/{reservation}/edit | admin.reservations.edit | App\Http\Controllers\AdminReservationController@edit | web, auth:admin |
| PUT, PATCH | admin/reservations/{reservation} | admin.reservations.update | App\Http\Controllers\AdminReservationController@update | web, auth:admin |
| DELETE | admin/reservations/{reservation} | admin.reservations.destroy | App\Http\Controllers\AdminReservationController@destroy | web, auth:admin |
| GET, HEAD | admin/inventory | admin.inventory.index | App\Http\Controllers\ItemDashboardController@index | web, auth:admin |
| GET, HEAD | admin/inventory/dashboard | admin.inventory.dashboard | App\Http\Controllers\ItemDashboardController@index | web, auth:admin |
| GET, HEAD | admin/inventory/items | admin.inventory.items.index | App\Http\Controllers\ItemMasterController@index | web, auth:admin |
| GET, HEAD | admin/inventory/items/create | admin.inventory.items.create | App\Http\Controllers\ItemMasterController@create | web, auth:admin |
| POST | admin/inventory/items | admin.inventory.items.store | App\Http\Controllers\ItemMasterController@store | web, auth:admin |
| GET, HEAD | admin/inventory/items/{item} | admin.inventory.items.show | App\Http\Controllers\ItemMasterController@show | web, auth:admin |
| GET, HEAD | admin/inventory/items/{item}/edit | admin.inventory.items.edit | App\Http\Controllers\ItemMasterController@edit | web, auth:admin |
| PUT | admin/inventory/items/{item} | admin.inventory.items.update | App\Http\Controllers\ItemMasterController@update | web, auth:admin |
| DELETE | admin/inventory/items/{item} | admin.inventory.items.destroy | App\Http\Controllers\ItemMasterController@destroy | web, auth:admin |
| GET, HEAD | admin/inventory/stock | admin.inventory.stock.index | App\Http\Controllers\ItemTransactionController@index | web, auth:admin |
| POST | admin/inventory/stock | admin.inventory.stock.store | App\Http\Controllers\ItemTransactionController@store | web, auth:admin |
| GET, HEAD | admin/inventory/stock/transactions | admin.inventory.stock.transactions.index | App\Http\Controllers\ItemTransactionController@transactions | web, auth:admin |
| GET, HEAD | admin/inventory/gtn/search-items | admin.inventory.gtn.search-items | App\Http\Controllers\GoodsTransferNoteController@searchItems | web, auth:admin |
| GET, HEAD | admin/inventory/gtn/item-stock | admin.inventory.gtn.item-stock | App\Http\Controllers\GoodsTransferNoteController@getItemStock | web, auth:admin |
| GET, HEAD | admin/inventory/gtn | admin.inventory.gtn.index | App\Http\Controllers\GoodsTransferNoteController@index | web, auth:admin |
| GET, HEAD | admin/inventory/gtn/create | admin.inventory.gtn.create | App\Http\Controllers\GoodsTransferNoteController@create | web, auth:admin |
| POST | admin/inventory/gtn | admin.inventory.gtn.store | App\Http\Controllers\GoodsTransferNoteController@store | web, auth:admin |
| GET, HEAD | admin/inventory/gtn/{gtn} | admin.inventory.gtn.show | App\Http\Controllers\GoodsTransferNoteController@show | web, auth:admin |
| GET, HEAD | admin/suppliers | admin.suppliers.index | App\Http\Controllers\SupplierController@index | web, auth:admin |
| GET, HEAD | admin/suppliers/create | admin.suppliers.create | App\Http\Controllers\SupplierController@create | web, auth:admin |
| POST | admin/suppliers | admin.suppliers.store | App\Http\Controllers\SupplierController@store | web, auth:admin |
| GET, HEAD | admin/suppliers/{supplier} | admin.suppliers.show | App\Http\Controllers\SupplierController@show | web, auth:admin |
| GET, HEAD | admin/suppliers/{supplier}/edit | admin.suppliers.edit | App\Http\Controllers\SupplierController@edit | web, auth:admin |
| PUT | admin/suppliers/{supplier} | admin.suppliers.update | App\Http\Controllers\SupplierController@update | web, auth:admin |
| DELETE | admin/suppliers/{supplier} | admin.suppliers.destroy | App\Http\Controllers\SupplierController@destroy | web, auth:admin |
| GET, HEAD | admin/suppliers/{supplier}/pending-grns | admin.suppliers. | App\Http\Controllers\SupplierController@pendingGrns | web, auth:admin |
| GET, HEAD | admin/suppliers/{supplier}/pending-pos | admin.suppliers. | App\Http\Controllers\SupplierController@pendingPos | web, auth:admin |
| GET, HEAD | admin/grn | admin.grn.index | App\Http\Controllers\GrnDashboardController@index | web, auth:admin |
| GET, HEAD | admin/grn/create | admin.grn.create | App\Http\Controllers\GrnDashboardController@create | web, auth:admin |
| POST | admin/grn | admin.grn.store | App\Http\Controllers\GrnDashboardController@store | web, auth:admin |
| GET, HEAD | admin/grn/{grn} | admin.grn.show | App\Http\Controllers\GrnDashboardController@show | web, auth:admin |
| GET, HEAD | admin/orders | admin.orders.index | App\Http\Controllers\AdminOrderController@index | web, auth:admin |
| GET, HEAD | admin/orders/create | admin.orders.create | App\Http\Controllers\AdminOrderController@create | web, auth:admin |
| POST | admin/orders | admin.orders.store | App\Http\Controllers\AdminOrderController@store | web, auth:admin |
| GET, HEAD | admin/orders/{order} | admin.orders.show | App\Http\Controllers\AdminOrderController@show | web, auth:admin |
| GET, HEAD | admin/orders/{order}/edit | admin.orders.edit | App\Http\Controllers\AdminOrderController@edit | web, auth:admin |
| PUT | admin/orders/{order} | admin.orders.update | App\Http\Controllers\AdminOrderController@update | web, auth:admin |
| DELETE | admin/orders/{order} | admin.orders.destroy | App\Http\Controllers\AdminOrderController@destroy | web, auth:admin |
| GET, HEAD | admin/orders/takeaway | admin.orders.takeaway.index | App\Http\Controllers\AdminOrderController@indexTakeaway | web, auth:admin |
| GET, HEAD | admin/orders/takeaway/create | admin.orders.takeaway.create | App\Http\Controllers\AdminOrderController@createTakeaway | web, auth:admin |
| POST | admin/orders/takeaway | admin.orders.takeaway.store | App\Http\Controllers\AdminOrderController@storeTakeaway | web, auth:admin |
| GET, HEAD | admin/orders/takeaway/{order} | admin.orders.takeaway.show | App\Http\Controllers\AdminOrderController@showTakeaway | web, auth:admin |
| GET, HEAD | admin/orders/takeaway/{order}/edit | admin.orders.takeaway.edit | App\Http\Controllers\AdminOrderController@editTakeaway | web, auth:admin |
| PUT | admin/orders/takeaway/{order} | admin.orders.takeaway.update | App\Http\Controllers\AdminOrderController@updateTakeaway | web, auth:admin |
| DELETE | admin/orders/takeaway/{order} | admin.orders.takeaway.destroy | App\Http\Controllers\AdminOrderController@destroyTakeaway | web, auth:admin |
| GET, HEAD | admin/organizations | admin.organizations.index | App\Http\Controllers\OrganizationController@index | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/create | admin.organizations.create | App\Http\Controllers\OrganizationController@create | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/organizations | admin.organizations.store | App\Http\Controllers\OrganizationController@store | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/{organization}/edit | admin.organizations.edit | App\Http\Controllers\OrganizationController@edit | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT, PATCH | admin/organizations/{organization} | admin.organizations.update | App\Http\Controllers\OrganizationController@update | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/organizations/{organization} | admin.organizations.destroy | App\Http\Controllers\OrganizationController@destroy | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/{organization}/summary | admin.organizations.summary | App\Http\Controllers\OrganizationController@summary | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT | admin/organizations/{organization}/regenerate-key | admin.organizations.regenerate-key | App\Http\Controllers\OrganizationController@regenerateKey | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/{organization}/branches | admin.branches.index | App\Http\Controllers\BranchController@index | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/{organization}/branches/create | admin.branches.create | App\Http\Controllers\BranchController@create | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/organizations/{organization}/branches | admin.branches.store | App\Http\Controllers\BranchController@store | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/{organization}/branches/{branch}/edit | admin.branches.edit | App\Http\Controllers\BranchController@edit | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT | admin/organizations/{organization}/branches/{branch} | admin.branches.update | App\Http\Controllers\BranchController@update | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/organizations/{organization}/branches/{branch} | admin.branches.destroy | App\Http\Controllers\BranchController@destroy | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/branches | admin.branches.global | App\Http\Controllers\BranchController@globalIndex | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/users | admin.users.index | App\Http\Controllers\UserController@index | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/roles | admin.roles.index | App\Http\Controllers\RoleController@index | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/subscription-plans | admin.subscription-plans.index | App\Http\Controllers\Admin\SubscriptionPlanController@index | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/subscription-plans/create | admin.subscription-plans.create | App\Http\Controllers\Admin\SubscriptionPlanController@create | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/subscription-plans | admin.subscription-plans.store | App\Http\Controllers\Admin\SubscriptionPlanController@store | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/subscription-plans/{subscription_plan} | admin.subscription-plans.show | App\Http\Controllers\Admin\SubscriptionPlanController@show | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/subscription-plans/{subscription_plan}/edit | admin.subscription-plans.edit | App\Http\Controllers\Admin\SubscriptionPlanController@edit | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT, PATCH | admin/subscription-plans/{subscription_plan} | admin.subscription-plans.update | App\Http\Controllers\Admin\SubscriptionPlanController@update | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/subscription-plans/{subscription_plan} | admin.subscription-plans.destroy | App\Http\Controllers\Admin\SubscriptionPlanController@destroy | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/organizations/activate | admin.organizations.activate.form | App\Http\Controllers\OrganizationController@showActivationForm | web |
| POST | admin/organizations/activate | admin.organizations.activate.submit | App\Http\Controllers\OrganizationController@activateOrganization | web |
| GET, HEAD | admin/branches/activate | admin.branches.activate.form | App\Http\Controllers\BranchController@showActivationForm | web, auth:admin |
| POST | admin/branches/activate | admin.branches.activate.submit | App\Http\Controllers\BranchController@activateBranch | web, auth:admin |
| GET, HEAD | admin/subscriptions/{subscription}/edit | admin.subscriptions.edit | App\Http\Controllers\SubscriptionController@edit | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT, PATCH | admin/subscriptions/{subscription} | admin.subscriptions.update | App\Http\Controllers\SubscriptionController@update | web, web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/roles/create | admin.roles.create | App\Http\Controllers\RoleController@create | web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/roles | admin.roles.store | App\Http\Controllers\RoleController@store | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/roles/{role}/edit | admin.roles.edit | App\Http\Controllers\RoleController@edit | web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT, PATCH | admin/roles/{role} | admin.roles.update | App\Http\Controllers\RoleController@update | web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/roles/{role} | admin.roles.destroy | App\Http\Controllers\RoleController@destroy | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/modules | admin.modules.index | App\Http\Controllers\Admin\ModuleController@index | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/modules/create | admin.modules.create | App\Http\Controllers\Admin\ModuleController@create | web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/modules | admin.modules.store | App\Http\Controllers\Admin\ModuleController@store | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/modules/{module}/edit | admin.modules.edit | App\Http\Controllers\Admin\ModuleController@edit | web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT, PATCH | admin/modules/{module} | admin.modules.update | App\Http\Controllers\Admin\ModuleController@update | web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/modules/{module} | admin.modules.destroy | App\Http\Controllers\Admin\ModuleController@destroy | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/roles/{role}/permissions | admin.roles.permissions | App\Http\Controllers\RoleController@permissions | web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/roles/{role}/permissions | admin.roles.permissions.update | App\Http\Controllers\RoleController@updatePermissions | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/users/create | admin.users.create | App\Http\Controllers\UserController@create | web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/users | admin.users.store | App\Http\Controllers\UserController@store | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/users/{user}/edit | admin.users.edit | App\Http\Controllers\UserController@edit | web, auth:admin, App\Http\Middleware\SuperAdmin |
| PUT | admin/users/{user} | admin.users.update | App\Http\Controllers\UserController@update | web, auth:admin, App\Http\Middleware\SuperAdmin |
| DELETE | admin/users/{user} | admin.users.destroy | App\Http\Controllers\UserController@destroy | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/users/{user}/assign-role | admin.users.assign-role | App\Http\Controllers\UserController@assignRoleForm | web, auth:admin, App\Http\Middleware\SuperAdmin |
| POST | admin/users/{user}/assign-role | admin.users.assign-role.store | App\Http\Controllers\UserController@assignRole | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/admin/organizations/{organization}/users/create | admin.admin.users.create | App\Http\Controllers\UserController@create | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/admin/organizations/{organization}/branches/{branch}/users/create | admin.admin.branch.users.create | App\Http\Controllers\UserController@create | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/users/{user} | admin.users.show | App\Http\Controllers\UserController@show | web, auth:admin, App\Http\Middleware\SuperAdmin |
| GET, HEAD | admin/orders/enhanced-create | admin.orders.enhanced-create | App\Http\Controllers\AdminOrderController@enhancedCreate | web, auth:admin |
| POST | admin/orders/enhanced-store | admin.orders.enhanced-store | App\Http\Controllers\AdminOrderController@enhancedStore | web, auth:admin |
| POST | admin/orders/{order}/confirm-stock | admin.orders.confirm-stock | App\Http\Controllers\AdminOrderController@confirmOrderStock | web, auth:admin |
| DELETE | admin/orders/{order}/cancel-with-stock | admin.orders.cancel-with-stock | App\Http\Controllers\AdminOrderController@cancelOrderWithStock | web, auth:admin |
| GET, HEAD | admin/dashboard/realtime-inventory | admin.dashboard.realtime-inventory | App\Http\Controllers\RealtimeDashboardController@index | web, auth:admin |
| GET, HEAD | menus/safety-dashboard | admin.menus.safety-dashboard | App\Http\Controllers\Admin\MenuController@safetyDashboard | web |
| POST | admin/diagnose-table | admin. | App\Http\Controllers\DatabaseTestController@diagnoseTable | web, auth:admin |
| POST | admin/run-migrations | admin. | App\Http\Controllers\DatabaseTestController@runMigrations | web, auth:admin |
| POST | admin/run-seeder | admin. | App\Http\Controllers\DatabaseTestController@runSeeder | web, auth:admin |
| POST | admin/full-diagnose | admin. | App\Http\Controllers\DatabaseTestController@fullDiagnose | web, auth:admin |
| POST | admin/fresh-migrate | admin. | App\Http\Controllers\DatabaseTestController@freshMigrate | web, auth:admin |
| POST | admin/test-orders | admin. | App\Http\Controllers\DatabaseTestController@testOrderCreation | web, auth:admin |
| GET, HEAD | admin/system-stats | admin. | App\Http\Controllers\DatabaseTestController@getSystemStats | web, auth:admin |
| GET, HEAD | admin/order-stats | admin. | App\Http\Controllers\DatabaseTestController@getOrderStats | web, auth:admin |
| GET, HEAD | admin/recent-orders | admin. | App\Http\Controllers\DatabaseTestController@getRecentOrders | web, auth:admin |
| GET, HEAD | admin/orders-preview | admin. | App\Http\Controllers\DatabaseTestController@getOrdersPreview | web, auth:admin |
| GET, HEAD | branch/users/create | admin.branch.users.create | App\Http\Controllers\Admin\BranchController@users | web, auth:admin |
| GET, HEAD | customers/index | admin.customers.index | App\Http\Controllers\Admin\CustomerController@index | web, auth:admin |
<!-- | GET, HEAD | digital-menu/index | admin.digital-menu.index | App\Http\Controllers\Admin\DigitalMenuController@index | web, auth:admin | -->
| GET, HEAD | settings/index | admin.settings.index | App\Http\Controllers\Admin\SettingController@index | web, auth:admin |
| GET, HEAD | reports/index | admin.reports.index | App\Http\Controllers\Admin\ReportController@index | web, auth:admin |
| GET, HEAD | debug/routes | admin.debug.routes | App\Http\Controllers\Admin\DebugController@routes | web, auth:admin |
| GET, HEAD | debug/routes/test | admin.debug.routes.test | App\Http\Controllers\Admin\DebugController@routes | web, auth:admin |
| GET, HEAD | debug/routes/generate | admin.debug.routes.generate | App\Http\Controllers\Admin\DebugController@routes | web, auth:admin |
| GET, HEAD | debug/routes/export | admin.debug.routes.export | App\Http\Controllers\Admin\DebugController@routes | web, auth:admin |
| GET, HEAD | employees/index | admin.employees.index | App\Http\Controllers\Admin\EmployeeController@index | web, auth:admin |
| POST | employees/store | admin.employees.store | App\Http\Controllers\Admin\EmployeeController@store | web, auth:admin |
| GET, HEAD | employees/show | admin.employees.show | App\Http\Controllers\Admin\EmployeeController@show | web, auth:admin |
| PUT | employees/update | admin.employees.update | App\Http\Controllers\Admin\EmployeeController@update | web, auth:admin |
| GET, HEAD | employees/create | admin.employees.create | App\Http\Controllers\Admin\EmployeeController@create | web, auth:admin |
| GET, HEAD | employees/restore | admin.employees.restore | App\Http\Controllers\Admin\EmployeeController@restore | web, auth:admin |
| GET, HEAD | employees/edit | admin.employees.edit | App\Http\Controllers\Admin\EmployeeController@edit | web, auth:admin |
| DELETE | employees/destroy | admin.employees.destroy | App\Http\Controllers\Admin\EmployeeController@destroy | web, auth:admin |
| GET, HEAD | grn/link-payment | admin.grn.link-payment | App\Http\Controllers\Admin\GrnController@link-payment | web, auth:admin |
| GET, HEAD | payments/show | admin.payments.show | App\Http\Controllers\Admin\PaymentController@show | web, auth:admin |
| GET, HEAD | inventory/gtn/items-with-stock | admin.inventory.gtn.items-with-stock | App\Http\Controllers\Admin\InventoryController@gtn | web, auth:admin |
| GET, HEAD | inventory/gtn/update | admin.inventory.gtn.update | App\Http\Controllers\Admin\InventoryController@gtn | web, auth:admin |
| GET, HEAD | inventory/gtn/print | admin.inventory.gtn.print | App\Http\Controllers\Admin\InventoryController@gtn | web, auth:admin |
| GET, HEAD | inventory/gtn/edit | admin.inventory.gtn.edit | App\Http\Controllers\Admin\InventoryController@gtn | web, auth:admin |
| GET, HEAD | inventory/items/restore | admin.inventory.items.restore | App\Http\Controllers\Admin\InventoryController@items | web, auth:admin |
| GET, HEAD | inventory/stock/update | admin.inventory.stock.update | App\Http\Controllers\Admin\InventoryController@stock | web, auth:admin |
| GET, HEAD | inventory/stock/edit | admin.inventory.stock.edit | App\Http\Controllers\Admin\InventoryController@stock | web, auth:admin |
| GET, HEAD | inventory/stock/show | admin.inventory.stock.show | App\Http\Controllers\Admin\InventoryController@stock | web, auth:admin |
| GET, HEAD | menus/index | admin.menus.index | App\Http\Controllers\Admin\MenuController@index | web, auth:admin |
| GET, HEAD | menus/bulk/store | admin.menus.bulk-store | App\Http\Controllers\Admin\MenuController@bulk | web, auth:admin |
| GET, HEAD | menus/create | admin.menus.create | App\Http\Controllers\Admin\MenuController@create | web, auth:admin |
| GET, HEAD | menus/calendar/data | admin.menus.calendar.data | App\Http\Controllers\Admin\MenuController@calendar | web, auth:admin |
| POST | menus/store | admin.menus.store | App\Http\Controllers\Admin\MenuController@store | web, auth:admin |
| GET, HEAD | menus/show | admin.menus.show | App\Http\Controllers\Admin\MenuController@show | web, auth:admin |
| PUT | menus/update | admin.menus.update | App\Http\Controllers\Admin\MenuController@update | web, auth:admin |
| GET, HEAD | menus/calendar | admin.menus.calendar | App\Http\Controllers\Admin\MenuController@calendar | web, auth:admin |
| GET, HEAD | menus/edit | admin.menus.edit | App\Http\Controllers\Admin\MenuController@edit | web, auth:admin |
| GET, HEAD | menus/list | admin.menus.list | App\Http\Controllers\Admin\MenuController@list | web, auth:admin |
| GET, HEAD | menus/bulk/create | admin.menus.bulk.create | App\Http\Controllers\Admin\MenuController@bulk | web, auth:admin |
| GET, HEAD | menus/preview | admin.menus.preview | App\Http\Controllers\Admin\MenuController@preview | web, auth:admin |
| GET, HEAD | orders/archive-old-menus | admin.orders.archive-old-menus | App\Http\Controllers\Admin\OrderController@archive-old-menus | web, auth:admin |
| GET, HEAD | orders/menu-safety-status | admin.orders.menu-safety-status | App\Http\Controllers\Admin\OrderController@menu-safety-status | web, auth:admin |
| GET, HEAD | orders/reservations/store | admin.orders.reservations.store | App\Http\Controllers\Admin\OrderController@reservations | web, auth:admin |
| GET, HEAD | orders/update-cart | admin.orders.update-cart | App\Http\Controllers\Admin\OrderController@update-cart | web, auth:admin |
| GET, HEAD | orders/reservations/index | admin.orders.reservations.index | App\Http\Controllers\Admin\OrderController@reservations | web, auth:admin |
| GET, HEAD | orders/orders/reservations/edit | admin.orders.orders.reservations.edit | App\Http\Controllers\Admin\OrderController@orders | web, auth:admin |
| GET, HEAD | orders/takeaway/branch | admin.orders.takeaway.branch | App\Http\Controllers\Admin\OrderController@takeaway | web, auth:admin |
| GET, HEAD | orders/dashboard | admin.orders.dashboard | App\Http\Controllers\Admin\OrderController@dashboard | web, auth:admin |
| GET, HEAD | check-table-availability | admin.check-table-availability | App\Http\Controllers\Admin\CheckTableAvailabilityController@index | web, auth:admin |
| GET, HEAD | orders/reservations/create | admin.orders.reservations.create | App\Http\Controllers\Admin\OrderController@reservations | web, auth:admin |
| GET, HEAD | orders/reservations/edit | admin.orders.reservations.edit | App\Http\Controllers\Admin\OrderController@reservations | web, auth:admin |
| GET, HEAD | reservations/assign-steward | admin.reservations.assign-steward | App\Http\Controllers\Admin\ReservationController@assign-steward | web, auth:admin |
| GET, HEAD | reservations/check-in | admin.reservations.check-in | App\Http\Controllers\Admin\ReservationController@check-in | web, auth:admin |
| GET, HEAD | reservations/check-out | admin.reservations.check-out | App\Http\Controllers\Admin\ReservationController@check-out | web, auth:admin |
| GET, HEAD | orders/orders/reservations/create | admin.orders.orders.reservations.create | App\Http\Controllers\Admin\OrderController@orders | web, auth:admin |
| PUT | grn/update | admin.grn.update | App\Http\Controllers\Admin\GrnController@update | web, auth:admin |
| GET, HEAD | grn/print | admin.grn.print | App\Http\Controllers\Admin\GrnController@print | web, auth:admin |
| GET, HEAD | grn/edit | admin.grn.edit | App\Http\Controllers\Admin\GrnController@edit | web, auth:admin |
| GET, HEAD | grn/verify | admin.grn.verify | App\Http\Controllers\Admin\GrnController@verify | web, auth:admin |
| GET, HEAD | purchase-orders/show | admin.purchase-orders.show | App\Http\Controllers\Admin\PurchaseOrderController@show | web, auth:admin |
| GET, HEAD | purchase-orders/index | admin.purchase-orders.index | App\Http\Controllers\Admin\PurchaseOrderController@index | web, auth:admin |
| GET, HEAD | payments/index | admin.payments.index | App\Http\Controllers\Admin\PaymentController@index | web, auth:admin |
| POST | payments/store | admin.payments.store | App\Http\Controllers\Admin\PaymentController@store | web, auth:admin |
| PUT | payments/update | admin.payments.update | App\Http\Controllers\Admin\PaymentController@update | web, auth:admin |
| GET, HEAD | payments/edit | admin.payments.edit | App\Http\Controllers\Admin\PaymentController@edit | web, auth:admin |
| GET, HEAD | payments/print | admin.payments.print | App\Http\Controllers\Admin\PaymentController@print | web, auth:admin |
| DELETE | payments/destroy | admin.payments.destroy | App\Http\Controllers\Admin\PaymentController@destroy | web, auth:admin |
| POST | purchase-orders/store | admin.purchase-orders.store | App\Http\Controllers\Admin\PurchaseOrderController@store | web, auth:admin |
| PUT | purchase-orders/update | admin.purchase-orders.update | App\Http\Controllers\Admin\PurchaseOrderController@update | web, auth:admin |
| GET, HEAD | purchase-orders/create | admin.purchase-orders.create | App\Http\Controllers\Admin\PurchaseOrderController@create | web, auth:admin |
| GET, HEAD | purchase-orders/print | admin.purchase-orders.print | App\Http\Controllers\Admin\PurchaseOrderController@print | web, auth:admin |
| GET, HEAD | purchase-orders/approve | admin.purchase-orders.approve | App\Http\Controllers\Admin\PurchaseOrderController@approve | web, auth:admin |
| GET, HEAD | purchase-orders/edit | admin.purchase-orders.edit | App\Http\Controllers\Admin\PurchaseOrderController@edit | web, auth:admin |
| GET, HEAD | suppliers/purchase-orders | admin.suppliers.purchase-orders | App\Http\Controllers\Admin\SupplierController@purchase-orders | web, auth:admin |
| GET, HEAD | orders/reservations/summary | admin.orders.reservations.summary | App\Http\Controllers\Admin\OrderController@reservations | web, auth:admin |
| GET, HEAD | orders/takeaway/summary | admin.orders.takeaway.summary | App\Http\Controllers\Admin\OrderController@takeaway | web, auth:admin |
| GET, HEAD | orders/summary | admin.orders.summary | App\Http\Controllers\Admin\OrderController@summary | web, auth:admin |
| GET, HEAD | bills/show | admin.bills.show | App\Http\Controllers\Admin\BillController@show | web, auth:admin |
| GET, HEAD | inventory/items/added-items | admin.inventory.items.added-items | App\Http\Controllers\Admin\InventoryController@items | web, auth:admin |
