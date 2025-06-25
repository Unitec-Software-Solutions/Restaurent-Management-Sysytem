@extends('layouts.admin')

@section('title', 'Production Request Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Production Request Management
                    </h1>
                    <p class="text-gray-600 mt-1">Manage and approve production requests</p>
                </div>
                <a href="{{ route('admin.production.orders.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Tab Navigation -->
            <div class="bg-white rounded-xl shadow-sm mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            data-tab="pending-approval">
                            Pending Approval
                            @if ($pendingApprovalRequests->count() > 0)
                                <span
                                    class="bg-red-100 text-red-800 ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $pendingApprovalRequests->count() }}
                                </span>
                            @endif
                        </button>
                        <button
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                            data-tab="approved-requests">
                            Approved Requests
                            @if ($approvedRequests->count() > 0)
                                <span
                                    class="bg-green-100 text-green-800 ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $approvedRequests->count() }}
                                </span>
                            @endif
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->

            <!-- Pending Approval Tab -->
            <div id="pending-approval" class="tab-content hidden">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Requests Pending Approval</h2>
                        <p class="text-sm text-gray-600 mt-1">Review and approve production requests from branches</p>
                    </div>

                    <!-- Pending Approval Tab Content -->
                    @if ($pendingApprovalRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request Details</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Required Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($pendingApprovalRequests as $request)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">Request #{{ $request->id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->request_date->format('M d, Y') }}</div>
                                                @if ($request->notes)
                                                    <div class="text-xs text-gray-400 mt-1 max-w-48 truncate">
                                                        {{ $request->notes }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->createdBy->name ?? 'Unknown' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ number_format($request->getTotalQuantityRequested()) }} total qty
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $request->required_date->format('M d, Y') }}</div>
                                                @if ($request->required_date->isPast())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Overdue
                                                    </span>
                                                @elseif($request->required_date->isToday())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        Today
                                                    </span>
                                                @elseif($request->required_date->isTomorrow())
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Tomorrow
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-3">
                                                    <a href="{{ route('admin.production.requests.show', $request) }}"
                                                        class="text-blue-600 hover:text-blue-900" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.production.requests.show-approval', $request) }}"
                                                        class="text-green-600 hover:text-green-900"
                                                        title="Approve with Details">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('admin.production.requests.approve', $request) }}"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900"
                                                            title="Quick Approve (all requested quantities)"
                                                            onclick="return confirm('Approve all items with requested quantities?')">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('admin.production.requests.cancel', $request) }}"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                            title="Reject Request"
                                                            onclick="return confirm('Are you sure you want to reject this request?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-clipboard-check text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No requests pending approval</p>
                            <p class="text-sm">All production requests have been processed</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approved Requests Tab -->
            <div id="approved-requests" class="tab-content hidden">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Approved Requests</h2>
                        <p class="text-sm text-gray-600 mt-1">Approved requests ready for production aggregation</p>
                    </div>

                    @if ($approvedRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Approved Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($approvedRequests as $request)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#{{ $request->id }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->request_date->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->branch->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $request->items->count() }} items
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ number_format($request->getTotalQuantityApproved(), 2) }} approved
                                                    qty</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $request->approved_at->format('M d, Y') }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $request->approvedBy->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.production.requests.show', $request) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-eye mr-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- <div class="p-6 border-t border-gray-200">
                            <button type="button" id="aggregateSelectedBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 disabled:bg-gray-400"
                                disabled>
                                <i class="fas fa-layer-group mr-2"></i>Aggregate Selected for Production
                            </button>
                        </div> --}}
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-box text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900">No approved requests</h3>
                            <p class="text-gray-500">Approved requests will appear here ready for production.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Aggregate Production Tab -->
            <div id="aggregate-production" class="tab-content hidden">
                <!-- Include the existing aggregate functionality here -->
                @include('admin.production.orders.partials.aggregate-form')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Tab functionality
                const tabButtons = document.querySelectorAll('.tab-button');
                const tabContents = document.querySelectorAll('.tab-content');

                function showTab(targetTab) {
                    // Hide all tabs
                    tabContents.forEach(content => content.classList.add('hidden'));
                    tabButtons.forEach(button => {
                        button.classList.remove('border-blue-500', 'text-blue-600');
                        button.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Show target tab
                    document.getElementById(targetTab).classList.remove('hidden');
                    document.querySelector(`[data-tab="${targetTab}"]`).classList.remove('border-transparent',
                        'text-gray-500');
                    document.querySelector(`[data-tab="${targetTab}"]`).classList.add('border-blue-500',
                        'text-blue-600');
                }

                tabButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        showTab(button.dataset.tab);
                    });
                });

                // Show first tab by default
                showTab('pending-approval');

                // Select all functionality
                const selectAllApproved = document.getElementById('selectAllApproved');
                const requestCheckboxes = document.querySelectorAll('.request-checkbox');
                const aggregateBtn = document.getElementById('aggregateSelectedBtn');

                if (selectAllApproved) {
                    selectAllApproved.addEventListener('change', function() {
                        requestCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateAggregateButton();
                    });
                }

                requestCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateAggregateButton);
                });

                function updateAggregateButton() {
                    const selectedCount = document.querySelectorAll('.request-checkbox:checked').length;
                    if (aggregateBtn) {
                        aggregateBtn.disabled = selectedCount === 0;
                        aggregateBtn.textContent = selectedCount > 0 ?
                            `Aggregate ${selectedCount} Selected Requests` :
                            'Aggregate Selected for Production';
                    }
                }

                // Aggregate selected requests
                if (aggregateBtn) {
                    aggregateBtn.addEventListener('click', function() {
                        const selectedRequests = Array.from(document.querySelectorAll(
                                '.request-checkbox:checked'))
                            .map(cb => cb.value);

                        if (selectedRequests.length > 0) {
                            // Switch to aggregate tab and update form
                            showTab('aggregate-production');

                            // Update the aggregate form with selected requests
                            if (window.updateSelectedRequests) {
                                window.updateSelectedRequests(selectedRequests);
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
