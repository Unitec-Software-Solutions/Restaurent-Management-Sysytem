@extends('layouts.admin')
@section('header-title', 'GRN List')
{{-- @section('header-subtitle', 'Manage all goods received notes for your organization.') --}}


@section('content')
    <!-- Page Content -->
    <div class="p-4 rounded-lg">
        <!-- Header with buttons                         inventory path                              -->
        <div class="sticky top-0 z-10 mb-6">
            <x-nav-buttons :items="[
                ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
            ]" active="Goods Received Notes" />
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.grn.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search GRN</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter GRN number" aria-label="Search GRN" autocomplete="off"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- PO Number Filter (Disabled) -->
                <div>
                    <label for="po_number" class="block text-sm font-medium text-gray-400 mb-1">PO Number</label>
                    <input type="text" name="po_number" id="po_number" placeholder="PO Number"
                        value="{{ request('po_number') }}"
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100  focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        disabled>
                </div>



                <!-- Branch Filter -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" id="branch_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="start_date" id="start_date"
                            value="{{ request('start_date', $startDate ?? \Carbon\Carbon::now()->subDays(30)->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="end_date" id="end_date"
                            value="{{ request('end_date', $endDate ?? \Carbon\Carbon::now()->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.grn.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>

                <!-- Supplier Filter -->
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" id="supplier_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Verified" {{ request('status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>





                <!-- Sort By -->
                {{-- <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select name="sort_by" id="sort_by"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="received_date" {{ request('sort_by') == 'received_date' ? 'selected' : '' }}>
                            Received Date</option>
                        <option value="grn_number" {{ request('sort_by') == 'grn_number' ? 'selected' : '' }}>GRN Number
                        </option>
                        <option value="total_amount" {{ request('sort_by') == 'total_amount' ? 'selected' : '' }}>Total
                            Amount</option>
                    </select>
                </div>

                <!-- Sort Direction -->
                <div>
                    <label for="sort_dir" class="block text-sm font-medium text-gray-700 mb-1">Sort Direction</label>
                    <select name="sort_dir" id="sort_dir"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div> --}}


            </form>
        </div>

        <!-- GRN List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Goods Received Notes</h2>
                    <p class="text-sm text-gray-500">
                        Showing {{ $grns->firstItem() }} to {{ $grns->lastItem() }} of {{ $grns->total() }} GRNs
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Organization: {{ Auth::user()->organization->name }}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a
                        class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                        <i class="fas fa-file-export mr-2"></i> Export
                    </a>
                    <a href="{{ route('admin.grn.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New GRN
                    </a>
                </div>
            </div>

            <!-- GRN Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN
                                Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref
                                No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($grns as $grn)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.grn.show', $grn->grn_id) }}'">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">{{ $grn->grn_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $grn->received_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $grn->supplier->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $grn->supplier->code ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($grn->purchaseOrder)
                                        <div class="font-medium">{{ $grn->purchaseOrder->po_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $grn->purchaseOrder->order_date->format('d M Y') }}</div>
                                    @else
                                        <div class="text-gray-500">No PO</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ $grn->items->count() }} item{{ $grn->items->count() == 1 ? '' : 's' }}</div>
                                    <div class="text-sm text-gray-500">
                                        Total: {{ $grn->items->sum('received_quantity') }}
                                        unit{{ $grn->items->sum('received_quantity') == 1 ? '' : 's' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ number_format($grn->total_amount, 2) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($grn->status == 'Pending')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($grn->status == 'Verified')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Verified
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.grn.show', $grn->grn_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.grn.print', $grn->grn_id) }}"
                                            class="text-blue-600 hover:text-blue-800" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        {{-- @if ($grn->status == 'Pending')
                                            <a href="{{ route('admin.grn.edit', $grn->grn_id) }}"
                                                class="text-gray-600 hover:text-gray-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No GRNs found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-white border-t border-gray-200">
                {{ $grns->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- JavaScript for toggling details -->
    <script>
        function toggleGRNDetails(id) {
            const element = document.getElementById(id);
            element.classList.toggle('hidden');
        }
    </script>

    <style>
        /* !!! remove-001 later !!! */
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .grn-item-details {
            display: none;
        }

        .grn-item-details.show {
            display: block;
        }
    </style>
@endsection
