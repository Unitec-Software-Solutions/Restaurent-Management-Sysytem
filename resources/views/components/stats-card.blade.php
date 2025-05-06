@props([
    'title',
    'value',
    'icon',
    'colorClass' => 'bg-indigo-500',
    'trend' => null,
    'trendValue' => null
])

<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md p-3 {{ $colorClass }}">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $icon !!}
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $title }}</dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900">{{ $value }}</div>
                        @if($trend)
                            <div class="ml-2 flex items-baseline text-sm font-semibold {{ $trend === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                <svg class="self-center flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    @if($trend === 'up')
                                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    @else
                                        <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    @endif
                                </svg>
                                <span class="sr-only">{{ $trend === 'up' ? 'Increased' : 'Decreased' }} by</span>
                                {{ $trendValue }}
                            </div>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>