@extends('layouts.admin')

@section('header-title', 'Goods Transfer Notes')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                    ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                    ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                    ['name' => 'Stock Release Notes', 'link' => route('admin.inventory.srn.index')],
                    ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                    ['name' => 'Goods Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                    ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
                ]" active="Goods Transfer Notes" />
            </div>

            <!-- Move x-data to wrap both filter and GTN list/tabs -->
            <div x-data="{
                tab: '{{ request('tab', 'outgoing') }}',
                setTab(t) {
                    this.tab = t;
                    document.getElementById('tab-input').value = t;
                }
            }">
                <!-- Filters with Export -->
                <x-module-filters
                    :searchValue="request('search', '')"
                    {{-- :statusOptions="[
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'in_delivery' => 'In Delivery',
                        'delivered' => 'Delivered'
                    ]"
                    :selectedStatus="request('origin_status', '')" --}}
                    {{-- :branches="$branches" --}}
                    :showDateRange="true"
                    :selectedBranch="request('branch_id', '')"
                    :showBranchFilter="true"
                    :showStatusFilter="true"

                    :customFilters="[
                        [
                            'name' => 'from_branch_id',
                            'label' => 'From Branch',
                            'type' => 'select',
                            'options' => $branches->pluck('name', 'id')->toArray(),
                            'placeholder' => 'All From Branches'
                        ],
                        [
                            'name' => 'to_branch_id',
                            'label' => 'To Branch',
                            'type' => 'select',
                            'options' => $branches->pluck('name', 'id')->toArray(),
                            'placeholder' => 'All To Branches'
                        ]
                    ]"
                >
                    <!-- Hidden input to keep track of tab -->
                    <input type="hidden" name="tab" id="tab-input" :value="tab">
                </x-module-filters>

                <!-- GTN List Tabs -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                                @if (Auth::guard('admin')->user()->is_super_admin)
                                    Organization: All Organizations (Super Admin)
                                @elseif(Auth::guard('admin')->user()->organization)
                                    Organization: {{ Auth::guard('admin')->user()->organization->name }}
                                @else
                                    Organization: Not Assigned
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="#"
                                class="bg-indigo-600 hover:bg-indigo-700 opacity-50 cursor-not-allowed text-white px-4 py-2 rounded-lg flex items-center pointer-events-none">
                                <i class="fas fa-file-export mr-2"></i> Export
                            </a>
                            @if (Auth::guard('admin')->user()->is_super_admin)
                                <a href="{{ route('admin.inventory.gtn.create') }}"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> New GTN (Select Org)
                                </a>
                            @else
                                <a href="{{ route('admin.inventory.gtn.create') }}"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> New GTN
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b px-6 pt-4 flex space-x-4 justify-center">
                        <button
                            :class="tab === 'outgoing' ? 'border-indigo-600 text-indigo-600' :
                                'border-transparent text-gray-500'"
                            class="pb-2 px-3 border-b-2 font-medium focus:outline-none" type="button"
                            @click="setTab('outgoing')">
                            Outgoing GTNs
                        </button>
                        <button
                            :class="tab === 'incoming' ? 'border-indigo-600 text-indigo-600' :
                                'border-transparent text-gray-500'"
                            class="pb-2 px-3 border-b-2 font-medium focus:outline-none" type="button"
                            @click="setTab('incoming')">
                            Incoming GTNs
                        </button>
                    </div>

                    <!-- Outgoing GTNs Table -->
                    <div x-show="tab === 'outgoing'" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        GTN Details</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        To Branch</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Origin Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Receiver Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php
                                    $outgoingGtns = $gtns; // Show all GTNs as outgoing for now
                                @endphp
                                @forelse($outgoingGtns as $gtn)
                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        onclick="window.location='{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}'">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-indigo-600">{{ $gtn->gtn_number }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($gtn->transfer_date)->format('d M Y') }}
                                            </div>
                                            @if ($gtn->confirmed_at)
                                                <div class="text-xs text-blue-600">
                                                    Confirmed:
                                                    {{ \Illuminate\Support\Carbon::parse($gtn->confirmed_at)->format('d M Y H:i') }}
                                                </div>
                                            @endif
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
                                            @if ($gtn->items->where('item_status', 'accepted')->count() > 0)
                                                <div class="text-xs text-green-600">
                                                    {{ $gtn->items->where('item_status', 'accepted')->count() }} accepted
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $originStatus = $gtn->origin_status ?? 'draft';
                                                $originStatusColors = [
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                                    'in_delivery' => 'bg-yellow-100 text-yellow-800',
                                                    'delivered' => 'bg-green-100 text-green-800',
                                                ];
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $originStatusColors[$originStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $originStatus)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $receiverStatus = $gtn->receiver_status ?? 'pending';
                                                $receiverStatusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'received' => 'bg-blue-100 text-blue-800',
                                                    'verified' => 'bg-purple-100 text-purple-800',
                                                    'accepted' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'partially_accepted' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $receiverStatusColors[$receiverStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $receiverStatus)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}"
                                                    class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.inventory.gtn.print', $gtn->gtn_id) }}"
                                                    class="text-blue-600 hover:text-blue-800" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if ($gtn->isDraft())
                                                    <a href="{{ route('admin.inventory.gtn.edit', $gtn->gtn_id) }}"
                                                        class="text-green-600 hover:text-green-800" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
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
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        GTN Details</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        From Branch</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Origin Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Receiver Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php
                                    // Filter out draft GTNs from incoming view - only show confirmed or later status
                                    $incomingGtns = $gtns->filter(function ($gtn) {
                                        return $gtn->origin_status !== 'draft';
                                    });
                                @endphp
                                @forelse($incomingGtns as $gtn)
                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        onclick="window.location='{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}'">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-indigo-600">{{ $gtn->gtn_number }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($gtn->transfer_date)->format('d M Y') }}
                                            </div>
                                            @if ($gtn->received_at)
                                                <div class="text-xs text-blue-600">
                                                    Received:
                                                    {{ \Illuminate\Support\Carbon::parse($gtn->received_at)->format('d M Y H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $gtn->fromBranch->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $gtn->fromBranch->code ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>{{ $gtn->items->count() }} items</div>
                                            <div class="text-sm text-gray-500">
                                                Total: {{ $gtn->items->sum('transfer_quantity') }} units
                                            </div>
                                            @if ($gtn->items->where('item_status', 'accepted')->count() > 0)
                                                <div class="text-xs text-green-600">
                                                    {{ $gtn->items->sum('quantity_accepted') }} units accepted
                                                </div>
                                            @endif
                                            @if ($gtn->items->where('item_status', 'rejected')->count() > 0)
                                                <div class="text-xs text-red-600">
                                                    {{ $gtn->items->sum('quantity_rejected') }} units rejected
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $originStatus = $gtn->origin_status ?? 'draft';
                                                $originStatusColors = [
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                                    'in_delivery' => 'bg-yellow-100 text-yellow-800',
                                                    'delivered' => 'bg-green-100 text-green-800',
                                                ];
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $originStatusColors[$originStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $originStatus)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $receiverStatus = $gtn->receiver_status ?? 'pending';
                                                $receiverStatusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'received' => 'bg-blue-100 text-blue-800',
                                                    'verified' => 'bg-purple-100 text-purple-800',
                                                    'accepted' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'partially_accepted' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $receiverStatusColors[$receiverStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $receiverStatus)) }}
                                            </span>
                                            @if ($gtn->isPending() && $gtn->isConfirmed())
                                                <div class="mt-1">
                                                    <span class="px-1 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">
                                                        Action Required
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('admin.inventory.gtn.show', $gtn->gtn_id) }}"
                                                    class="text-blue-600 hover:text-blue-800" title="Process GTN">
                                                    <i class="fas fa-cogs"></i>
                                                </a>
                                                <a href="{{ route('admin.inventory.gtn.print', $gtn->gtn_id) }}"
                                                    class="text-green-600 hover:text-green-800" title="Print">
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
        </div>
    </div>

    <style>
        /* Custom styles for unified GTN system */
        .status-indicator {
            position: relative;
        }

        .status-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -10px;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .workflow-progress {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .workflow-step {
            width: 20px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
        }

        .workflow-step.completed {
            background: #10b981;
        }

        .workflow-step.current {
            background: #3b82f6;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
