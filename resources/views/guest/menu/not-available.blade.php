@extends('layouts.guest')

@section('title', 'Menu Not Available')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <!-- Icon -->
            <div class="text-yellow-400 text-6xl mb-6">
                <i class="fas fa-calendar-times"></i>
            </div>
            
            <!-- Message -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Menu Not Available</h1>
            <p class="text-gray-600 mb-6">
                We're sorry, but the menu for <strong>{{ $branch->name }}</strong> is not available for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}.
            </p>
            
            <!-- Suggestions -->
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">You can try:</h3>
                <ul class="text-sm text-gray-600 space-y-2 text-left">
                    <li class="flex items-center">
                        <i class="fas fa-calendar mr-2 text-indigo-600"></i>
                        Selecting a different date
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                        Choosing another branch location
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone mr-2 text-indigo-600"></i>
                        Calling us at {{ $branch->phone }}
                    </li>
                </ul>
            </div>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="{{ route('guest.menu.branch-selection', ['date' => $date]) }}" 
                   class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store mr-2"></i>
                    Choose Different Branch
                </a>
                
                <button onclick="showDatePicker()" 
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar mr-2"></i>
                    Select Different Date
                </button>
                
                <a href="tel:{{ $branch->phone }}" 
                   class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                    <i class="fas fa-phone mr-2"></i>
                    Call Restaurant
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Date Picker Modal -->
<div id="datePicker" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Select Date</h3>
            <button onclick="hideDatePicker()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('guest.menu.view', ['branchId' => $branch->id]) }}" method="GET">
            <div class="mb-4">
                <input type="date" 
                       name="date" 
                       value="{{ $date }}"
                       min="{{ now()->format('Y-m-d') }}"
                       max="{{ now()->addDays(30)->format('Y-m-d') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="hideDatePicker()" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    View Menu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDatePicker() {
    document.getElementById('datePicker').classList.remove('hidden');
}

function hideDatePicker() {
    document.getElementById('datePicker').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('datePicker').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDatePicker();
    }
});
</script>
@endpush
