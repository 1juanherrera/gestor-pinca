import { useApiResource } from "../Connection/getApi";

export const useItems = () => {
  return useApiResource('/items');
}