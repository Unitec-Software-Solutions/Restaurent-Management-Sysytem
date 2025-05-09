@props(['items'])

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Selling Items</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Category</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Quantity Sold</th>
                    <th scope="col" class="px-6 py-3">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ $item->category->name }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                        <td class="px-6 py-4">{{ $item->quantity_sold }}</td>
                        <td class="px-6 py-4">${{ number_format($item->revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">No top selling items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>