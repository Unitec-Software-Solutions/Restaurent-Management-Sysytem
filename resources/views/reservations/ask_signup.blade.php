@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('New Customer Registration') }}</div>

                <div class="card-body">
                    <p class="text-center mb-4">This phone number is not registered. Would you like to create an account?</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <form method="GET" action="{{ route('register') }}">
                            <input type="hidden" name="phone" value="{{ $phone }}">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Yes, Create Account') }}
                            </button>
                        </form>

                        <form method="GET" action="{{ route('reservation.create') }}">
                            <input type="hidden" name="phone" value="{{ $phone }}">
                            <button type="submit" class="btn btn-secondary">
                                {{ __('No, Continue as Guest') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 