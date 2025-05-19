<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Menu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .category-card {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .category-card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Menu Items</h1>
        
        @if($foodItems->count() > 0)
            <div class="row">
                @foreach($foodItems as $item)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                @if($item->img)
                                    <img src="{{ asset('storage/' . $item->img) }}" alt="{{ $item->name }}" class="mb-2" style="width: 100%; height: 200px; object-fit: cover;">
                                @else
                                    <div class="no-image" style="width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="font-size: 2rem; color: #ccc;"></i>
                                    </div>
                                @endif
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text">Price: {{ $item->price }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No items found in this category.</div>
        @endif
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 