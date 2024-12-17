import axios from 'axios';

// Dynamically set the backend URL based on the environment
// const API_BASE_URL = 'https://shopping-cart-vert-nine.vercel.app'
const API_BASE_URL = 'http://localhost:8000/frontend/index.php'
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
