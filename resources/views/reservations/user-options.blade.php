@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Welcome Back!</h2>
            </div>

            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    We found an account associated with this phone number. Would you like to:
                </p>

                <div class="space-y-4">
                    <form action="{{ route('reservations.user') }}" method="POST">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $phone }}">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Login to your account
                        </button>
                    </form>

                    <form action="{{ route('reservations.guest') }}" method="POST">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $phone }}">
                        <button type="submit" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Continue as Guest
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 