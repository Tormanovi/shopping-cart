import axios from 'axios';

const API_BASE_URL = "http://tormanovi.com/scandiweb/backend/api.php";

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
