@extends('layouts.guest')

@section('title', 'Select Restaurant Branch')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <h1 class="text-2xl font-bold text-gray-900">Select Your Branch</h1>
                <p class="text-gray-600 mt-1">Choose a location to view the menu for {{ $date }}</p>
            </div>
        </div>
    </div>

    <!-- Branch Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($branches as $branch)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <!-- Branch Image Placeholder -->
                    <div class="bg-indigo-100 h-48 flex items-center justify-center">
                        <i class="fas fa-utensils text-indigo-600 text-4xl"></i>
                    </div>
                    
                    <!-- Branch Info -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $branch->name }}</h3>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                {{ $branch->address }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                {{ $branch->phone }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($branch->opening_time)->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($branch->closing_time)->format('g:i A') }}
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        @php
                            $now = now();
                            $currentTime = $now->format('H:i');
                            $isOpen = $currentTime >= $branch->opening_time && $currentTime <= $branch->closing_time;
                        @endphp
                        
                        <div class="mb-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $isOpen ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $isOpen ? 'Open' : 'Closed' }}
                            </span>
                        </div>
                        
                        <!-- View Menu Button -->
                        <a href="{{ route('guest.menu.view', ['branchId' => $branch->id, 'date' => $date]) }}" 
                           class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                            <i class="fas fa-utensils mr-2"></i>
                            View Menu
                        </a>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 text-5xl mb-4">
                        <i class="fas fa-store-slash"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No Branches Available</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        We're sorry, but there are no restaurant branches available at the moment.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add any branch-specific JavaScript here
document.addEventListener('DOMContentLoaded', function() {
    // Example: Track branch selection
    const branchLinks = document.querySelectorAll('a[href*="guest.menu.view"]');
    branchLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Analytics or tracking code
            console.log('Branch selected:', this.href);
        });
    });
});
</script>
@endpush
