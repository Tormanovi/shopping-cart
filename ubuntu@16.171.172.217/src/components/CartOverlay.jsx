import React, { useEffect, useState } from 'react';

const CartOverlay = ({ cartItems, toggleCart, removeFromCart, addToCart, placeOrder }) => {
  const calculateTotal = () => {
    return cartItems.reduce(
      (total, item) => total + item.quantity * parseFloat(item.price || 0),
      0
    );
  };

  const calculateTotalItems = () => {
    return cartItems.reduce((total, item) => total + item.quantity, 0);
  };

  const [total, setTotal] = useState(calculateTotal());
  const [totalItems, setTotalItems] = useState(calculateTotalItems());

  useEffect(() => {
    setTotal(calculateTotal());
    setTotalItems(calculateTotalItems());
  }, [cartItems]);

  const handlePlaceOrder = async () => {
    try {
      const productIds = cartItems.map((item) => item.id);
      const total = cartItems.reduce(
        (sum, item) => sum + item.quantity * parseFloat(item.price),
        0
      );
      const currency = cartItems[0]?.currency_symbol || '$';

      const response = await api.post('/index.php', {
        productIds: productIds,
        total: total.toFixed(2),
        currency: currency,
      });

      console.log('Backend Response:', response.data);

      if (response.data.errors) {
        alert(`Error: ${response.data.errors[0].message}`);
        return;
      }

      alert('Order placed successfully!');
      setCartItems([]);
      setTotal(0);
      setTotalItems(0);
    } catch (error) {
      console.error('Error placing order:', error);
      alert('Failed to place order. Please try again.');
    }
  };

  return (
    <div className="cart-overlay">
      <button className="close-btn" onClick={toggleCart}>
        ✖ Close
      </button>
      <h2>My Cart</h2>

      {cartItems.length > 0 ? (
        <ul className="cart-items">
          {cartItems.map((item, index) => (
            <li key={`${item.id}-${index}`} className="cart-item">
              {item.gallery && item.gallery.length > 0 ? (
                <img
                  src={item.selectedImage || item.gallery[0]}
                  alt={item.name || 'No name available'}
                  className="cart-item-image"
                />
              ) : (
                <p>No image available</p>
              )}

              <div className="cart-item-details">
                <p><strong>{item.name || 'No name available'}</strong></p>
                <p>Price: ${parseFloat(item.price || 0).toFixed(2)}</p>
                <p>Quantity: {item.quantity}</p>

                {/* Colors */}
                {item.attributes?.filter(attr => attr.name.toLowerCase() === 'color').length > 0 && (
                  <div>
                    <strong>Colors:</strong>
                    <div style={{ display: 'flex', gap: '5px' }}>
                      {item.attributes
                        .filter(attr => attr.name.toLowerCase() === 'color')
                        .map((attr, index) => (
                          <div
                            key={index}
                            className={`color-box ${
                              item.selectedAttributes?.color === attr.value ? 'selected' : ''
                            }`}
                            style={{ backgroundColor: attr.value }}
                          >
                            {item.selectedAttributes?.color === attr.value && (
                              <span className="checkmark">✔</span>
                            )}
                          </div>
                        ))}
                    </div>
                  </div>
                )}

                {/* Sizes */}
                {item.attributes?.filter(attr => attr.name.toLowerCase() === 'size').length > 0 && (
                  <div>
                    <strong>Sizes:</strong>
                    <div style={{ display: 'flex', gap: '5px' }}>
                      {item.attributes
                        .filter(attr => attr.name.toLowerCase() === 'size')
                        .map((attr, index) => (
                          <span
                            key={index}
                            className={`size-label ${
                              item.selectedAttributes?.size === attr.value ? 'selected' : ''
                            }`}
                          >
                            {attr.value}
                          </span>
                        ))}
                    </div>
                  </div>
                )}
              </div>

              <div className="cart-item-actions">
                <button
                  className="increase-btn"
                  onClick={() => addToCart(item)}
                >
                  +
                </button>
                <button
                  className="decrease-btn"
                  onClick={() => removeFromCart(item.id, item.selectedAttributes)}
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

      <div className="cart-total">
        <h3>Total Items: {cartItems.length > 0 ? totalItems : 0}</h3>
        <h3>Total Price: {cartItems.length > 0 ? `$${total.toFixed(2)}` : '$0.00'}</h3>
      </div>

      {cartItems.length > 0 && (
        <button
          className="place-order-btn"
          onClick={handlePlaceOrder}
        >
          Place Order
        </button>
      )}
    </div>
  );
};

export default CartOverlay;
