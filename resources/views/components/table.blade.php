@props(['headers'])

<div class="rounded-lg border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto rounded-lg">
        <table class="w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur">
                <tr>
                    @foreach($headers as $header)
                    <th 
                        scope="col" 
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap"
                    >
                        {{ $header }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-4 py-6 text-center text-gray-400">
                            No records found
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>