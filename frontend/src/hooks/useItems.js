import { useApiResource } from "../Connection/getApi";

export const useItems = () => {
  return useApiResource('/items', 'items', 'Error al obtener los items');
}