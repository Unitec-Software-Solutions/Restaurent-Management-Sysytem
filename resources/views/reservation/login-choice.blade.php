@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Welcome Back!</div>

                <div class="card-body">
                    <p class="text-center mb-4">We found an account with the phone number: <strong>{{ $phone }}</strong></p>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            Login to Your Account
                        </a>
                        
                        <form method="POST" action="{{ route('reservations.proceed-as-guest') }}">
                            @csrf
                            <input type="hidden" name="phone" value="{{ $phone }}">
                            <button type="submit" class="btn btn-outline-secondary w-100">
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