import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';

const ProductDetails = ({ addToCart }) => {
  const { id } = useParams(); // Get product ID from the URL
  const navigate = useNavigate();
  const [product, setProduct] = useState(null);
  const [selectedImage, setSelectedImage] = useState('');
  const [selectedSize, setSelectedSize] = useState(''); // State for selected size
  const [selectedColor, setSelectedColor] = useState(''); // State for selected color

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const response = await api.get();
        const productData = response.data.find((item) => item.id === id);
        setProduct(productData);
        setSelectedImage(productData?.gallery[0]); // Set the first image as default

        // Automatically select the first available size
        const defaultSize =
          productData?.attributes?.find((attr) => attr.name.toLowerCase() === 'size')?.value || '';
        setSelectedSize(defaultSize);

        // Automatically select the first available color
        const defaultColor =
          productData?.attributes?.find((attr) => attr.name.toLowerCase() === 'color')?.value || '';
        setSelectedColor(defaultColor);
      } catch (error) {
        console.error('Error fetching product details:', error);
      }
    };

    fetchProduct();
  }, [id]);

  if (!product) {
    return <p>Loading product details...</p>;
  }

  // Function to handle adding to cart
  const handleAddToCart = () => {
    addToCart({
      ...product,
      selectedAttributes: {
        size: selectedSize,
        color: selectedColor,
      },
    });
    navigate('/'); // Navigate back to the main page
  };

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
        <p>
          <strong>Price:</strong>{' '}
          {product.price ? `$${Number(product.price).toFixed(2)}` : 'Price not available'}
        </p>

        {/* Product Attributes */}
        {product.attributes && product.attributes.length > 0 && (
          <div className="product-attributes">
            {/* Size Buttons */}
            {product.attributes
              .filter((attr) => attr.name.toLowerCase() === 'size')
              .length > 0 && (
              <div className="size-selector">
                <strong>Select Size:</strong>
                <div>
                  {product.attributes
                    .filter((attr) => attr.name.toLowerCase() === 'size')
                    .map((attr, index) => (
                      <button
                        key={index}
                        className={`attribute-button ${
                          selectedSize === attr.value ? 'selected' : ''
                        }`}
                        onClick={() => setSelectedSize(attr.value)}
                      >
                        {attr.value}
                      </button>
                    ))}
                </div>
              </div>
            )}

            {/* Color Buttons */}
            {product.attributes
              .filter((attr) => attr.name.toLowerCase() === 'color')
              .length > 0 && (
              <div className="color-selector">
                <strong>Select Color:</strong>
                <div>
                  {product.attributes
                    .filter((attr) => attr.name.toLowerCase() === 'color')
                    .map((attr, index) => (
                      <button
                        key={index}
                        className={`attribute-button ${
                          selectedColor === attr.value ? 'selected' : ''
                        }`}
                        onClick={() => setSelectedColor(attr.value)}
                        style={{
                          backgroundColor: attr.value,
                          border:
                            selectedColor === attr.value ? '2px solid black' : '1px solid #ccc',
                        }}
                      />
                    ))}
                </div>
              </div>
            )}
          </div>
        )}

        {/* Add to Cart Button */}
        <button onClick={handleAddToCart}>Add to Cart</button>

        {/* Product Description */}
        <div
          className="product-description"
          dangerouslySetInnerHTML={{
            __html: product.description || 'No description available',
          }}
        />
      </div>
    </div>
  );
};

export default ProductDetails;
