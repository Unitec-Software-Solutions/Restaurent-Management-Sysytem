@extends('layouts.admin')

@section('title', 'Production Session Details')
@section('header-title', 'Production Session Details - ' . $session->session_name)
@section('content')
    <div class="mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $session->session_name }}</h1>
                    <p class="text-gray-600 mt-1">Production Order: {{ $session->productionOrder->production_order_number }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $session->getStatusBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                    </span>
                    <a href="{{ route('admin.production.sessions.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sessions
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg border border-red-200 shadow">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Session Information -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Session Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Session Name</label>
                                <p class="text-sm text-gray-900">{{ $session->session_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Supervisor</label>
                                <p class="text-sm text-gray-900">
                                    {{ $session->supervisor ? $session->supervisor->name : 'Not assigned' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <p class="text-sm text-gray-900">
                                    {{ $session->start_time ? $session->start_time->format('M d, Y g:i A') : 'Not started' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <p class="text-sm text-gray-900">
                                    {{ $session->end_time ? $session->end_time->format('M d, Y g:i A') : 'Not completed' }}
                                </p>
                            </div>
                        </div>
                        @if ($session->notes)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <p class="text-sm text-gray-900">{{ $session->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Production Items -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Items to Produce</h2>
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
                                            Target Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produced</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Wasted</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($session->productionOrder->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($item->quantity_to_produce, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($item->quantity_produced, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($item->quantity_wasted, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $progress =
                                                        $item->quantity_to_produce > 0
                                                            ? ($item->quantity_produced / $item->quantity_to_produce) *
                                                                100
                                                            : 0;
                                                @endphp
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                                        style="width: {{ $progress }}%"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ number_format($progress, 1) }}%
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Ingredient Management -->
                    @if ($session->productionOrder->ingredients->count() > 0)

                        {{-- <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900">Ingredients</h2>
                                @if ($session->status === 'in_progress')
                                    <button onclick="toggleIssueIngredientsModal()"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-hand-paper mr-2"></i>Issue Ingredients
                                    </button>
                                @endif
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ingredient</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Planned</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Issued</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Consumed</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Returned</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($session->productionOrder->ingredients as $ingredient)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $ingredient->ingredient->name }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $ingredient->unit_of_measurement }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($ingredient->planned_quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($ingredient->issued_quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($ingredient->consumed_quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($ingredient->returned_quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($ingredient->isFullyIssued())
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Fully Issued
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div> --}}
                    @endif

                    <!-- Production Output Recording -->
                    @if ($session->status === 'in_progress')
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900">Record Production Output</h2>
                                <button onclick="toggleProductionOutputModal()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                    <i class="fas fa-clipboard-check mr-2"></i>Record Output
                                </button>
                            </div>
                            <p class="text-sm text-gray-600">
                                Record the actual quantities produced and any waste to automatically update inventory.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Session Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Actions</h3>
                        <div class="space-y-3">
                            @if ($session->canBeStarted())
                                <form method="POST" action="{{ route('admin.production.sessions.start', $session) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-play mr-2"></i>Start Session
                                    </button>
                                </form>
                            @endif

                            @if ($session->status === 'in_progress')
                                <button onclick="toggleCompleteSessionModal()"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                    <i class="fas fa-check mr-2"></i>Complete Session
                                </button>
                            @endif

                            @if ($session->canBeCancelled())
                                <form method="POST" action="{{ route('admin.production.sessions.cancel', $session) }}"
                                    onsubmit="return confirm('Are you sure you want to cancel this session?')">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-times mr-2"></i>Cancel Session
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Summary -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Items to Produce:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ $session->productionOrder->items->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Target Quantity:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($session->productionOrder->items->sum('quantity_to_produce'), 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Produced:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($session->productionOrder->items->sum('quantity_produced'), 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Wasted:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($session->productionOrder->items->sum('quantity_wasted'), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Session Timeline -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Session Created</div>
                                    <div class="text-sm text-gray-500">{{ $session->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </div>
                            </div>

                            @if ($session->start_time)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Session Started</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $session->start_time->format('M d, Y g:i A') }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($session->end_time)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Session Completed</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $session->end_time->format('M d, Y g:i A') }}</div>
                                        <div class="text-sm text-green-600">Duration:
                                            {{ $session->getFormattedDuration() }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Ingredients Modal -->
    <div id="issueIngredientsModal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Issue Ingredients</h3>
                <form action="{{ route('admin.production.sessions.issue-ingredients', $session) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        @foreach ($session->productionOrder->ingredients as $ingredient)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium">{{ $ingredient->ingredient->name }}</span>
                                    <span class="text-sm text-gray-500">Planned:
                                        {{ number_format($ingredient->planned_quantity, 3) }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Issue Quantity</label>
                                        <input type="number" name="ingredients[{{ $loop->index }}][issued_quantity]"
                                            step="0.001" min="0" max="{{ $ingredient->getRemainingToIssue() }}"
                                            value="{{ $ingredient->getRemainingToIssue() }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <input type="hidden" name="ingredients[{{ $loop->index }}][ingredient_id]"
                                            value="{{ $ingredient->id }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                                        <input type="text" name="ingredients[{{ $loop->index }}][notes]"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleIssueIngredientsModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                            Issue Ingredients
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Production Output Modal -->
    <div id="productionOutputModal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Record Production Output</h3>
                <form action="{{ route('admin.production.sessions.record-production', $session) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        @foreach ($session->productionOrder->items as $item)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium">{{ $item->item->name }}</span>
                                    <span class="text-sm text-gray-500">Target:
                                        {{ number_format($item->quantity_to_produce, 2) }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity Produced</label>
                                        <input type="number"
                                            name="production_items[{{ $loop->index }}][quantity_produced]"
                                            step="0.01" min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <input type="hidden" name="production_items[{{ $loop->index }}][item_id]"
                                            value="{{ $item->item_id }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity Wasted</label>
                                        <input type="number"
                                            name="production_items[{{ $loop->index }}][quantity_wasted]" step="0.01"
                                            min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Batch Number</label>
                                        <input type="text" name="production_items[{{ $loop->index }}][batch_number]"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input type="date" name="production_items[{{ $loop->index }}][expiry_date]"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700">Waste Reason</label>
                                    <input type="text" name="production_items[{{ $loop->index }}][waste_reason]"
                                        placeholder="Optional - reason for waste"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700">Quality Notes</label>
                                    <textarea name="production_items[{{ $loop->index }}][quality_notes]" rows="2"
                                        placeholder="Optional - quality observations" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleProductionOutputModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            Record Production
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleIssueIngredientsModal() {
            const modal = document.getElementById('issueIngredientsModal');
            modal.classList.toggle('hidden');
        }

        function toggleProductionOutputModal() {
            const modal = document.getElementById('productionOutputModal');
            modal.classList.toggle('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const issueModal = document.getElementById('issueIngredientsModal');
            const outputModal = document.getElementById('productionOutputModal');

            if (event.target == issueModal) {
                issueModal.classList.add('hidden');
            }
            if (event.target == outputModal) {
                outputModal.classList.add('hidden');
            }
        }
    </script>
@endsection
