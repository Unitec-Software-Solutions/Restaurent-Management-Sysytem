@extends('layouts.admin')

@section('header-title', 'Stock Release Note Details')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Back and Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.inventory.srn.index') }}" class="flex items-center text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to SRNs
            </a>
            <div class="flex space-x-2">
                @if ($note->status === 'Pending')
                    <form id="verifySrnForm" action="{{ route('admin.inventory.srn.verify', $note->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="Verified">
                        <button type="button" onclick="openVerifyModal()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-check mr-2"></i> Verify SRN
                        </button>
                    </form>
                @endif
                <a href="#" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <!-- SRN Header Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 {{ $note->status === 'Pending' ? 'border-yellow-500' : ($note->status === 'Verified' ? 'border-green-500' : 'border-red-500') }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center flex-wrap gap-4 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">SRN #{{ $note->srn_number ?? 'N/A' }}</h1>
                        <div class="flex items-center space-x-2">
                            <p class="text-sm text-gray-500">SRN Status:</p>
                            @if ($note->status === 'Pending')
                                <x-partials.badges.status-badge status="warning" text="Pending" />
                            @elseif($note->status === 'Verified')
                                <x-partials.badges.status-badge status="success" text="Verified" />
                            @elseif($note->status === 'Rejected')
                                <x-partials.badges.status-badge status="danger" text="Rejected" />
                            @else
                                <x-partials.badges.status-badge status="default" text="{{ $note->status ?? 'N/A' }}" />
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day mr-2"></i>
                            <span>Release Date: {{ $note->release_date ? \Carbon\Carbon::parse($note->release_date)->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        @if ($note->reference_number)
                            <div class="flex items-center">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span>Reference #: {{ $note->reference_number }}</span>
                            </div>
                        @endif
                        @if ($note->release_type)
                            <div class="flex items-center">
                                <i class="fas fa-tags mr-2"></i>
                                <span>Type: {{ ucfirst($note->release_type) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-3xl font-bold text-indigo-600">Rs. {{ number_format($note->total_amount ?? 0, 2) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Amount</div>
                </div>
            </div>
        </div>

        <!-- SRN Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Organization Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Organization Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Organization Name</p>
                        <p class="font-medium">{{ $note->organization->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <!-- Branch Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Branch Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Branch Name</p>
                        <p class="font-medium">{{ $note->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Branch Code</p>
                        <p class="font-medium">{{ $note->branch->code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium">{{ $note->branch->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact</p>
                        <p class="font-medium">{{ $note->branch->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <!-- SRN Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <h2 class="text-lg font-semibold mb-4 text-indigo-700">SRN Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-semibold">Rs. {{ number_format($note->total_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Released By:</span>
                            <span class="font-medium">{{ $note->releasedByUser->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Received By:</span>
                            <span class="font-medium">{{ $note->receivedByUser->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Verified By:</span>
                            <span class="font-medium">{{ $note->verifiedByUser->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $note->created_at ? $note->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SRN Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold">Released Items</h2>
                <p class="text-sm text-gray-500">Items included in this stock release note</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($note->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->item->name ?? $item->item_id ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">ID: {{ $item->item_id ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <span class="font-medium text-indigo-600">{{ number_format($item->release_quantity ?? 0, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    Rs. {{ number_format($item->unit_price ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="font-semibold">Rs. {{ number_format(($item->release_quantity ?? 0) * ($item->unit_price ?? 0), 2) }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $item->notes ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">Total Amount:</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">Rs. {{ number_format($note->total_amount ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        @if ($note->notes)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-2">SRN Notes</h2>
                <div class="prose max-w-none">
                    {!! nl2br(e($note->notes)) !!}
                </div>
            </div>
        @endif

        <!-- Confirm Verification Modal -->
        <div id="verifyModal" class="fixed inset-0 z-50 hidden bg-black/50 items-center justify-center">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-xl mr-3">
                        <i class="fas fa-exclamation-triangle text-green-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">Confirm SRN Verification</h2>
                </div>
                <p class="mb-6 text-gray-700">
                    Are you sure you want to verify this SRN? This action cannot be undone.
                </p>
                <div class="flex gap-3 mt-6">
                    <button id="confirmVerifyBtn"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Yes, Verify SRN
                    </button>
                    <button type="button" onclick="closeVerifyModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

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
    </style>
@endpush

@push('scripts')
    <script>
        function openVerifyModal() {
            var modal = document.getElementById('verifyModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeVerifyModal() {
            var modal = document.getElementById('verifyModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var confirmBtn = document.getElementById('confirmVerifyBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    document.getElementById('verifySrnForm').submit();
                });
            }
            // Add smooth hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.classList.add('hover-row');
            });
        });
    </script>
@endpush
