<?php

return array (
  'admin' => 
  array (
    'total_routes' => 206,
    'routes' => 
    array (
      0 => 
      array (
        'name' => 'admin.auth.check',
        'uri' => 'admin/auth/debug',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminAuthTestController@checkAuth',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      1 => 
      array (
        'name' => 'admin.login',
        'uri' => 'admin/login',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminAuthController@showLoginForm',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      2 => 
      array (
        'name' => 'admin.login.submit',
        'uri' => 'admin/login',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminAuthController@login',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      3 => 
      array (
        'name' => 'admin.logout.action',
        'uri' => 'admin/logout',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminAuthController@adminLogout',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      4 => 
      array (
        'name' => 'admin.dashboard',
        'uri' => 'admin/dashboard',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminController@dashboard',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      5 => 
      array (
        'name' => 'admin.profile.index',
        'uri' => 'admin/profile',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminController@profile',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      6 => 
      array (
        'name' => 'admin.testpage',
        'uri' => 'admin/testpage',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminTestPageController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      7 => 
      array (
        'name' => 'admin.reservations.index',
        'uri' => 'admin/reservations',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      8 => 
      array (
        'name' => 'admin.reservations.create',
        'uri' => 'admin/reservations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      9 => 
      array (
        'name' => 'admin.reservations.store',
        'uri' => 'admin/reservations',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      10 => 
      array (
        'name' => 'admin.reservations.show',
        'uri' => 'admin/reservations/{reservation}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      11 => 
      array (
        'name' => 'admin.reservations.edit',
        'uri' => 'admin/reservations/{reservation}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      12 => 
      array (
        'name' => 'admin.reservations.update',
        'uri' => 'admin/reservations/{reservation}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      13 => 
      array (
        'name' => 'admin.reservations.destroy',
        'uri' => 'admin/reservations/{reservation}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\AdminReservationController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      14 => 
      array (
        'name' => 'admin.inventory.index',
        'uri' => 'admin/inventory',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemDashboardController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      15 => 
      array (
        'name' => 'admin.inventory.dashboard',
        'uri' => 'admin/inventory/dashboard',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemDashboardController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      16 => 
      array (
        'name' => 'admin.inventory.items.index',
        'uri' => 'admin/inventory/items',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      17 => 
      array (
        'name' => 'admin.inventory.items.create',
        'uri' => 'admin/inventory/items/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      18 => 
      array (
        'name' => 'admin.inventory.items.store',
        'uri' => 'admin/inventory/items',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      19 => 
      array (
        'name' => 'admin.inventory.items.show',
        'uri' => 'admin/inventory/items/{item}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      20 => 
      array (
        'name' => 'admin.inventory.items.edit',
        'uri' => 'admin/inventory/items/{item}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      21 => 
      array (
        'name' => 'admin.inventory.items.update',
        'uri' => 'admin/inventory/items/{item}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      22 => 
      array (
        'name' => 'admin.inventory.items.destroy',
        'uri' => 'admin/inventory/items/{item}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\ItemMasterController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      23 => 
      array (
        'name' => 'admin.inventory.stock.index',
        'uri' => 'admin/inventory/stock',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemTransactionController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      24 => 
      array (
        'name' => 'admin.inventory.stock.store',
        'uri' => 'admin/inventory/stock',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ItemTransactionController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      25 => 
      array (
        'name' => 'admin.inventory.stock.transactions.index',
        'uri' => 'admin/inventory/stock/transactions',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ItemTransactionController@transactions',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      26 => 
      array (
        'name' => 'admin.inventory.gtn.search-items',
        'uri' => 'admin/inventory/gtn/search-items',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@searchItems',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      27 => 
      array (
        'name' => 'admin.inventory.gtn.item-stock',
        'uri' => 'admin/inventory/gtn/item-stock',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@getItemStock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      28 => 
      array (
        'name' => 'admin.inventory.gtn.index',
        'uri' => 'admin/inventory/gtn',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      29 => 
      array (
        'name' => 'admin.inventory.gtn.create',
        'uri' => 'admin/inventory/gtn/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      30 => 
      array (
        'name' => 'admin.inventory.gtn.store',
        'uri' => 'admin/inventory/gtn',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      31 => 
      array (
        'name' => 'admin.inventory.gtn.show',
        'uri' => 'admin/inventory/gtn/{gtn}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GoodsTransferNoteController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      32 => 
      array (
        'name' => 'admin.suppliers.index',
        'uri' => 'admin/suppliers',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      33 => 
      array (
        'name' => 'admin.suppliers.create',
        'uri' => 'admin/suppliers/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      34 => 
      array (
        'name' => 'admin.suppliers.store',
        'uri' => 'admin/suppliers',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      35 => 
      array (
        'name' => 'admin.suppliers.show',
        'uri' => 'admin/suppliers/{supplier}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      36 => 
      array (
        'name' => 'admin.suppliers.edit',
        'uri' => 'admin/suppliers/{supplier}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      37 => 
      array (
        'name' => 'admin.suppliers.update',
        'uri' => 'admin/suppliers/{supplier}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      38 => 
      array (
        'name' => 'admin.suppliers.destroy',
        'uri' => 'admin/suppliers/{supplier}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      39 => 
      array (
        'name' => 'admin.suppliers.',
        'uri' => 'admin/suppliers/{supplier}/pending-grns',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@pendingGrns',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      40 => 
      array (
        'name' => 'admin.suppliers.',
        'uri' => 'admin/suppliers/{supplier}/pending-pos',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SupplierController@pendingPos',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      41 => 
      array (
        'name' => 'admin.grn.index',
        'uri' => 'admin/grn',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GrnDashboardController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      42 => 
      array (
        'name' => 'admin.grn.create',
        'uri' => 'admin/grn/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GrnDashboardController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      43 => 
      array (
        'name' => 'admin.grn.store',
        'uri' => 'admin/grn',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\GrnDashboardController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      44 => 
      array (
        'name' => 'admin.grn.show',
        'uri' => 'admin/grn/{grn}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GrnDashboardController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      45 => 
      array (
        'name' => 'admin.orders.index',
        'uri' => 'admin/orders',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      46 => 
      array (
        'name' => 'admin.orders.create',
        'uri' => 'admin/orders/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      47 => 
      array (
        'name' => 'admin.orders.store',
        'uri' => 'admin/orders',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      48 => 
      array (
        'name' => 'admin.orders.show',
        'uri' => 'admin/orders/{order}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      49 => 
      array (
        'name' => 'admin.orders.edit',
        'uri' => 'admin/orders/{order}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      50 => 
      array (
        'name' => 'admin.orders.update',
        'uri' => 'admin/orders/{order}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      51 => 
      array (
        'name' => 'admin.orders.destroy',
        'uri' => 'admin/orders/{order}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      52 => 
      array (
        'name' => 'admin.orders.takeaway.index',
        'uri' => 'admin/orders/takeaway',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@indexTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      53 => 
      array (
        'name' => 'admin.orders.takeaway.create',
        'uri' => 'admin/orders/takeaway/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@createTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      54 => 
      array (
        'name' => 'admin.orders.takeaway.store',
        'uri' => 'admin/orders/takeaway',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@storeTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      55 => 
      array (
        'name' => 'admin.orders.takeaway.show',
        'uri' => 'admin/orders/takeaway/{order}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@showTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      56 => 
      array (
        'name' => 'admin.orders.takeaway.edit',
        'uri' => 'admin/orders/takeaway/{order}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@editTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      57 => 
      array (
        'name' => 'admin.orders.takeaway.update',
        'uri' => 'admin/orders/takeaway/{order}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@updateTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      58 => 
      array (
        'name' => 'admin.orders.takeaway.destroy',
        'uri' => 'admin/orders/takeaway/{order}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@destroyTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      59 => 
      array (
        'name' => 'admin.organizations.index',
        'uri' => 'admin/organizations',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      60 => 
      array (
        'name' => 'admin.organizations.create',
        'uri' => 'admin/organizations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      61 => 
      array (
        'name' => 'admin.organizations.store',
        'uri' => 'admin/organizations',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      62 => 
      array (
        'name' => 'admin.organizations.edit',
        'uri' => 'admin/organizations/{organization}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      63 => 
      array (
        'name' => 'admin.organizations.update',
        'uri' => 'admin/organizations/{organization}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      64 => 
      array (
        'name' => 'admin.organizations.destroy',
        'uri' => 'admin/organizations/{organization}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      65 => 
      array (
        'name' => 'admin.organizations.summary',
        'uri' => 'admin/organizations/{organization}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      66 => 
      array (
        'name' => 'admin.organizations.regenerate-key',
        'uri' => 'admin/organizations/{organization}/regenerate-key',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@regenerateKey',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      67 => 
      array (
        'name' => 'admin.branches.index',
        'uri' => 'admin/organizations/{organization}/branches',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      68 => 
      array (
        'name' => 'admin.branches.create',
        'uri' => 'admin/organizations/{organization}/branches/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      69 => 
      array (
        'name' => 'admin.branches.store',
        'uri' => 'admin/organizations/{organization}/branches',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      70 => 
      array (
        'name' => 'admin.branches.edit',
        'uri' => 'admin/organizations/{organization}/branches/{branch}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      71 => 
      array (
        'name' => 'admin.branches.update',
        'uri' => 'admin/organizations/{organization}/branches/{branch}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      72 => 
      array (
        'name' => 'admin.branches.destroy',
        'uri' => 'admin/organizations/{organization}/branches/{branch}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      73 => 
      array (
        'name' => 'admin.users.create',
        'uri' => 'admin/organizations/{organization}/users/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      74 => 
      array (
        'name' => 'admin.branch.users.create',
        'uri' => 'admin/organizations/{organization}/branches/{branch}/users/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      75 => 
      array (
        'name' => 'admin.branches.global',
        'uri' => 'admin/branches',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@globalIndex',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      76 => 
      array (
        'name' => 'admin.users.index',
        'uri' => 'admin/users',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      77 => 
      array (
        'name' => 'admin.roles.index',
        'uri' => 'admin/roles',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      78 => 
      array (
        'name' => 'admin.subscription-plans.index',
        'uri' => 'admin/subscription-plans',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      79 => 
      array (
        'name' => 'admin.subscription-plans.create',
        'uri' => 'admin/subscription-plans/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      80 => 
      array (
        'name' => 'admin.subscription-plans.store',
        'uri' => 'admin/subscription-plans',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      81 => 
      array (
        'name' => 'admin.subscription-plans.show',
        'uri' => 'admin/subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      82 => 
      array (
        'name' => 'admin.subscription-plans.edit',
        'uri' => 'admin/subscription-plans/{subscription_plan}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      83 => 
      array (
        'name' => 'admin.subscription-plans.update',
        'uri' => 'admin/subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      84 => 
      array (
        'name' => 'admin.subscription-plans.destroy',
        'uri' => 'admin/subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      85 => 
      array (
        'name' => 'admin.organizations.activate.form',
        'uri' => 'admin/organizations/activate',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@showActivationForm',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      86 => 
      array (
        'name' => 'admin.organizations.activate.submit',
        'uri' => 'admin/organizations/activate',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@activateOrganization',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      87 => 
      array (
        'name' => 'admin.branches.activate.form',
        'uri' => 'admin/branches/activate',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@showActivationForm',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      88 => 
      array (
        'name' => 'admin.branches.activate.submit',
        'uri' => 'admin/branches/activate',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@activateBranch',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      89 => 
      array (
        'name' => 'admin.subscriptions.edit',
        'uri' => 'admin/subscriptions/{subscription}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SubscriptionController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      90 => 
      array (
        'name' => 'admin.subscriptions.update',
        'uri' => 'admin/subscriptions/{subscription}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\SubscriptionController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth:admin',
          3 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      91 => 
      array (
        'name' => 'admin.roles.create',
        'uri' => 'admin/roles/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      92 => 
      array (
        'name' => 'admin.roles.store',
        'uri' => 'admin/roles',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      93 => 
      array (
        'name' => 'admin.roles.edit',
        'uri' => 'admin/roles/{role}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      94 => 
      array (
        'name' => 'admin.roles.update',
        'uri' => 'admin/roles/{role}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      95 => 
      array (
        'name' => 'admin.roles.destroy',
        'uri' => 'admin/roles/{role}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      96 => 
      array (
        'name' => 'admin.modules.index',
        'uri' => 'admin/modules',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      97 => 
      array (
        'name' => 'admin.modules.create',
        'uri' => 'admin/modules/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      98 => 
      array (
        'name' => 'admin.modules.store',
        'uri' => 'admin/modules',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      99 => 
      array (
        'name' => 'admin.modules.edit',
        'uri' => 'admin/modules/{module}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      100 => 
      array (
        'name' => 'admin.modules.update',
        'uri' => 'admin/modules/{module}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      101 => 
      array (
        'name' => 'admin.modules.destroy',
        'uri' => 'admin/modules/{module}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\ModuleController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      102 => 
      array (
        'name' => 'admin.roles.permissions',
        'uri' => 'admin/roles/{role}/permissions',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@permissions',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      103 => 
      array (
        'name' => 'admin.roles.permissions.update',
        'uri' => 'admin/roles/{role}/permissions',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@updatePermissions',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      104 => 
      array (
        'name' => 'admin.users.create',
        'uri' => 'admin/users/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      105 => 
      array (
        'name' => 'admin.users.store',
        'uri' => 'admin/users',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      106 => 
      array (
        'name' => 'admin.users.edit',
        'uri' => 'admin/users/{user}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      107 => 
      array (
        'name' => 'admin.users.update',
        'uri' => 'admin/users/{user}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      108 => 
      array (
        'name' => 'admin.users.destroy',
        'uri' => 'admin/users/{user}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      109 => 
      array (
        'name' => 'admin.users.assign-role',
        'uri' => 'admin/users/{user}/assign-role',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@assignRoleForm',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      110 => 
      array (
        'name' => 'admin.users.assign-role.store',
        'uri' => 'admin/users/{user}/assign-role',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@assignRole',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      111 => 
      array (
        'name' => 'admin.users.show',
        'uri' => 'admin/users/{user}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
          2 => 'App\\Http\\Middleware\\SuperAdmin',
        ),
      ),
      112 => 
      array (
        'name' => 'admin.orders.enhanced-create',
        'uri' => 'admin/orders/enhanced-create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@enhancedCreate',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      113 => 
      array (
        'name' => 'admin.orders.enhanced-store',
        'uri' => 'admin/orders/enhanced-store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@enhancedStore',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      114 => 
      array (
        'name' => 'admin.orders.confirm-stock',
        'uri' => 'admin/orders/{order}/confirm-stock',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@confirmOrderStock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      115 => 
      array (
        'name' => 'admin.orders.cancel-with-stock',
        'uri' => 'admin/orders/{order}/cancel-with-stock',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\AdminOrderController@cancelOrderWithStock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      116 => 
      array (
        'name' => 'admin.dashboard.realtime-inventory',
        'uri' => 'admin/dashboard/realtime-inventory',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RealtimeDashboardController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      117 => 
      array (
        'name' => 'admin.menus.safety-dashboard',
        'uri' => 'menus/safety-dashboard',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@safetyDashboard',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      118 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/diagnose-table',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@diagnoseTable',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      119 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/run-migrations',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@runMigrations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      120 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/run-seeder',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@runSeeder',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      121 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/full-diagnose',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@fullDiagnose',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      122 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/fresh-migrate',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@freshMigrate',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      123 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/test-orders',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@testOrderCreation',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      124 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/system-stats',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@getSystemStats',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      125 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/order-stats',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@getOrderStats',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      126 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/recent-orders',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@getRecentOrders',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      127 => 
      array (
        'name' => 'admin.',
        'uri' => 'admin/orders-preview',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\DatabaseTestController@getOrdersPreview',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      128 => 
      array (
        'name' => 'admin.customers.index',
        'uri' => 'customers/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\CustomerController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      129 => 
      array (
        'name' => 'admin.digital-menu.index',
        'uri' => 'digital-menu/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\DigitalMenuController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      130 => 
      array (
        'name' => 'admin.settings.index',
        'uri' => 'settings/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SettingController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      131 => 
      array (
        'name' => 'admin.reports.index',
        'uri' => 'reports/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\ReportController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      132 => 
      array (
        'name' => 'admin.debug.routes',
        'uri' => 'debug/routes',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\DebugController@routes',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      133 => 
      array (
        'name' => 'admin.debug.routes.test',
        'uri' => 'debug/routes/test',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\DebugController@routes',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      134 => 
      array (
        'name' => 'admin.debug.routes.generate',
        'uri' => 'debug/routes/generate',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\DebugController@routes',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      135 => 
      array (
        'name' => 'admin.debug.routes.export',
        'uri' => 'debug/routes/export',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\DebugController@routes',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      136 => 
      array (
        'name' => 'admin.employees.index',
        'uri' => 'employees/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      137 => 
      array (
        'name' => 'admin.employees.store',
        'uri' => 'employees/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      138 => 
      array (
        'name' => 'admin.employees.show',
        'uri' => 'employees/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      139 => 
      array (
        'name' => 'admin.employees.update',
        'uri' => 'employees/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      140 => 
      array (
        'name' => 'admin.employees.create',
        'uri' => 'employees/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      141 => 
      array (
        'name' => 'admin.employees.restore',
        'uri' => 'employees/restore',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@restore',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      142 => 
      array (
        'name' => 'admin.employees.edit',
        'uri' => 'employees/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      143 => 
      array (
        'name' => 'admin.employees.destroy',
        'uri' => 'employees/destroy',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\EmployeeController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      144 => 
      array (
        'name' => 'admin.grn.link-payment',
        'uri' => 'grn/link-payment',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\GrnController@link-payment',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      145 => 
      array (
        'name' => 'admin.payments.show',
        'uri' => 'payments/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      146 => 
      array (
        'name' => 'admin.inventory.gtn.items-with-stock',
        'uri' => 'inventory/gtn/items-with-stock',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@gtn',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      147 => 
      array (
        'name' => 'admin.inventory.gtn.update',
        'uri' => 'inventory/gtn/update',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@gtn',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      148 => 
      array (
        'name' => 'admin.inventory.gtn.print',
        'uri' => 'inventory/gtn/print',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@gtn',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      149 => 
      array (
        'name' => 'admin.inventory.gtn.edit',
        'uri' => 'inventory/gtn/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@gtn',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      150 => 
      array (
        'name' => 'admin.inventory.items.restore',
        'uri' => 'inventory/items/restore',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@items',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      151 => 
      array (
        'name' => 'admin.inventory.stock.update',
        'uri' => 'inventory/stock/update',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@stock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      152 => 
      array (
        'name' => 'admin.inventory.stock.edit',
        'uri' => 'inventory/stock/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@stock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      153 => 
      array (
        'name' => 'admin.inventory.stock.show',
        'uri' => 'inventory/stock/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@stock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      154 => 
      array (
        'name' => 'admin.menus.index',
        'uri' => 'menus/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      155 => 
      array (
        'name' => 'admin.menus.bulk.store',
        'uri' => 'menus/bulk/store',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@bulk',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      156 => 
      array (
        'name' => 'admin.menus.create',
        'uri' => 'menus/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      157 => 
      array (
        'name' => 'admin.menus.calendar.data',
        'uri' => 'menus/calendar/data',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@calendar',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      158 => 
      array (
        'name' => 'admin.menus.store',
        'uri' => 'menus/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      159 => 
      array (
        'name' => 'admin.menus.show',
        'uri' => 'menus/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      160 => 
      array (
        'name' => 'admin.menus.update',
        'uri' => 'menus/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      161 => 
      array (
        'name' => 'admin.menus.calendar',
        'uri' => 'menus/calendar',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@calendar',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      162 => 
      array (
        'name' => 'admin.menus.edit',
        'uri' => 'menus/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      163 => 
      array (
        'name' => 'admin.menus.list',
        'uri' => 'menus/list',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@list',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      164 => 
      array (
        'name' => 'admin.menus.bulk.create',
        'uri' => 'menus/bulk/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@bulk',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      165 => 
      array (
        'name' => 'admin.menus.preview',
        'uri' => 'menus/preview',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\MenuController@preview',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      166 => 
      array (
        'name' => 'admin.orders.archive-old-menus',
        'uri' => 'orders/archive-old-menus',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@archive-old-menus',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      167 => 
      array (
        'name' => 'admin.orders.menu-safety-status',
        'uri' => 'orders/menu-safety-status',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@menu-safety-status',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      168 => 
      array (
        'name' => 'admin.orders.reservations.store',
        'uri' => 'orders/reservations/store',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@reservations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      169 => 
      array (
        'name' => 'admin.orders.update-cart',
        'uri' => 'orders/update-cart',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@update-cart',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      170 => 
      array (
        'name' => 'admin.orders.reservations.index',
        'uri' => 'orders/reservations/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@reservations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      171 => 
      array (
        'name' => 'admin.orders.orders.reservations.edit',
        'uri' => 'orders/orders/reservations/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@orders',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      172 => 
      array (
        'name' => 'admin.orders.takeaway.branch',
        'uri' => 'orders/takeaway/branch',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@takeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      173 => 
      array (
        'name' => 'admin.orders.dashboard',
        'uri' => 'orders/dashboard',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@dashboard',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      174 => 
      array (
        'name' => 'admin.check-table-availability',
        'uri' => 'check-table-availability',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\CheckTableAvailabilityController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      175 => 
      array (
        'name' => 'admin.orders.reservations.create',
        'uri' => 'orders/reservations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@reservations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      176 => 
      array (
        'name' => 'admin.orders.reservations.edit',
        'uri' => 'orders/reservations/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@reservations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      177 => 
      array (
        'name' => 'admin.reservations.assign-steward',
        'uri' => 'reservations/assign-steward',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\ReservationController@assign-steward',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      178 => 
      array (
        'name' => 'admin.reservations.check-in',
        'uri' => 'reservations/check-in',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\ReservationController@check-in',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      179 => 
      array (
        'name' => 'admin.reservations.check-out',
        'uri' => 'reservations/check-out',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\ReservationController@check-out',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      180 => 
      array (
        'name' => 'admin.orders.orders.reservations.create',
        'uri' => 'orders/orders/reservations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@orders',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      181 => 
      array (
        'name' => 'admin.grn.update',
        'uri' => 'grn/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\GrnController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      182 => 
      array (
        'name' => 'admin.grn.print',
        'uri' => 'grn/print',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\GrnController@print',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      183 => 
      array (
        'name' => 'admin.grn.edit',
        'uri' => 'grn/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\GrnController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      184 => 
      array (
        'name' => 'admin.grn.verify',
        'uri' => 'grn/verify',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\GrnController@verify',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      185 => 
      array (
        'name' => 'admin.purchase-orders.show',
        'uri' => 'purchase-orders/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      186 => 
      array (
        'name' => 'admin.purchase-orders.index',
        'uri' => 'purchase-orders/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      187 => 
      array (
        'name' => 'admin.payments.index',
        'uri' => 'payments/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      188 => 
      array (
        'name' => 'admin.payments.store',
        'uri' => 'payments/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      189 => 
      array (
        'name' => 'admin.payments.update',
        'uri' => 'payments/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      190 => 
      array (
        'name' => 'admin.payments.create',
        'uri' => 'payments/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      191 => 
      array (
        'name' => 'admin.payments.edit',
        'uri' => 'payments/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      192 => 
      array (
        'name' => 'admin.payments.print',
        'uri' => 'payments/print',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@print',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      193 => 
      array (
        'name' => 'admin.payments.destroy',
        'uri' => 'payments/destroy',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PaymentController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      194 => 
      array (
        'name' => 'admin.purchase-orders.store',
        'uri' => 'purchase-orders/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      195 => 
      array (
        'name' => 'admin.purchase-orders.update',
        'uri' => 'purchase-orders/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      196 => 
      array (
        'name' => 'admin.purchase-orders.create',
        'uri' => 'purchase-orders/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      197 => 
      array (
        'name' => 'admin.purchase-orders.print',
        'uri' => 'purchase-orders/print',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@print',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      198 => 
      array (
        'name' => 'admin.purchase-orders.approve',
        'uri' => 'purchase-orders/approve',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@approve',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      199 => 
      array (
        'name' => 'admin.purchase-orders.edit',
        'uri' => 'purchase-orders/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\PurchaseOrderController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      200 => 
      array (
        'name' => 'admin.suppliers.purchase-orders',
        'uri' => 'suppliers/purchase-orders',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SupplierController@purchase-orders',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      201 => 
      array (
        'name' => 'admin.orders.reservations.summary',
        'uri' => 'orders/reservations/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@reservations',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      202 => 
      array (
        'name' => 'admin.orders.takeaway.summary',
        'uri' => 'orders/takeaway/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@takeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      203 => 
      array (
        'name' => 'admin.orders.summary',
        'uri' => 'orders/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\OrderController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      204 => 
      array (
        'name' => 'admin.bills.show',
        'uri' => 'bills/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\BillController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      205 => 
      array (
        'name' => 'admin.inventory.items.added-items',
        'uri' => 'inventory/items/added-items',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\InventoryController@items',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 100.0,
      'details' => 
      array (
        'web' => 230,
        'auth:admin' => 199,
        'App\\Http\\Middleware\\SuperAdmin' => 49,
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100.0,
      'issues' => 
      array (
      ),
    ),
  ),
  'organization' => 
  array (
    'total_routes' => 0,
    'routes' => 
    array (
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 0,
      'details' => 
      array (
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100,
      'issues' => 
      array (
      ),
    ),
  ),
  'branch' => 
  array (
    'total_routes' => 0,
    'routes' => 
    array (
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 0,
      'details' => 
      array (
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100,
      'issues' => 
      array (
      ),
    ),
  ),
  'api' => 
  array (
    'total_routes' => 0,
    'routes' => 
    array (
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 0,
      'details' => 
      array (
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100,
      'issues' => 
      array (
      ),
    ),
  ),
  'guest' => 
  array (
    'total_routes' => 17,
    'routes' => 
    array (
      0 => 
      array (
        'name' => 'guest.menu.index',
        'uri' => 'guest/menu',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@viewMenu',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      1 => 
      array (
        'name' => 'guest.menu.date',
        'uri' => 'guest/menu/date/{date}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@viewMenuByDate',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      2 => 
      array (
        'name' => 'guest.menu.special',
        'uri' => 'guest/menu/special',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@viewSpecialMenu',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      3 => 
      array (
        'name' => 'guest.cart.add',
        'uri' => 'guest/cart/add',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@addToCart',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      4 => 
      array (
        'name' => 'guest.cart.update',
        'uri' => 'guest/cart/update',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@updateCart',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      5 => 
      array (
        'name' => 'guest.cart.remove',
        'uri' => 'guest/cart/remove/{itemId}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@removeFromCart',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      6 => 
      array (
        'name' => 'guest.cart.view',
        'uri' => 'guest/cart',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@viewCart',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      7 => 
      array (
        'name' => 'guest.cart.clear',
        'uri' => 'guest/cart/clear',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@clearCart',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      8 => 
      array (
        'name' => 'guest.order.create',
        'uri' => 'guest/order/create',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@createOrder',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      9 => 
      array (
        'name' => 'guest.order.track',
        'uri' => 'guest/order/{orderNumber}/track',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@trackOrder',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      10 => 
      array (
        'name' => 'guest.order.details',
        'uri' => 'guest/order/{orderNumber}/details',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@orderDetails',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      11 => 
      array (
        'name' => 'guest.reservation.create',
        'uri' => 'guest/reservation/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@createReservation',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      12 => 
      array (
        'name' => 'guest.reservation.store',
        'uri' => 'guest/reservation/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@storeReservation',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      13 => 
      array (
        'name' => 'guest.reservation.details',
        'uri' => 'guest/reservation/{confirmationCode}/details',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@reservationDetails',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      14 => 
      array (
        'name' => 'guest.session.info',
        'uri' => 'guest/session/info',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Guest\\GuestController@sessionInfo',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      15 => 
      array (
        'name' => 'guest.order.confirmation',
        'uri' => 'guest/order/confirmation',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GuestController@order',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      16 => 
      array (
        'name' => 'guest.reservation.confirmation',
        'uri' => 'guest/reservation/confirmation',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\GuestController@reservation',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 100.0,
      'details' => 
      array (
        'web' => 17,
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100.0,
      'issues' => 
      array (
      ),
    ),
  ),
  'public' => 
  array (
    'total_routes' => 79,
    'routes' => 
    array (
      0 => 
      array (
        'name' => 'sanctum.csrf-cookie',
        'uri' => 'sanctum/csrf-cookie',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      1 => 
      array (
        'name' => 'laravel-folio',
        'uri' => '{fallbackPlaceholder}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'Closure',
        'middleware' => 
        array (
        ),
      ),
      2 => 
      array (
        'name' => 'home',
        'uri' => '/',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'Closure',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      3 => 
      array (
        'name' => 'customer.dashboard',
        'uri' => 'customer-dashboard',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\CustomerDashboardController@showReservationsByPhone',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      4 => 
      array (
        'name' => 'reservations.create',
        'uri' => 'reservations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      5 => 
      array (
        'name' => 'reservations.store',
        'uri' => 'reservations/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      6 => 
      array (
        'name' => 'reservations.payment',
        'uri' => 'reservations/{reservation}/payment',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@payment',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      7 => 
      array (
        'name' => 'reservations.process-payment',
        'uri' => 'reservations/{reservation}/process-payment',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@processPayment',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      8 => 
      array (
        'name' => 'reservations.confirm',
        'uri' => 'reservations/{reservation}/confirm',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@confirm',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      9 => 
      array (
        'name' => 'reservations.summary',
        'uri' => 'reservations/{reservation}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      10 => 
      array (
        'name' => 'reservations.review',
        'uri' => 'reservations/review',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'POST',
          2 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@review',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      11 => 
      array (
        'name' => 'reservations.cancel',
        'uri' => 'reservations/{reservation}/cancel',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@cancel',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      12 => 
      array (
        'name' => 'reservations.show',
        'uri' => 'reservations/{reservation}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      13 => 
      array (
        'name' => 'reservations.cancellation-success',
        'uri' => 'reservations/cancellation-success',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@cancellationSuccess',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      14 => 
      array (
        'name' => 'orders.index',
        'uri' => 'orders',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      15 => 
      array (
        'name' => 'orders.all',
        'uri' => 'orders/all',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@allOrders',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      16 => 
      array (
        'name' => 'orders.update-cart',
        'uri' => 'orders/update-cart',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@updateCart',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      17 => 
      array (
        'name' => 'orders.create',
        'uri' => 'orders/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      18 => 
      array (
        'name' => 'orders.store',
        'uri' => 'orders/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      19 => 
      array (
        'name' => 'orders.summary',
        'uri' => 'orders/{order}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      20 => 
      array (
        'name' => 'orders.edit',
        'uri' => 'orders/{order}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      21 => 
      array (
        'name' => 'orders.destroy',
        'uri' => 'orders/{order}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      22 => 
      array (
        'name' => 'orders.update',
        'uri' => 'orders/{order}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      23 => 
      array (
        'name' => 'orders.check-stock',
        'uri' => 'orders/check-stock',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@checkStock',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      24 => 
      array (
        'name' => 'orders.print-kot',
        'uri' => 'orders/{order}/print-kot',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@printKOT',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      25 => 
      array (
        'name' => 'orders.print-bill',
        'uri' => 'orders/{order}/print-bill',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@printBill',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      26 => 
      array (
        'name' => 'orders.mark-preparing',
        'uri' => 'orders/{order}/mark-preparing',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@markAsPreparing',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      27 => 
      array (
        'name' => 'orders.mark-ready',
        'uri' => 'orders/{order}/mark-ready',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@markAsReady',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      28 => 
      array (
        'name' => 'orders.complete',
        'uri' => 'orders/{order}/complete',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@completeOrder',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      29 => 
      array (
        'name' => 'orders.takeaway.index',
        'uri' => 'orders/takeaway',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@indexTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      30 => 
      array (
        'name' => 'orders.takeaway.create',
        'uri' => 'orders/takeaway/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@createTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      31 => 
      array (
        'name' => 'orders.takeaway.store',
        'uri' => 'orders/takeaway/store',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@storeTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      32 => 
      array (
        'name' => 'orders.takeaway.edit',
        'uri' => 'orders/takeaway/{order}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@editTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      33 => 
      array (
        'name' => 'orders.takeaway.summary',
        'uri' => 'orders/takeaway/{order}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      34 => 
      array (
        'name' => 'orders.takeaway.destroy',
        'uri' => 'orders/takeaway/{order}/delete',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@destroyTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      35 => 
      array (
        'name' => 'orders.takeaway.update',
        'uri' => 'orders/takeaway/{order}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@updateTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      36 => 
      array (
        'name' => 'orders.takeaway.submit',
        'uri' => 'orders/takeaway/{order}/submit',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@submitTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      37 => 
      array (
        'name' => 'orders.takeaway.show',
        'uri' => 'orders/takeaway/{order}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@showTakeaway',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
      ),
      38 => 
      array (
        'name' => 'branches.summary',
        'uri' => 'branches/{branch}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      39 => 
      array (
        'name' => 'branches.regenerate-key',
        'uri' => 'branches/{branch}/regenerate-key',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@regenerateKey',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      40 => 
      array (
        'name' => 'organizations.index',
        'uri' => 'organizations',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      41 => 
      array (
        'name' => 'organizations.create',
        'uri' => 'organizations/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      42 => 
      array (
        'name' => 'organizations.store',
        'uri' => 'organizations',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      43 => 
      array (
        'name' => 'organizations.edit',
        'uri' => 'organizations/{organization}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      44 => 
      array (
        'name' => 'organizations.update',
        'uri' => 'organizations/{organization}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      45 => 
      array (
        'name' => 'organizations.destroy',
        'uri' => 'organizations/{organization}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      46 => 
      array (
        'name' => 'organizations.summary',
        'uri' => 'organizations/{organization}/summary',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@summary',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      47 => 
      array (
        'name' => 'organizations.regenerate-key',
        'uri' => 'organizations/{organization}/regenerate-key',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@regenerateKey',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      48 => 
      array (
        'name' => 'branches.index',
        'uri' => 'organizations/{organization}/branches',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      49 => 
      array (
        'name' => 'branches.create',
        'uri' => 'organizations/{organization}/branches/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      50 => 
      array (
        'name' => 'branches.store',
        'uri' => 'organizations/{organization}/branches',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      51 => 
      array (
        'name' => 'branches.edit',
        'uri' => 'organizations/{organization}/branches/{branch}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      52 => 
      array (
        'name' => 'branches.update',
        'uri' => 'organizations/{organization}/branches/{branch}',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      53 => 
      array (
        'name' => 'branches.destroy',
        'uri' => 'organizations/{organization}/branches/{branch}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      54 => 
      array (
        'name' => 'branches.global',
        'uri' => 'branches',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@globalIndex',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      55 => 
      array (
        'name' => 'users.index',
        'uri' => 'users',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      56 => 
      array (
        'name' => 'roles.index',
        'uri' => 'roles',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      57 => 
      array (
        'name' => 'subscription-plans.index',
        'uri' => 'subscription-plans',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@index',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      58 => 
      array (
        'name' => 'subscription-plans.create',
        'uri' => 'subscription-plans/create',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@create',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      59 => 
      array (
        'name' => 'subscription-plans.store',
        'uri' => 'subscription-plans',
        'methods' => 
        array (
          0 => 'POST',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@store',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      60 => 
      array (
        'name' => 'subscription-plans.show',
        'uri' => 'subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@show',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      61 => 
      array (
        'name' => 'subscription-plans.edit',
        'uri' => 'subscription-plans/{subscription_plan}/edit',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@edit',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      62 => 
      array (
        'name' => 'subscription-plans.update',
        'uri' => 'subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'PUT',
          1 => 'PATCH',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@update',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      63 => 
      array (
        'name' => 'subscription-plans.destroy',
        'uri' => 'subscription-plans/{subscription_plan}',
        'methods' => 
        array (
          0 => 'DELETE',
        ),
        'action' => 'App\\Http\\Controllers\\Admin\\SubscriptionPlanController@destroy',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:admin',
        ),
      ),
      64 => 
      array (
        'name' => 'payments.process',
        'uri' => 'payments/process',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\PaymentController@process',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      65 => 
      array (
        'name' => 'orders.payment',
        'uri' => 'orders/payment',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@payment',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      66 => 
      array (
        'name' => 'roles.assign',
        'uri' => 'roles/assign',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@assign',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      67 => 
      array (
        'name' => 'users.assign-role.store',
        'uri' => 'users/assign-role/store',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\UserController@assign-role',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      68 => 
      array (
        'name' => 'kitchen.orders.index',
        'uri' => 'kitchen/orders/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\KitchenController@orders',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      69 => 
      array (
        'name' => 'reservations.index',
        'uri' => 'reservations/index',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@index',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      70 => 
      array (
        'name' => 'orders.show',
        'uri' => 'orders/show',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrderController@show',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      71 => 
      array (
        'name' => 'reservations.update',
        'uri' => 'reservations/update',
        'methods' => 
        array (
          0 => 'PUT',
        ),
        'action' => 'App\\Http\\Controllers\\ReservationController@update',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      72 => 
      array (
        'name' => 'branch',
        'uri' => 'branch',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\BranchController@index',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      73 => 
      array (
        'name' => 'organization',
        'uri' => 'organization',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\OrganizationController@index',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      74 => 
      array (
        'name' => 'role',
        'uri' => 'role',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\RoleController@index',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      75 => 
      array (
        'name' => 'subscription.expired',
        'uri' => 'subscription/expired',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SubscriptionController@expired',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      76 => 
      array (
        'name' => 'subscription.upgrade',
        'uri' => 'subscription/upgrade',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SubscriptionController@upgrade',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      77 => 
      array (
        'name' => 'subscription.required',
        'uri' => 'subscription/required',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\SubscriptionController@required',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      78 => 
      array (
        'name' => 'storage.local',
        'uri' => 'storage/{path}',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'Closure',
        'middleware' => 
        array (
        ),
      ),
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 97.47,
      'details' => 
      array (
        'web' => 112,
        'auth:admin' => 26,
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 100.0,
      'issues' => 
      array (
      ),
    ),
  ),
  'auth' => 
  array (
    'total_routes' => 1,
    'routes' => 
    array (
      0 => 
      array (
        'name' => 'login',
        'uri' => 'login',
        'methods' => 
        array (
          0 => 'GET',
          1 => 'HEAD',
        ),
        'action' => 'App\\Http\\Controllers\\AdminAuthController@showLoginForm',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
    ),
    'middleware_coverage' => 
    array (
      'percentage' => 100.0,
      'details' => 
      array (
        'web' => 1,
      ),
    ),
    'naming_consistency' => 
    array (
      'percentage' => 0.0,
      'issues' => 
      array (
        0 => 
        array (
          'route' => 'login',
          'expected_prefix' => 'auth.',
        ),
      ),
    ),
  ),
);
