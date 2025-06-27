<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Restaurant Management System') }}</title>
    <meta name="description" content="Restaurant Management System">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Enhanced Sidebar Assets -->
    @vite(['resources/css/sidebar.css', 'resources/js/sidebar.js'])
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
    <style>
        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
        }

        .item-row:hover {
            background-color: #f1f5f9;
        }

        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>


</head>

<body class="bg-[#F3F4FF] dark:bg-gray-900 h-full">


    <!-- Enhanced Sidebar -->
    @auth
        <x-admin-sidebar />
    @endauth

    <!-- Header -->
    @include('partials.header.admin-header')

    <!-- Main Content -->
    <main class="lg:ml-64 transition-all duration-300">
        
        <!-- Mobile Header Spacer -->
        <div class="h-16 lg:hidden"></div>
        
        <!-- Content Container -->
        <div class="p-4 lg:p-6 bg-[#F3F4FF] min-h-screen">
            <!-- Breadcrumbs -->
            {{-- @include('partials.breadcrumbs') // Disabled for now --}}

            <!-- Page Content -->
            @yield('content')

            @if(session('subscription_alert'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        {{ session('subscription_alert') }}
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Logout Modal -->
    @include('partials.modals.logout-modal')

    <!-- Scripts -->
    <script>
        // Enhanced sidebar functionality will be handled by sidebar.js
        // Alpine.js store for sidebar state management
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
                
                toggle() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('sidebar_collapsed', this.collapsed);
                },
                
                collapse() {
                    this.collapsed = true;
                    localStorage.setItem('sidebar_collapsed', true);
                },
                
                expand() {
                    this.collapsed = false;
                    localStorage.setItem('sidebar_collapsed', false);
                }
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
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')
</body>

</html>
