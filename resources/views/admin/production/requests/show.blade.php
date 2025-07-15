@extends('layouts.admin')

@section('header-title', 'Production Request Details - ' . $productionRequest->id)

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.production.requests.index') }}"
                class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Requests
            </a>
            <div class="flex space-x-2">
                @if ($productionRequest->canBeSubmitted() && $productionRequest->created_by_user_id === Auth::id())
                    <form method="POST" action="{{ route('admin.production.requests.submit', $productionRequest) }}"
                        class="inline">
                        @csrf
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </form>
                @endif

                @if ($productionRequest->canBeApproved() && !Auth::user()->branch_id)
                    <form method="POST" action="{{ route('admin.production.requests.approve', $productionRequest) }}"
                        class="inline">
                        @csrf
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-check mr-2"></i> Approve Request
                        </button>
                    </form>
                @endif

                @if ($productionRequest->canBeCancelled())
                    <form method="POST" action="{{ route('admin.production.requests.cancel', $productionRequest) }}"
                        onsubmit="return confirm('Are you sure you want to cancel this request?')" class="inline">
                        @csrf
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancel Request
                        </button>
                    </form>
                @endif

                <button onclick="window.print()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-emerald-600 mr-2"></i>
                    <span class="text-sm font-medium text-emerald-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Production Request Header Card -->
        <div
            class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4
            {{ $productionRequest->status === 'draft'
                ? 'border-gray-500'
                : ($productionRequest->status === 'submitted'
                    ? 'border-yellow-500'
                    : ($productionRequest->status === 'approved'
                        ? 'border-blue-500'
                        : ($productionRequest->status === 'in_production'
                            ? 'border-indigo-500'
                            : ($productionRequest->status === 'completed'
                                ? 'border-green-500'
                                : 'border-red-500')))) }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">Production Request #{{ $productionRequest->id }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">Status:</p>
                            <x-partials.badges.status-badge status="{{ $productionRequest->getStatusBadgeClass() }}"
                                text="{{ ucfirst($productionRequest->status) }}" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day mr-2"></i>
                            <span>Request Date: {{ $productionRequest->request_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span
                                class="{{ $productionRequest->required_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                                Required Date: {{ $productionRequest->required_date->format('M d, Y') }}
                                @if ($productionRequest->required_date->isPast())
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                        Overdue
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-3xl font-bold text-indigo-600">
                        {{ number_format($productionRequest->getTotalQuantityRequested()) }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">Total Requested Quantity</div>
                    @if ($productionRequest->getTotalQuantityProduced() > 0)
                        <div class="text-lg font-semibold text-green-600 mt-1">
                            Received: {{ number_format($productionRequest->getTotalQuantityProduced(), 2) }} units
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Production Request Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Request Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Request Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch</p>
                        <p class="font-medium">{{ $productionRequest->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created By</p>
                        <p class="font-medium">{{ $productionRequest->createdBy->name ?? 'N/A' }}</p>
                    </div>
                    @if ($productionRequest->approved_by_user_id)
                        <div>
                            <p class="text-sm text-gray-500">Approved By</p>
                            <p class="font-medium">{{ $productionRequest->approvedBy->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Approved At</p>
                            <p class="font-medium">{{ $productionRequest->approved_at->format('M d, Y g:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <h2 class="text-lg font-semibold mb-4 text-indigo-700">Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-semibold">{{ $productionRequest->items->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Requested Quantity:</span>
                        <span
                            class="font-semibold">{{ number_format($productionRequest->getTotalQuantityRequested()) }}</span>
                    </div>
                    @if ($productionRequest->status !== 'draft')
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Approved Quantity:</span>
                            <span
                                class="font-semibold">{{ number_format($productionRequest->getTotalQuantityApproved()) }}</span>
                        </div>
                    @endif
                    @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Received Quantity:</span>
                            <span
                                class="font-semibold text-green-600">{{ number_format($productionRequest->getTotalQuantityProduced()) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Production Progress:</span>
                            <span
                                class="font-semibold text-blue-600">{{ number_format($productionRequest->getProductionProgress(), 1) }}%</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $productionRequest->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        @if ($productionRequest->updated_at && $productionRequest->updated_at != $productionRequest->created_at)
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium">{{ $productionRequest->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
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
        </div>

        <!-- Requested Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Requested Items</h2>
                <p class="text-sm text-gray-500">Items included in this production request</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Requested</th>
                            @if ($productionRequest->status !== 'draft')
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Approved</th>
                            @endif
                            @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Received</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Progress</th>
                            @endif
                            @if ($productionRequest->status === 'completed')
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Distributed</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($productionRequest->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->item->name }}</div>
                                    @if ($item->item->description)
                                        <div class="text-sm text-gray-500">{{ $item->item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span
                                        class="font-medium text-indigo-600">{{ number_format($item->quantity_requested, 2) }}</span>
                                    <div class="text-xs text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                </td>
                                @if ($productionRequest->status !== 'draft')
                                    <td class="px-4 py-3 text-right text-sm">
                                        @if ($productionRequest->status === 'submitted' && !Auth::user()->branch_id)
                                            <form method="POST" action="#" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity_approved"
                                                    value="{{ $item->quantity_approved ?? $item->quantity_requested }}"
                                                    min="0" max="{{ $item->quantity_requested }}" step="0.01"
                                                    class="w-20 text-sm rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                                <button type="submit" class="ml-1 text-blue-600 hover:text-blue-900"
                                                    title="Update">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span
                                                class="font-medium text-green-600">{{ number_format($item->quantity_approved ?? 0, 2) }}</span>
                                            <div class="text-xs text-gray-500">{{ $item->item->unit_of_measurement }}
                                            </div>
                                        @endif
                                    </td>
                                @endif
                                @if (in_array($productionRequest->status, ['approved', 'in_production', 'completed']))
                                    <td class="px-4 py-3 text-right text-sm">
                                        <span
                                            class="font-medium text-purple-600">{{ number_format($item->quantity_produced ?? 0, 2) }}</span>
                                        <div class="text-xs text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @php
                                            $progress =
                                                $item->quantity_approved > 0
                                                    ? ($item->quantity_produced / $item->quantity_approved) * 100
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
                                    <td class="px-4 py-3 text-right text-sm">
                                        <span
                                            class="font-medium text-emerald-600">{{ number_format($item->quantity_distributed ?? 0, 2) }}</span>
                                        <div class="text-xs text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-sm">
                                    @if ($item->notes)
                                        <div class="text-gray-900">{{ $item->notes }}</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($productionRequest->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">Request Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($productionRequest->notes)) !!}
                </div>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .prose {
                color: #374151;
                line-height: 1.6;
            }

            .prose a {
                color: #4f46e5;
                text-decoration: underline;
            }

            .hover-row:hover {
                background-color: #f8fafc;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: all 0.2s ease;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: white !important;
                }

                .shadow-sm {
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add smooth hover effects to table rows
                const tableRows = document.querySelectorAll('tbody tr');
                tableRows.forEach(row => {
                    row.classList.add('hover-row');
                });
            });
        </script>
    @endpush
@endsection
