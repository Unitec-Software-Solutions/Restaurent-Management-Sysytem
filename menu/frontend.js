import React from 'react';
import { FaUtensils, FaHamburger, FaIceCream, FaGlassCheers } from 'react-icons/fa';
import './Frontend.css';

const Frontend = () => {
  const menuItems = {
    Starters: [
      { name: 'Garlic Bread', price: '$5.99' },
      { name: 'Bruschetta', price: '$6.99' },
    ],
    Mains: [
      { name: 'Grilled Salmon', price: '$15.99' },
      { name: 'Beef Steak', price: '$18.99' },
    ],
    Desserts: [
      { name: 'Chocolate Cake', price: '$7.99' },
      { name: 'Cheesecake', price: '$8.99' },
    ],
    Drinks: [
      { name: 'Mojito', price: '$9.99' },
      { name: 'Red Wine', price: '$12.99' },
    ],
  };

  return (
    <div className="frontend-container">
      <h1>Digital Menu</h1>
      <div className="menu-categories">
        {Object.entries(menuItems).map(([category, items]) => (
          <div key={category} className="category">
            <div className="category-header">
              {category === 'Starters' && <FaUtensils className="icon" />}
              {category === 'Mains' && <FaHamburger className="icon" />}
              {category === 'Desserts' && <FaIceCream className="icon" />}
              {category === 'Drinks' && <FaGlassCheers className="icon" />}
              <h2>{category}</h2>
            </div>
            <ul>
              {items.map((item, index) => (
                <li key={index}>
                  <span>{item.name}</span>
                  <span>{item.price}</span>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Frontend;