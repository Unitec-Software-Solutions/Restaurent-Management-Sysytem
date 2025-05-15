@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <!-- Notifications -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Goods Received Notes</h2>
        <a href="{{ route('admin.inventory.grn.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create GRN</a>
    </div>

    <!-- Filters -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.inventory.grn.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search GRN</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by GRN number"
                       class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Branch</label>
                <select name="branch_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier  Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Supplier</label>
                <select name="supplier_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- Supplier  Filter -->

            <!-- Filter Buttons -->
            <div class="flex items-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Apply Filters
                </button>
                @if(request('search') || request('branch_id'))
                    <a href="{{ route('admin.inventory.grn.index') }}" 
                       class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- GRN Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        GRN Number
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Branch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Supplier
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Total Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                @forelse($grns as $grn)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $grn->grn_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $grn->branch->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $grn->supplier->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $grn->created_at->format('Y-m-d') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $grn->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($grn->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                    'bg-red-100 text-red-800') }}">
                                {{ ucfirst($grn->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                ${{ number_format($grn->total_amount, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.inventory.grn.show', $grn) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    View
                                </a>
                                @if($grn->status === 'pending')
                                    <a href="{{ route('admin.inventory.grn.edit', $grn) }}" 
                                       class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.inventory.grn.destroy', $grn) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to delete this GRN?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No GRN records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $grns->links() }}
    </div>
</div>
@endsection