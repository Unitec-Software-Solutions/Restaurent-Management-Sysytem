import React, { useEffect, useState } from 'react';
import axios from 'axios';

const MenuForm = ({ menuCategoryId }) => {
  const [menuItems, setMenuItems] = useState([]);

  useEffect(() => {
    // Fetch menu items based on the selected category ID
    axios.get(`/api/menu_items/${menuCategoryId}`)
      .then(response => {
        setMenuItems(response.data);
      })
      .catch(error => {
        console.error('Error fetching menu items:', error);
      });
  }, [menuCategoryId]);

  return (
    <form>
      <label htmlFor="menu-item-select">Select Menu Item:</label>
      <select id="menu-item-select">
        {menuItems.map((item, index) => (
          <option key={index} value={item}>
            {item}
          </option>
        ))}
      </select>
    </form>
  );
};

export default MenuForm; 