@extends('layouts.app')

@section('title', 'Create Organization')
@section('header', 'Create Organization')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Create Organization</h1>

    <form method="POST" action="{{ route('organizations.store') }}">
        @csrf

        <!-- Organization Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Organization Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('name')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('email')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Address Field -->
        <div class="mb-4">
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <textarea id="address" name="address" rows="3" required 
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('address') }}</textarea>
            @error('address')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone -->
        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('phone')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            Create Organization
        </button>
    </form>
</div>
@endsection
