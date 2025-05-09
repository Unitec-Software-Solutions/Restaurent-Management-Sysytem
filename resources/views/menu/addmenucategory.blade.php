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
        }
        .item-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 1rem;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
        }
        .time-slot-heading {
            font-size: 1rem;
        }
        .time-slot-label {
            color: #000;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Add Menu Category</h1>

    <form action="{{ route('menu.storeCategory') }}" method="POST">
        @csrf

        <!-- Category Name Dropdown -->
        <div class="form-section">
            <label for="category_name" class="form-label">Category Name:</label>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle form-control" type="button" id="categoryDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Select Category
                </button>
                <div class="dropdown-menu" aria-labelledby="categoryDropdown">
                    @foreach($groupedItems as $category => $items)
                        <a class="dropdown-item" href="#" data-value="{{ $category }}" data-items="{{ json_encode($items) }}">{{ $category }}</a>
                    @endforeach
                </div>
            </div>
            <input type="hidden" id="category_name" name="category_name" required>
        </div>

        <!-- Filtered Items (Sheet) -->
        <div class="form-section">
            <div class="item-list" id="filteredItems">
                <!-- Items will be dynamically displayed here -->
            </div>
        </div>

        <!-- Menu Item Dropdown -->
        <div class="form-section">
            <label for="menuItem">Select Menu Item</label>
            <select name="menu_item_id" id="menuItem" class="form-control">
                <option value="">Select Menu Item</option>
                @foreach ($menuItems as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <!-- Food Item Dropdown -->
            <label for="foodItem" class="mt-3">Select Food Item</label>
            <select name="food_item_id" id="foodItem" class="form-control">
                <option value="">Select Food Item</option>
                @foreach ($foodItems as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Time Slots Availability -->
        <div class="form-section">
            <h2 class="time-slot-heading">Time Slots Availability</h2>
            <div class="row">
                @foreach($timeSlots as $slot)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="time_slot_{{ $slot->id }}" name="time_slots[]" value="{{ $slot->id }}">
                            <label class="form-check-label time-slot-label" for="time_slot_{{ $slot->id }}">{{ $slot->time }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Handle dropdown item selection
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const selectedCategory = this.getAttribute('data-value');
            const selectedText = this.textContent;

            // Update the dropdown button text
            document.getElementById('categoryDropdown').textContent = selectedText;

            // Set the hidden input value
            document.getElementById('category_name').value = selectedCategory;

            // Get the items for the selected category
            const items = JSON.parse(this.getAttribute('data-items'));

            // Clear the existing items
            const itemList = document.getElementById('filteredItems');
            itemList.innerHTML = '';

            // Display the filtered items as a sheet
            items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'form-check';
                itemDiv.innerHTML = `
                    <input type="checkbox" class="form-check-input" id="item_${item.id}" name="items[]" value="${item.id}">
                    <label class="form-check-label" for="item_${item.id}">${item.name} (${item.price})</label>
                `;
                itemList.appendChild(itemDiv);
            });
        });
    });
</script>
</body>
</html>
