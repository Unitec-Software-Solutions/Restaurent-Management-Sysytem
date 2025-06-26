<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-full pt-16 bg-[#515DEF] border-r border-[#515DEF] dark:border-[#515DEF] transition-transform duration-300 ease-in-out transform -translate-x-full lg:translate-x-0"
    aria-label="Sidebar">

    <div class="flex flex-col h-full text-white">
        {{-- Scrollable top section --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Logo/Header --}}
            <div class="flex items-center gap-2 px-4 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#515DEF]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-white font-bold text-xl">RM SYSTEMS</span>
            </div>

            {{-- Navigation --}}
            <div class="px-4 pb-4">
                <ul class="space-y-2">
                    @foreach ($menuItems as $item)
                        {{-- Special handling for Orders menu --}}
                        @if($item['title'] === 'Orders')
                            <li>
                                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                                    <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                                        class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors duration-200
                                        {{ request()->routeIs('admin.orders.*') 
                                            ? 'bg-white text-gray-700 border-white' 
                                            : 'bg-transparent text-white border-white hover:bg-white/10' }}"
                                        data-route="{{ $item['route'] }}">

                                        @if ($item['icon_type'] === 'svg')
                                            @if(view()->exists('partials.icons.' . $item['icon']))
                                                @include('partials.icons.' . $item['icon'])
                                            @else
                                                <i class="fas fa-shopping-cart w-5 text-center"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-{{ $item['icon'] }} w-5 text-center"></i>
                                        @endif
                                        
                                        <span class="font-medium">{{ $item['title'] }}</span>
                                        
                                        @if(isset($item['badge']) && $item['badge'] > 0)
                                            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif

                                        @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                                            <i class="fas fa-chevron-down w-4 text-center ml-auto transition-transform duration-200" 
                                               data-submenu-toggle="{{ $item['route'] }}"></i>
                                        @endif
                                    </a>

                                    {{-- Orders sub-menu --}}
                                    @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                                        <ul class="ml-8 mt-1 space-y-1 {{ request()->routeIs('admin.orders.*') ? 'block' : 'hidden' }}" 
                                            data-submenu="{{ $item['route'] }}">
                                            @foreach ($item['sub_items'] as $subItem)
                                                @if(\Illuminate\Support\Facades\Route::has($subItem['route']))
                                                    <li>
                                                        <a href="{{ route($subItem['route'], $subItem['route_params'] ?? []) }}"
                                                            class="flex items-center gap-2 px-3 py-1 rounded border transition-colors duration-200 text-sm
                                                            {{ request()->routeIs($subItem['route']) 
                                                                ? 'bg-white text-gray-700 border-white' 
                                                                : 'bg-transparent text-white border-white hover:bg-white/10' }}"
                                                            data-route="{{ $subItem['route'] }}">
                                                            
                                                            @if ($subItem['icon_type'] === 'svg')
                                                                @if(view()->exists('partials.icons.' . $subItem['icon']))
                                                                    @include('partials.icons.' . $subItem['icon'])
                                                                @else
                                                                    <i class="fas fa-circle w-4 text-center text-gray-400"></i>
                                                                @endif
                                                            @else
                                                                <i class="fas fa-{{ $subItem['icon'] }} w-4 text-center"></i>
                                                            @endif
                                                            
                                                            <span>{{ $subItem['title'] }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif
                            </li>
                        @else
                            {{-- Regular menu items --}}
                            <li>
                                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                                    <a href="{{ route($item['route'], $item['route_params'] ?? []) }}"
                                        class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors duration-200
                                        {{ request()->routeIs($item['route'] . '*') 
                                            ? 'bg-white text-gray-700 border-white' 
                                            : 'bg-transparent text-white border-white hover:bg-white/10' }}"
                                        data-route="{{ $item['route'] }}">

                                        @if ($item['icon_type'] === 'svg')
                                            @if(view()->exists('partials.icons.' . $item['icon']))
                                                @include('partials.icons.' . $item['icon'])
                                            @else
                                                <i class="fas fa-circle w-5 text-center text-gray-400" title="Icon missing: {{ $item['icon'] }}"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-{{ $item['icon'] }} w-5 text-center"></i>
                                        @endif
                                        
                                        <span class="font-medium">{{ $item['title'] }}</span>
                                        
                                        @if(isset($item['badge']) && $item['badge'] > 0)
                                            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif

                                        @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                                            <i class="fas fa-chevron-down w-4 text-center ml-auto transition-transform duration-200" 
                                               data-submenu-toggle="{{ $item['route'] }}"></i>
                                        @endif
                                    </a>

                                    {{-- Sub-menu items --}}
                                    @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                                        <ul class="ml-8 mt-1 space-y-1 {{ request()->routeIs($item['route'] . '*') ? 'block' : 'hidden' }}" 
                                            data-submenu="{{ $item['route'] }}">
                                            @foreach ($item['sub_items'] as $subItem)
                                                @if(\Illuminate\Support\Facades\Route::has($subItem['route']))
                                                    <li>
                                                        <a href="{{ route($subItem['route'], $subItem['route_params'] ?? []) }}"
                                                            class="flex items-center gap-2 px-3 py-1 rounded border transition-colors duration-200 text-sm
                                                            {{ request()->routeIs($subItem['route']) 
                                                                ? 'bg-white text-gray-700 border-white' 
                                                                : 'bg-transparent text-white border-white hover:bg-white/10' }}"
                                                            data-route="{{ $subItem['route'] }}">
                                                            
                                                            @if ($subItem['icon_type'] === 'svg')
                                                                @if(view()->exists('partials.icons.' . $subItem['icon']))
                                                                    @include('partials.icons.' . $subItem['icon'])
                                                                @else
                                                                    <i class="fas fa-circle w-4 text-center text-gray-400"></i>
                                                                @endif
                                                            @else
                                                                <i class="fas fa-{{ $subItem['icon'] }} w-4 text-center"></i>
                                                            @endif
                                                            
                                                            <span>{{ $subItem['title'] }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                @else
                                    {{-- Route doesn't exist - show disabled item --}}
                                    <span class="flex items-center gap-3 px-4 py-2 rounded-xl border text-gray-400 cursor-not-allowed"
                                          title="Route not available: {{ $item['route'] }}">
                                        <i class="fas fa-exclamation-triangle w-5 text-center"></i>
                                        <span class="font-medium">{{ $item['title'] }}</span>
                                        <small class="ml-auto text-xs">(N/A)</small>
                                    </span>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>

                {{-- Debug Information (only in development) --}}
                @if(config('app.debug'))
                    <div class="mt-6 p-3 bg-black/20 rounded-lg">
                        <h4 class="text-xs font-semibold mb-2 text-white/80">Debug Info</h4>
                        <div class="text-xs text-white/60 space-y-1">
                            <div>Guard: {{ auth()->getDefaultDriver() }}</div>
                            <div>User: {{ $currentUser ? $currentUser->name : 'Not authenticated' }}</div>
                            <div>Routes: {{ count($menuItems) }} available</div>
                            <div id="auth-status" class="text-green-400">Checking...</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sticky bottom section with logout only --}}
        <div class="px-4 py-4 border-t border-[#6A71F0]">
            <ul class="space-y-2">
                <li>
                    <button onclick="toggleLogoutModal()"
                        class="w-full text-left flex items-center border gap-3 rounded-xl px-4 py-2 transition-colors hover:bg-[#6A71F0]">
                        @if(view()->exists('partials.icons.log-out'))
                            @include('partials.icons.log-out')
                        @else
                            <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        @endif
                        <span class="font-medium">Sign Out</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</aside>

{{-- Sidebar Enhancement Scripts --}}
@if(config('app.debug'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time authentication status monitor
    function checkAuthStatus() {
        fetch('/admin/auth/debug')
            .then(response => response.json())
            .then(data => {
                const statusEl = document.getElementById('auth-status');
                if (statusEl) {
                    statusEl.textContent = data.auth_admin_check ? '✓ Authenticated' : '✗ Not authenticated';
                    statusEl.className = data.auth_admin_check ? 'text-green-400' : 'text-red-400';
                }
            })
            .catch(error => {
                console.error('Auth check failed:', error);
                const statusEl = document.getElementById('auth-status');
                if (statusEl) {
                    statusEl.textContent = '⚠ Check failed';
                    statusEl.className = 'text-yellow-400';
                }
            });
    }

    // Enhanced submenu toggle functionality
    document.querySelectorAll('[data-submenu-toggle]').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu-toggle');
            const submenu = document.querySelector(`[data-submenu="${submenuId}"]`);
            
            if (submenu) {
                submenu.classList.toggle('hidden');
                this.classList.toggle('rotate-180');
                
                // Store submenu state in localStorage
                const isOpen = !submenu.classList.contains('hidden');
                localStorage.setItem(`submenu-${submenuId}`, isOpen ? 'open' : 'closed');
            }
        });
    });

    // Restore submenu states from localStorage
    document.querySelectorAll('[data-submenu]').forEach(submenu => {
        const route = submenu.getAttribute('data-submenu');
        const state = localStorage.getItem(`submenu-${route}`);
        const toggle = document.querySelector(`[data-submenu-toggle="${route}"]`);
        
        if (state === 'open') {
            submenu.classList.remove('hidden');
            if (toggle) toggle.classList.add('rotate-180');
        }
    });

    // Monitor sidebar link clicks for debugging
    document.querySelectorAll('[data-route]').forEach(link => {
        link.addEventListener('click', function(e) {
            const route = this.getAttribute('data-route');
            console.log('Sidebar navigation:', {
                route: route,
                href: this.href,
                timestamp: new Date().toISOString()
            });

            // Check for potential redirect loops
            if (this.href.includes('/admin/login')) {
                e.preventDefault();
                console.error('🚨 Redirect loop detected!', {
                    route: route,
                    href: this.href,
                    currentUrl: window.location.href
                });
                alert('Authentication error detected. Please check console for details.');
            }
        });
    });

    // Initial auth check
    checkAuthStatus();
    
    // Periodic auth check (every 30 seconds)
    setInterval(checkAuthStatus, 30000);
});
</script>
@endif
