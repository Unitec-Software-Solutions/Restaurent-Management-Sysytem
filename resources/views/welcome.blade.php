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

                    <div class="animate-fade-in flex flex-col md:flex-row gap-4 justify-center mt-8">
                        <a href="{{ route('reservations.create') }}"
                           class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                            Make a Reservation
                        </a>
                        <a href="{{ route('orders.create') }}"
                           class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl">
                            Place an Order
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