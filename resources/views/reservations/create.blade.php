@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Make a Reservation</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('reservation.store') }}">
                        @csrf

                        @if(!auth()->user()->is_registered)
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Your Name</label>
                            <input type="text" 
                                   class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   value="{{ old('customer_name') }}" 
                                   required>
                            @error('customer_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="reservation_date" class="form-label">Date</label>
                            <input type="date" 
                                   class="form-control @error('reservation_date') is-invalid @enderror" 
                                   id="reservation_date" 
                                   name="reservation_date" 
                                   value="{{ old('reservation_date') }}" 
                                   required>
                            @error('reservation_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" 
                                           name="start_time" 
                                           value="{{ old('start_time') }}" 
                                           min="09:00"
                                           max="21:00"
                                           required>
                                    @error('start_time')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" 
                                           name="end_time" 
                                           value="{{ old('end_time') }}" 
                                           min="10:00"
                                           max="22:00"
                                           required>
                                    @error('end_time')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="party_size" class="form-label">Party Size</label>
                            <input type="number" 
                                   class="form-control @error('party_size') is-invalid @enderror" 
                                   id="party_size" 
                                   name="party_size" 
                                   value="{{ old('party_size') }}" 
                                   min="1" 
                                   max="20" 
                                   required>
                            @error('party_size')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Special Requests</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" 
                                      id="special_requests" 
                                      name="special_requests" 
                                      rows="3">{{ old('special_requests') }}</textarea>
                            @error('special_requests')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Continue to Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const dateInput = document.getElementById('reservation_date');

    function updateTimeConstraints() {
        const startTime = new Date('1970-01-01T' + startTimeInput.value);
        const minEndTime = new Date(startTime.getTime() + 60 * 60 * 1000); // 1 hour minimum
        const maxEndTime = new Date(startTime.getTime() + 4 * 60 * 60 * 1000); // 4 hours maximum
        
        // Set min and max for end time
        endTimeInput.min = minEndTime.toTimeString().slice(0, 5);
        endTimeInput.max = maxEndTime.toTimeString().slice(0, 5);
        
        // If current end time is invalid, update it
        if (endTimeInput.value) {
            const endTime = new Date('1970-01-01T' + endTimeInput.value);
            if (endTime < minEndTime) {
                endTimeInput.value = endTimeInput.min;
            } else if (endTime > maxEndTime) {
                endTimeInput.value = endTimeInput.max;
            }
        }
    }

    function validateStartTime() {
        const startTime = new Date('1970-01-01T' + startTimeInput.value);
        const openingTime = new Date('1970-01-01T09:00');
        const closingTime = new Date('1970-01-01T21:00');

        if (startTime < openingTime || startTime > closingTime) {
            startTimeInput.setCustomValidity('Start time must be between 9:00 AM and 9:00 PM.');
        } else {
            startTimeInput.setCustomValidity('');
        }

        // For same-day reservations, check if time is in the past
        if (dateInput.value === new Date().toISOString().split('T')[0]) {
            const now = new Date();
            const selectedTime = new Date();
            selectedTime.setHours(startTime.getHours(), startTime.getMinutes());
            
            if (selectedTime < now) {
                startTimeInput.setCustomValidity('Start time cannot be in the past.');
            }
        }
    }

    function validateEndTime() {
        const startTime = new Date('1970-01-01T' + startTimeInput.value);
        const endTime = new Date('1970-01-01T' + endTimeInput.value);
        const closingTime = new Date('1970-01-01T22:00');

        if (endTime <= startTime) {
            endTimeInput.setCustomValidity('End time must be after start time.');
        } else if (endTime > closingTime) {
            endTimeInput.setCustomValidity('End time cannot be after 10:00 PM.');
        } else {
            const duration = (endTime - startTime) / (1000 * 60 * 60); // duration in hours
            if (duration < 1) {
                endTimeInput.setCustomValidity('Reservation must be at least 1 hour long.');
            } else if (duration > 4) {
                endTimeInput.setCustomValidity('Reservation cannot exceed 4 hours.');
            } else {
                endTimeInput.setCustomValidity('');
            }
        }
    }

    // Add event listeners
    startTimeInput.addEventListener('change', function() {
        validateStartTime();
        updateTimeConstraints();
        validateEndTime();
    });

    endTimeInput.addEventListener('change', validateEndTime);
    dateInput.addEventListener('change', validateStartTime);
});
</script>
@endpush

@endsection 