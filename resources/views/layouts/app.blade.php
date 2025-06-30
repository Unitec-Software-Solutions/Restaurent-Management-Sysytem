<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0e6fa71.js" crossorigin="anonymous"></script>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 1s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Minimal Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-[#515DEF]">Restaurant Name</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Simple Footer -->
    <footer class="bg-white py-4 border-t">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-600">
            &copy; {{ date('Y') }} Restaurant Name. All rights reserved.
        </div>
    </footer>
</body>
</html>