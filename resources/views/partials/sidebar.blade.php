<aside class="w-64 bg-white shadow">
    <div class="p-4">
        <h2 class="text-lg font-semibold">{{ Auth::user()?->organization?->name ?? 'N/A' }}</h2>
        @if(Auth::user()?->branch)
            <p class="text-sm text-gray-600">{{ Auth::user()?->branch?->name }}</p>
        @endif
    </div>
    
    <nav class="mt-6">
        @foreach(Auth::user()?->role?->modules ?? [] as $module)
            <a href="{{ route($module->name.'.index') }}" 
               class="block px-4 py-2 text-gray-600 hover:bg-gray-100 {{ request()->routeIs($module->name.'.*') ? 'bg-gray-100' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $module->name)) }}
            </a>
        @endforeach
        
        @can('manage_organization')
            <div class="mt-8 border-t pt-4">
                <p class="px-4 text-xs font-semibold text-gray-500">ADMINISTRATION</p>
                <a href="{{ route('organizations.edit') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">Organization Settings</a>
                @can('manage_branches')
                    <a href="{{ route('branches.index') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">Branches</a>
                @endcan
                @can('manage_users')
                    <a href="{{ route('users.index') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">Users</a>
                @endcan
                @can('manage_roles')
                    <a href="{{ route('roles.index') }}" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">Roles</a>
                @endcan
            </div>
        @endcan
    </nav>
</aside>
