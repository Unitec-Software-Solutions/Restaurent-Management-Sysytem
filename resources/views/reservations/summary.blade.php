@extends('layouts.app')

@section('title', 'Reservation Summary')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Reservation Summary</h1>
            <p class="text-gray-600">Review your reservation details and choose your next step</p>
        </div>

        <!-- Reservation Details Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-calendar-check mr-3"></i>
                    Reservation Details
                </h2>
            </div>
            
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Customer Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            Customer Information
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-user text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Name</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-phone text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Phone</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->phone }}</p>
                                </div>
                            </div>
                            
                            @if($reservation->email)
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Email</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->email }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Reservation Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            Reservation Information
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Date</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->date->format('F d, Y') }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-clock text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Time</span>
                                    <p class="font-medium text-gray-900">
                                        {{ $reservation->start_time->format('h:i A') }} - {{ $reservation->end_time->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Number of People</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->number_of_people }} guests</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-store text-gray-400 w-5 mr-3"></i>
                                <div>
                                    <span class="text-sm text-gray-500">Branch</span>
                                    <p class="font-medium text-gray-900">{{ $reservation->branch->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                @if($reservation->comments)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Special Instructions</h3>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $reservation->comments }}</p>
                </div>
                @endif

                <!-- Reservation Fee -->
                @if($reservation->reservation_fee > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900">Reservation Fee</h3>
                                <p class="text-sm text-blue-700">A reservation fee is required to confirm your booking</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-blue-900">LKR {{ number_format($reservation->reservation_fee, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Selection -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-question-circle mr-3"></i>
                    What would you like to do next?
                </h2>
            </div>
            
            <div class="p-6">
                <form action="{{ route('reservations.confirm', $reservation) }}" method="POST">
                    @csrf
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Make Order Option -->
                        <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-blue-500 transition-colors cursor-pointer order-option" 
                             onclick="selectOption('make_order')">
                            <input type="radio" name="action" value="make_order" id="make_order" class="hidden">
                            <label for="make_order" class="cursor-pointer block">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-utensils text-2xl text-blue-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Make an Order</h3>
                                    <p class="text-gray-600 mb-4">Browse our menu and place your order now. You can pre-order your meals for a seamless dining experience.</p>
                                    
                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <p class="text-sm text-blue-800">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Pre-order your favorite dishes
                                        </p>
                                        <p class="text-sm text-blue-800">
                                            <i class="fas fa-clock mr-2"></i>
                                            Faster service when you arrive
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Payment Only Option -->
                        <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-green-500 transition-colors cursor-pointer order-option" 
                             onclick="selectOption('payment_only')">
                            <input type="radio" name="action" value="payment_only" id="payment_only" class="hidden">
                            <label for="payment_only" class="cursor-pointer block">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-credit-card text-2xl text-green-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Payment Only</h3>
                                    <p class="text-gray-600 mb-4">Just pay the reservation fee now and order when you arrive at the restaurant.</p>
                                    
                                    <div class="bg-green-50 rounded-lg p-3">
                                        <p class="text-sm text-green-800">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Quick reservation confirmation
                                        </p>
                                        <p class="text-sm text-green-800">
                                            <i class="fas fa-menu mr-2"></i>
                                            Order when you arrive
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 text-center">
                        <button type="submit" id="confirmBtn" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold text-lg transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" 
                                disabled>
                            <i class="fas fa-arrow-right mr-2"></i>
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="{{ route('reservations.index') }}" 
               class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Reservations
            </a>
        </div>
    </div>
</div>

<script>
function selectOption(value) {
    // Remove selected class from all options
    document.querySelectorAll('.order-option').forEach(option => {
        option.classList.remove('border-blue-500', 'border-green-500', 'ring-2', 'ring-blue-200', 'ring-green-200');
        option.classList.add('border-gray-200');
    });
    
    // Clear all radio buttons
    document.querySelectorAll('input[name="action"]').forEach(radio => {
        radio.checked = false;
    });
    
    // Select the clicked option
    const selectedOption = document.querySelector(`input[value="${value}"]`);
    const selectedContainer = selectedOption.closest('.order-option');
    
    selectedOption.checked = true;
    selectedContainer.classList.remove('border-gray-200');
    
    if (value === 'make_order') {
        selectedContainer.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
    } else {
        selectedContainer.classList.add('border-green-500', 'ring-2', 'ring-green-200');
    }
    
    // Enable confirm button
    document.getElementById('confirmBtn').disabled = false;
}

// Handle form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const selectedAction = document.querySelector('input[name="action"]:checked');
    if (!selectedAction) {
        e.preventDefault();
        alert('Please select an option to continue.');
        return;
    }
    
    // Show loading state
    const btn = document.getElementById('confirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
});
</script>
@endsection
                        <button type="submit" id="confirmBtn" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold text-lg transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" 
                                disabled>
                            <i class="fas fa-arrow-right mr-2"></i>
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="{{ route('reservations.index') }}" 
               class="text-gray-600 hover:text-gray-800 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Reservations
            </a>
        </div>
    </div>

            @if($reservation->special_requests)
            <div class="mt-5 pt-4 border-t border-blue-100">
                <p class="text-sm font-medium text-blue-600 flex items-center">
                    <i class="fas fa-edit mr-2"></i> Special Requests
                </p>
                <p class="font-semibold text-gray-800 mt-1">
                    {{ $reservation->special_requests }}
                </p>
            </div>
            @endif
        </div>

        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded mb-8 flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3 text-xl"></i>
            <div>
                Your reservation is confirmed. Please arrive 10 minutes before your scheduled time.
            </div>
        </div>

        


        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/customer-dashboard" class="btn-primary text-white font-semibold py-3 px-6 rounded-lg flex-1 sm:flex-none text-center">
                <i class="fas fa-home mr-2"></i> Return to Dashboard
            </a>
            <a href="{{ route('reservations.payment', $reservation) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <i class="fas fa-credit-card mr-2"></i> Payment
            </a>
            <a href="{{ route('orders.create', ['reservation' => $reservation->id]) }}"
               class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <i class="fas fa-utensils mr-2"></i> Place Order for This Reservation
            </a>
            <button id="printBtn" class="bg-white border border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 hover:bg-gray-50 hover:border-blue-300 hover:text-blue-600">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>

        <div class="flex justify-end mt-4">
            <button id="cancelBtn" class="text-red-600 hover:text-red-800 font-medium flex items-center transition-colors duration-200">
                <i class="fas fa-times-circle mr-2"></i> Cancel Reservation
            </button>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Confirm Cancellation</h3>
            <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mb-6">
            <p class="text-gray-700 mb-4">Are you sure you want to cancel this reservation?</p>
            <p class="text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-2"></i> This action cannot be undone.</p>
        </div>
        <div class="flex justify-end gap-3">
            <button id="cancelNo" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">No, Keep It</button>
            <form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                @csrf
                <button id="cancelYes" type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Yes, Cancel</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const printBtn = document.getElementById('printBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const cancelModal = document.getElementById('cancelModal');
        const closeModal = document.getElementById('closeModal');
        const cancelNo = document.getElementById('cancelNo');

        if (printBtn) {
            printBtn.addEventListener('click', function () {
                const printBtn = this;
                printBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Loading...';
                setTimeout(() => {
                    window.print();
                    printBtn.innerHTML = '<i class="fas fa-print mr-2"></i> Print';
                }, 800);
            });
        }

        if (cancelBtn && cancelModal && closeModal && cancelNo) {
            cancelBtn.addEventListener('click', () => cancelModal.classList.remove('hidden'));
            closeModal.addEventListener('click', () => cancelModal.classList.add('hidden'));
            cancelNo.addEventListener('click', () => cancelModal.classList.add('hidden'));
        }
    });
</script>
@endpush
@endsection

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleCheck {
        0% {
            transform: scale(0);
            opacity: 0;
        }

        70% {
            transform: scale(1.15);
            opacity: 1;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .confirmation-card {
        animation: fadeIn 0.5s ease-out forwards;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .btn-primary {
        background-image: linear-gradient(to right, #3b82f6, #6366f1);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
    }

    .bg-green-500 svg {
        animation: scaleCheck 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
    }

    /* Custom animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.7s ease-out forwards;
    }

    .animate-pulse {
        animation: pulse 2s infinite;
    }
</style>
