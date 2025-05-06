@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">What would you like to do?</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Make a Reservation</h5>
                                    <p class="card-text">Book a table for your dining experience</p>
                                    <a href="{{ route('reservation.create') }}" class="btn btn-primary">
                                        Book a Table
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Place an Order</h5>
                                    <p class="card-text">Order food for delivery or pickup</p>
                                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                                        Start Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 