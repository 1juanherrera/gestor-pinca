import axios from 'axios';
import { useMutation, useQuery } from '@tanstack/react-query';

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/gestorpinca/api';

const api = axios.create({
  baseURL: API_BASE_URL,
});

export async function apiRequest({ endpoint, data = null, errorMsg, method }) {
  try {
    const response = await api.request({
      url: endpoint,
      method,
      data,
    });
    return response.data;
  } catch (error) {
    throw new Error(errorMsg + ': ' + error.message);
  }
}

export function useApiResource(endpoint, queryKey = endpoint, errorMsg, method = 'get') {
  return useQuery({
    queryKey: [queryKey],
    queryFn: () => apiRequest({ method, endpoint, errorMsg }),
  });
}

export function useApiMutation(endpoint, errorMsg, method = 'post') {
  return useMutation({
    mutationFn: (data) =>
      apiRequest({
        method,
        endpoint,
        data,
        errorMsg,
      }),
  });
}