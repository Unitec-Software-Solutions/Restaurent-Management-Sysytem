@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-4 md:py-5">
            <div class="flex justify-between items-center">
                <a href="#" class="flex items-center text-2xl font-bold">
                    <i class="fas fa-calendar-check text-primary mr-2"></i>
                    <span class="text-slate-800">Reserve</span>
                </a>
                
                <nav class="hidden md:block">
                    <ul class="flex space-x-8">
                        <li><a href="#features" class="nav-link text-slate-700 hover:text-primary font-medium">Features</a></li>
                        <li><a href="#mission" class="nav-link text-slate-700 hover:text-primary font-medium">Mission</a></li>
                        <li><a href="#testimonials" class="nav-link text-slate-700 hover:text-primary font-medium">Testimonials</a></li>
                        <li><a href="#" class="ml-4 px-6 py-2 border-2 border-primary text-primary font-semibold rounded-full hover:bg-primary hover:text-white transition">Sign In</a></li>
                    </ul>
                </nav>
                
                <button class="md:hidden text-slate-700 focus:outline-none" id="menuToggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>


    <!-- Mobile Menu -->
    <div class="md:hidden fixed inset-0 bg-white z-40 pt-20 px-4 hidden" id="mobileMenu">
        <div class="flex flex-col space-y-6 py-6">
            <a href="#features" class="text-lg font-medium text-slate-700 hover:text-primary">Features</a>
            <a href="#mission" class="text-lg font-medium text-slate-700 hover:text-primary">Mission</a>
            <a href="#testimonials" class="text-lg font-medium text-slate-700 hover:text-primary">Testimonials</a>
            <a href="#" class="mt-4 px-6 py-3 border-2 border-primary text-primary font-semibold rounded-full hover:bg-primary hover:text-white text-center">Sign In</a>
        </div>
    </div>

    <!-- Main Content Section -->
    <section class="hero-bg pt-32 pb-20 md:pt-40 md:pb-28 relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-12 lg:mb-0 lg:pr-12 animate-fade-in">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        <span class="text-slate-800">Welcome to </span> 
                        <span class="text-primary">Reserve</span>
                        <span class="text-slate-800 block mt-2">Restaurant Management System</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-600 mb-8 max-w-xl">
                        Streamline your restaurant operations with our comprehensive management solution. 
                        Manage orders, reservations, staff, and customers all in one place.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('reservations.create') }}" 
                           class="px-8 py-4 btn-gradient font-semibold rounded-full shadow-lg text-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Make a Reservation
                        </a>
                        <a href="{{ route('orders.takeaway.create') }}" 
                           class="px-8 py-4 bg-green-600 text-white font-semibold rounded-full shadow-lg text-center hover:bg-green-700 transition">
                            <i class="fas fa-utensils mr-2"></i> Place an Order
                        </a>
                    </div>
                </div>
                
                <div class="lg:w-1/2 relative">
                    <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl floating">
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Restaurant booking system" 
                             class="w-full h-auto">
                    </div>
                    <div class="absolute -bottom-8 -left-8 w-64 h-64 bg-indigo-100 rounded-full opacity-50 z-0"></div>
                    <div class="absolute -top-8 -right-8 w-64 h-64 bg-purple-100 rounded-full opacity-50 z-0"></div>
                </div>
            </div>
    </div>
    </section>


    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking a link
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('shadow-md');
                header.classList.remove('shadow-sm');
            } else {
                header.classList.remove('shadow-md');
                header.classList.add('shadow-sm');
            }
        });
        
        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.animate-fade-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>

@endsection

@section('show-footer')
    @include('partials.footer.welcome-footer')
@endsection
