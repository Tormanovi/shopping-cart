import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

const Header = ({ toggleCart, cartItems }) => {
  const location = useLocation();
  const navigate = useNavigate();

  // Calculate total items in cart
  const calculateTotalItems = () => {
    return cartItems.reduce((total, item) => total + item.quantity, 0);
  };

  const totalItems = calculateTotalItems(); // Total number of items in the cart

  // Determine the selected category based on the current location
  const getSelectedCategory = () => {
    if (location.pathname === '/') return 'all';
    if (location.pathname === '/clothes') return 'clothes';
    if (location.pathname === '/tech') return 'tech';
    return null; // No category selected
  };

  const selectedCategory = getSelectedCategory();

  // Handle navigation and set the selected category
  const handleNavigation = (category) => {
    if (category === 'all') navigate('/');
    else navigate(`/${category}`);
  };

  return (
    <header className="header">
      <h1 className="store-name">Scandiweb Store</h1>
      <nav>
        <ul className="nav-links">
          <li>
            <button
              className={`category-link ${selectedCategory === 'all' ? 'selected' : ''}`}
              onClick={() => handleNavigation('all')}
            >
              All
            </button>
          </li>
          <li>
            <button
              className={`category-link ${selectedCategory === 'clothes' ? 'selected' : ''}`}
              onClick={() => handleNavigation('clothes')}
            >
              Clothes
            </button>
          </li>
          <li>
            <button
              className={`category-link ${selectedCategory === 'tech' ? 'selected' : ''}`}
              onClick={() => handleNavigation('tech')}
            >
              Tech
            </button>
          </li>
        </ul>
      </nav>

      {/* Cart Button with Total Items */}
      <button className="cart-btn" onClick={toggleCart}>
        🛒 {totalItems > 0 && <span className="cart-count">{totalItems}</span>}
      </button>
    </header>
  );
};

export default Header;
