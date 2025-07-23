{{-- filepath: resources/views/admin/kitchen/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kitchen Dashboard')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kitchen Dashboard</h1>
                <p class="text-gray-600">Monitor kitchen operations and order status</p>
            </div>
            <div class="flex gap-3">
                <button onclick="refreshKitchenData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
                <a href="{{ route('admin.kitchen.kots.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-receipt mr-2"></i> View KOTs
                </a>
            </div>
        </div>
    </div>

    <!-- Real-time Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pending Orders</h3>
                    <p class="text-2xl font-bold text-gray-900" id="pending-count">{{ $pendingOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-fire text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">In Preparation</h3>
                    <p class="text-2xl font-bold text-gray-900" id="preparing-count">{{ $preparingOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Ready to Serve</h3>
                    <p class="text-2xl font-bold text-gray-900" id="ready-count">{{ $readyOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-utensils text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Today</h3>
                    <p class="text-2xl font-bold text-gray-900" id="total-today">{{ $totalToday ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kitchen Stations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Active Kitchen Stations -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Kitchen Stations</h3>
                <a href="{{ route('admin.kitchen.stations.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm">
                    Manage Stations <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($kitchenStations ?? [] as $station)
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full {{ $station->is_active ? 'bg-green-100' : 'bg-gray-100' }}">
                                <i class="fas fa-industry {{ $station->is_active ? 'text-green-600' : 'text-gray-600' }}"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="font-medium text-gray-900">{{ $station->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $station->description }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $station->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $station->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-3">
                            <i class="fas fa-industry"></i>
                        </div>
                        <p class="text-gray-500">No kitchen stations found</p>
                        <a href="{{ route('admin.kitchen.stations.create') }}" class="text-indigo-600 hover:text-indigo-700 text-sm">
                            Create your first kitchen station
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent KOTs -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent KOTs</h3>
                <a href="{{ route('admin.kitchen.kots.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-3" id="recent-kots">
                @forelse($recentKots ?? [] as $kot)
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 rounded-full {{ $kot->status === 'pending' ? 'bg-yellow-100' : ($kot->status === 'preparing' ? 'bg-blue-100' : 'bg-green-100') }}">
                                <i class="fas fa-receipt {{ $kot->status === 'pending' ? 'text-yellow-600' : ($kot->status === 'preparing' ? 'text-blue-600' : 'text-green-600') }}"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="font-medium text-gray-900">KOT #{{ $kot->id }}</h4>
                                <p class="text-sm text-gray-500">{{ $kot->order->customer_name ?? 'Table Order' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $kot->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   ($kot->status === 'preparing' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($kot->status) }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ $kot->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-3">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <p class="text-gray-500">No recent KOTs</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.orders.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-plus text-indigo-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">New Order</p>
                    <p class="text-sm text-gray-500">Create order</p>
                </div>
            </a>

            {{--
            <a href="{{ route('admin.kitchen.queue.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Order Queue</p>
                    <p class="text-sm text-gray-500">View kitchen queue</p>
                </div>
            </a>
            --}}

            <button onclick="printAllKOTs()" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-print text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Print KOTs</p>
                    <p class="text-sm text-gray-500">Batch print</p>
                </div>
            </button>

            <a href="{{ route('admin.menus.manager') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-utensils text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Menu Manager</p>
                    <p class="text-sm text-gray-500">Manage menu items</p>
                </div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Real-time updates for kitchen dashboard
function refreshKitchenData() {
    fetch('{{ route("admin.kitchen.status") }}', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('pending-count').textContent = data.counts.pending;
            document.getElementById('preparing-count').textContent = data.counts.preparing;
            document.getElementById('ready-count').textContent = data.counts.ready;
            document.getElementById('total-today').textContent = data.counts.total_today;
        }
    })
    .catch(error => console.error('Error refreshing kitchen data:', error));
}

function printAllKOTs() {
    if (confirm('Print all pending KOTs?')) {
        window.open('{{ route("admin.kitchen.kots.print-all") }}', '_blank');
    }
}

// Auto-refresh every 30 seconds
setInterval(refreshKitchenData, 30000);
</script>
@endpush
@endsection
