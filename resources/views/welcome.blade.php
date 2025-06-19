@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <main>
            <div class="text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-4 sm:mb-6 animate-fade-in leading-tight">
                    Welcome to
                    <span class="text-[#515DEF]">RMS</span>
                </h1>
                <p class="text-base sm:text-lg md:text-xl text-gray-700 mb-6 sm:mb-8 max-w-md sm:max-w-2xl mx-auto animate-fade-in">
                    Streamline your restaurant operations with our comprehensive management solution.
                    Manage orders, inventory, staff, and customers all in one place.
                </p>
                <div class="animate-fade-in flex flex-col gap-3 sm:gap-4 md:flex-row justify-center mt-6 sm:mt-8 w-full">
                    <a href="{{ route('reservations.create') }}"
                        class="inline-flex items-center justify-center w-full md:w-64 bg-[#515DEF] text-white px-6 py-3 sm:px-8 sm:py-3 rounded-lg text-base sm:text-lg font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <i class="fas fa-calendar-alt mr-2"></i> Make a Reservation
                    </a>
                    <a href="{{ route('orders.takeaway.create') }}"
                        class="inline-flex items-center justify-center w-full md:w-64 bg-green-600 text-white px-6 py-3 sm:px-8 sm:py-3 rounded-lg text-base sm:text-lg font-medium hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-400">
                        <i class="fas fa-utensils mr-2"></i> Place an Order
                    </a>
                </div>
            </div>
        </main>
    </div>
@endsection

@section('show-footer')
    @include('partials.footer.welcome-footer')
@endsection
