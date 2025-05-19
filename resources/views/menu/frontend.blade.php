<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Frontend</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .menu-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .category-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .category-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .category-icon {
            font-size: 2rem;
            margin-right: 0.5rem;
        }
        .food-item {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
            display: flex;
            justify-content: space-between;
        }
        .food-item:last-child {
            border-bottom: none;
        }
        .food-price {
            font-weight: 600;
            color: #28a745;
        }
    </style>
</head>
<body>
<div class="menu-container">
    <h1 class="text-center mb-4">Our Menu</h1>

    @foreach ($groupedMenuData as $categoryName => $foodItems)
        <div class="category-section">
            <div class="category-name">
                @if ($categoryName == 'Starters')
                    <span class="category-icon">🥗</span>
                @elseif ($categoryName == 'Mains')
                    <span class="category-icon">🍽️</span>
                @elseif ($categoryName == 'Desserts')
                    <span class="category-icon">🍰</span>
                @elseif ($categoryName == 'Drinks')
                    <span class="category-icon">🍹</span>
                @endif
                {{ $categoryName }}
            </div>
            @if ($foodItems->isEmpty() || $foodItems->first()->food_name === null)
                <div class="food-item">No items available.</div>
            @else
                <ul>
                    @foreach ($foodItems as $foodItem)
                        <li class="food-item">
                            <span>{{ $foodItem->food_name }}</span>
                            <span class="food-price">${{ number_format($foodItem->price, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 