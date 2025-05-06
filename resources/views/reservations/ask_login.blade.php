@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Welcome Back!</div>

                <div class="card-body">
                    <p>We found your account with phone number: {{ $phone_number }}</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            Login to Your Account
                        </a>
                        
                        <form action="{{ route('reservations.proceed-as-guest') }}" method="POST">
                            @csrf
                            <input type="hidden" name="phone_number" value="{{ $phone_number }}">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                Continue as Guest
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 