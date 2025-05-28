{{-- Not in use --}}
{{-- resources\views\partials\logout-modal.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Logout</h2>
            </div>
            <div class="p-6">
                <p class="mb-4">Are you sure you want to logout?</p>
                <form method="POST" action="{{ route('admin.logout.action') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Confirm Logout
                    </button>
                </form>
                <a href="{{ url()->previous() }}" class="block mt-4 text-center text-blue-500 hover:underline">Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection
