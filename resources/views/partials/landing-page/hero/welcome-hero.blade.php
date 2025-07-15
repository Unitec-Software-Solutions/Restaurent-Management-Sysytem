<section class="hero-bg pt-24 pb-16 md:pt-32 md:pb-20 relative overflow-hidden" style="padding-top: 0%; padding-bottom: 0%;">
    <div class="container mx-auto px-4">
        <div class="flex flex-col-reverse lg:flex-row items-center gap-12 md:pt-24 md:pb-24">
            <!-- Left: Text & Actions -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center animate-fade-in">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight mb-6">
                    <span class="text-slate-800">Welcome to </span>
                    <span class="text-primary">Unitec</span>
                    <span class="text-slate-800 block mt-2">Restaurant Management System</span>
                </h1>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('reservations.create') }}"
                       class="px-6 py-3 btn-gradient font-semibold rounded-full shadow-lg text-center text-sm md:text-base flex items-center justify-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Make a Reservation
                    </a>
                    <a href="{{ route('orders.takeaway.create') }}"
                       class="px-6 py-3 bg-green-600 text-white font-semibold rounded-full shadow-lg text-center hover:bg-green-700 transition text-sm md:text-base flex items-center justify-center">
                        <i class="fas fa-utensils mr-2"></i> Place an Order
                    </a>
                </div>
            </div>
            <!-- Right: Image -->
            <div class="w-full lg:w-1/2 relative flex justify-center items-center" style="max-height: 400px;">
                <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl floating">
                    <img src="{{ asset('images/welcome_image.jpeg') }}"
                        alt="Chef preparing a gourmet dish in a modern restaurant kitchen with smiling staff in the background, warm lighting, and a welcoming atmosphere. The environment feels lively and inviting. Text on the image reads Unitec Restaurant Management System."
                         alt="Restaurant booking system"
                         class="w-full h-auto object-cover">
                </div>
                <div class="absolute -bottom-8 -left-8 w-64 h-64 bg-indigo-100 rounded-full opacity-50 z-0"></div>
                <div class="absolute -top-8 -right-8 w-64 h-64 bg-purple-100 rounded-full opacity-50 z-0"></div>
            </div>
        </div>
    </div>
</section>
