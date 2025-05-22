<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS - Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0e6fa71.js" crossorigin="anonymous"></script>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
        }
        .nav-gradient {
            background: linear-gradient(135deg, #D9DCFF 0%, #ffffff 100%);
        }
        .footer-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #D9DCFF 100%);
        }
    </style>
</head>
<body class="bg-[#D9DCFF] text-gray-800">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="nav-gradient shadow-sm border-b border-[#515DEF]/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-bold text-[#515DEF]">RMS</span>
                        <span class="ml-2 text-gray-600">Restaurant Management System</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <a href="{{ route('admin.login') }}" class="px-4 py-2 text-gray-700 hover:text-[#515DEF] transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In (admin login - test )
                        </a>
                        <a href="{{ route('reservations.create') }}" class="ml-4 px-4 py-2 bg-[#515DEF]/10 text-[#515DEF] rounded-md hover:bg-[#515DEF]/20 transition-colors font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
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
        </main>

        <!-- Footer -->
        <footer class="footer-gradient border-t border-[#515DEF]/20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center md:text-left">
                        <h3 class="text-lg font-semibold text-[#515DEF] mb-4">About RMS</h3>
                        <p class="text-gray-700">A comprehensive solution for modern restaurant management, helping you streamline operations and enhance customer experience.</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-[#515DEF] mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">Home</a></li>
                             {{-- <li><a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">Features</a></li>
                             <li><a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">Pricing</a></li> --}}
                             
                        </ul>
                    </div>
                    <div class="text-center md:text-right">
                        <h3 class="text-lg font-semibold text-[#515DEF] mb-4">Connect With Us</h3>
                        <div class="flex justify-center md:justify-end space-x-4">
                            <a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">Contact</a>
                            <a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-700 hover:text-[#515DEF] transition-colors">
                                <i class="fab fa-linkedin-in text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-[#515DEF]/20 text-center text-gray-700">
                    <p>© 2025 Unitec. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>