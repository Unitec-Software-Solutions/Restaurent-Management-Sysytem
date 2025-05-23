@extends('layouts.auth')

@section('title', 'RM SYSTEMS - User Login')

@section('content')
    <x-login-card 
        title="User Login" 
        subtitle="Login to access your account"
        formAction="{{ route('login') }}"
    >
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-login-card>
@endsection