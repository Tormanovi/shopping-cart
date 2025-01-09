import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Header from './components/Header';
import ProductList from './pages/ProductList';
import CartOverlay from './components/CartOverlay';
import ProductDetails from './pages/ProductDetails';

const App = () => {
  const [cartItems, setCartItems] = useState(() => {
    const savedCart = localStorage.getItem('cartItems');
    return savedCart ? JSON.parse(savedCart) : [];
  });
  const [isCartVisible, setIsCartVisible] = useState(false);

  useEffect(() => {
    try {
      localStorage.setItem('cartItems', JSON.stringify(cartItems));
    } catch (error) {
      console.error('Error saving cart to localStorage:', error);
    }
  }, [cartItems]);

  const toggleCart = () => {
    setIsCartVisible(!isCartVisible);
    const cartOverlayActive = document.body.classList.contains('cart-overlay-active');
    if (cartOverlayActive) {
      document.body.classList.remove('cart-overlay-active');
    } else {
      document.body.classList.add('cart-overlay-active');
    }
  };

  const addToCart = (item) => {
    const existingItemIndex = cartItems.findIndex(
      (cartItem) =>
        cartItem.id === item.id &&
        cartItem.selectedAttributes.size === item.selectedAttributes.size &&
        cartItem.selectedAttributes.color === item.selectedAttributes.color
    );

    if (existingItemIndex >= 0) {
      const updatedCart = [...cartItems];
      updatedCart[existingItemIndex].quantity += 1;
      setCartItems(updatedCart);
    } else {
      setCartItems([
        ...cartItems,
        {
          ...item,
          quantity: 1,
        },
      ]);
    }
  };

  const removeFromCart = (productId) => {
    setCartItems((prevCartItems) =>
      prevCartItems
        .map((item) =>
          item.id === productId ? { ...item, quantity: item.quantity - 1 } : item
        )
        .filter((item) => item.quantity > 0)
    );
  };

  const placeOrder = () => {
    alert('Order has been placed successfully!');
    setCartItems([]);
    toggleCart();
  };

  return (
    <Router basename="/scandiweb/frontend">
      <div className="page-overlay"></div>
      <div className="app">
        <Header toggleCart={toggleCart} cartItems={cartItems} />
        {isCartVisible && (
          <CartOverlay
            cartItems={cartItems}
            toggleCart={toggleCart}
            addToCart={addToCart}
            removeFromCart={removeFromCart}
            placeOrder={placeOrder}
          />
        )}
        <div className={`main-content ${isCartVisible ? 'dimmed' : ''}`}>
          <Routes>
            <Route path="/" element={<Navigate to="/all" replace />} />
            <Route path="/all" element={<ProductList addToCart={addToCart} selectedCategory="all" />} />
            <Route path="/clothes" element={<ProductList addToCart={addToCart} selectedCategory="clothes" />} />
            <Route path="/tech" element={<ProductList addToCart={addToCart} selectedCategory="tech" />} />
            <Route path="/product/:id" element={<ProductDetails addToCart={addToCart} />} />
            <Route path="*" element={<Navigate to="/all" replace />} />
          </Routes>
        </div>
      </div>
    </Router>
  );
};

export default App;
