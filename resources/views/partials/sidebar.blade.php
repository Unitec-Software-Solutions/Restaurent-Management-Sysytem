<aside class="fixed top-0 left-0 h-screen w-64 bg-blue-800 text-white p-6 overflow-y-auto">
    <h1 class="text-2xl font-bold mb-10">RM SYSTEMS</h1>
    <nav class="space-y-4">
        <a href="{{ route('dashboard') }}" 
           onclick="console.log('Dashboard clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
        </a>
        <a href="{{ route('inventory') }}" 
           onclick="console.log('Inventory clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-boxes mr-2"></i>Inventory
        </a>
        <a href="{{ route('reservations') }}" 
           onclick="console.log('Reservations clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-calendar-check mr-2"></i>Reservations
        </a>
        <a href="{{ route('orders') }}" 
           onclick="console.log('Orders clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-receipt mr-2"></i>Orders
        </a>
        <a href="{{ route('reports') }}" 
           onclick="console.log('Reports clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-chart-bar mr-2"></i>Reports
        </a>
        <a href="{{ route('customers') }}" 
           onclick="console.log('Customers clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-users mr-2"></i>Customers
        </a>
        <hr class="my-4 border-blue-600">

        <!-- Digital Menu Link -->
        @auth
            @if(auth()->user()->isAdmin)
                <a href="{{ route('admin.menu') }}" 
                   class="block py-2 px-4 rounded hover:bg-blue-700 {{ request()->routeIs('admin.menu') ? 'bg-white text-blue-800 font-semibold' : '' }}">
                    <i class="fas fa-utensils mr-2"></i>Digital Menu (Admin)
                </a>
            @else
                <a href="{{ route('customer.menu') }}" 
                   class="block py-2 px-4 rounded hover:bg-blue-700 {{ request()->routeIs('customer.menu') ? 'bg-white text-blue-800 font-semibold' : '' }}">
                    <i class="fas fa-utensils mr-2"></i>Digital Menu
                </a>
            @endif
        @endauth

        <a href="{{ route('settings') }}" 
           onclick="console.log('Settings clicked')"
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-cog mr-2"></i>Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" 
                    onclick="console.log('Sign Out clicked')"
                    class="w-full text-left block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
            </button>
        </form>
    </nav>
</aside>