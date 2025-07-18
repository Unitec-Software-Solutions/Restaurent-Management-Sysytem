@extends('layouts.admin')

@section('title', 'Manage Production Requests')

@section('header-title', 'Manage Production Requests')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    // ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Requests" />
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-2 mt-0.5"></i>
                    <div class="text-sm text-green-800">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Production Request Management</h2>
                    <p class="text-sm text-gray-500 mt-1">Review and approve production requests from branches</p>
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
                    @if (!Auth::user()->branch_id && $approvedRequests->count() > 0)
                        <a href="{{ route('admin.production.requests.aggregate') }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-layer-group mr-2"></i>
                            Aggregate Requests
                            <span class="bg-green-800 text-white px-2 py-1 rounded-full text-xs ml-2">
                                {{ $approvedRequests->count() }}
                            </span>
                        </a>
                    @endif
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                    </a>
                </div>
            </div>

            <!-- Tab Navigation -->
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

            <!-- Tab Content -->
            <!-- Pending Approval Tab -->
            <div id="pending-approval" class="tab-content hidden">
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
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($pendingApprovalRequests as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-indigo-600">Request #{{ $request->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->request_date->format('d M Y') }}
                                            </div>
                                            @if ($request->notes)
                                                <div class="text-sm text-gray-400 mt-1 max-w-48 truncate">
                                                    {{ $request->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $request->branch->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->createdBy->name ?? 'Unknown' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>{{ $request->items->count() }} items</div>
                                            <div class="text-sm text-gray-500">
                                                {{ number_format($request->getTotalQuantityRequested()) }} total qty
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $request->required_date->format('d M Y') }}</div>
                                            @if ($request->required_date->isPast())
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mt-1">
                                                    Overdue
                                                </span>
                                            @elseif($request->required_date->isToday())
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mt-1">
                                                    Today
                                                </span>
                                            @elseif($request->required_date->isTomorrow())
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mt-1">
                                                    Tomorrow
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-3">



                                                <a href="{{ route('admin.production.requests.show', $request) }}"
                                                    class="text-indigo-600 hover:text-indigo-800" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.production.requests.show-approval', $request) }}"
                                                    class="text-blue-600 hover:text-blue-800" title="Detailed Approval">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('admin.production.requests.approve', $request) }}"
                                                    class="inline"
                                                    onsubmit="event.stopPropagation(); return confirm('Approve all items with requested quantities?');">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900"
                                                        title="Quick Approve">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('admin.production.requests.cancel', $request) }}"
                                                    class="inline"
                                                    onsubmit="event.stopPropagation(); return confirm('Are you sure you want to reject this request?');">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                                        title="Reject">
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
                    <div class="p-12 text-center text-gray-500">
                        <i class="fas fa-clipboard-check text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium">No requests pending approval</p>
                        <p class="text-sm">All production requests have been processed</p>
                    </div>
                @endif
            </div>

            <!-- Approved Requests Tab -->
            <div id="approved-requests" class="tab-content hidden">
                @if ($approvedRequests->count() > 0)
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
                                        Approved Date</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($approvedRequests as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-indigo-600">#{{ $request->id }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $request->request_date->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $request->branch->name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>{{ $request->items->count() }} items</div>
                                            <div class="text-sm text-gray-500">
                                                {{ number_format($request->getTotalQuantityApproved(), 2) }} approved qty
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium">{{ $request->approved_at->format('d M Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->approvedBy->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.production.requests.show', $request) }}"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-box text-4xl text-gray-300 mb-4"></i>
                        <p class="text-lg font-medium text-gray-900">No approved requests</p>
                        <p class="text-sm text-gray-500">Approved requests will appear here ready for production</p>
                    </div>
                @endif
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
