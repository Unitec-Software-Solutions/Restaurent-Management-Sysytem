<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Restaurant Management System') }}</title>
    <meta name="description" content="Restaurant Management System">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.3.0/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

            <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: "#eff6ff",
                            100: "#dbeafe",
                            200: "#bfdbfe",
                            300: "#93c5fd",
                            400: "#60a5fa",
                            500: "#3b82f6",
                            600: "#2563eb",
                            700: "#1d4ed8",
                            800: "#1e40af",
                            900: "#1e3a8a"
                        }
                    }
                }
            }
        };
    </script>


</head>

<body class="bg-gray-50 dark:bg-gray-900 h-full">

    <!-- Sidebar -->
    @auth
        @include('partials.sidebar.admin-sidebar')
    @endauth

    <!-- Header -->
    @include('partials.header.admin-header')

    <!-- Main Content -->
    <main class="p-4 lg:ml-64 pt-20 h-full">
      
        <!-- Breadcrumbs -->
        @include('partials.breadcrumbs') 

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Logout Modal -->
    @include('partials.modals.logout-modal')

    <!-- Scripts -->
<script>
    // Sidebar toggle for mobile
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        
        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            
            // Update aria-expanded attribute for accessibility
            const isExpanded = sidebar.classList.contains('-translate-x-full') ? 'false' : 'true';
            toggleButton.setAttribute('aria-expanded', isExpanded);
        });
    });

    // Logout modal toggle
    function toggleLogoutModal() {
        const modal = document.getElementById('logoutModal');
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }
</script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.3.0/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')
    <div id="sidebarBackdrop" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden hidden"></div>
</body>

</html>
