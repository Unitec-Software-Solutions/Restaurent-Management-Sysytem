@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h1 class="text-2xl font-bold text-gray-800">Review Your Reservation</h1>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Name</p>
                                <p class="font-medium">{{ $request->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium">{{ $request->email ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="font-medium">{{ $request->phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Reservation Details</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Branch</p>
                                <p class="font-medium">{{ $branch->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Date</p>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($request->date)->format('F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Time</p>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Number of People</p>
                                <p class="font-medium">{{ $request->number_of_people }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($request->comments)
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Special Requests</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-800">{{ $request->comments }}</p>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('reservations.store') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="name" value="{{ $request->name }}">
                    <input type="hidden" name="email" value="{{ $request->email }}">
                    <input type="hidden" name="phone" value="{{ $request->phone }}">
                    <input type="hidden" name="branch_id" value="{{ $request->branch_id }}">
                    <input type="hidden" name="date" value="{{ $request->date }}">
                    <input type="hidden" name="start_time" value="{{ $request->start_time }}">
                    <input type="hidden" name="end_time" value="{{ $request->end_time }}">
                    <input type="hidden" name="number_of_people" value="{{ $request->number_of_people }}">
                    <input type="hidden" name="comments" value="{{ $request->comments }}">

                    <div class="flex justify-between items-center">
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                            Confirm Reservation
                        </button>
                        <a href="{{ route('reservations.edit') }}" class="text-gray-600 hover:text-gray-800">
                            Edit Details
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 