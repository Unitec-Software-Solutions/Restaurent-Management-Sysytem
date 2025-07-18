@extends('layouts.admin')

@section('header-title', 'Stock Release Notes')

@section('content')
    <div class="container mx-auto px-4 py-8">
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
                ]" active="Stock Release Notes" />
            </div>

            <div x-data="{
                tab: '{{ request('tab', 'all') }}',
                setTab(t) {
                    this.tab = t;
                    document.getElementById('tab-input').value = t;
                }
            }">
                <!-- Filters -->
                <x-module-filters
                    :searchValue="request('search', '')"
                    :showDateRange="true"
                    :selectedBranch="request('branch_id', '')"
                    :showBranchFilter="true"
                    :showStatusFilter="true"
                    :customFilters="[
                        [
                            'name' => 'release_type',
                            'label' => 'Release Type',
                            'type' => 'select',
                            'options' => [
                                'wastage' => 'Wastage',
                                'sale' => 'Sale',
                                'transfer' => 'Transfer',
                                'usage' => 'Usage',
                                'kit' => 'Kit',
                                'staff_usage' => 'Staff Usage',
                                'internal_usage' => 'Internal Usage',
                                'other' => 'Other'
                            ],
                            'placeholder' => 'All Types'
                        ]
                    ]"
                >
                    <input type="hidden" name="tab" id="tab-input" :value="tab">
                </x-module-filters>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Stock Release Notes</h2>
                            <p class="text-sm text-gray-500">
                                @if ($notes instanceof \Illuminate\Pagination\LengthAwarePaginator || $notes instanceof \Illuminate\Pagination\Paginator)
                                    Showing {{ $notes->firstItem() ?? 0 }} to {{ $notes->lastItem() ?? 0 }} of
                                    {{ $notes->total() ?? 0 }} SRNs
                                @else
                                    {{ $notes->count() }} SRNs
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
                            <a href="{{ route('admin.inventory.srn.create') }}"
                                class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> New SRN
                            </a>
                        </div>
                    </div>

                    <!-- SRN Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SRN #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Release Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Release Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($notes as $note)
                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        onclick="window.location='{{ route('admin.inventory.srn.show', $note->id) }}'">
                                        <td class="px-4 py-3 text-sm font-semibold text-primary-700">
                                            <a href="{{ route('admin.inventory.srn.show', $note->id) }}" class="hover:underline">
                                                {{ $note->srn_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $note->branch->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 capitalize">
                                            {{ str_replace('_', ' ', $note->release_type) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ \Carbon\Carbon::parse($note->release_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold">
                                            Rs. {{ number_format($note->total_amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs font-medium
                                                @if($note->status == 'Pending') bg-yellow-100 text-yellow-800
                                                @elseif($note->status == 'Completed') bg-green-100 text-green-800
                                                @elseif($note->status == 'Rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $note->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('admin.inventory.srn.show', $note->id) }}"
                                               class="text-indigo-600 hover:text-indigo-900 mr-2" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.inventory.srn.edit', $note->id) }}"
                                               class="text-primary-600 hover:text-primary-900 mr-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No Stock Release Notes found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($notes instanceof \Illuminate\Pagination\LengthAwarePaginator || $notes instanceof \Illuminate\Pagination\Paginator)
                        <div class="px-6 py-4 bg-white border-t border-gray-200">
                            {{ $notes->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
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
