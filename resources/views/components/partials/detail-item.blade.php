@props(['label'])

<div class="border-b pb-2 last:border-0">
    <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
    <dd class="mt-1 text-gray-900">
        {{ $slot }}
    </dd>
</div>