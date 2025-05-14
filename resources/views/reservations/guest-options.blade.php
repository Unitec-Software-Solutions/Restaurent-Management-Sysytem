@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Continue as Guest</h2>
            </div>

            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    You can proceed with your reservation as a guest. Please note that you'll need to provide your contact information.
                </p>

                <form action="{{ route('reservations.guest') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $phone }}">
                    
                    <div class="mb-4">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Continue as Guest
                        </button>
                    </div>
                </form>

                <!-- <div class="mt-4 text-center">
                    <p class="text-gray-600">Already have an account?</p>
                    <form action="{{ route('reservations.user') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $phone }}">
                        <button type="submit" class="text-blue-500 hover:text-blue-700">
                            Login to your account
                        </button>
                    </form>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection 