@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 w-full max-w-2xl animate-fadeIn">


        <div class="flex justify-center my-6 relative">
            <div class="w-20 h-20 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center z-10 relative">
                <svg class="w-10 h-10 text-white absolute animate-scaleCheck" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-24 h-24 rounded-full bg-green-500/20 animate-pulse"></div>
        </div>

        <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 mb-2">Reservation Confirmed!</h2>
        <p class="text-gray-500 text-center mb-8">Your reservation has been successfully updated</p>

        <div class="bg-blue-50 rounded-lg p-5 mb-8 border border-blue-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-blue-600 flex items-center">
                        <i class="fas fa-store mr-2"></i> Branch
                    </p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $reservation->branch->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-600 flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Date & Time
                    </p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ \Carbon\Carbon::parse($reservation->date)->format('l, F j, Y') }}<br>
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-600 flex items-center">
                        <i class="fas fa-user-friends mr-2"></i> Guests
                    </p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $reservation->guest_count }} person(s)</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-600 flex items-center">
                        <i class="fas fa-hashtag mr-2"></i> Reservation ID
                    </p>
                    <p class="font-semibold text-gray-800 mt-1">#{{ $reservation->code }}</p>
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
            <button id="printBtn" class="bg-white border border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 hover:bg-gray-50 hover:border-blue-300 hover:text-blue-600">
                <i class="fas fa-print mr-2"></i> Print
            </button>

            


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
