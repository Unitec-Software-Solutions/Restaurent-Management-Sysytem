<!DOCTYPE html>
<html>
<head>
    <title>Digital Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .category-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
            cursor: pointer;
            font-size: 1.5rem; /* Increased text size */
        }
        .category-item:hover {
            background-color: #f8f9fa;
        }
        .category-icon {
            margin-right: 20px;
            display: inline-block;
            transition: transform 0.3s ease-in-out;
            font-size: 2rem; /* Increased icon size */
        }
        
        /* Animation for All icon */
        .fa-list:hover {
            animation: spin 1s ease-in-out;
        }
        
        /* Animation for Coffee icon */
        .fa-coffee:hover {
            animation: bounce 0.5s ease-in-out;
        }
        
        /* Animation for Cheese icon */
        .fa-cheese:hover {
            animation: tilt 0.5s ease-in-out;
        }
        
        /* Animation for Snowflake icon */
        .fa-snowflake:hover {
            animation: spin 1s linear infinite;
        }
        
        /* Animation for Box icon */
        .fa-box-open:hover {
            animation: shake 0.5s ease-in-out;
        }

        /* Keyframes for animations */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes tilt {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Digital Menu</h1>
        
        <!-- Vertical Category List -->
        <div class="list-group">
            <div class="category-item" onclick="showCategory('all')">
                <i class="fas fa-list category-icon"></i>
                All
            </div>
            <div class="category-item" onclick="showCategory('beverages')">
                <i class="fas fa-coffee category-icon"></i>
                Beverages
            </div>
            <div class="category-item" onclick="showCategory('dairy')">
                <i class="fas fa-cheese category-icon"></i>
                Dairy Products
            </div>
            <div class="category-item" onclick="showCategory('frozen')">
                <i class="fas fa-snowflake category-icon"></i>
                Frozen Foods
            </div>
            <div class="category-item" onclick="showCategory('packaging')">
                <i class="fas fa-box-open category-icon"></i>
                Packaging
            </div>
        </div>
    </div>

    <script>
        // Category switching function
        function showCategory(category) {
            // Here you would handle the category selection
            console.log(`Selected category: ${category}`);
            // You can add your logic to filter or load content based on the category
        }
    </script>
</body>
</html> 