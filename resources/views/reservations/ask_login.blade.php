@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Existing Customer') }}</div>

                <div class="card-body">
                    <p class="text-center mb-4">This phone number is already registered. Would you like to login?</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <form method="GET" action="{{ route('login') }}">
                            <input type="hidden" name="phone" value="{{ $phone }}">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Yes, Login') }}
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