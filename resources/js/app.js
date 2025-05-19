import './bootstrap';
const { Client } = require('pg');

// Create a connection to the PostgreSQL database
const client = new Client({
  user: 'test_user', // Replace with your PostgreSQL username
  host: '192.168.8.136',
  database: 'test_db',
  password: 'root', // Replace with your PostgreSQL password
  port: 5432, // Default PostgreSQL port
});

// Connect to the database
await client.connect();

// Fetch the "name" column from the "inventory_categories" table
const res = await client.query('SELECT name FROM inventory_categories');
const categoryNames = res.rows.map(row => row.name);

// Close the connection
await client.end();

// Display the data on the /menu page under the topic "Our Menu"
const menuSection = document.createElement('div');
menuSection.innerHTML = `
  <h2>Our Menu</h2>
  <ul>
    ${categoryNames.map(category => `<li>${category}</li>`).join('')}
  </ul>
`;

document.body.appendChild(menuSection);
