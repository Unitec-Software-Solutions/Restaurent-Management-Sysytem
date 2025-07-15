@extends('layouts.admin')

@section('title', 'Production Order Details')
@section('header-title', 'Production Order Details')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Production Order #{{ $productionOrder->id }}
                    </h1>
                    <p class="text-gray-600 mt-1">{{ $productionOrder->production_order_number }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.production.orders.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-lg border border-green-200 shadow">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productionOrder->getStatusBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $productionOrder->status)) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Production Date</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->production_date->format('M d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Items</label>
                        <p class="text-sm text-gray-900">{{ $productionOrder->items->count() }} unique items</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Quantity</label>
                        <p class="text-sm text-gray-900">{{ number_format($productionOrder->getTotalQuantityOrdered()) }}
                        </p>
                    </div>
                </div>

                @if ($productionOrder->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $productionOrder->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items to Produce -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Items to Produce</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity to Produce</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity Produced</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($productionOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->item->unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity_to_produce, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->quantity_produced, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $progress =
                                                $item->quantity_to_produce > 0
                                                    ? ($item->quantity_produced / $item->quantity_to_produce) * 100
                                                    : 0;
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $progress }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($progress, 1) }}%</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ingredients Required -->
            @if ($productionOrder->ingredients->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Required Ingredients</h2>
                            @if ($productionOrder->status === 'approved')
                                <button type="button" id="issueIngredientsBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-box mr-2"></i>Issue Ingredients
                                </button>
                            @endif
                        </div>
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
                                        Planned Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issued Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Consumed</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Source</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionOrder->ingredients as $ingredient)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $ingredient->ingredient->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $ingredient->unit_of_measurement }}</div>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($ingredient->isFullyIssued())
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Fully Issued
                                                </span>
                                            @elseif($ingredient->issued_quantity > 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Partially Issued
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Not Issued
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $ingredient->is_manually_added ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $ingredient->is_manually_added ? 'Manual' : 'Recipe' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $ingredient->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Ingredient Requirements -->
            {{-- @if ($productionOrder->ingredients->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Ingredient Requirements</h2>
                         @if ($productionOrder->status === 'draft')
                                <button type="button" onclick="openIngredientManagement()"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-edit mr-2"></i>Manage Ingredients
                                </button>
                            @endif
                        </div>
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
                                        Planned Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Issued Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Consumed</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Source</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($productionOrder->ingredients as $ingredient)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $ingredient->ingredient->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $ingredient->unit_of_measurement }}
                                            </div>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($ingredient->isFullyConsumed())
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif($ingredient->isFullyIssued())
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Issued
                                                </span>
                                            @elseif($ingredient->issued_quantity > 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Partial
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if ($ingredient->is_manually_added)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Manual
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Recipe
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $ingredient->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif --}}

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if ($productionOrder->canBeApproved())
                        <form method="POST" action="{{ route('admin.production.orders.approve', $productionOrder) }}"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                <i class="fas fa-check mr-2"></i>Approve Order
                            </button>
                        </form>
                    @endif

                    @if ($productionOrder->canStartProduction())
                        <a href="{{ route('admin.production.sessions.create', ['order_id' => $productionOrder->id]) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                            <i class="fas fa-play mr-2"></i>Start Production
                        </a>
                    @endif

                    @if ($productionOrder->canBeCancelled())
                        <form method="POST" action="{{ route('admin.production.orders.cancel', $productionOrder) }}"
                            class="inline" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Ingredients Modal -->
    <div id="issueIngredientsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Issue Ingredients to Kitchen</h3>
                        <button type="button" id="closeIssueModalBtn" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="issueIngredientsForm"
                        action="{{ route('admin.production.orders.issue-ingredients', $productionOrder) }}"
                        method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Ingredient</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Planned
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Already
                                            Issued</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Issue
                                            Now</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Remaining</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($productionOrder->ingredients as $index => $ingredient)
                                        <tr>
                                            <td class="px-4 py-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $ingredient->ingredient->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $ingredient->unit_of_measurement }}
                                                </div>
                                                <input type="hidden"
                                                    name="ingredients[{{ $index }}][ingredient_item_id]"
                                                    value="{{ $ingredient->ingredient_item_id }}">
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ number_format($ingredient->planned_quantity, 3) }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ number_format($ingredient->issued_quantity, 3) }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number"
                                                    name="ingredients[{{ $index }}][issued_quantity]"
                                                    step="0.001" min="0"
                                                    max="{{ $ingredient->getRemainingToIssue() }}"
                                                    value="{{ $ingredient->getRemainingToIssue() }}"
                                                    class="w-24 px-2 py-1 border border-gray-300 rounded text-sm issue-quantity">
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900 remaining-quantity">
                                                {{ number_format($ingredient->getRemainingToIssue(), 3) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" id="cancelIssueBtn"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Issue Ingredients
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Production Modal -->
    @if ($productionOrder->status === 'in_progress')
        <div id="completeProductionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Complete Production</h3>
                            <button type="button" id="closeCompleteModalBtn" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <form id="completeProductionForm"
                            action="{{ route('admin.production.orders.complete', $productionOrder) }}" method="POST">
                            @csrf
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Item</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Target</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Already Produced</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Produced Now</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($productionOrder->items as $index => $item)
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $item->item->unit_of_measurement }}</div>
                                                    <input type="hidden" name="items[{{ $index }}][item_id]"
                                                        value="{{ $item->item_id }}">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    {{ number_format($item->quantity_to_produce, 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    {{ number_format($item->quantity_produced, 2) }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number"
                                                        name="items[{{ $index }}][quantity_produced]"
                                                        step="0.01" min="0"
                                                        max="{{ $item->quantity_to_produce - $item->quantity_produced }}"
                                                        value="{{ $item->quantity_to_produce - $item->quantity_produced }}"
                                                        class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" id="cancelCompleteBtn"
                                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                    Complete Production
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Issue Ingredients Modal
            const issueModal = document.getElementById('issueIngredientsModal');
            const issueBtn = document.getElementById('issueIngredientsBtn');
            const closeIssueModalBtn = document.getElementById('closeIssueModalBtn');
            const cancelIssueBtn = document.getElementById('cancelIssueBtn');

            if (issueBtn) {
                issueBtn.addEventListener('click', () => issueModal.classList.remove('hidden'));
            }
            if (closeIssueModalBtn) {
                closeIssueModalBtn.addEventListener('click', () => issueModal.classList.add('hidden'));
            }
            if (cancelIssueBtn) {
                cancelIssueBtn.addEventListener('click', () => issueModal.classList.add('hidden'));
            }

            // Update remaining quantities when issue quantities change
            document.querySelectorAll('.issue-quantity').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const plannedQty = parseFloat(row.cells[1].textContent);
                    const alreadyIssued = parseFloat(row.cells[2].textContent);
                    const issueNow = parseFloat(this.value) || 0;
                    const remaining = plannedQty - alreadyIssued - issueNow;

                    row.querySelector('.remaining-quantity').textContent = remaining.toFixed(3);

                    // Validate input
                    if (issueNow > (plannedQty - alreadyIssued)) {
                        this.setCustomValidity('Cannot issue more than remaining quantity');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            });

            // Complete Production Modal
            const completeModal = document.getElementById('completeProductionModal');
            const completeBtn = document.getElementById('completeProductionBtn');
            const closeCompleteModalBtn = document.getElementById('closeCompleteModalBtn');
            const cancelCompleteBtn = document.getElementById('cancelCompleteBtn');

            if (completeBtn) {
                completeBtn.addEventListener('click', () => completeModal.classList.remove('hidden'));
            }
            if (closeCompleteModalBtn) {
                closeCompleteModalBtn.addEventListener('click', () => completeModal.classList.add('hidden'));
            }
            if (cancelCompleteBtn) {
                cancelCompleteBtn.addEventListener('click', () => completeModal.classList.add('hidden'));
            }
        });
    </script>
@endpush
