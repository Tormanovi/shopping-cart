import axios from 'axios';

// Dynamically set the backend URL based on the environment
const API_BASE_URL =
  process.env.NODE_ENV === 'production'
    ? 'https://your-deployed-backend-domain.com/index.php' // Replace with your deployed backend URL
    : 'http://localhost:8000/frontend/index.php'; // Local development URL

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
