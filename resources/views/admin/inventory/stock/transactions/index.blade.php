@extends('layouts.admin')
@php
    // Pass the controller instance to the view for helper methods
    $controller = app(\App\Http\Controllers\admin\ItemTransactionController::class);

    // Default date range: last 30 days
    $defaultDateFrom = now()->subDays(29)->format('Y-m-d');
    $defaultDateTo = now()->format('Y-m-d');
    $dateFrom = request('date_from', $defaultDateFrom);
    $dateTo = request('date_to', $defaultDateTo);
@endphp
@section('header-title', 'Stock Transactions')

@section('content')

    {{-- Debug Info Card for Stock Transactions --}}
    {{-- @if (config('app.debug'))
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-medium text-indigo-800">🔍 Stock Transactions Debug Info</h3>
            <a href="{{ route('admin.inventory.stock.transactions.index', array_merge(request()->query(), ['debug' => 1])) }}"
               class="text-xs text-indigo-600 hover:text-indigo-800">
                Full Debug (@dd)
            </a>
        </div>
        <div class="text-xs text-indigo-700 mt-2 grid grid-cols-4 gap-4">
            <div>
                <p><strong>Transactions Variable:</strong> {{ isset($transactions) ? 'Set (' . $transactions->count() . ')' : 'NOT SET' }}</p>
                <p><strong>DB Transactions:</strong> {{ \App\Models\ItemTransaction::count() }}</p>
            </div>
            <div>
                <p><strong>Branches Variable:</strong> {{ isset($branches) ? 'Set (' . $branches->count() . ')' : 'NOT SET' }}</p>
                <p><strong>Items Variable:</strong> {{ isset($items) ? 'Set (' . $items->count() . ')' : 'NOT SET' }}</p>
            </div>
            <div>
                <p><strong>Date Range:</strong> {{ $dateFrom }} to {{ $dateTo }}</p>
                <p><strong>Search:</strong> {{ request('search', 'None') }}</p>
            </div>
            <div>
                <p><strong>Transaction Type:</strong> {{ request('transaction_type', 'All') }}</p>
                <p><strong>Branch Filter:</strong> {{ request('branch_id', 'All') }}</p>
            </div>
        </div>
    </div>
@endif --}}

    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg ">
                <x-nav-buttons :items="[
                    ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                    ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                    ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                    ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                    ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                    ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
                ]" active="Transactions" />
            </div>

            <!-- Filters with Export -->
            <x-module-filters :action="route('admin.inventory.stock.transactions.index')" :export-permission="'export_inventory'" :export-filename="'inventory_transactions_export.xlsx'">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Item name or code"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <div class="relative">
                        <select name="branch_id"
                            class="w-full pl-4 pr-8 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                    <div class="relative">
                        <select name="transaction_type"
                            class="w-full pl-4 pr-8 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            @foreach ([
            'purchase_order' => 'Purchase Order',
            'sales_order' => 'Sales Order',
            'adjustment' => 'Adjustment',
            'audit' => 'Audit',
            'gtn_outgoing' => 'GTN Outgoing',
            'gtn_incoming' => 'GTN Incoming',
            'gtn_rejection' => 'GTN Rejection',
            'write_off' => 'Write Off',
            'transfer' => 'Transfer',
            'usage' => 'Usage',
            'production_issue' => 'Production Issue',
            'production_in' => 'Production In',
        ] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ request('transaction_type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </x-module-filters>



            <!-- Transaction List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Stock Transactions</h2>
                        <p class="text-sm text-gray-500">
                            @if (
                                $transactions instanceof \Illuminate\Pagination\LengthAwarePaginator ||
                                    $transactions instanceof \Illuminate\Pagination\Paginator)
                                Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of
                                {{ $transactions->total() ?? 0 }} Transactions
                            @else
                                {{ $transactions->count() }} Transactions
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            @if(Auth::guard('admin')->user()->is_super_admin)
                                Organization: All Organizations (Super Admin)
                            @elseif(Auth::guard('admin')->user()->organization)
                                Organization: {{ Auth::guard('admin')->user()->organization->name }}
                            @else
                                Organization: Not Assigned
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" onclick="window.print()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg flex items-center">
                            <i class="fas fa-print mr-2"></i> Print
                        </button>
                        <a href="#"
                            class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                            <i class="fas fa-file-export mr-2"></i> Export
                        </a>

                    </div>
                </div>



                <!-- Transactions Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Branch</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($transactions as $tx)
                                    @php
                                        $typeColors = [
                                            'purchase_order' => 'bg-blue-100 text-blue-800',
                                            'sales_order' => 'bg-purple-100 text-purple-800',
                                            'adjustment' => 'bg-yellow-100 text-yellow-800',
                                            'audit' => 'bg-gray-100 text-gray-800',
                                            'gtn_outgoing' => 'bg-red-100 text-red-800',
                                            'gtn_incoming' => 'bg-green-100 text-green-800',
                                            'gtn_rejection' => 'bg-orange-100 text-orange-800',
                                            'write_off' => 'bg-red-100 text-red-800',
                                            'transfer' => 'bg-indigo-100 text-indigo-800',
                                            'usage' => 'bg-purple-100 text-purple-800',
                                            'production_issue' => 'bg-orange-100 text-orange-800',
                                            'production_in' => 'bg-green-100 text-green-800',
                                        ];
                                        $isIn = !$controller->isStockOut($tx->transaction_type);
                                    @endphp

                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        onclick="window.location='{{ route('admin.inventory.stock.show', $tx->id) }}'">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $tx->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $tx->created_at->format('h:i A') }}</div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($tx->item)
                                                <div class="font-medium text-gray-900">{{ $tx->item->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $tx->item->item_code }}</div>
                                            @else
                                                <div class="text-gray-500 italic">Item deleted</div>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ optional($tx->branch)->name ?? '-' }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $typeColors[$tx->transaction_type] ?? ($isIn ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucwords(str_replace('_', ' ', $tx->transaction_type)) }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="{{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                                                @php
                                                    // Ensure the correct sign for outgoing transactions
                                                    $quantity = number_format($tx->quantity, 2);
                                                @endphp
                                                {{ $quantity }}
                                                <span
                                                    class="text-xs text-gray-500">{{ $tx->item->unit_of_measurement ?? 'N/A' }}</span>
                                            </div>
                                        </td>


                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500  truncate">
                                                @php
                                                    $words = str_word_count(strip_tags($tx->notes), 1);
                                                    $preview = implode(' ', array_slice($words, 0, 3));
                                                    $more = count($words) > 3 ? '...' : '';
                                                @endphp
                                                {{ $preview }}{{ $more }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('admin.inventory.stock.show', $tx->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (auth()->user()->can('edit_inventory'))
                                                    <a href="{{ route('admin.inventory.stock.edit', ['item_id' => $tx->inventory_item_id, 'branch_id' => $tx->branch_id]) }}"
                                                        class="text-blue-600 hover:text-blue-800" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No transactions found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($transactions->hasPages())
                        <div class="p-4 border-t">
                            {{ $transactions->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection
