# Guest Routes

Generated on: 2025-06-25 17:00:20
Total routes: 17

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|-----------|
| GET, HEAD | guest/menu | guest.menu.index | App\Http\Controllers\Guest\GuestController@viewMenu | web |
| GET, HEAD | guest/menu/date/{date} | guest.menu.date | App\Http\Controllers\Guest\GuestController@viewMenuByDate | web |
| GET, HEAD | guest/menu/special | guest.menu.special | App\Http\Controllers\Guest\GuestController@viewSpecialMenu | web |
| POST | guest/cart/add | guest.cart.add | App\Http\Controllers\Guest\GuestController@addToCart | web |
| POST | guest/cart/update | guest.cart.update | App\Http\Controllers\Guest\GuestController@updateCart | web |
| DELETE | guest/cart/remove/{itemId} | guest.cart.remove | App\Http\Controllers\Guest\GuestController@removeFromCart | web |
| GET, HEAD | guest/cart | guest.cart.view | App\Http\Controllers\Guest\GuestController@viewCart | web |
| DELETE | guest/cart/clear | guest.cart.clear | App\Http\Controllers\Guest\GuestController@clearCart | web |
| POST | guest/order/create | guest.order.create | App\Http\Controllers\Guest\GuestController@createOrder | web |
| GET, HEAD | guest/order/{orderNumber}/track | guest.order.track | App\Http\Controllers\Guest\GuestController@trackOrder | web |
| GET, HEAD | guest/order/{orderNumber}/details | guest.order.details | App\Http\Controllers\Guest\GuestController@orderDetails | web |
| GET, HEAD | guest/reservation/create | guest.reservation.create | App\Http\Controllers\Guest\GuestController@createReservation | web |
| POST | guest/reservation/store | guest.reservation.store | App\Http\Controllers\Guest\GuestController@storeReservation | web |
| GET, HEAD | guest/reservation/{confirmationCode}/details | guest.reservation.details | App\Http\Controllers\Guest\GuestController@reservationDetails | web |
| GET, HEAD | guest/session/info | guest.session.info | App\Http\Controllers\Guest\GuestController@sessionInfo | web |
| GET, HEAD | guest/order/confirmation | guest.order.confirmation | App\Http\Controllers\GuestController@order | web |
| GET, HEAD | guest/reservation/confirmation | guest.reservation.confirmation | App\Http\Controllers\GuestController@reservation | web |
