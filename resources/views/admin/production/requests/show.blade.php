@extends('layouts.admin')

@section('title', 'Production Request Details')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Production Request
                        #{{ $productionRequest->id }}</h1>
                    <p class="text-gray-600 mt-1">{{ $productionRequest->branch->name }} -
                        {{ $productionRequest->request_date->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productionRequest->getStatusBadgeClass() }}">
                        {{ ucfirst($productionRequest->status) }}
                    </span>
                    <a href="{{ route('admin.production.requests.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Request Information -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Request Date</label>
                                <p class="text-gray-900">{{ $productionRequest->request_date->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Required Date</label>
                                <p
                                    class="text-gray-900 {{ $productionRequest->required_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                                    {{ $productionRequest->required_date->format('M d, Y') }}
                                    @if ($productionRequest->required_date->isPast())
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                            Overdue
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Branch</label>
                                <p class="text-gray-900">{{ $productionRequest->branch->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Created By</label>
                                <p class="text-gray-900">{{ $productionRequest->createdBy->name }}</p>
                            </div>
                            @if ($productionRequest->approved_by_user_id)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Approved By</label>
                                    <p class="text-gray-900">{{ $productionRequest->approvedBy->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Approved At</label>
                                    <p class="text-gray-900">{{ $productionRequest->approved_at->format('M d, Y g:i A') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($productionRequest->notes)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Notes</label>
                                <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $productionRequest->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Requested Items -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900">Requested Items</h2>
                                <div class="text-sm text-gray-500">
                                    {{ $productionRequest->items->count() }} items |
                                    {{ number_format($productionRequest->getTotalQuantityRequested()) }} total quantity
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requested</th>
                                        @if ($productionRequest->status !== 'draft')
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Approved</th>
                                        @endif
                                        @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Produced</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Progress</th>
                                        @endif
                                        @if ($productionRequest->status === 'completed')
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Distributed</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($productionRequest->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $item->item->name }}</div>
                                                        @if ($item->item->description)
                                                            <div class="text-sm text-gray-500">
                                                                {{ $item->item->description }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 font-medium">
                                                    {{ number_format($item->quantity_requested, 2) }}</div>
                                                <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}
                                                </div>
                                            </td>
                                            @if ($productionRequest->status !== 'draft')
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($productionRequest->status === 'submitted' && !Auth::user()->branch_id)
                                                        <form method="POST"
                                                            action="{{ route('production.request-items.update-approved', $item) }}"
                                                            class="inline-block">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="number" name="quantity_approved"
                                                                value="{{ $item->quantity_approved ?? $item->quantity_requested }}"
                                                                min="0" max="{{ $item->quantity_requested }}"
                                                                step="0.01"
                                                                class="w-20 text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                            <button type="submit"
                                                                class="ml-1 text-blue-600 hover:text-blue-900"
                                                                title="Update">
                                                                <i class="fas fa-check text-xs"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <div class="text-sm text-gray-900 font-medium">
                                                            {{ number_format($item->quantity_approved ?? 0, 2) }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $item->item->unit_of_measurement }}</div>
                                                    @endif
                                                </td>
                                            @endif
                                            @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 font-medium">
                                                        {{ number_format($item->quantity_produced ?? 0, 2) }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $item->item->unit_of_measurement }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $progress =
                                                            $item->quantity_approved > 0
                                                                ? ($item->quantity_produced /
                                                                        $item->quantity_approved) *
                                                                    100
                                                                : 0;
                                                    @endphp
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: {{ min(100, $progress) }}%"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ number_format($progress, 1) }}%</div>
                                                </td>
                                            @endif
                                            @if ($productionRequest->status === 'completed')
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 font-medium">
                                                        {{ number_format($item->quantity_distributed ?? 0, 2) }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $item->item->unit_of_measurement }}</div>
                                                </td>
                                            @endif
                                            <td class="px-6 py-4">
                                                @if ($item->notes)
                                                    <div class="text-sm text-gray-900">{{ $item->notes }}</div>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Summary Stats -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Items:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ $productionRequest->items->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Requested Quantity:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($productionRequest->getTotalQuantityRequested()) }}</span>
                            </div>
                            @if ($productionRequest->status !== 'draft')
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Approved Quantity:</span>
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ number_format($productionRequest->getTotalQuantityApproved()) }}</span>
                                </div>
                            @endif
                            @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Produced Quantity:</span>
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ number_format($productionRequest->getTotalQuantityProduced()) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Production Progress:</span>
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ number_format($productionRequest->getProductionProgress(), 1) }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            @if ($productionRequest->canBeSubmitted() && $productionRequest->created_by_user_id === Auth::id())
                                <form method="POST"
                                    action="{{ route('admin.production.requests.submit', $productionRequest) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                                    </button>
                                </form>
                            @endif

                            @if ($productionRequest->canBeApproved() && !Auth::user()->branch_id)
                                <form method="POST"
                                    action="{{ route('admin.production.requests.approve', $productionRequest) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-check mr-2"></i>Approve Request
                                    </button>
                                </form>
                            @endif

                            @if ($productionRequest->canBeCancelled())
                                <form method="POST"
                                    action="{{ route('admin.production.requests.cancel', $productionRequest) }}"
                                    onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-times mr-2"></i>Cancel Request
                                    </button>
                                </form>
                            @endif

                            <!-- Print Request -->
                            <button onclick="window.print()"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-print mr-2"></i>Print Request
                            </button>
                        </div>
                    </div>

                    <!-- Timeline -->
                    @if ($productionRequest->status !== 'draft')
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Request Created</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $productionRequest->created_at->format('M d, Y g:i A') }}</div>
                                        <div class="text-sm text-gray-500">by {{ $productionRequest->createdBy->name }}
                                        </div>
                                    </div>
                                </div>

                                @if ($productionRequest->status !== 'draft')
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Request Submitted</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $productionRequest->updated_at->format('M d, Y g:i A') }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if ($productionRequest->approved_at)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Request Approved</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $productionRequest->approved_at->format('M d, Y g:i A') }}</div>
                                            <div class="text-sm text-gray-500">by
                                                {{ $productionRequest->approvedBy->name }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if ($productionRequest->status === 'completed')
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">Production Completed</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $productionRequest->updated_at->format('M d, Y g:i A') }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style media="print">
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    </style>
@endsection
