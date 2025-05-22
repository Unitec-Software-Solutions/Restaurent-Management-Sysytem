<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Preload Fonts -->
    <link rel="preload" href="https://fonts.bunny.net/css?family=Nunito" as="style">
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/css/app.css'])

    <!-- Flowbite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />

    <!-- Scripts (deferred for performance) -->
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div id="app">
        <div class="antialiased">
            <!-- Navbar -->
            <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="px-3 py-3 lg:px-5 lg:pl-3 flex justify-between items-center">
                    <!-- Left Side -->
                    <div class="flex items-center">
                        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button"
                            class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                            <span class="sr-only">Open sidebar</span>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" />
                            </svg>
                        </button>
                        <a href="{{ url('/') }}" class="flex ml-2 md:mr-24">
                            <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-black">
                                {{ config('app.name', 'Laravel') }}
                            </span>
                        </a>
                    </div>

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
                @include('partials.sidebar.customer-sidebar')
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
</body>
</html>
