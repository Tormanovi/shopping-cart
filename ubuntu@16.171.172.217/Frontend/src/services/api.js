import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/frontend/index.php'; // Replace with your backend URL.

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
