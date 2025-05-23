@extends('layouts.auth')

@section('title', 'RM SYSTEMS - Admin Login')

@section('content')
    <x-login-card 
        title="Admin Login" 
        subtitle="Login to access admin panel"
        formAction="{{ route('admin.login') }}"
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
        
        <!-- Additional admin-specific content can go here -->
    </x-login-card>
@endsection