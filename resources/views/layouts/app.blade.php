<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0e6fa71.js" crossorigin="anonymous"></script>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            .p-6,
            .px-6,
            .py-3,
            .py-4 {
                padding: 4px !important;
            }

            h1,
            h3 {
                font-size: 16px !important;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                background: white;
                font-size: 11px;
                line-height: 1.3;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
            }

            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .grn-table th,
            .grn-table td {
                padding: 6px !important;
            }
        }

        .status-badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }

        .status-pending {
            @apply bg-yellow-100 text-yellow-800;
        }

        .status-verified {
            @apply bg-green-100 text-green-800;
        }

        .status-rejected {
            @apply bg-red-100 text-red-800;
        }

        .status-default {
            @apply bg-gray-100 text-gray-800;
        }

    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class=" no-print nav-gradient shadow-sm border-b border-[#515DEF]/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <span class="text-2xl font-bold text-[#515DEF]">ORG</span>
                        <span class="ml-2 text-gray-600">Name</span>
                    </a>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <!-- 🔘 Test Page Button -->
                        <a href="{{ route('admin.testpage') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                            🧪 Test Page
                        </a>
                        <a href="{{ route('admin.login') }}"
                            class="px-4 py-2 text-gray-700 hover:text-[#515DEF] transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In (admin login - test )
                        </a>
                        <a href="{{ route('reservations.create') }}"
                            class="ml-4 px-4 py-2 bg-[#515DEF]/10 text-[#515DEF] rounded-md hover:bg-[#515DEF]/20 transition-colors font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex flex-1">
            @include('partials.sidebar')

            <!-- Main Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>

        <!-- Footer Section - Only shown if yielded -->
        @hasSection('show-footer')
            @include('partials.footer.welcome-footer')
        @endif

    </div>
    @livewireScripts
    @stack('scripts')
</body>

</html>
