@extends('layouts.guest')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Complete Registration</h1>
    
    <form method="POST" action="{{ route('complete-registration') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" id="name" name="name" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            Complete Registration
        </button>
    </form>
</div>
@endsection
