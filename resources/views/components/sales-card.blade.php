@props([
    'title',
    'value',
    'change',
    'changeType' => 'up', // 'up' or 'down'
    'icon'
])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p>
        </div>
        <div class="flex items-center">
            <span class="text-sm font-medium {{ $changeType === 'up' ? 'text-green-600' : 'text-red-600' }}">
                {{ $change }}
            </span>
            <svg class="w-5 h-5 ml-1 {{ $changeType === 'up' ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20">
                @if($changeType === 'up')
                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                @else
                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                @endif
            </svg>
        </div>
    </div>
</div>