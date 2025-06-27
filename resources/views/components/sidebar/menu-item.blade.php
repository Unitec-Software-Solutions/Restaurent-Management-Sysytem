{{-- Individual Menu Item Component --}}
@if(!isset($item['permission']) || $item['permission'] === null || (auth()->user() && auth()->user()->can($item['permission'])))
    <li class="{{ isset($item['sub_items']) && count($item['sub_items']) > 0 ? 'menu-item-with-submenu' : '' }}">
        <a href="{{ isset($item['route']) ? (isset($item['route_params']) ? route($item['route'], $item['route_params']) : route($item['route'])) : '#' }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl border transition-colors
            {{ ($item['active'] ?? false)
                ? 'bg-white text-gray-700 border-white'
                : 'bg-transparent text-white border-white hover:bg-white/10' }}
            {{ isset($item['sub_items']) && count($item['sub_items']) > 0 ? 'submenu-toggle' : '' }}">

            {{-- Icon --}}
            @if(isset($item['icon']))
                @if(str_starts_with($item['icon'], 'fa'))
                    <i class="{{ $item['icon'] }} w-5 text-center"></i>
                @else
                    @includeIf('partials.icons.' . $item['icon'])
                @endif
            @endif

            {{-- Label --}}
            <span class="font-medium flex-1">{{ $item['name'] }}</span>

            {{-- Badge --}}
            @if(isset($item['badge']) && $item['badge'] !== null && $item['badge'] > 0)
                <span id="{{ strtolower(str_replace([' ', '-'], '_', $item['name'])) }}_badge" 
                      class="bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
                    {{ $item['badge'] }}
                </span>
            @endif

            {{-- Submenu arrow --}}
            @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
                <svg class="w-4 h-4 transition-transform submenu-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            @endif
        </a>

        {{-- Submenu --}}
        @if(isset($item['sub_items']) && count($item['sub_items']) > 0)
            <ul class="submenu ml-8 mt-1 space-y-1 {{ ($item['active'] ?? false) ? '' : 'hidden' }}">
                @foreach($item['sub_items'] as $subItem)
                    @if(!isset($subItem['permission']) || $subItem['permission'] === null || (auth()->user() && auth()->user()->can($subItem['permission'])))
                        <li>
                            <a href="{{ isset($subItem['route']) ? (isset($subItem['route_params']) ? route($subItem['route'], $subItem['route_params']) : route($subItem['route'])) : '#' }}"
                                class="flex items-center gap-2 px-3 py-1 rounded border transition-colors text-sm
                                {{ request()->routeIs($subItem['route'] ?? '')
                                    ? 'bg-white text-gray-700 border-white'
                                    : 'bg-transparent text-white border-white hover:bg-white/10' }}">
                                
                                {{-- Sub Icon --}}
                                @if(isset($subItem['icon']))
                                    @if(str_starts_with($subItem['icon'], 'fa'))
                                        <i class="{{ $subItem['icon'] }} w-4 text-center"></i>
                                    @else
                                        @includeIf('partials.icons.' . $subItem['icon'])
                                    @endif
                                @endif
                                
                                <span>{{ $subItem['name'] }}</span>
                                
                                {{-- Sub Badge --}}
                                @if(isset($subItem['badge']) && $subItem['badge'] !== null && $subItem['badge'] > 0)
                                    <span class="bg-red-500 text-white text-xs rounded-full px-1 py-0.5 min-w-[16px] text-center ml-auto">
                                        {{ $subItem['badge'] }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </li>
@endif
