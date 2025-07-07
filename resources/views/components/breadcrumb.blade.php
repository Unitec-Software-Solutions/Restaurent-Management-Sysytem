{{-- Enhanced Breadcrumb Component for Menu Items and Inventory --}}
@props([
    'items' => [],
    'current' => '',
    'type' => 'default' // default, menu-items, inventory
])

<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-1 text-sm text-gray-600">
        {{-- Home/Dashboard --}}
        <li>
            <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">
                <i class="fas fa-home mr-1"></i>Dashboard
            </a>
        </li>

        {{-- Items from props --}}
        @foreach($items as $item)
            <li class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                @if($loop->last)
                    <span class="font-medium
                        @if($type === 'menu-items') text-orange-600
                        @elseif($type === 'inventory') text-blue-600
                        @else text-indigo-600
                        @endif
                    ">{{ $item['name'] }}</span>
                @else
                    <a href="{{ $item['url'] ?? '#' }}" class="hover:text-indigo-600 transition-colors">
                        {{ $item['name'] }}
                    </a>
                @endif
            </li>
        @endforeach

        {{-- Current page if provided --}}
        @if($current)
            <li class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                <span class="font-medium
                    @if($type === 'menu-items') text-orange-600
                    @elseif($type === 'inventory') text-blue-600
                    @else text-gray-900
                    @endif
                ">{{ $current }}</span>
            </li>
        @endif
    </ol>
</nav>

{{-- Context helper badges --}}
@if($type === 'menu-items')
    <div class="flex items-center gap-2 mb-4">
        <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">
            🍳 Menu Items Section
        </span>
        <span class="text-xs text-gray-500">Both KOT recipes and buy & sell items</span>
    </div>
@elseif($type === 'inventory')
    <div class="flex items-center gap-2 mb-4">
        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
            📦 Inventory Section
        </span>
        <span class="text-xs text-gray-500">Buy & sell items only - not KOT recipes</span>
    </div>
@endif
