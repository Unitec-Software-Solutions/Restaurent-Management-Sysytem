<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RM SYSTEMS</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.3.0/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            "50": "#eff6ff",
                            "100": "#dbeafe",
                            "200": "#bfdbfe",
                            "300": "#93c5fd",
                            "400": "#60a5fa",
                            "500": "#3b82f6",
                            "600": "#2563eb",
                            "700": "#1d4ed8",
                            "800": "#1e40af",
                            "900": "#1e3a8a"
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    @auth
    @include('partials.sidebar')
    @endauth

    <!-- Navbar -->
    <nav class="fixed top-0 z-30 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <button id="toggleSidebar" aria-expanded="true" aria-controls="sidebar" class="p-2 text-gray-600 rounded cursor-pointer lg:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="flex ml-2 md:mr-24">
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">Admin Dashboard</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="hidden mr-3 -mb-1 sm:block">
                        <span></span>
                    </div>

                    <!-- Notifications dropdown -->
                    <div class="flex items-center ml-3">


                        <!-- Profile dropdown -->
                    <!-- Settings Dropdown -->
                    {{-- <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <a href="{{ route('admin.logout.page') }}" class="text-gray-500 hover:text-gray-700">Logout</a>
                        </div>
                    </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
     
    <div class="p-4 lg:ml-64">
        <div class="p-4 mt-14">

            <!-- Main Content Area -->
            <div class="">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.3.0/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('lg:flex');
            document.getElementById('sidebar').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>

</html>