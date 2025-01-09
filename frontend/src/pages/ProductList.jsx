import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const ProductList = ({ addToCart, selectedCategory }) => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const response = await api.get(); // Fetch products from API
        setProducts(response.data || []); // Set the products array
        setLoading(false);
      } catch (err) {
        console.error('Error fetching products:', err);
        setError('Failed to load products.');
        setLoading(false);
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

  if (loading) return <p>Loading products...</p>;
  if (error) return <p>{error}</p>;

  return (
    <div className="product-list">
      {filteredProducts.length > 0 ? (
        filteredProducts.map((product) => (
          <div key={product.id} className="product-card">
            {/* Product Image */}
            {product.gallery && product.gallery.length > 0 ? (
              <img
                src={product.gallery[0]} // Display the first image in the gallery
                alt={product.name || 'No name available'}
                className="product-image"
              />
            ) : (
              <p>No image available</p>
            )}

            {/* Product Details */}
            <h3>{product.name || 'No name available'}</h3>
            <p>
              {product.currency_symbol || '$'}
              {parseFloat(product.price).toFixed(2) || '0.00'}
            </p>

            {/* Action Buttons */}
            <div className="product-actions">
              <button
                className="view-details-btn"
                onClick={() => viewProductDetails(product.id)} // Navigate to product details page
              >
                View Details
              </button>
              <button
                className="add-to-cart-btn"
                onClick={() =>
                  addToCart({
                    ...product,
                    selectedAttributes: {
                      size: product.attributes?.find((attr) => attr.name === 'Size')?.value || '',
                      color: product.attributes?.find((attr) => attr.name === 'Color')?.value || '',
                    },
                  })
                }
              >
                Add to Cart
              </button>
            </div>
          </div>
        ))
      ) : (
        <p>No products found.</p>
      )}
    </div>
  );
};

export default ProductList;
