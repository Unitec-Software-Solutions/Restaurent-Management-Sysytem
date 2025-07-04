{{-- filepath: resources/views/admin/kitchen/kots/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'KOT Management')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">KOT Management</h1>
                <p class="text-gray-600">Kitchen Order Tickets - Track and manage order preparation</p>
            </div>
            <div class="flex gap-3">
                <button onclick="refreshKOTs()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
                <button onclick="printAllKOTs()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print All
                </button>
                <a href="{{ route('admin.kitchen.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Kitchen
                </a>
            </div>
        </div>
    </div>

    <!-- KOT Status Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-wrap gap-3">
            <button onclick="filterKOTs('all')" class="px-4 py-2 rounded-lg border filter-btn active" data-status="all">
                All KOTs
            </button>
            <button onclick="filterKOTs('pending')" class="px-4 py-2 rounded-lg border filter-btn" data-status="pending">
                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                Pending
            </button>
            <button onclick="filterKOTs('preparing')" class="px-4 py-2 rounded-lg border filter-btn" data-status="preparing">
                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                Preparing
            </button>
            <button onclick="filterKOTs('ready')" class="px-4 py-2 rounded-lg border filter-btn" data-status="ready">
                <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                Ready
            </button>
            <button onclick="filterKOTs('completed')" class="px-4 py-2 rounded-lg border filter-btn" data-status="completed">
                <span class="inline-block w-2 h-2 bg-gray-500 rounded-full mr-2"></span>
                Completed
            </button>
        </div>
    </div>

    <!-- KOT Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="kot-grid">
        @forelse($kots ?? [] as $kot)
            <div class="kot-card bg-white rounded-lg shadow-sm border-l-4 
                {{ $kot->status === 'pending' ? 'border-yellow-500' : 
                   ($kot->status === 'preparing' ? 'border-blue-500' : 
                   ($kot->status === 'ready' ? 'border-green-500' : 'border-gray-500')) }}" 
                 data-status="{{ $kot->status }}">
                
                <!-- KOT Header -->
                <div class="p-4 border-b">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">KOT #{{ $kot->id }}</h3>
                            <p class="text-sm text-gray-600">Order #{{ $kot->order_id }}</p>
                            <p class="text-xs text-gray-500">{{ $kot->created_at->format('M d, Y H:i A') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $kot->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($kot->status === 'preparing' ? 'bg-blue-100 text-blue-800' : 
                                   ($kot->status === 'ready' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($kot->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="p-4 border-b bg-gray-50">
                    <div class="text-sm">
                        <p class="font-medium text-gray-900">{{ $kot->order->customer_name ?? 'Guest Customer' }}</p>
                        @if($kot->order->customer_phone)
                            <p class="text-gray-600">{{ $kot->order->customer_phone }}</p>
                        @endif
                        <p class="text-gray-600">{{ $kot->order->branch->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- KOT Items -->
                <div class="p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Items to Prepare:</h4>
                    <div class="space-y-2">
                        @forelse($kot->kotItems ?? [] as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-900">{{ $item->itemMaster->name ?? 'Item' }}</span>
                                <span class="font-medium text-gray-700">×{{ $item->quantity }}</span>
                            </div>
                            @if($item->special_instructions)
                                <p class="text-xs text-orange-600 italic">{{ $item->special_instructions }}</p>
                            @endif
                        @empty
                            <p class="text-gray-500 text-sm">No items found</p>
                        @endforelse
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex gap-2">
                        @if($kot->status === 'pending')
                            <button onclick="updateKOTStatus({{ $kot->id }}, 'preparing')" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                                Start Preparing
                            </button>
                        @elseif($kot->status === 'preparing')
                            <button onclick="updateKOTStatus({{ $kot->id }}, 'ready')" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                                Mark Ready
                            </button>
                        @elseif($kot->status === 'ready')
                            <button onclick="updateKOTStatus({{ $kot->id }}, 'completed')" 
                                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                                Complete
                            </button>
                        @endif
                        
                        <button onclick="printKOT({{ $kot->id }})" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No KOTs Found</h3>
                <p class="text-gray-500">Kitchen Order Tickets will appear here when orders are placed.</p>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
.filter-btn.active {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.kot-card {
    transition: transform 0.2s ease-in-out;
}

.kot-card:hover {
    transform: translateY(-2px);
}
</style>
@endpush

@push('scripts')
<script>
function filterKOTs(status) {
    const cards = document.querySelectorAll('.kot-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update button states
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.status === status) {
            btn.classList.add('active');
        }
    });
    
    // Filter cards
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function updateKOTStatus(kotId, status) {
    fetch(`/admin/kitchen/kots/${kotId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update KOT status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the KOT');
    });
}

function printKOT(kotId) {
    window.open(`/admin/kitchen/kots/${kotId}/print`, '_blank');
}

function printAllKOTs() {
    if (confirm('Print all pending KOTs?')) {
        window.open('/admin/kitchen/kots/print-all', '_blank');
    }
}

function refreshKOTs() {
    location.reload();
}

// Auto-refresh every 60 seconds
setInterval(refreshKOTs, 60000);
</script>
@endpush
@endsection