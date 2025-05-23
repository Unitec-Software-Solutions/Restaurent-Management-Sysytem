@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 animate-fade-in">
                Welcome to
                <span class="text-[#515DEF]">RMS</span>
            </h1>

            <p class="text-xl text-gray-700 mb-8 max-w-2xl mx-auto animate-fade-in">
                Streamline your restaurant operations with our comprehensive management solution.
                Manage orders, inventory, staff, and customers all in one place.
            </p>

            <div class="animate-fade-in flex flex-col md:flex-row gap-4 justify-center mt-8">
                <a href="{{ route('reservations.create') }}"
                    class="inline-flex items-center justify-center w-64 bg-[#515DEF] text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                    <i class="fas fa-calendar-alt mr-2"></i> Make a Reservation
                </a>
                <a href="{{ route('orders.create') }}"
                    class="inline-flex items-center justify-center w-64 bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl">
                    <i class="fas fa-utensils mr-2"></i> Place an Order
                </a>
            </div>

        </div>
    </div>
@endsection

@section('show-footer')
        @include('partials.footer.welcome-footer')
@endsection
