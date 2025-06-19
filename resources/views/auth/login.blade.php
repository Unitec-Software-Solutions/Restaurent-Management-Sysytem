@extends('layouts.guest')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Login</h1>
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            Login
        </button>
    </form>
</div>
@endsection