@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 bg-gray-50">
    <div class="max-w-4xl mx-auto animate-fade-in">
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                <div class="flex items-center mb-3 sm:mb-0">
                    <div class="bg-blue-100 p-2 rounded-lg mr-4">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Review Your Reservation</h1>
                        <p class="text-sm text-gray-600">Please verify your details before confirming</p>
                    </div>
                </div>
                <span class="bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                    <i class="far fa-calendar-alt mr-1 text-blue-500"></i>
                    <span>{{ \Carbon\Carbon::parse($request->date)->format('d-m-Y') }}</span>
                </span>
            </div>

            <!-- Content -->
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Customer Information -->
                <div class="space-y-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-100 hover:shadow-sm transition-shadow duration-200">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                            Customer Information
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $request->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $request->email ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $request->phone }}</p>
                            </div>
                        </div>
                    </div>

                    @if($request->comments)
                    <div class="bg-white p-5 rounded-xl border border-gray-100 hover:shadow-sm transition-shadow duration-200">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-comment-dots text-blue-500 mr-2"></i>
                            Special Requests
                        </h2>
                        <p class="text-gray-700">{{ $request->comments }}</p>
                    </div>
                    @endif
                </div>

                <!-- Reservation Details -->
                <div class="space-y-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-100 hover:shadow-sm transition-shadow duration-200">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-store text-blue-500 mr-2"></i>
                            Reservation Details
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $branch->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date</p>
                                <p class="font-medium text-gray-800 mt-1">{{ \Carbon\Carbon::parse($request->date)->format('F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Time</p>
                                <p class="font-medium text-gray-800 mt-1">
                                    {{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Number of Guests</p>
                                <div class="mt-1">
                                    <span class="inline-block bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $request->number_of_people }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-5 rounded-xl border border-blue-100 hover:shadow-sm transition-shadow duration-200">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Important Information
                        </h2>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Please arrive 10 minutes before your reservation time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Table will be held for 15 minutes past reservation time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Call us if you're running late</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <form method="POST" action="{{ route('reservations.store') }}" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="name" value="{{ $request->name }}">
                    <input type="hidden" name="email" value="{{ $request->email }}">
                    <input type="hidden" name="phone" value="{{ $request->phone }}">
                    <input type="hidden" name="branch_id" value="{{ $request->branch_id }}">
                    <input type="hidden" name="date" value="{{ $request->date }}">
                    <input type="hidden" name="start_time" value="{{ $request->start_time }}">
                    <input type="hidden" name="end_time" value="{{ $request->end_time }}">
                    <input type="hidden" name="number_of_people" value="{{ $request->number_of_people }}">
                    <input type="hidden" name="comments" value="{{ $request->comments }}">
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center transition-all duration-300 hover:shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        Confirm Reservation
                    </button>
                </form>
                
                <a href="{{ route('reservations.create', $request->all()) }}" class="w-full sm:w-auto text-gray-600 hover:text-gray-800 font-medium py-2 px-4 rounded-lg border border-gray-300 bg-white flex items-center justify-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Reservation
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 animate-fade-in">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Reservation Confirmed!</h3>
            <p class="text-sm text-gray-500 mb-6">
                Your table has been reserved. A confirmation has been sent to your email.
            </p>
            <div class="bg-gray-50 p-4 rounded-lg mb-6 text-left">
                <p class="text-sm font-medium text-gray-700 mb-1">Reservation #: <span class="font-bold">R-{{ rand(100000, 999999) }}</span></p>
                <p class="text-sm text-gray-600">
                    {{ $request->name }}, {{ $request->number_of_people }} guests on 
                    {{ \Carbon\Carbon::parse($request->date)->format('M j') }} at 
                    {{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }}
                </p>
            </div>
            <a href="{{ route('home') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                Done
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmationModal = document.getElementById('confirmationModal');
        const form = document.querySelector('form');

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state on button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Show confirmation modal
                confirmationModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                // Actually submit the form after showing the modal
                setTimeout(() => {
                    form.submit();
                }, 2000);
            }, 1500);
        });

        // Close modal when clicking outside
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                confirmationModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });
    });
</script>
@endpush