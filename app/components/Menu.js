import React from 'react';
import './Menu.css';

const Menu = ({ items }) => {
  return (
    <div className="menu">
      <h2>Our Menu</h2>
      <ul>
        {items.map((item, index) => (
          <li key={index}>
            <img src={item.image} alt={item.name} className="menu-item-image" />
            <h3>{item.name}</h3>
            <p>{item.description}</p>
            <p>Price: ${item.price}</p>
          </li>
        ))}
      </ul>
    </div>
  );
};

export default Menu; 