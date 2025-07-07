<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Restaurant Management') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])


    <!-- Additional Styles -->
    @stack('styles')

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
    <div class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <i class="fas fa-utensils text-indigo-600 text-2xl mr-3"></i>
                            <span class="text-xl font-bold text-gray-900">{{ config('app.name', 'Restaurant') }}</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="{{ route('guest.menu.view') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-utensils mr-2"></i>Menu
                        </a>
                        <a href="{{ route('guest.reservations.create') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Table
                        </a>
                        <a href="{{ route('orders.takeaway.create') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-shopping-bag mr-2"></i>Takeaway
                        </a>
                        <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-user mr-2"></i>My Orders
                        </a>
                    </div>

                    <!-- Auth Links -->
                    <div class="flex items-center space-x-4">
                        @guest
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>Staff Login
                            </a>
                        @else
                            <div class="relative">
                                <button onclick="toggleDropdown('user-menu')" class="flex items-center text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-user-circle mr-2"></i>{{ Auth::user()->name }}
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ route('admin.logout.action') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endguest

                        <!-- Cart Icon -->
                        <div class="relative">
                            <button onclick="toggleCart()" class="text-gray-600 hover:text-indigo-600 p-2 rounded-lg transition-colors">
                                <i class="fas fa-shopping-cart text-lg"></i>
                                <span id="cart-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                            </button>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-gray-600 hover:text-indigo-600">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div id="mobile-menu" class="hidden md:hidden pb-3 pt-2">
                    <div class="space-y-1">
                        <a href="{{ route('guest.menu.view') }}" class="block px-3 py-2 text-gray-600 hover:text-indigo-600 rounded-lg text-sm font-medium">
                            <i class="fas fa-utensils mr-2"></i>Menu
                        </a>
                        <a href="{{ route('guest.reservations.create') }}" class="block px-3 py-2 text-gray-600 hover:text-indigo-600 rounded-lg text-sm font-medium">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Table
                        </a>
                        <a href="{{ route('orders.takeaway.create') }}" class="block px-3 py-2 text-gray-600 hover:text-indigo-600 rounded-lg text-sm font-medium">
                            <i class="fas fa-shopping-bag mr-2"></i>Takeaway
                        </a>
                        <a href="{{ route('customer.dashboard') }}" class="block px-3 py-2 text-gray-600 hover:text-indigo-600 rounded-lg text-sm font-medium">
                            <i class="fas fa-user mr-2"></i>My Orders
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Session Messages -->
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-green-400 hover:text-green-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-yellow-400 hover:text-yellow-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-utensils text-indigo-400 text-2xl mr-3"></i>
                            <span class="text-xl font-bold">{{ config('app.name', 'Restaurant') }}</span>
                        </div>
                        <p class="text-gray-300 mb-4">
                            Experience the finest dining with our carefully crafted menu and exceptional service.
                            Fresh ingredients, skilled chefs, and a passion for culinary excellence.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('guest.menu.view') }}" class="text-gray-300 hover:text-indigo-400 transition-colors">Menu</a></li>
                            <li><a href="{{ route('guest.reservations.create') }}" class="text-gray-300 hover:text-indigo-400 transition-colors">Reservations</a></li>
                            <li><a href="{{ route('orders.takeaway.create') }}" class="text-gray-300 hover:text-indigo-400 transition-colors">Takeaway Orders</a></li>
                            <li><a href="{{ route('customer.dashboard') }}" class="text-gray-300 hover:text-indigo-400 transition-colors">Order Status</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-indigo-400"></i>
                                <span>123 Restaurant St, Food City</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone mr-2 text-indigo-400"></i>
                                <span>+1 (555) 123-4567</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-2 text-indigo-400"></i>
                                <span>info@restaurant.com</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-clock mr-2 text-indigo-400"></i>
                                <span>Mon-Sun: 11AM - 11PM</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Restaurant') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-80 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Your Cart</h2>
                <button onclick="toggleCart()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div id="cart-items">
                    <!-- Cart items will be populated by JavaScript -->
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                        <p>Your cart is empty</p>
                    </div>
                </div>
            </div>
            <div class="border-t p-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">Total:</span>
                    <span id="cart-total" class="text-lg font-bold text-indigo-600">$0.00</span>
                </div>
                <button onclick="proceedToCheckout()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div id="cart-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40" onclick="toggleCart()"></div>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // User dropdown toggle
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#' + id) && !event.target.closest('button')) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Cart functionality
        function toggleCart() {
            const sidebar = document.getElementById('cart-sidebar');
            const overlay = document.getElementById('cart-overlay');

            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Cart management
        let cart = JSON.parse(localStorage.getItem('restaurant_cart') || '[]');

        function updateCartUI() {
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');

            if (cart.length === 0) {
                cartCount.classList.add('hidden');
                cartItems.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                        <p>Your cart is empty</p>
                    </div>
                `;
                cartTotal.textContent = '$0.00';
            } else {
                cartCount.classList.remove('hidden');
                cartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);

                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                cartTotal.textContent = '$' + total.toFixed(2);

                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center justify-between py-3 border-b">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${item.name}</h4>
                            <p class="text-sm text-gray-500">$${item.price.toFixed(2)} each</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateCartItem(${item.id}, ${item.quantity - 1})" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="w-8 text-center">${item.quantity}</span>
                            <button onclick="updateCartItem(${item.id}, ${item.quantity + 1})" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button onclick="removeFromCart(${item.id})" class="ml-2 text-red-400 hover:text-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        }

        function addToCart(id, name, price) {
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            localStorage.setItem('restaurant_cart', JSON.stringify(cart));
            updateCartUI();
        }

        function updateCartItem(id, quantity) {
            if (quantity <= 0) {
                removeFromCart(id);
                return;
            }

            const item = cart.find(item => item.id === id);
            if (item) {
                item.quantity = quantity;
                localStorage.setItem('restaurant_cart', JSON.stringify(cart));
                updateCartUI();
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            localStorage.setItem('restaurant_cart', JSON.stringify(cart));
            updateCartUI();
        }

        function proceedToCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            // Redirect to checkout or show checkout modal
            window.location.href = '{{ route("orders.create") }}';
        }

        // Initialize cart UI on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartUI();

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"], [class*="bg-yellow-50"]');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            });
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
