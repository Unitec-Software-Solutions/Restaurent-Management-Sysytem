@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-10">
    <h1 class="text-2xl font-bold text-gray-900">🧪 Web App Function Test Page</h1>

    {{-- ✅ ADMIN FUNCTIONS --}}
<div class="space-y-6">
    <h2 class="text-2xl font-semibold text-indigo-700 border-b border-indigo-200 pb-2">🛠️ Admin Functions</h2>
    <p class="text-sm text-gray-600">
        These pages are designed for administrative operations such as managing reservations, orders, inventory, stock, suppliers, and user profiles.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        <x-test-tile label="Reservations" route="admin.reservations.index" />

        <!-- Orders -->
        <x-test-tile label="Orders (Admin)" route="admin.orders.index" disabled />
        <x-test-tile label="Create Takeaway" route="admin.orders.takeaway.create" disabled />

        <!-- Inventory -->
        <x-test-tile label="Inventory Dashboard" route="admin.inventory.index" />
        <x-test-tile label="Items" route="admin.inventory.items.index" />
        <x-test-tile label="Add Item" route="admin.inventory.items.create" />
        <x-test-tile label="Item Categories (json)" route="admin.inventory.categories.index" />

        <!-- Stock -->
        <x-test-tile label="Stock Transactions" route="admin.inventory.stock.index" />
        {{-- <x-test-tile label="New Stock Entry" route="admin.inventory.stock.create" /> --}}

        <!-- Suppliers -->
        <x-test-tile label="Suppliers" route="admin.suppliers.index" />
        <x-test-tile label="Add Supplier" route="admin.suppliers.create" />

        <!-- Admin Utilities -->
        <x-test-tile label="Profile" route="admin.profile.index" />
    </div>
</div>

{{-- 🌐 PUBLIC FUNCTIONS --}}
<div class="space-y-6">
    <h2 class="text-2xl font-semibold text-green-700 border-b border-green-200 pb-2">🌐 Public Functions</h2>
    <p class="text-sm text-gray-600">
        These views represent the customer-facing side of the system.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        <x-test-tile label="Home" route="home" />
        <x-test-tile label="Customer Dashboard" route="customer.dashboard" />
        <x-test-tile label="Create Reservation" route="reservations.create" />
        <!-- <x-test-tile label="Review Reservation" route="reservations.review" /> -->
        <x-test-tile label="Create Order" route="orders.create" />
    </div>
</div>


    {{-- 🧪 ADMIN SAMPLE PAGES --}}
    <div class="space-y-6">
    <h2 class="text-2xl font-semibold text-indigo-700 border-b border-indigo-200 pb-2">
        🧪 Admin Sample Pages
    </h2>
    <p class="text-sm text-gray-600">
        These are only sample admin pages for UI demonstration purposes. They do not contain backend logic or functional processing — primarily form views and layout placeholders.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        <x-test-tile label="Dashboard" route="admin.dashboard" />
        <x-test-tile label="Customers" route="admin.customers.index" />
        <x-test-tile label="Digital Menu" route="admin.digital-menu.index" />
        <x-test-tile label="Settings" route="admin.settings.index" />
        <x-test-tile label="Reports" route="admin.reports.index" />
        <x-test-tile label="Web Test Page" route="admin.testpage" />
    </div>
</div>
</div>
@endsection
