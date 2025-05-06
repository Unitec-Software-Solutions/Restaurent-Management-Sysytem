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

                        <div class="mb-3">
                            <label for="reservation_time" class="form-label">Time</label>
                            <input type="time" 
                                   class="form-control @error('reservation_time') is-invalid @enderror" 
                                   id="reservation_time" 
                                   name="reservation_time" 
                                   value="{{ old('reservation_time') }}" 
                                   required>
                            @error('reservation_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
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
@endsection 