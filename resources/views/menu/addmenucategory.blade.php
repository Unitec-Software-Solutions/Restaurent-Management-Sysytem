<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Category</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-section label {
            font-weight: 600;
            color: #495057;
        }
        .item-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: 8px;
            background-color: #fff;
        }
        .form-check {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .form-check:last-child {
            border-bottom: none;
        }
        .form-check-input {
            margin-top: 0.3rem;
        }
        .form-check-label {
            margin-left: 0.5rem;
            color: #212529;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
        }
        h1 {
            color: #343a40;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Add Menu Category</h1>

    <form action="/menuadd/menu/category" method="POST">
        @csrf

        <!-- Menu Items Section -->
        <div class="form-section">
            <label>Select Menu Items:</label>
            <div class="item-list">
                @foreach($menuItems as $item)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="menu_items[]" value="{{ $item->id }}" id="item_{{ $item->id }}">
                        <label class="form-check-label" for="item_{{ $item->id }}">
                            {{ $item->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Time Slots Section -->
        <div class="form-section">
            <label>Time Slots Availability:</label>
            <div class="item-list">
                @foreach($timeSlots as $slot)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="time_slots[]" value="{{ $slot->id }}" id="slot_{{ $slot->id }}">
                        <label class="form-check-label" for="slot_{{ $slot->id }}">
                            {{ $slot->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Add Category</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
