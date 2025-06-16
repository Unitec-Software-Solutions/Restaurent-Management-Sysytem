@extends('layouts.admin')

@section('header-title', 'Goods Transfer Notes')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="  justify-between items-center mb-4">
            <div class="rounded-lg ">
                <x-nav-buttons :items="[
                    ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                    ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                    ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                    ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                    ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                    ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
                ]" active="Transfer Notes" />
            </div>

            <!-- Search and Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <form method="GET" action="{{ route('admin.inventory.gtn.index') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- From Branch Filter -->
                    <div>
                        <label for="from_branch_id" class="block text-sm font-medium text-gray-700 mb-1">From Branch</label>
                        <select name="from_branch_id" id="from_branch_id"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Branch Filter -->
                    <div>
                        <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-1">To Branch</label>
                        <select name="to_branch_id" id="to_branch_id"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed
                            </option>
                            <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
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
                        <a href="{{ route('admin.inventory.gtn.index') }}"
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- GTN List Tabs -->
            <div x-data="{ tab: 'outgoing' }" class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Goods Transfer Notes</h2>
                        <p class="text-sm text-gray-500">
                            @if ($gtns instanceof \Illuminate\Pagination\LengthAwarePaginator || $gtns instanceof \Illuminate\Pagination\Paginator)
                                Showing {{ $gtns->firstItem() ?? 0 }} to {{ $gtns->lastItem() ?? 0 }} of
                                {{ $gtns->total() ?? 0 }} GTNs
                            @else
                                {{ $gtns->count() }} GTNs
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Organization: {{ Auth::user()->organization->name }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="#"
                            class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                            <i class="fas fa-file-export mr-2"></i> Export
                        </a>
                        <a href="{{ route('admin.inventory.gtn.create') }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> New GTN
                        </a>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b px-6 pt-4 flex space-x-4 justify-center">
                    <button
                        :class="tab === 'outgoing' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
                        class="pb-2 px-3 border-b-2 font-medium focus:outline-none" @click="tab = 'outgoing'">
                        Outgoing GTNs
                    </button>
                    <button
                        :class="tab === 'incoming' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
                        class="pb-2 px-3 border-b-2 font-medium focus:outline-none" @click="tab = 'incoming'">
                        Incoming GTNs
                    </button>
                </div>

                <!-- Outgoing GTNs Table -->
                <div x-show="tab === 'outgoing'" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    GTN Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    From Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    To Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $userBranchId = request('from_branch_id') ?? (Auth::user()->branch_id ?? null);
                                $outgoingGtns = $gtns->filter(fn($gtn) => $gtn->from_branch_id == $userBranchId);
                            @endphp
                            @forelse($outgoingGtns as $gtn)
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location='{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}'">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-indigo-600">{{ $gtn->gtn_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ \Illuminate\Support\Carbon::parse($gtn->transfer_date)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $gtn->fromBranch->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $gtn->fromBranch->code ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $gtn->toBranch->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $gtn->toBranch->code ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>{{ $gtn->items->count() }} items</div>
                                        <div class="text-sm text-gray-500">
                                            Total: {{ $gtn->items->sum('transfer_quantity') }} units
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($gtn->status == 'Pending')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($gtn->status == 'Confirmed')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Confirmed
                                            </span>
                                        @elseif($gtn->status == 'Approved')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Approved
                                            </span>
                                        @elseif($gtn->status == 'Verified')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Verified
                                            </span>
                                        @elseif($gtn->status == 'Completed')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('admin.inventory.gtn.print', $gtn->gtn_id) }}"
                                                class="text-blue-600 hover:text-blue-800" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No outgoing GTNs found matching your criteria
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Incoming GTNs Table -->
                <div x-show="tab === 'incoming'" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    GTN Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    From Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    To Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $userBranchId = request('to_branch_id') ?? (Auth::user()->branch_id ?? null);
                                $incomingGtns = $gtns->filter(fn($gtn) => $gtn->to_branch_id == $userBranchId);
                            @endphp
                            @forelse($incomingGtns as $gtn)
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location='{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}'">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-indigo-600">{{ $gtn->gtn_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ \Illuminate\Support\Carbon::parse($gtn->transfer_date)->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $gtn->fromBranch->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $gtn->fromBranch->code ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $gtn->toBranch->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $gtn->toBranch->code ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>{{ $gtn->items->count() }} items</div>
                                        <div class="text-sm text-gray-500">
                                            Total: {{ $gtn->items->sum('transfer_quantity') }} units
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($gtn->status == 'Pending')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($gtn->status == 'Confirmed')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Confirmed
                                            </span>
                                        @elseif($gtn->status == 'Approved')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Approved
                                            </span>
                                        @elseif($gtn->status == 'Verified')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Verified
                                            </span>
                                        @elseif($gtn->status == 'Completed')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('admin.inventory.gtn.print', $gtn->gtn_id) }}"
                                                class="text-blue-600 hover:text-blue-800" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No incoming GTNs found matching your criteria
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($gtns instanceof \Illuminate\Pagination\LengthAwarePaginator || $gtns instanceof \Illuminate\Pagination\Paginator)
                    <div class="px-6 py-4 bg-white border-t border-gray-200">
                        {{ $gtns->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endsection
