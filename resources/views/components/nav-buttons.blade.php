@props(['items' => [], 'active' => ''])

<div class="sticky top-0 z-10 mb-6">
    <div class="flex space-x-1 items-center justify-center p-1">
        @foreach ($items as $item)
            @php
                $isActive = $active === $item['name'];
            @endphp

            <a href="{{ $item['link'] }}"
               class="px-6 py-2 rounded-lg bg-[#eaecff] transition-all duration-200 text-sm font-medium
                      {{ $isActive 
                         ? 'bg-[#515DEF] text-indigo-700 border-b-2 border-indigo-500 font-semibold' 
                         : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200' }}">
                {{ $item['name'] }}
            </a>
        @endforeach
    </div>
</div>