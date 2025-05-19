<!DOCTYPE html>
<html>
<head>
    <title>Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .menu-item {
            margin-bottom: 20px;
        }
        .menu-icon {
            color: black;
            margin-right: 10px;
        }
        .filter-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Our Menu</h1>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name...">
                </div>
                <div class="col-md-3">
                    <select id="priceFilter" class="form-select">
                        <option value="">Filter by price</option>
                        <option value="0-10">$0 - $10</option>
                        <option value="10-20">$10 - $20</option>
                        <option value="20-50">$20 - $50</option>
                        <option value="50+">$50+</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="row" id="menuItemsContainer">
            @foreach($menuItems as $item)
                <div class="col-md-4 mb-4 menu-item-card" data-name="{{ strtolower($item->name) }}" data-price="{{ $item->price }}">
                    <div class="card menu-item">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-utensils menu-icon"></i>
                                {{ $item->name }}
                            </h5>
                            <p class="card-text">
                                <i class="fas fa-tag menu-icon"></i>
                                Price: ${{ number_format($item->price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function applyFilters() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const priceRange = document.getElementById('priceFilter').value;
            
            document.querySelectorAll('.menu-item-card').forEach(card => {
                const itemName = card.getAttribute('data-name');
                const itemPrice = parseFloat(card.getAttribute('data-price'));
                
                // Name filter
                const nameMatch = itemName.includes(searchValue);
                
                // Price filter
                let priceMatch = true;
                if (priceRange) {
                    const [min, max] = priceRange.split('-');
                    if (max === '+') {
                        priceMatch = itemPrice >= parseFloat(min);
                    } else {
                        priceMatch = itemPrice >= parseFloat(min) && itemPrice <= parseFloat(max);
                    }
                }
                
                // Show/hide based on filters
                card.style.display = (nameMatch && priceMatch) ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
