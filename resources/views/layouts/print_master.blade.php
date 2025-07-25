<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            html, body {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                background: white;
                font-size: 11px;
                line-height: 1.4;
                color: #000;
            }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; border: none !important; margin: 0; padding: 0; width: 100%; height: auto; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { padding: 4px 6px !important; font-size: 10px !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-sm print-container">
        @yield('content')
    </div>

    <script>
        // Auto-print when the page loads
        window.addEventListener('load', function() {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
