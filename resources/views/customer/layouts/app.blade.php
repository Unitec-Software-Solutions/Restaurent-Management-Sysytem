<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Menu</title>
    <!-- Add your CSS and JS files here -->
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
</head>
<body>
    <div class="customer-wrapper">
        @yield('content')
    </div>
    <!-- Add your JS files here -->
    <script src="{{ asset('js/customer.js') }}"></script>
</body>
</html> 