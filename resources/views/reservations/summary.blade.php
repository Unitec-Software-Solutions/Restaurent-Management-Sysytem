@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reservation Summary</h2>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Reservation #{{ $reservation->id }}</h5>
            <p>Branch: {{ $reservation->branch->name }}</p>
            <p>Date: {{ $reservation->date->format('M d, Y') }}</p>
            <p>Time: {{ $reservation->start_time }} - {{ $reservation->end_time }}</p>
            <p>Guests: {{ $reservation->number_of_people }}</p>
            
            @if($reservation->status === 'pending')
                <a href="{{ route('reservations.payment', $reservation) }}" 
                   class="btn btn-primary">
                    Make Payment
                </a>
                
                <a href="{{ route('orders.takeaway.create', ['reservation' => $reservation]) }}" 
                   class="btn btn-success">
                    Place Order
                </a>
            @endif
            
            <form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                @csrf
                <button type="submit" class="btn btn-danger">Cancel Reservation</button>
            </form>
        </div>
    </div>
</div>
@endsection