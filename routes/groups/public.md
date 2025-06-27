# Public Routes

Generated on: 2025-06-25 17:00:20
Total routes: 80

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|-----------|
| GET, HEAD | sanctum/csrf-cookie | sanctum.csrf-cookie | Laravel\Sanctum\Http\Controllers\CsrfCookieController@show | web |
| GET, HEAD | {fallbackPlaceholder} | laravel-folio | Closure |  |
| GET, HEAD | / | home | Closure | web |
| GET, HEAD | customer-dashboard | customer.dashboard | App\Http\Controllers\CustomerDashboardController@showReservationsByPhone | web, web |
| GET, HEAD | reservations/create | reservations.create | App\Http\Controllers\ReservationController@create | web, web |
| POST | reservations/store | reservations.store | App\Http\Controllers\ReservationController@store | web, web |
| GET, HEAD | reservations/{reservation}/payment | reservations.payment | App\Http\Controllers\ReservationController@payment | web, web |
| POST | reservations/{reservation}/process-payment | reservations.process-payment | App\Http\Controllers\ReservationController@processPayment | web, web |
| POST | reservations/{reservation}/confirm | reservations.confirm | App\Http\Controllers\ReservationController@confirm | web, web |
| GET, HEAD | reservations/{reservation}/summary | reservations.summary | App\Http\Controllers\ReservationController@summary | web, web |
| GET, POST, HEAD | reservations/review | reservations.review | App\Http\Controllers\ReservationController@review | web, web |
| POST | reservations/{reservation}/cancel | reservations.cancel | App\Http\Controllers\ReservationController@cancel | web, web |
| GET, HEAD | reservations/{reservation} | reservations.show | App\Http\Controllers\ReservationController@show | web, web |
| GET, HEAD | reservations/cancellation-success | reservations.cancellation-success | App\Http\Controllers\ReservationController@cancellationSuccess | web, web |
| GET, HEAD | orders | orders.index | App\Http\Controllers\OrderController@index | web, web |
| GET, HEAD | orders/all | orders.all | App\Http\Controllers\OrderController@allOrders | web, web |
| POST | orders/update-cart | orders.update-cart | App\Http\Controllers\OrderController@updateCart | web, web |
| GET, HEAD | orders/create | orders.create | App\Http\Controllers\OrderController@create | web, web |
| POST | orders/store | orders.store | App\Http\Controllers\OrderController@store | web, web |
| GET, HEAD | orders/{order}/summary | orders.summary | App\Http\Controllers\OrderController@summary | web, web |
| GET, HEAD | orders/{order}/edit | orders.edit | App\Http\Controllers\OrderController@edit | web, web |
| DELETE | orders/{order} | orders.destroy | App\Http\Controllers\OrderController@destroy | web, web |
| PUT | orders/{order} | orders.update | App\Http\Controllers\OrderController@update | web, web |
| POST | orders/check-stock | orders.check-stock | App\Http\Controllers\OrderController@checkStock | web, web |
| POST | orders/{order}/print-kot | orders.print-kot | App\Http\Controllers\OrderController@printKOT | web, web |
| POST | orders/{order}/print-bill | orders.print-bill | App\Http\Controllers\OrderController@printBill | web, web |
| POST | orders/{order}/mark-preparing | orders.mark-preparing | App\Http\Controllers\OrderController@markAsPreparing | web, web |
| POST | orders/{order}/mark-ready | orders.mark-ready | App\Http\Controllers\OrderController@markAsReady | web, web |
| POST | orders/{order}/complete | orders.complete | App\Http\Controllers\OrderController@completeOrder | web, web |
| GET, HEAD | orders/takeaway | orders.takeaway.index | App\Http\Controllers\OrderController@indexTakeaway | web, web |
| GET, HEAD | orders/takeaway/create | orders.takeaway.create | App\Http\Controllers\OrderController@createTakeaway | web, web |
| POST | orders/takeaway/store | orders.takeaway.store | App\Http\Controllers\OrderController@storeTakeaway | web, web |
| GET, HEAD | orders/takeaway/{order}/edit | orders.takeaway.edit | App\Http\Controllers\OrderController@editTakeaway | web, web |
| GET, HEAD | orders/takeaway/{order}/summary | orders.takeaway.summary | App\Http\Controllers\OrderController@summary | web, web |
| DELETE | orders/takeaway/{order}/delete | orders.takeaway.destroy | App\Http\Controllers\OrderController@destroyTakeaway | web, web |
| PUT | orders/takeaway/{order} | orders.takeaway.update | App\Http\Controllers\OrderController@updateTakeaway | web, web |
| POST | orders/takeaway/{order}/submit | orders.takeaway.submit | App\Http\Controllers\OrderController@submitTakeaway | web, web |
| GET, HEAD | orders/takeaway/{order} | orders.takeaway.show | App\Http\Controllers\OrderController@showTakeaway | web, web |
| GET, HEAD | branches/{branch}/summary | branches.summary | App\Http\Controllers\BranchController@summary | web, auth:admin |
| PUT | branches/{branch}/regenerate-key | branches.regenerate-key | App\Http\Controllers\BranchController@regenerateKey | web, auth:admin |
| GET, HEAD | organizations | organizations.index | App\Http\Controllers\OrganizationController@index | web, auth:admin |
| GET, HEAD | organizations/create | organizations.create | App\Http\Controllers\OrganizationController@create | web, auth:admin |
| POST | organizations | organizations.store | App\Http\Controllers\OrganizationController@store | web, auth:admin |
| GET, HEAD | organizations/{organization}/edit | organizations.edit | App\Http\Controllers\OrganizationController@edit | web, auth:admin |
| PUT, PATCH | organizations/{organization} | organizations.update | App\Http\Controllers\OrganizationController@update | web, auth:admin |
| DELETE | organizations/{organization} | organizations.destroy | App\Http\Controllers\OrganizationController@destroy | web, auth:admin |
| GET, HEAD | organizations/{organization}/summary | organizations.summary | App\Http\Controllers\OrganizationController@summary | web, auth:admin |
| PUT | organizations/{organization}/regenerate-key | organizations.regenerate-key | App\Http\Controllers\OrganizationController@regenerateKey | web, auth:admin |
| GET, HEAD | organizations/{organization}/branches | branches.index | App\Http\Controllers\BranchController@index | web, auth:admin |
| GET, HEAD | organizations/{organization}/branches/create | branches.create | App\Http\Controllers\BranchController@create | web, auth:admin |
| POST | organizations/{organization}/branches | branches.store | App\Http\Controllers\BranchController@store | web, auth:admin |
| GET, HEAD | organizations/{organization}/branches/{branch}/edit | branches.edit | App\Http\Controllers\BranchController@edit | web, auth:admin |
| PUT | organizations/{organization}/branches/{branch} | branches.update | App\Http\Controllers\BranchController@update | web, auth:admin |
| DELETE | organizations/{organization}/branches/{branch} | branches.destroy | App\Http\Controllers\BranchController@destroy | web, auth:admin |
| GET, HEAD | branches | branches.global | App\Http\Controllers\BranchController@globalIndex | web, auth:admin |
| GET, HEAD | users | users.index | App\Http\Controllers\UserController@index | web, auth:admin |
| GET, HEAD | roles | roles.index | App\Http\Controllers\RoleController@index | web, auth:admin |
| GET, HEAD | subscription-plans | subscription-plans.index | App\Http\Controllers\Admin\SubscriptionPlanController@index | web, auth:admin |
| GET, HEAD | subscription-plans/create | subscription-plans.create | App\Http\Controllers\Admin\SubscriptionPlanController@create | web, auth:admin |
| POST | subscription-plans | subscription-plans.store | App\Http\Controllers\Admin\SubscriptionPlanController@store | web, auth:admin |
| GET, HEAD | subscription-plans/{subscription_plan} | subscription-plans.show | App\Http\Controllers\Admin\SubscriptionPlanController@show | web, auth:admin |
| GET, HEAD | subscription-plans/{subscription_plan}/edit | subscription-plans.edit | App\Http\Controllers\Admin\SubscriptionPlanController@edit | web, auth:admin |
| PUT, PATCH | subscription-plans/{subscription_plan} | subscription-plans.update | App\Http\Controllers\Admin\SubscriptionPlanController@update | web, auth:admin |
| DELETE | subscription-plans/{subscription_plan} | subscription-plans.destroy | App\Http\Controllers\Admin\SubscriptionPlanController@destroy | web, auth:admin |
| GET, HEAD | payments/process | payments.process | App\Http\Controllers\PaymentController@process | web |
| GET, HEAD | orders/payment | orders.payment | App\Http\Controllers\OrderController@payment | web |
| GET, HEAD | roles/assign | roles.assign | App\Http\Controllers\RoleController@assign | web |
| GET, HEAD | payments/create | payments.create | App\Http\Controllers\PaymentController@create | web |
| GET, HEAD | users/assign-role/store | users.assign-role.store | App\Http\Controllers\UserController@assign-role | web |
| GET, HEAD | kitchen/orders/index | kitchen.orders.index | App\Http\Controllers\KitchenController@orders | web |
| GET, HEAD | reservations/index | reservations.index | App\Http\Controllers\ReservationController@index | web |
| GET, HEAD | orders/show | orders.show | App\Http\Controllers\OrderController@show | web |
| PUT | reservations/update | reservations.update | App\Http\Controllers\ReservationController@update | web |
| GET, HEAD | branch | branch | App\Http\Controllers\BranchController@index | web |
| GET, HEAD | organization | organization | App\Http\Controllers\OrganizationController@index | web |
| GET, HEAD | role | role | App\Http\Controllers\RoleController@index | web |
| GET, HEAD | subscription/expired | subscription.expired | App\Http\Controllers\SubscriptionController@expired | web |
| GET, HEAD | subscription/upgrade | subscription.upgrade | App\Http\Controllers\SubscriptionController@upgrade | web |
| GET, HEAD | subscription/required | subscription.required | App\Http\Controllers\SubscriptionController@required | web |
| GET, HEAD | storage/{path} | storage.local | Closure |  |
