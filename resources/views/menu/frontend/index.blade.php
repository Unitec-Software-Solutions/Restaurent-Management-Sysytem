<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Menu - {{ config('app.name') }}</title>
    <!-- Use Laravel Mix for Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white flex flex-col p-4 space-y-2">
            <div class="text-2xl font-bold mb-4">{{ config('app.name') }}</div>
            <a href="{{ route('dashboard') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Dashboard</a>
            <a href="{{ route('inventory') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Inventory Management</a>
            <a href="{{ route('reservations') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Reservation Management</a>
            <a href="{{ route('orders') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Order Management</a>
            <a href="{{ route('reports') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Reports</a>
            <a href="{{ route('customers') }}" class="bg-blue-700 py-2 px-4 rounded hover:bg-blue-600 text-white no-underline">Customer Management</a>
            <div class="mt-auto">
                <a href="{{ route('admin.') }}" class="bg-blue-600 w-full py-2 px-4 rounded hover:bg-blue-500 text-white no-underline">Digital Menu</a>
                <a href="{{ route('settings') }}" class="bg-blue-600 w-full py-2 px-4 rounded hover:bg-blue-500 text-white no-underline mt-2">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 w-full py-2 px-4 rounded hover:bg-red-500 mt-2">Sign Out</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Digital Menu</h1>
                <div class="flex items-center space-x-2">
                    <div class="text-right">
                        <div class="font-semibold">@if ($user)
                            {{ $user->name }}
                        @else
                            Guest
                        @endif</div>
                        <div class="text-sm text-gray-500">{{ $user?->role ?? 'Guest' }}</div>
                    </div>
                    <div class="w-10 h-10 bg-gray-300 rounded-full"></div>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="flex space-x-4 mb-6">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">All</button>
                @if (isset($categories) && $categories->count() > 0)
                    @foreach ($categories as $category)
                        <button class="bg-gray-200 px-4 py-2 rounded">{{ $category->name }}</button>
                    @endforeach
                @else
                    <p>No categories available.</p>
                @endif
                <a href="{{ route('menu-items.create') }}" class="ml-auto bg-purple-600 text-white px-4 py-2 rounded">Add Menu Item +</a>
            </div>

            @if (isset($categories) && $categories->count() > 0)
                @foreach ($categories as $category)
                    <!-- Menu Sections -->
                    <section class="mb-8">
                        <h2 class="text-xl font-bold text-blue-700 mb-4">{{ strtoupper($category->name) }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($category->menuItems as $item)
                            <div class="flex items-center justify-between bg-white p-4 rounded shadow">
                                <div class="flex items-center space-x-4">
                                    <img src="{{ asset('storage/' . $item->image) }}" class="w-12 h-12" alt="{{ $item->name }}"/>
                                    <div>
                                        <div class="font-semibold">{{ $item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->description }}</div>
                                    </div>
                                </div>
                                <div class="text-blue-600 font-bold">{{ $item->formatted_price }}</div>
                            </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        </main>
    </div>
</body>
</html>