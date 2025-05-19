<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    @if(request()->is('frontend'))
        <!-- Admin sidebar -->
        @include('partials.admin-sidebar')
    @endif

<<<<<<< HEAD
    <main class="@if(request()->is('frontend')) ml-64 @endif p-6 flex-1 overflow-y-auto">
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
=======
                    <!-- Right Side -->
                    <div class="flex items-center">
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('admin.login') }}"
                                   class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    Login
                                </a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 ml-2">
                                    Register
                                </a>
                            @endif
                        @else
                            <!-- Remove logout link and form since only admin login is used -->
                        @endguest
                    </div>
                </div>
            </nav>

            <!-- Sidebar -->
            @auth
                @include('partials.sidebar')
            @endauth

            <!-- Main Content -->
            <main class="p-4 @auth sm:ml-64 @endauth">
                <div class="mt-14">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
>>>>>>> 9b0d85d46c950a4c8ad21af3b600ca06fa755550
</body>
</html>