import axios from 'axios';
import { useMutation, useQuery } from '@tanstack/react-query';

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

const api = axios.create({
  baseURL: API_BASE_URL,
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export async function apiRequest({ endpoint, data = null, method }) {
  try {
    const response = await api.request({
      url: endpoint,
      method,
      data,
    });
    return response.data;
  } catch (error) {
    const serverMessage = error.response?.data?.message 
                          || error.response?.data?.error 
                          || error.message;

    const finalError = new Error(serverMessage);
    
    finalError.responseData = error.response?.data;
    
    throw finalError;
  }
}

export function useApiResource(endpoint, queryKey = endpoint, errorMsg, method = 'GET') {
  return useQuery({
    queryKey: [queryKey],
    queryFn: () => apiRequest({ method, endpoint, errorMsg }),
    enabled: !!endpoint,
  });
}

export function useApiMutation(endpoint, errorMsg) {
  return useMutation({
    mutationFn: (data) =>
      apiRequest({
        method: "POST",
        endpoint,
        data,
        errorMsg,
      }),
  });
}

export function useApiDelete(baseEndpoint, errorMsg) {
  return useMutation({
    mutationFn: (id) =>
      apiRequest({
        method: "DELETE",
        endpoint: `${baseEndpoint}/${id}`,
        errorMsg,
      }),
  });
}

export function useApiUpdate(baseEndpoint, errorMsg) {
  return useMutation({
    mutationFn: ({ id, data }) => {
      if (!data || Object.keys(data).length === 0) {
        console.warn("Intentando actualizar sin datos válidos:");
        console.table({ id, data });
        throw new Error("No se proporcionaron datos para actualizar.");
      }

      return apiRequest({
        method: "PUT",
        endpoint: `${baseEndpoint}/${id}`,
        data,
        errorMsg,
      });
    },
  });
}
