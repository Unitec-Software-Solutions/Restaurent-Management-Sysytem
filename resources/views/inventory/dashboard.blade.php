@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Summary Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stats-card 
            title="Total Items"
            value="{{ number_format($totalItems) }}"
            color-class="bg-blue-100"
            text-color="text-blue-800"
            icon='<path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>'
        />

        <x-stats-card 
            title="Total Stock Value"
            value="${{ number_format($totalStockValue, 2) }}"
            color-class="bg-green-100"
            text-color="text-green-800"
            icon='<path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>'
        />

        <x-stats-card 
            title="Low Stock"
            value="{{ $lowStockItems->count() }}"
            color-class="bg-amber-100"
            text-color="text-amber-800"
            icon='<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>'
        />

        <x-stats-card 
            title="Expiring Soon"
            value="{{ $soonToExpireItems->count() }}"
            color-class="bg-red-100"
            text-color="text-red-800"
            icon='<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>'
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expiry Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Soon to Expire</h3>
                <a href="-demo-" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
            </div>
            
            @if($soonToExpireItems->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-400">No items expiring soon</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($soonToExpireItems as $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <p class="font-medium text-gray-700">{{ $item->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->category->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-600">
                                {{ Carbon\Carbon::parse($item->expiry_date)->format('M d') }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $item->stocks->sum('current_quantity') }} {{ $item->unit_of_measurement }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Transactions Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
                <a href="transactions-demo" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
            </div>

            @if($recentTransactions->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-400">No recent transactions</p>
                </div>
            @else
                <div class="flow-root">
                    <div class="-my-3 divide-y divide-gray-100">
                        @foreach($recentTransactions as $transaction)
                        <div class="grid grid-cols-5 items-center py-3 hover:bg-gray-50 px-2 rounded-lg">
                            <div class="col-span-3">
                                <p class="font-medium text-gray-700">{{ $transaction->item->name }}</p>
                                <p class="text-sm text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-center">
                                <x-badge type="{{ $transaction->isIncomingTransaction() ? 'success' : 'danger' }}" size="sm">
                                    {{ $transaction->getFormattedTypeAttribute() }}
                                </x-badge>
                            </div>
                            <div class="text-right font-medium {{ $transaction->isIncomingTransaction() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->isIncomingTransaction() ? '+' : '-' }}{{ $transaction->quantity }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Inventory Chart -->
    <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Value by Category</h3>
        <div class="chart-container" style="position: relative; height: 300px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        const categories = @json($inventoryValueByCategory->pluck('category_name'));
        const values = @json($inventoryValueByCategory->pluck('total_value'));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Inventory Value ($)',
                    data: values,
                    backgroundColor: '#3B82F6',
                    borderColor: '#2563EB',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { callback: value => '$' + value }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
@endsection