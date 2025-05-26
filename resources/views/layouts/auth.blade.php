<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RM SYSTEMS')</title>
    
    <!-- Tailwind CSS via CDN (consider installing locally for production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-indigo-100 flex items-center justify-center min-h-screen">
    @yield('content')
    
    @stack('scripts')
</body>
</html>