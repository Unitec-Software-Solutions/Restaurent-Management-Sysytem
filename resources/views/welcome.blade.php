<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS - Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
        }
    </style>
</head>
<body class="bg-white">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-bold text-blue-600">RMS</span>
                        <span class="ml-2 text-gray-600">Restaurant Management System</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <a href="{{ route('admin.login') }}" class="px-4 py-2 text-gray-600 hover:text-blue-600 transition-colors">
                            Sign In
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
                        <span class="text-blue-600">RMS</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto animate-fade-in">
                        Streamline your restaurant operations with our comprehensive management solution. 
                        Manage orders, inventory, staff, and customers all in one place.
                    </p>

                    <div class="animate-fade-in">
                        <a href="/register" 
                           class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg 
                                  text-lg font-medium hover:bg-blue-700 transition-colors
                                  shadow-lg hover:shadow-xl">
                            Get Started Now
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-600">
                    <p>© 2025 Unitec. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>