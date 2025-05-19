document.addEventListener('DOMContentLoaded', function() {
    // Menu data
    const menuItems = [
        {
            id: 1,
            name: "Bruschetta",
            description: "Toasted bread topped with tomatoes, garlic, and fresh basil.",
            price: 8.99,
            category: "starters",
            image: "https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80"
        },
        {
            id: 2,
            name: "Caesar Salad",
            description: "Romaine lettuce, croutons, parmesan cheese with Caesar dressing.",
            price: 10.99,
            category: "starters",
            image: "https://images.unsplash.com/photo-1546793665-c74683f339c1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80"
        },
        {
            id: 3,
            name: "Grilled Salmon",
            description: "Fresh salmon fillet grilled to perfection with lemon butter sauce.",
            price: 22.99,
            category: "mains",
            image: "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
        },
        {
            id: 4,
            name: "Filet Mignon",
            description: "8oz tender beef filet with roasted vegetables and mashed potatoes.",
            price: 32.99,
            category: "mains",
            image: "https://images.unsplash.com/photo-1588168333986-5078d3ae3976?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80"
        },
        {
            id: 5,
            name: "Chocolate Lava Cake",
            description: "Warm chocolate cake with a molten center, served with vanilla ice cream.",
            price: 9.99,
            category: "desserts",
            image: "https://images.unsplash.com/photo-1564355808539-22fda35bed7e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1630&q=80"
        },
        {
            id: 6,
            name: "Tiramisu",
            description: "Classic Italian dessert with layers of coffee-soaked ladyfingers and mascarpone cream.",
            price: 8.99,
            category: "desserts",
            image: "https://images.unsplash.com/photo-1535920527002-b35e96722eb9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80"
        },
        {
            id: 7,
            name: "Craft Beer",
            description: "Selection of local craft beers. Ask your server for today's options.",
            price: 6.99,
            category: "drinks",
            image: "https://images.unsplash.com/photo-1535958636474-b021ee887b13?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
        },
        {
            id: 8,
            name: "House Wine",
            description: "Glass of our premium house red or white wine.",
            price: 9.99,
            category: "drinks",
            image: "https://images.unsplash.com/photo-1551024506-0bccd828d307?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1557&q=80"
        }
    ];

    // DOM elements
    const menuItemsContainer = document.querySelector('.menu-items');
    const categoryButtons = document.querySelectorAll('.category-btn');
    const searchInput = document.querySelector('.search-bar input');

    // Display all menu items
    function displayMenuItems(items) {
        menuItemsContainer.innerHTML = '';
        
        if (items.length === 0) {
            menuItemsContainer.innerHTML = '<p class="no-items">No items found. Please try a different search or category.</p>';
            return;
        }
        
        items.forEach(item => {
            const menuItemElement = document.createElement('div');
            menuItemElement.classList.add('menu-item');
            menuItemElement.setAttribute('data-category', item.category);
            
            menuItemElement.innerHTML = `
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="item-details">
                    <h3>${item.name}</h3>
                    <p>${item.description}</p>
                    <div class="item-price">
                        <span class="price">$${item.price.toFixed(2)}</span>
                        <button class="add-to-cart" data-id="${item.id}">Add to Cart</button>
                    </div>
                </div>
            `;
            
            menuItemsContainer.appendChild(menuItemElement);
        });
    }

    // Filter menu items by category
    function filterByCategory(category) {
        if (category === 'all') {
            displayMenuItems(menuItems);
            return;
        }
        
        const filteredItems = menuItems.filter(item => item.category === category);
        displayMenuItems(filteredItems);
    }

    // Search menu items
    function searchItems(searchTerm) {
        const filteredItems = menuItems.filter(item => 
            item.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
            item.description.toLowerCase().includes(searchTerm.toLowerCase())
        );
        displayMenuItems(filteredItems);
    }

    // Event listeners
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            // Filter items
            const category = button.getAttribute('data-category');
            filterByCategory(category);
        });
    });

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.trim();
        if (searchTerm === '') {
            const activeCategory = document.querySelector('.category-btn.active').getAttribute('data-category');
            filterByCategory(activeCategory);
        } else {
            searchItems(searchTerm);
        }
    });

    // Initialize the menu
    displayMenuItems(menuItems);

    // You can add interactivity here if needed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Menu loaded successfully');
    });
});