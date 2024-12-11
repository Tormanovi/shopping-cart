import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';

const ProductDetails = ({ addToCart }) => {
  const { id } = useParams(); // Get product ID from the URL
  const navigate = useNavigate();
  const [product, setProduct] = useState(null);
  const [selectedImage, setSelectedImage] = useState('');

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        console.log('Fetching product data...');
        const response = await api.get();
        console.log('API Response:', response.data); // Log the entire response

        const productData = response.data.products.find(
          (item) => item.id === id
        );
        console.log('Fetched product data:', productData); // Log the matched product

        setProduct(productData);
        setSelectedImage(productData?.gallery[0]); // Set the first image as default
      } catch (error) {
        console.error('Error fetching product details:', error);
      }
    };

    fetchProduct();
  }, [id]);

  if (!product) {
    console.log('Product not found or still loading...');
    return <p>Loading product details...</p>;
  }

  // Debugging price handling
  console.log('Product Details:', product); // Log the entire product object
  console.log('Product Price:', product.price); // Log the price specifically

  return (
    <div className="product-details">
      <div className="product-images">
        <div className="image-thumbnails">
          {product.gallery.map((img, index) => (
            <img
              key={index}
              src={img}
              alt={`Thumbnail ${index}`}
              onClick={() => setSelectedImage(img)}
              className={selectedImage === img ? 'selected' : ''}
            />
          ))}
        </div>
        <div className="selected-image">
          <img src={selectedImage} alt="Selected product" />
        </div>
      </div>
      <div className="product-info">
  <h1>{product.name || 'No name available'}</h1>

  {/* Simplified Price Rendering */}
  <p>
    <strong>Price:</strong>{' '}
    {product.price ? `$${Number(product.price).toFixed(2)}` : 'Price not available'}
  </p>

  {/* Add to Cart Button */}
  <button
    onClick={() => {
      console.log('Adding product to cart:', product); // Debug the product being added
      addToCart(product);
      navigate('/'); // Navigate back to the main page
    }}
  >
    Add to Cart
  </button>

  <div
    className="product-description"
    dangerouslySetInnerHTML={{ __html: product.description || 'No description available' }}
  />
</div>
    </div>
  );
};

export default ProductDetails;
