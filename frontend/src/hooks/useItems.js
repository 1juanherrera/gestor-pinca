import { useApiResource } from "../connection/getApi";

export const useItems = () => {
  return useApiResource('/items');
}