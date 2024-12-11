import React, { useEffect, useState } from 'react';

const CartOverlay = ({ cartItems, toggleCart, removeFromCart, addToCart, placeOrder }) => {
  // Calculate the total sum of all items in the cart
  const calculateTotal = () => {
    return cartItems.reduce(
      (total, item) => total + item.quantity * parseFloat(item.price || 0),
      0
    );
  };

  // Calculate the total number of items in the cart
  const calculateTotalItems = () => {
    return cartItems.reduce((total, item) => total + item.quantity, 0);
  };

  const [total, setTotal] = useState(calculateTotal());
  const [totalItems, setTotalItems] = useState(calculateTotalItems());

  useEffect(() => {
    // Recalculate total and totalItems whenever cartItems change
    setTotal(calculateTotal());
    setTotalItems(calculateTotalItems());
  }, [cartItems]);

  return (
    <div className="cart-overlay">
      <button className="close-btn" onClick={toggleCart}>
        ✖ Close
      </button>
      <h2>My Cart</h2>

      {/* List of items in the cart */}
      {cartItems.length > 0 ? (
        <ul className="cart-items">
          {cartItems.map((item) => (
            <li key={item.id} className="cart-item">
              {/* Product Image */}
              {item.gallery && item.gallery.length > 0 ? (
                <img
                  src={item.gallery[0]}
                  alt={item.name || 'No name available'}
                  className="cart-item-image"
                />
              ) : (
                <p>No image available</p>
              )}

              {/* Product Details */}
              <div className="cart-item-details">
                <p>{item.name || 'No name available'}</p>
                <p>
                  {"$"}
                  {parseFloat(item.price || 0).toFixed(2)}
                </p>
                <p>Quantity: {item.quantity}</p>
              </div>

              {/* Quantity Management Buttons */}
              <div className="cart-item-actions">
                <button
                  className="increase-btn"
                  onClick={() => addToCart(item)}
                >
                  +
                </button>
                <button
                  className="decrease-btn"
                  onClick={() => removeFromCart(item.id)}
                >
                  -
                </button>
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <p>Your cart is empty.</p>
      )}

      {/* Display Total Items and Total Sum */}
      <div className="cart-total">
        <h3>
          Total Items: {cartItems.length > 0 ? totalItems : 0}
        </h3>
        <h3>
          Total Price: {cartItems.length > 0 ? `$${total.toFixed(2)}` : '$0.00'}
        </h3>
      </div>

      {/* Place Order Button */}
      {cartItems.length > 0 && (
        <button
          className="place-order-btn"
          onClick={() => {
            placeOrder();
            setTotal(0); // Reset total after placing the order
            setTotalItems(0); // Reset total items after placing the order
          }}
        >
          Place Order
        </button>
      )}
    </div>
  );
};

export default CartOverlay;
