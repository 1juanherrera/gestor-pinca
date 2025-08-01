import axios from 'axios';

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/gestorpinca/api';

const api = axios.create({
  baseURL: API_BASE_URL,
})

export async function fetchItems() {
  try {
    const response = await api.get('/items');
    return response.data;
  } catch (error) {
    throw new Error('Error al obtener los items');
  }
}