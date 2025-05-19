<aside class="fixed top-0 left-0 h-screen w-64 bg-blue-800 text-white p-6 overflow-y-auto">
    <h1 class="text-2xl font-bold mb-10">Admin Panel</h1>
    <nav class="space-y-4">
        <a href="{{ route('dashboard') }}" 
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
        </a>
        <a href="{{ route('menu.admin-index') }}" 
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-utensils mr-2"></i>Digital Menu
        </a>
        <a href="{{ route('inventory') }}" 
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-boxes mr-2"></i>Inventory
        </a>
        <a href="{{ route('reports') }}" 
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-chart-bar mr-2"></i>Reports
        </a>
        <a href="{{ route('settings') }}" 
           class="block py-2 px-4 rounded hover:bg-blue-700">
            <i class="fas fa-cog mr-2"></i>Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" 
                    class="w-full text-left block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
            </button>
        </form>
    </nav>
</aside> 