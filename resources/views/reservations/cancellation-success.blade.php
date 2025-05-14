@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Reservation Cancelled Successfully</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="mb-4">Your reservation has been cancelled successfully.</h5>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('reservations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Make New Reservation
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Go to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 