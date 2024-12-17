import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const ProductList = ({ addToCart, selectedCategory }) => {
  const [products, setProducts] = useState([]);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const response = await api.get();
        console.log('API Response:', response.data); // Debug to ensure data is fetched
        setProducts(response.data.products || []);
      } catch (error) {
        console.error('Error fetching products:', error);
      }
    };

    fetchProducts();
  }, []);

  const filteredProducts =
    selectedCategory === 'all'
      ? products
      : products.filter((product) => product.category === selectedCategory);

  const viewProductDetails = (productId) => {
    navigate(`/product/${productId}`);
  };

  return (
    <div className="product-list">
      {filteredProducts.length > 0 ? (
        filteredProducts.map((product) => (
          <div key={product.id} className="product-card">
            {/* Product Image */}
            {product.gallery && product.gallery.length > 0 ? (
              <img
                src={product.gallery[0]} // First image in the gallery array
                alt={product.name || 'No name available'}
                className="product-image"
              />
            ) : (
              <p>No image available</p>
            )}

            {/* Product Details */}
            <h3>{product.name || 'No name available'}</h3>

            {/* Product Price */}
            <p>
              {product.price
                ? `$${parseFloat(product.price).toFixed(2)}`
                : 'Price not available'}
            </p>

            {/* Action Buttons */}
            <div className="product-actions">
              <button
                className="view-details-btn"
                onClick={() => viewProductDetails(product.id)}
              >
                View Details
              </button>
              <button
  className="add-to-cart-btn"
  onClick={() => {
    // Find the first size and color attributes
    const firstSize = product.attributes?.find(attr => attr.name.toLowerCase() === 'size')?.value || '';
    const firstColor = product.attributes?.find(attr => attr.name.toLowerCase() === 'color')?.value || '';

    // Add the product to the cart with selected attributes
    addToCart({
      ...product,
      selectedAttributes: {
        size: firstSize,
        color: firstColor,
      },
    });
  }}
>
  Add to Cart
</button>

            </div>
          </div>
        ))
      ) : (
        <p>Products Loading...</p>
      )}
    </div>
  );
};

export default ProductList;
