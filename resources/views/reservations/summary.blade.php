@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Reservation Details</h2>
        <span class="badge bg-{{ $reservation->status === 'confirmed' ? 'success' : ($reservation->status === 'pending' ? 'warning' : 'danger') }}">
            {{ ucfirst($reservation->status) }}
        </span>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Reservation #{{ $reservation->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted">Branch</h6>
                        <p>{{ $reservation->branch->name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Date & Time</h6>
                        <p>
                            {{ $reservation->date->format('l, F j, Y') }}<br>
                            {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - 
                            {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted">Guests</h6>
                        <p>{{ $reservation->number_of_people }} person(s)</p>
                    </div>
                    
                    @if($reservation->special_requests)
                    <div class="mb-3">
                        <h6 class="text-muted">Special Requests</h6>
                        <p>{{ $reservation->special_requests }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($reservation->status === 'pending')
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Reservation Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('reservations.payment', $reservation) }}" 
                   class="btn btn-primary px-4">
                    <i class="bi bi-credit-card me-2"></i>Make Payment
                </a>
                
                <a href="{{ route('orders.takeaway.create', ['reservation_id' => $reservation->id]) }}" 
                   class="btn btn-success px-4">
                    <i class="bi bi-cart-plus me-2"></i>Place Order
                </a>
                
                <form method="POST" action="{{ route('reservations.confirm', $reservation) }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-check-circle me-2"></i>Confirm Reservation
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <div class="d-flex justify-content-end">
        <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-outline-danger px-4" 
                    onclick="return confirm('Are you sure you want to cancel this reservation?')">
                <i class="bi bi-x-circle me-2"></i>Cancel Reservation
            </button>
        </form>
    </div>
    
    @if($reservation->status === 'confirmed')
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        Your reservation is confirmed. Please arrive 10 minutes before your scheduled time.
    </div>
    @endif
</div>

<style>
    .card {
        border-radius: 10px;
        border: none;
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    h6.text-muted {
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
</style>
@endsection